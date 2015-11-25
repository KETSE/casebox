<?php
namespace CB\Solr;

use CB\Config;
use CB\DB;
use CB\Util;
use CB\DataModel as DM;

/**
 * Solr client class used by CaseBox to make changes into solr
 */

class Client extends Service
{
    /**
     * running cron for updating tree changes into solr
     */
    public static function runCron()
    {
        if (\CB\Config::getFlag('disableTriggers')
            || \CB\Config::getFlag('disableSolrIndexing')
        ) {
            return;
        }

        $solrClient = new \CB\Solr\Client();
        $solrClient->updateTree();
        unset($solrClient);
    }

    /**
     * running background cron for updating tree changes into solr
     */
    public static function runBackgroundCron()
    {
        $coreName = \CB\Config::get('core_name');

        if (\CB\Config::getFlag('disableTriggers')
            || \CB\Config::getFlag('disableSolrIndexing')
        ) {
            return;
        }

        $cmd = 'php -f "'.\CB\CRONS_DIR.'run_cron.php" -- -n solr_update_tree -c '.$coreName.' > '.\CB\LOGS_DIR.'bg_solr_update_tree.log &';
        if (\CB\IS_WINDOWS) {
            $cmd = 'start /D "'.\CB\CRONS_DIR.'" php -f "run_cron.php" -- -n solr_update_tree -c '.$coreName.' > '.\CB\LOGS_DIR.'bg_solr_update_tree.log';
        }

        pclose(popen($cmd, "r"));
    }

    /**
     * prepare a record with data from database to be indexed in solr
     * @param  array reference $r
     * @return void
     */
    private function prepareDBRecord(&$r)
    {
        /* set template data */
        if (!empty($r['template_id'])) {
            $template = \CB\Templates\SingletonCollection::getInstance()->getTemplate($r['template_id']);
            $r['template_type'] = $template->getData()['type'];
            $r['iconCls'] = $template->getData()['iconCls'];
        }

        /* consider node type sort column (ntsc) equal to 1 unit more
        than total count of folder templates */
        $r['ntsc'] = sizeof($this->folderTemplates) + 100;

        /* decrease ntsc (make 1 unit more important) in case of 'case' object types */
        if (@$r['template_type'] == 'case') {
            $r['ntsc']--;
        }

        /* if there is a folder template then set its ntsc
        equal to its index in folder_templates array */
        if (in_array($r['template_id'], $this->folderTemplates)) {
            $r['ntsc'] = 1;
        }

        /* make some trivial type checks */
        $r['ntsc'] = intval($r['ntsc']);
        $r['system'] = @intval($r['system']);

        if (empty($r['pids'])) {
            $r['pids'] = null;
            $r['path'] = null;
        } else {
            $r['pids'] = explode(',', $r['pids']);
            //exclude itself from pids
            array_pop($r['pids']);
            $r['path'] = implode('/', $r['pids']);
        }

        /* fill "ym" fields for date faceting by cdate, date, date_end */
        $ym1 = str_replace('-', '', substr($r['cdate'], 2, 5));
        $ym2 = str_replace('-', '', substr($r['date'], 2, 5));
        $ym3 = str_replace('-', '', substr($r['date_end'], 2, 5));

        if (empty($ym3)) {
            $ym3 = $ym2;
        }

        if (!empty($ym1)) {
            $r['ym1'] = $ym1;
        }

        if (!empty($ym2)) {
            $r['ym2'] = $ym2;
        }

        if (!empty($ym3)) {
            $r['ym3'] = $ym3;
        }

        $r['content'] = $r['name'] . "\n";

        if (!empty($r['sys_data']['solr'])) {
            foreach ($r['sys_data']['solr'] as $k => $v) {
                $r[$k] = $v;

                //add string values to content field
                if (is_string($v)) {
                    $r['content'] .= (in_array($k, array('date_start', 'date_end', 'dates'))
                        ? substr($v, 0, 10)
                        : $v
                    )."\n";
                }
            }

            //override content field if set in sys_data['solr']
            if (!empty($r['sys_data']['solr']['content'])) {
                $r['content'] = $r['sys_data']['solr']['content'];
            }
        }

        //encode special chars for string values
        foreach ($r as $k => $v) {
            if (is_string($v)) {
                $r[$k] = htmlspecialchars($v, ENT_COMPAT);
            }
        }

        //add last_action_tdt field
        $la = empty($r['udate'])
            ? $r['cdate']
            : $r['udate'];
        if (!empty($r['sys_data']['lastAction'])) {
            $la = $r['sys_data']['lastAction']['time'];
        } elseif (!empty($r['sys_data']['lastComment']) &&
            ($r['sys_data']['lastComment'] > $la)
        ) {
            $la = $r['sys_data']['lastComment']['date'];
        }
        $r['last_action_tdt'] = $la;

        $this->filterSolrFields($r);
    }

    /**
     * append file contents to content field for file records
     * @param  array &$records
     * @return void
     */
    protected function appendFileContents(&$records)
    {
        $fileRecords = array();

        foreach ($records as &$r) {
            if (!empty($r['template_type']) && ($r['template_type'] == 'file')) {
                $fileRecords[$r['id']] = &$r;
            }
        }

        if (!empty($fileRecords)) {
            $filesDir = Config::get('files_dir');

            $cpaths = DM\Files::getContentPaths(array_keys($fileRecords));

            foreach ($cpaths as $id => $cpath) {
                $r = &$fileRecords[$id];
                $filename =  $filesDir . $cpath . '.gz';

                if (file_exists($filename)) {
                    $content = file_get_contents($filename);
                    $r['content'] .= "\n" . mb_substr(gzuncompress($content), 0, 1024 * 1024); //max 1MB
                }
                unset($content);
                unset($r);
            }
        }
    }

    private function updateCronLastActionTime($cronId)
    {
        if (empty($cronId)) {
            return;
        }
        $cache_var_name = 'update_cron_'.$cronId;
        /* if less than 20 seconds have passed then skip updating db */
        if (\CB\Cache::exist($cache_var_name) &&
            ( (time() - \CB\Cache::get($cache_var_name)) < 20)
        ) {
            return;
        }

        \CB\Cache::set($cache_var_name, time());

        $id = DM\Crons::toId($cronId, 'cron_id');
        if (empty($id)) {
            DM\Crons::create(
                array(
                    'cron_id' => $cronId
                    ,'last_action' => 'CURRENT_TIMESTAMP'
                )
            );

        } else {
            DM\Crons::update(
                array(
                    'id' => $id
                    ,'last_action' => 'CURRENT_TIMESTAMP'
                )
            );
        }
    }

    /**
     * update tree nodes into solr
     *
     * @param string[] $p {
     *     @type boolean $all if true then all nodes will be updated into solr,
     *                          otherwise - only the nodes marked as updated will be reindexed in solr
     *     @type int[]  $id    id or array of object ids to update
     *
     *     @type varchar $cron_id when this function is called by a cron then cron_id should be passed
     *
     *     @type boolean $nolimit if true then no limit will be applied to maximum indexed nodes
     *                            (default 2000)
     * }
     */
    public function updateTree($p = array())
    {
        /* connect to solr service */
        $this->connect();

        $eventParams = array(
            'class' => &$this
            ,'params' => &$p
        );
        $this->folderTemplates = \CB\Config::get('folder_templates');

        \CB\fireEvent('onBeforeSolrUpdate', $eventParams);

        /** @type int the last processed document id */
        $lastId = 0;
        $indexedDocsCount = 0;
        $all = !empty($p['all']);
        $nolimit = !empty($p['nolimit']);

        /* prepeare where condition for sql depending on incomming params */
        $where = '(t.updated > 0) AND (t.draft = 0) AND (t.id > $1)';

        if ($all) {
            $this->deleteByQuery('*:*');
            $where = '(t.id > $1) AND (t.draft = 0) ';

            \CB\Templates\SingletonCollection::getInstance()->loadAll();

        } elseif (!empty($p['id'])) {
            $ids = \CB\Util\toNumericArray($p['id']);
            $where = '(t.id in (0'.implode(',', $ids).') ) and (t.id > $1)';
        }

        $sql = 'SELECT t.id
                ,t.pid
                ,ti.pids
                ,ti.case_id
                ,ti.acl_count
                ,ti.security_set_id
                ,t.name
                ,t.system
                ,t.template_id
                ,t.target_id
                ,t.size
                ,DATE_FORMAT(t.`date`, \'%Y-%m-%dT%H:%i:%sZ\') `date`
                ,DATE_FORMAT(t.`date_end`, \'%Y-%m-%dT%H:%i:%sZ\') `date_end`
                ,t.oid
                ,t.cid
                ,DATE_FORMAT(t.cdate, \'%Y-%m-%dT%H:%i:%sZ\') `cdate`
                ,t.uid
                ,DATE_FORMAT(t.udate, \'%Y-%m-%dT%H:%i:%sZ\') `udate`
                ,t.did
                ,DATE_FORMAT(t.ddate, \'%Y-%m-%dT%H:%i:%sZ\') `ddate`
                ,t.dstatus
                ,t.updated
                ,o.sys_data
            FROM tree t
            LEFT JOIN tree_info ti ON t.id = ti.id
            LEFT JOIN objects o ON o.id = t.id
            where '.$where.'
            ORDER BY t.id
            LIMIT 500';

        $docs = true;

        while (!empty($docs) && ($nolimit || ($indexedDocsCount < 2000))) {
            $docs = array();

            $res = DB\dbQuery($sql, $lastId) or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $lastId = $r['id'];

                /* process full object update only if:
                    - updated = 1
                    - specific ids are specified
                    - if $all parameter is true
                */
                if ($all || !empty($p['id']) || ($r['updated'] & 1)) {
                    $r['sys_data'] = Util\toJsonArray($r['sys_data']);

                    $this->prepareDBRecord($r);

                    $docs[$r['id']] = $r;
                }
                $this->updateCronLastActionTime(@$p['cron_id']);
            }
            $res->close();

            if (!empty($docs)) {
                //append file contents for files to content field
                $this->appendFileContents($docs);

                $this->addDocuments($docs);

                /* reset updated flag into database for processed documents */
                DB\dbQuery(
                    'UPDATE tree
                        ,tree_info
                    SET tree.updated = 0
                        ,tree_info.updated = 0
                    WHERE tree.id in ('.implode(',', array_keys($docs)).')
                        AND tree_info.id = tree.id'
                ) or die(DB\dbQueryError());

                $this->updateCronLastActionTime(@$p['cron_id']);

                $this->commit();

                $indexedDocsCount += sizeof($docs);
            }
        }

        $this->updateTreeInfo($p);

        \CB\fireEvent('onSolrUpdate', $eventParams);
    }

    /**
     * updating modified nodes info into solr from tree)info table
     */
    private function updateTreeInfo ($p)
    {
        /* connect to solr service */
        $this->connect();

        /** @type int the last processed document id */
        $lastId = 0;

        /* prepeare $where condition for sql */
        $where = 'ti.id > $1';
        if (!empty($p['id'])) {
            $ids = \CB\Util\toNumericArray($p['id']);
            $where = 'ti.id in (0'.implode(',', $ids).')';
        }

        $sql = 'SELECT ti.id
                    ,ti.pids
                    ,ti.case_id
                    ,ti.acl_count
                    ,ti.security_set_id
                    ,t.name `case`
            FROM tree_info ti
            LEFT JOIN tree t
                ON ti.case_id = t.id
            WHERE '.$where.'
                AND ti.updated = 1
            ORDER BY ti.id
            LIMIT 200';

        $docs = true;
        while (!empty($docs)) {
            $docs = array();

            $res = DB\dbQuery($sql, $lastId) or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $lastId = $r['id'];
                $r['update'] = true;

                if (empty($r['pids'])) {
                    $r['pids'] = null;
                    $r['path'] = null;
                } else {
                    $r['pids'] = explode(',', $r['pids']);
                    //exclude itself from pids
                    array_pop($r['pids']);
                    $r['path'] = implode('/', $r['pids']);
                }

                //encode special chars for string values
                foreach ($r as $k => $v) {
                    if (is_string($v)) {
                        $r[$k] = htmlspecialchars($v, ENT_COMPAT);
                    }
                }

                $docs[$r['id']] = $r;

                $this->updateCronLastActionTime(@$p['cron_id']);
            }
            $res->close();

            if (!empty($docs)) {
                $this->addDocuments($docs);

                /* reset updated flag into database for processed documents info */
                DB\dbQuery(
                    'UPDATE tree_info
                    SET updated = 0
                    WHERE id IN ('.implode(', ', array_keys($docs)).')'
                ) or die(DB\dbQueryError());

                $this->updateCronLastActionTime(@$p['cron_id']);

                $this->commit();
            }
        }

    }
    private function filterSolrFields(&$doc)
    {
        $some_fields = array('iconCls', 'updated', 'sys_data');

        foreach ($doc as $fn => $fv) {
            if (in_array($fn, $some_fields)
                || empty($fn)
                || ( ($fv !== false)
                    && ( (!is_scalar($fv) && empty($fv))
                        || (is_scalar($fv) && (strlen($fv) == 0))
                    )
                )
            ) {
                unset($doc[$fn]);
            }
        }
    }

    /* ----------------------- functions ---------------------------------*/

    /**
     * escape Lucene special chars
     *
     * Lucene characters that need escaping with \ are + - && || ! ( ) { } [ ] ^ " ~ * ? : \
     *
     * @param  scalar $v incoming string
     * @return scalar escaped variable
     */
    public static function escapeLuceneChars($v)
    {
        $luceneReservedCharacters = preg_quote('+-&|!(){}[]^"~*?:\\');
        $v = preg_replace_callback(
            '/([' . $luceneReservedCharacters . '])/',
            function ($matches) {
                return '\\' . $matches[0];
            },
            $v
        );

        return $v;
    }
}
