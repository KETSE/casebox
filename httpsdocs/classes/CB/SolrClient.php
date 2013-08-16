<?php
namespace CB;

class SolrClient
{
    public $connected = false;
    public $solr = null;
    public $solr_fields = array('id'
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

    public function SolrClient ($p = array())
    {
        $this->init($p);
    }

    private function init($p = array())
    {
        $this->host = empty($p['host']) ? CONFIG\SOLR_HOST : $p['host'];
        $this->port = empty($p['port']) ? CONFIG\SOLR_PORT : $p['port'];
        $this->core = empty($p['core']) ? CONFIG\SOLR_CORE : $p['core'];
        $this->initialized = true;
    }

    public function connect($p = array())
    {
        if ($this->connected) {
            return $this->solr;
        }
        if (empty($this->initialized)) {
            $this->init();
        }

        require_once SOLR_CLIENT;
        $this->solr = new \Apache_Solr_Service($this->host, $this->port, $this->core);
        if (! $this->solr->ping()) {
            throw new \Exception('Solr_connection_error'.( isDebugHost() ? ' ('.$this->host.':'.$this->port.' -> '.$this->core.' )' : ''), 1);
        }
        $this->connected = true;

        return $this->solr;
    }
    public function addDocument($d)
    {
        $doc = new \Apache_Solr_Document();
        foreach ($d as $fn => $fv) {
            if (in_array($fn, $this->solr_fields) && ( ($fn == 'dstatus') || !empty($fv) || ($fv === false))) {
                $doc->$fn = $fv;
            }
        }
        try {
            fireEvent('beforeNodeSolrUpdate', $doc);
            $this->solr->addDocument($doc);
            fireEvent('nodeSolrUpdate', $doc);
        } catch (\Exception $e) {
            echo "\n\n-------------------------------------------";
            echo "\n\nError (id={".$doc->id."}): {$e->__toString()}\n";

            return false;
        }

        return true;
    }

    public function updateDocuments($docs)
    {
        $url = 'http://localhost:8983'.$this->core.'/update/json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:application/json; charset=utf-8"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array_values($docs)));
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
            print "curl_error:" . curl_error($ch);
        }
    }

    public function addDocuments(&$docs)
    {
        $addDocs = array();
        $updateDocs = array();
        foreach ($docs as $in_doc) {
            if (empty($in_doc['update'])) {
                $doc = new \Apache_Solr_Document();
                foreach ($in_doc as $fn => $fv) {
                    if (in_array($fn, $this->solr_fields) && ( ($fn == 'dstatus') || !empty($fv) || ($fv === false))) {
                        $doc->$fn = $fv;
                    }
                }
                fireEvent('beforeNodeSolrUpdate', $doc);
                $addDocs[] = $doc;
            } else {
                $doc = array();
                foreach ($in_doc as $fn => $fv) {
                    if (in_array($fn, $this->solr_fields)) {
                        if ($fn == 'id') {
                            $doc[$fn] = $fv;
                        } else {
                            $doc[$fn] = array( 'set' => $fv );
                        }
                    }
                }
                //htmlspecialchars($multivalue, ENT_NOQUOTES, 'UTF-8')
                $updateDocs[] = $doc;
            }
        }
        try {
            if (!empty($addDocs)) {
                $this->solr->addDocuments($addDocs);
            }
            if (!empty($updateDocs)) {
                $this->updateDocuments($updateDocs);
            }

        } catch (\Exception $e) {
            echo "\n\n-------------------------------------------";
            echo "\n\nError (adding multiple documents): {$e->__toString()}\n";
            print_r($addDocs);
            print_r($updateDocs);

            return false;
        }

        for ($i=0; $i < sizeof($addDocs); $i++) {
            fireEvent('nodeSolrUpdate', $addDocs[$i]);
        }

        for ($i=0; $i < sizeof($updateDocs); $i++) {
            fireEvent('nodeSolrUpdate', $updateDocs[$i]);
        }

        return true;
    }

    public function commit()
    {
        $this->solr->commit();
    }

    public static function runCron()
    {
        $solr = new SolrClient();
        $solr->connect();
        $solr->updateTree();
        unset($solr);
    }
    public static function runBackgroundCron()
    {
        $cmd = 'php -f '.CRONS_PATH.'run_cron.php solr_update_tree '.CORENAME.' > '.CRONS_PATH.'bg_solr_update_tree.log &';
        if (isWindows()) {
            $cmd = 'start /D "'.CRONS_PATH.'" php -f run_cron.php solr_update_tree '.CORENAME.' > '.CRONS_PATH.'bg_solr_update_tree.log';
        }
        // echo "$cmd \n";
        pclose(popen($cmd, "r"));
    }
    public function updateTree($all = false, $cron_id = false)
    {
        $this->connect();
        // $log_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'update_tree_'.CORENAME.'_log';
        // error_log("\n\rStart at ".date('H:i:s')."\n\r", 3, $log_file);

        $lastId = 0;
        $sql = 'SELECT t.id
                    ,t.pid
                    ,ti.pids
                    ,ti.path
                    ,ti.case_id
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
            ,nt.`type` template_type
            ,t.updated
            FROM tree t
            LEFT JOIN tree_info ti ON t.id = ti.id
            LEFT JOIN templates nt ON t.template_id = nt.id
            where '.($all ? ' (t.id > $1) ORDER BY t.id ' : ' t.updated > 0 ').
            'limit 200';

        $cases_info = array();

        $docs = true;
        while (!empty($docs)) {
            $docs = array();

            $res = DB\dbQuery($sql, $lastId) or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $lastId = $r['id'];
                if ($all || ($r['updated'] & 1)) { //update all object info
                    $id = $r['id'];
                    $type = $r['type'];
                    // if ($r['type'] == 2) {
                    //     $id = $r['target_id']; //link
                    //     $type = $r['target_type']; //link
                    // }

                    if (!empty($r['case_id'])) {
                        if (!isset($cases_info[$r['case_id']])) {
                            $cases_info[$r['case_id']] = array('id' => $r['id']);
                            $cres = DB\dbQuery(
                                'SELECT coalesce(custom_title, title) name
                                FROM objects
                                WHERE id = $1',
                                $r['case_id']
                            ) or die(DB\dbQueryError());

                            if ($cr = $cres->fetch_row()) {
                                $cases_info[$r['case_id']]['case'] = $cr[0];
                            }
                            $cres->close();
                            Objects::setCaseRolesFields($cases_info[$r['case_id']]);
                            unset($cases_info[$r['case_id']]['id']);
                        }
                        $r = array_merge($r, $cases_info[$r['case_id']]);
                    }

                    $r['ntsc'] = sizeof($GLOBALS['folder_templates']) + 1;
                    $r['content'] = $r['name'];

                    switch ($r['template_type']) {
                        case 'case':
                            $r['ntsc']--;
                            $r = array_merge($r, Objects::getSolrData($id));
                            break;
                        case 'object':
                        case 'email':
                            $r = array_merge($r, Objects::getSolrData($id));
                            break;
                        case 'file':
                            $r = array_merge($r, Files::getSolrData($id));
                            break;
                        case 'task':
                            $r = array_merge($r, Tasks::getSolrData($id));
                            break;
                    }

                    $folder_index = array_search($r['template_id'], $GLOBALS['folder_templates']);
                    if ($folder_index !== false) {
                        $r['ntsc'] = $folder_index;
                    }

                    $r['ntsc'] = intval($r['ntsc']);
                    $r['system'] = intval($r['system']);
                    $r['type'] = intval($r['type']);
                    $r['subtype'] = intval($r['subtype']);
                    $r['pids'] = empty($r['pids']) ? null : explode(',', $r['pids']);
                    $docs[$r['id']] = $r;

                }
                if (!empty($cron_id)) {
                    DB\dbQuery(
                        'UPDATE crons
                        SET last_action = CURRENT_TIMESTAMP
                        WHERE cron_id = $1',
                        $cron_id
                    ) or die('error updating crons last action');
                }

            }
            $res->close();
            if (!empty($docs)) {
                // error_log(print_r($docs, 1), 3, $log_file);
                try {
                    $this->addDocuments($docs);

                    $sql2 = 'UPDATE tree
                             , tree_info
                        SET tree.updated = 0
                          , tree_info.updated = 0
                        WHERE tree.id in ('.implode(',', array_keys($docs)).')
                            AND tree_info.id = tree.id';

                    DB\dbQuery($sql2, $r['id']) or die(DB\dbQueryError());
                } catch (\Exception $e) {
                    // error_log( " \n\r CANNOT add documents\n", 3, $log_file);
                }
                if (!empty($cron_id)) {
                    DB\dbQuery(
                        'UPDATE crons
                        SET last_action = CURRENT_TIMESTAMP
                        WHERE cron_id = $1',
                        $cron_id
                    ) or die('error updating crons last action');
                }

                try {
                    $this->commit();
                } catch (\Exception $e) {
                    // error_log( " \n\r CANNOT COMMIT\n", 3, $log_file);
                    exit();
                }

            }
        }
        // error_log( "\n\rEnd at ".date('H:i:s')."\n\r", 3, $log_file);

        $this->updateTreeInfo($cron_id);
    }

    private function updateTreeInfo ($cron_id)
    {
        $lastId = 0;
        $sql = 'SELECT id
                    ,pids
                    ,`path`
                    ,case_id
                    ,security_set_id
            FROM tree_info
            WHERE id > $1
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

                if (!empty($cron_id)) {
                    DB\dbQuery(
                        'UPDATE crons
                        SET last_action = CURRENT_TIMESTAMP
                        WHERE cron_id = $1',
                        $cron_id
                    ) or die('error updating crons last action');
                }

            }
            $res->close();

            if (!empty($docs)) {
                try {
                    $this->addDocuments($docs);
                    DB\dbQuery(
                        'UPDATE tree_info
                        SET updated = 0
                        WHERE id IN ('.implode(', ', array_keys($docs)).')'
                    ) or die(DB\dbQueryError());
                } catch (\Exception $e) {
                    echo "error adding documents to solr";
                    // error_log( " \n\r CANNOT add documents\n", 3, $log_file);
                }
                if (!empty($cron_id)) {
                    DB\dbQuery(
                        'UPDATE crons
                        SET last_action = CURRENT_TIMESTAMP
                        WHERE cron_id = $1',
                        $cron_id
                    ) or die('error updating crons last action');
                }

                try {
                    $this->commit();
                } catch (\Exception $e) {
                    // error_log( " \n\r CANNOT COMMIT\n", 3, $log_file);
                    exit();
                }

            }
        }

    }

    public function deleteId($id)
    {
        $this->deleteByQuery('id:'.$id.' OR pids:'.$id);
    }

    public function deleteByQuery($query)
    {
        $this->connect();
        $this->solr->deleteByQuery($query);
        try {
            $this->commit();
        } catch (\Exception $e) {
            die("Cannot commit after delete\n");
        }
    }

    public function optimize()
    {
        $this->connect();
        $this->solr->optimize();
        $this->commit();
    }

    /* ----------------------- functions ---------------------------------*/
    public function currentWeekDiapazon()
    {
          $time1 = strtotime('previous monday');
          $time2 = strtotime('previous monday + 1 week');

        return date('Y-m-d\TH:i:s\Z', $time1).' TO '.date('Y-m-d\TH:i:s\Z', $time2);
    }
}
