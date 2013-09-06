<?php
namespace CB\Solr;

use CB\DB as DB;

/**
 * Solr client class used by CaseBox to make changes into solr
 */

class Client extends Service
{
    /**
     * acceptable solr fields list
     * @var array
     */
    private $solr_fields = array('id'
        ,'pid'
        ,'pids'
        ,'path'
        ,'name'
        ,'system'
        ,'type'
        ,'subtype'
        ,'size'
        ,'date'
        ,'date_end'
        ,'oid'
        ,'cid'
        ,'cdate'
        ,'uid'
        ,'udate'
        ,'did'
        ,'ddate'
        ,'dstatus'
        ,'case_id'
        ,'case'
        ,'template_id'
        ,'template_type'
        ,'user_ids'
        ,'acl_count'
        ,'security_set_id'
        ,'status'
        ,'category_id'
        ,'importance'
        ,'completed'
        ,'versions'
        ,'sys_tags'
        ,'tree_tags'
        ,'user_tags'
        ,'metas'
        ,'content'
        ,'ntsc'
        ,'role_ids1'
        ,'role_ids2'
        ,'role_ids3'
        ,'role_ids4'
        ,'role_ids5'
        // custom core fields
        ,'substatus'
    );

    /**
     * running cron for updating tree changes into solr
     */
    public static function runCron()
    {
        if (isset($GLOBALS['running_trigger'])
            || isset($GLOBALS['solr_index_disable_by_custom_script'])
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
        if (isset($GLOBALS['running_trigger'])
            || isset($GLOBALS['solr_index_disable_by_custom_script'])
        ) {
            return;
        }
        $cmd = 'php -f '.\CB\CRONS_PATH.'run_cron.php solr_update_tree '.\CB\CORENAME.' > '.\CB\LOGS_PATH.'bg_solr_update_tree.log &';
        if (\CB\isWindows()) {
            $cmd = 'start /D "'.\CB\CRONS_PATH.'" php -f run_cron.php solr_update_tree '.\CB\CORENAME.' > '.\CB\LOGS_PATH.'bg_solr_update_tree.log';
        }
        pclose(popen($cmd, "r"));
    }

    /**
     * function that assigns additional data to object rectord
     * @param  reference $objectRecord
     * @return void
     */
    private function getSolrData(&$objectRecord)
    {
        switch ($objectRecord['template_type']) {
            case 'case':
                \CB\Objects::getSolrData($objectRecord);
                break;
            case 'object':
            case 'email':
                \CB\Objects::getSolrData($objectRecord);
                break;
            case 'file':
                \CB\Files::getSolrData($objectRecord);
                break;
            case 'task':
                \CB\Tasks::getSolrData($objectRecord);
                break;
        }
    }

    /**
     * function that assigns additional data to an array of object rectords
     * @param  array reference $objectRecords
     * @return void
     */
    private function getBulkSolrData(&$objectRecords)
    {
        \CB\Objects::getBulkSolrData($objectRecords);
        \CB\Files::getBulkSolrData($objectRecords);
        \CB\Tasks::getBulkSolrData($objectRecords);
    }

    private function updateCronLastActionTime($cron_id)
    {
        if (empty($cron_id)) {
            return;
        }
        $cache_var_name = 'update_cron_'.$cron_id;
        /* if less than 20 seconds have passed then skip updating db */
        if (\CB\Cache::exist($cache_var_name) &&
            ( (time() - \CB\Cache::get($cache_var_name)) < 20)
        ) {
            return;
        }

        \CB\Cache::set($cache_var_name, time());

        DB\dbQuery(
            'UPDATE crons
            SET last_action = CURRENT_TIMESTAMP
            WHERE cron_id = $1',
            $cron_id
        ) or die('error updating crons last action');
    }

    /**
     * update tree nodes into solr
     *
     * @param string[] $p {
     *     @type boolean $all if true then all nodes will be updated into solr,
     *                          otherwise - only the nodes marked as updated will be reindexed in solr
     *     @type int[]  $id    id or array of object ids to update
     *     @type varchar $cron_id when this function is called by a cron then cron_id should be passed
     * }
     */
    public function updateTree($p = array())
    {
        /* connect to solr service */
        $this->connect();

        /** @type int the last processed document id */
        $lastId = 0;

        $templatesCollection = \CB\Templates\SingletonCollection::getInstance();

        /* prepeare where condition for sql depending on incomming params */
        $where = '(t.updated > 0) and (t.id > $1)';
        if (isset($p['all']) && ($p['all'] == true)) {
            $where = '(t.id > $1)';
            $templatesCollection->loadAll();
        } elseif (!empty($p['id'])) {
            $ids = \CB\Util\toNumericArray($p['id']);
            $where = '(t.id in (0'.implode(',', $ids).') ) and (t.id > $1)';
        }

        $sql = 'SELECT t.id
                    ,t.pid
                    ,ti.pids
                    ,ti.path
                    ,ti.case_id
                    ,ti.acl_count
                    ,ti.security_set_id
                    ,t.name
                    ,t.system
                    ,t.type
                    ,t.subtype
                    ,t.template_id
                    ,t.target_id
                    ,t.size
            -- ,CASE WHEN t.type = 2 then (SELECT `type` FROM tree WHERE id = t.target_id) ELSE null END `target_type`
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
            FROM tree t
            LEFT JOIN tree_info ti ON t.id = ti.id
            where '.$where.
            ' ORDER BY t.id
            limit 500';

        $docs = true;
        while (!empty($docs)) {
            $docs = array();

            $res = DB\dbQuery($sql, $lastId) or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $lastId = $r['id'];

                /* process full object update only if:
                    - updated = 1
                    - specific ids are specified
                    - if $all parameter is true
                */
                if (!empty($p['all']) || !empty($p['id']) || ($r['updated'] & 1)) {
                    /* set template data */
                    if (!empty($r['template_id'])) {
                        $template = $templatesCollection->getTemplate($r['template_id']);
                        $r['template_type'] = $template->getData()['type'];
                        $r['iconCls'] = $template->getData()['iconCls'];
                    }

                    /* set the case name if case_id is present*/
                    if (!empty($r['case_id'])) {
                        $r['case'] = \CB\Objects::getCaseName($r['case_id']);
                    }

                    /* consider node type sort column (ntsc) equal to 1 unit more
                    than total count of folder templates */
                    $r['ntsc'] = sizeof($GLOBALS['folder_templates']) + 1;

                    /* decrease ntsc (make 1 unit more important) in case of 'case' object types */
                    if (@$r['template_type'] == 'case') {
                        $r['ntsc']--;
                    }

                    /* if there is a folder template then set its ntsc
                    equal to its index in folder_templates array */
                    $folder_index = array_search($r['template_id'], $GLOBALS['folder_templates']);
                    if ($folder_index !== false) {
                        $r['ntsc'] = $folder_index;
                    }

                    $r['content'] = $r['name'];

                    /* add custom solr data based on template type */
                    // $this->getSolrData($r);

                    /* make some trivial type checks */
                    $r['ntsc'] = intval($r['ntsc']);
                    $r['system'] = @intval($r['system']);
                    $r['type'] = @intval($r['type']);
                    $r['subtype'] = @intval($r['subtype']);
                    $r['pids'] = empty($r['pids']) ? null : explode(',', $r['pids']);

                    // $this->filterSolrFields($r);

                    /* append the obtained record to the result */
                    $docs[$r['id']] = $r;
                }
                $this->updateCronLastActionTime(@$p['cron_id']);
            }
            $res->close();

            if (!empty($docs)) {
                $this->getBulkSolrData($docs);

                foreach ($docs as $doc_id => $doc) {
                    $this->filterSolrFields($docs[$doc_id]);
                }

                $this->addDocuments($docs);

                /* reset updated flag into database for processed documents */
                $sql2 = 'UPDATE tree
                         , tree_info
                    SET tree.updated = 0
                      , tree_info.updated = 0
                    WHERE tree.id in ('.implode(',', array_keys($docs)).')
                        AND tree_info.id = tree.id';

                DB\dbQuery($sql2) or die(DB\dbQueryError());

                $this->updateCronLastActionTime(@$p['cron_id']);

                $this->commit();
            }
        }

        $this->updateTreeInfo($p);
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
        $where = 'id > $1';
        if (!empty($p['id'])) {
            $ids = \CB\Util\toNumericArray($p['id']);
            $where = 'id in (0'.implode(',', $ids).')';
        }

        $sql = 'SELECT id
                    ,pids
                    ,`path`
                    ,case_id
                    ,acl_count
                    ,security_set_id
            FROM tree_info
            WHERE '.$where.'
                AND updated = 1
            ORDER BY id
            LIMIT 200';

        $docs = true;
        while (!empty($docs)) {
            $docs = array();

            $res = DB\dbQuery($sql, $lastId) or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $lastId = $r['id'];
                $r['update'] = true;
                $r['pids'] = empty($r['pids']) ? null : explode(',', $r['pids']);

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
        foreach ($doc as $fn => $fv) {
            if (!in_array($fn, $this->solr_fields) || ( empty($fv) && (strlen($fv) == 0) && ($fv !== false))) {
                unset($doc[$fn]);
            }
        }
    }

    /* ----------------------- functions ---------------------------------*/
    public function currentWeekDiapazon()
    {
          $time1 = strtotime('previous monday');
          $time2 = strtotime('previous monday + 1 week');

        return date('Y-m-d\TH:i:s\Z', $time1).' TO '.date('Y-m-d\TH:i:s\Z', $time2);
    }
}
