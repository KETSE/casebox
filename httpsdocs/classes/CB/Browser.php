<?php
namespace CB;

class Browser
{
    /* getCustomControllerResults function used to check if node has a controller specified in its "cfg" field
        if node have custom controller then results from the controller are returned, otherwise false is returned
     */
    public function getCustomControllerResults($path)
    {
        $rez = false;
        $ids = explode('/', $path);
        $id = array_pop($ids);
        while ((!is_numeric($id) || ($id < 1)) && !empty($ids)) {
            $id = array_pop($ids);
        }

        if (empty($id) || !is_numeric($id)) {
            return false;
        }

        $sql = 'select cfg from tree where id = $1';
        $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            if (!empty($r['cfg'])) {
                $r['cfg'] = json_decode($r['cfg']);
            }
            if (!empty($r['cfg']->controller)) {
                $userMenu = new UserMenu();
                $rez = $userMenu->{$r['cfg']->controller}($path);
                unset($userMenu);
            }
        }
         $res->close();

         return $rez;
    }

    public function getObjectsForField($p)
    {
        // ,"scope": 'tree' //project, parent, self, $node_id
        // ,"field": <field_name> //for field type

        // ,"descendants": true
        // /* filter used for objects */
        // ,+"tags": []
        // ,+"types": []
        // ,+"templates": []
        // ,"templateGroups": []

        //,+query - user query

        if (!empty($p->source)) {
            switch ($p->source) {
                case 'field':
                    if (empty($p->pidValue) || empty($p->field)) {
                        break;
                    }
                    $ids = Util\toNumericArray($p->pidValue);
                    if (empty($ids)) {
                        break;
                    }
                    /*get distinct target field values for selected objects in parent field */
                    $sql = 'SELECT od.value FROM
                        objects o
                        JOIN templates t ON t.id = o.`template_id`
                        JOIN templates_structure ts ON t.id = ts.`template_id` AND ts.name = $1
                        JOIN objects_data od ON o.id = od.`object_id` AND od.`field_id` = ts.id
                        WHERE o.`id` IN ('.implode(',', $ids).')';
                    $res = DB\dbQuery($sql, $p->field) or die(DB\dbQueryError());
                    $ids = array();
                    while ($r = $res->fetch_row()) {
                        if (!empty($r[0])) {
                            $v = explode(',', $r[0]);
                            for ($i=0; $i < sizeof($v); $i++) {
                                if (!empty($v[$i])) {
                                    $ids[$v[$i]] = 1;
                                }
                            }
                        }
                    }
                    $res->close();
                    $ids = array_keys($ids);
                    if (empty($ids)) {
                        return array('success' => true, 'data' => array() );
                    }

                    $p->ids = $ids;
                    break;

            }
        }

        $pids = false;
        if (!empty($p->scope)) {
            switch ($p->scope) {
                case 'project': /* limiting pid to project. If not in a project then to parent directory */
                    if (!empty($p->objectId) && is_numeric($p->objectId)) {
                        $sql = 'SELECT coalesce(ti.case_id, t.pid)
                            FROM tree t
                            JOIN tree_info ti
                                on t.id = ti.id
                            WHERE t.id = $1';
                        $res = DB\dbQuery($sql, $p->objectId) or die(DB\dbQueryError());
                        if ($r = $res->fetch_row()) {
                            $p->pids = $r[0];
                        }
                        $res->close();
                    } elseif (!empty($p->path)) {
                        $v = explode('/', $p->path);
                        $pids = 0;
                        while (!empty($v) && empty($pids)) {
                            $pids = array_pop($v);
                        }
                    }
                    break;
                case 'parent':
                    if (!empty($p->objectId) && is_numeric($p->objectId)) {
                        $sql = 'select pid from tree where id = $1 ';
                        $res = DB\dbQuery($sql, $p->objectId) or die(DB\dbQueryError());
                        if ($r = $res->fetch_row()) {
                            $p->pids = $r[0];
                        }
                        $res->close();
                    } elseif (!empty($p->path)) {
                        $v = explode('/', $p->path);
                        $pids = 0;
                        while (!empty($v) && empty($pids)) {
                            $pids = array_pop($v);
                        }
                    }

                    break;
                case 'self':
                    if (!empty($p->objectId) && is_numeric($p->objectId)) {
                        $sql = 'select id from tree where id = $1 ';
                        $res = DB\dbQuery($sql, $p->objectId) or die(DB\dbQueryError());
                        if ($r = $res->fetch_row()) {
                            $p->pids = $r[0];
                        }
                        $res->close();
                    } elseif (!empty($p->path)) {
                        $v = explode('/', $p->path);
                        $pids = 0;
                        while (!empty($v) && empty($pids)) {
                            $pids = array_pop($v);
                        }
                    }
                    break;
                case 'dependent':
                    if (!empty($p->pidValue)) {
                        $pids = Util\toNumericArray($p->pidValue);
                    }
                    break;
                default:
                    $pids = Util\toNumericArray($p->scope);
                    break;
            }
        }
        if (!empty($pids)) {
            if (empty($p->descendants)) {
                $p->pid = $pids;
            } else {
                $p->pids = $pids;
            }
        }

        $p->fl = 'id,name,type,subtype,template_id,status';
        if (!empty($p->fields)) {
            if (!is_array($p->fields)) {
                $p->fields = explode(',', $p->fields);
            }
            for ($i=0; $i < sizeof($p->fields); $i++) {
                $fieldName = trim($p->fields[$i]);
                if ($fieldName == 'project') {
                    $fieldName = 'case';
                }
                if (in_array(
                    $fieldName,
                    array(
                        'date'
                        ,'path'
                        ,'case'
                        ,'size'
                        ,'cid'
                        ,'oid'
                        ,'cdate'
                        ,'udate'
                    )
                )
                ) {
                    $p->fl .= ','.$fieldName;
                }
            }
        }

        $search = new Search();

        return $search->query($p);

        //return ;
    }

    public function createFolder($path)
    {
        $pid = explode('/', $path);
        $pid = array_pop($pid);
        if (!is_numeric($pid)) {
            return array('success' => false);
        }

        /* check security access */
        if (!Security::canCreateFolders($pid)) {
            throw new \Exception(L\Access_denied);
        }

        /* find default folder name */
        $newFolderName = L\NewFolder;
        $existing_names = array();
        $res = DB\dbQuery(
            'SELECT name
            FROM tree
            WHERE pid = $1
                AND name LIKE $2',
            array(
                $pid
                ,$newFolderName.'%'
            )
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_row()) {
            $existing_names[] = $r[0];
        }
        $res->close();
        $i = 1;
        while (in_array($newFolderName, $existing_names)) {
            $newFolderName = L\NewFolder.' ('.$i.')';
            $i++;
        }
        /* end of find default folder name */

        DB\dbQuery(
            'INSERT INTO tree (pid, user_id, `type`, `name`, cid, uid, template_id)
            VALUES ($1
                  , $2
                  , $3
                  , $4
                  , $2
                  , $2
                  , $3)',
            array(
                $pid
                ,$_SESSION['user']['id']
                ,1
                ,$newFolderName
                ,CONFIG\DEFAULT_FOLDER_TEMPLATE
            )
        ) or die(DB\dbQueryError());
        $id = DB\dbLastInsertId();
        Solr\Client::runCron();

        return array(
            'success' => true
            ,'path' => $path
            ,'data' => array(
                'nid' => $id
                ,'pid' => $pid
                ,'name' => $newFolderName
                ,'system' => 0
                ,'type' => 1
                ,'subtype' => 0
                ,'iconCls' => 'icon-folder'
                ,'cid' => $_SESSION['user']['id']
            )
        );
    }

    public function delete($paths)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }
        /* collecting ids from paths */
        $ids = array();
        foreach ($paths as $path) {
            $id = explode('/', $path);
            $id = array_pop($id);
            if (!is_numeric($id)) {
                return array('success' => false);
            }
            if (!Security::canDelete($id)) {
                throw new \Exception(L\Access_denied);
            }
            $ids[] = intval($id);
        }
        if (empty($ids)) {
            return array('success' => false);
        }

        /* before deleting we should check security for specified paths and all children */

        /* if access is granted then setting dstatus=1 for specified ids
        and dstatus = 2 for all their children /**/
        fireEvent('beforeNodeDbDelete', $ids);
        DB\dbQuery(
            'UPDATE tree
            SET did = $1
                    , dstatus = 1
                    , ddate = CURRENT_TIMESTAMP, updated = (updated | 1)
            WHERE id IN ('.implode(', ', $ids).')',
            $_SESSION['user']['id']
        ) or die(DB\dbQueryError());
        foreach ($ids as $id) {
            DB\dbQuery(
                'CALL p_mark_all_childs_as_deleted($1, $2)',
                array(
                    $id
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());
        }
        Solr\Client::runCron();

        fireEvent('nodeDbDelete', $ids);

        return array('success' => true, 'ids' => $ids);
    }

    public function rename($p)
    {
        $id = explode('/', $p->path);
        $id = array_pop($id);
        $p->name = trim($p->name);

        if (!is_numeric($id) || empty($p->name)) {
            return array('success' => false);
        }

        /* check security access */
        if (!Security::canWrite($id)) {
            throw new \Exception(L\Access_denied);
        }

        DB\dbQuery(
            'UPDATE tree
            SET name = $1
            WHERE id = $2',
            array(
                $p->name
                ,$id
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'UPDATE objects
            SET custom_title = $1
            WHERE id = $2',
            array(
                $p->name
                ,$id
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'UPDATE files
            SET name = $1
            WHERE id = $2',
            array(
                $p->name
                ,$id
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'UPDATE tasks
            SET title = $1
            WHERE id = $2',
            array(
                $p->name
                ,$id
            )
        ) or die(DB\dbQueryError());

        $sql = 'INSERT INTO objects_data (object_id, field_id, value)
            SELECT $1
                 , ts.id
                 , $2
            FROM tree t
            JOIN templates_structure ts ON t.template_id = ts.template_id
            WHERE t.id = $1
                AND ts.name = \'_title\'
            ON DUPLICATE KEY
                UPDATE `value` = $2';
        DB\dbQuery($sql, array($id, $p->name)) or die(DB\dbQueryError());

        /*updating renamed document into solr directly (before runing background cron)
            so that it'll be displayed with new name without delay*/
        $solrClient = new Solr\Client();
        $solrClient->updateTree(array('id' => $id));

        //running background cron to index other nodes
        $solrClient->runBackgroundCron();

        return array('success' => true, 'data' => array( 'id' => $id, 'newName' => $p->name) );
    }

    public function paste($p)
    {
        if (!is_numeric($p->pid) || empty($p->data)) {
            return array('success' => false, 'msg' => L\ErroneousInputData);
        }

        if (empty($p->confirmed)) {
            $p->confirmed = false;
        }
        $process_ids = array();
        // check if not pasting object to itself
        for ($i=0; $i < sizeof($p->data); $i++) {
            if ($p->pid == $p->data[$i]->id) {
                return array('success' => false, 'msg' => L\CannotCopyObjectToItself);
            }
            if ($this->isChildOf($p->pid, $p->data[$i]->id)) {
                return array('success' => false, 'msg' => L\CannotCopyObjectInsideItself);
            }

            $sql = 'select id, pid, name, `system`, template_id from tree where id = $1';
            $res = DB\dbQuery($sql, $p->data[$i]->id) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $process_ids[] = $r['id'];
                if (empty($p->action)) {
                    $p->action = 'copy';
                }
                if (!$p->confirmed && ($p->action !== 'copy')) {
                    $res2 = DB\dbQuery(
                        'SELECT id
                        FROM tree
                        WHERE pid = $1
                            AND SYSTEM = $2
                            AND name = $3
                            AND template_id = $4',
                        array(
                            $p->pid
                            ,$r['system']
                            ,$r['name']
                            ,$r['template_id']
                        )
                    ) or die(DB\dbQueryError());

                    if ($r2 = $res2->fetch_assoc()) {
                        //if($r2['id'] == $r['id']) return array('success' => false, 'msg' => L\CannotCopyObjectOverItself);
                        return array('success' => false, 'confirm' => true, 'msg' => L\ConfirmOverwriting);
                    }
                    $res2->close();
                }
            }
            $res->close();/**/
        }

        /* checking if processed ids names (of corresponding types) exists in target */

        if (empty($process_ids)) {
            return array('success' => true, 'pids' => array());
        }

        /* end of checking if processed ids names (of corresponding types) exists in target */

        $modified_pids = array($p->pid);
        switch ($p->action) {
            case 'copy':
                foreach ($process_ids as $id) {
                    $newName = '';
                    $sql = 'SELECT t1.name
                             , t2.name
                        FROM tree t1
                        LEFT JOIN tree t2 ON t2.pid = $2
                        AND t2.name = t1.name
                        WHERE t1.id = $1';
                    $res = DB\dbQuery($sql, array($id, $p->pid)) or die(DB\dbQueryError());
                    if ($r = $res->fetch_row()) {
                        $newName = empty($r[1]) ? $r[0] : $this->getNewCopyName($p->pid, $r[0]);
                    }
                    $res->close();

                    DB\dbQuery(
                        'INSERT INTO tree(pid
                            ,user_id
                            ,`system`
                            ,`type`
                            ,template_id
                            ,tag_id
                            ,name
                            ,`date`
                            ,`size`
                            ,is_main
                            ,cfg
                            ,cid
                            ,cdate
                            ,uid
                            ,udate
                            ,updated)
                        SELECT $2
                            ,user_id
                            ,0
                            ,`type`
                            ,template_id
                            ,tag_id
                            ,$4
                            ,`date`
                            ,`size`
                            ,is_main
                            ,cfg
                            ,$3
                            ,CURRENT_TIMESTAMP
                            ,$3
                            ,CURRENT_TIMESTAMP
                            ,1
                        FROM tree
                        WHERE id =$1',
                        array(
                            $id
                            ,$p->pid
                            ,$_SESSION['user']['id']
                            ,$newName
                        )
                    ) or die(DB\dbQueryError());
                    $obj_id = DB\dbLastInsertId();
                    $type = 0;
                    $res = DB\dbQuery(
                        'SELECT `type` FROM tree WHERE id = $1',
                        $id
                    ) or die(DB\dbQueryError());

                    if ($r = $res->fetch_row()) {
                        $type = $r[0];
                    }
                    $res->close();
                    switch ($type) {
                        case 3://case
                        case 4://case object
                            DB\dbQuery(
                                'INSERT INTO objects(id
                                    ,title
                                    ,custom_title
                                    ,template_id
                                    ,date_start
                                    ,date_end
                                    ,author
                                    ,iconCls
                                    ,details
                                    ,private_for_user
                                    ,cid
                                    ,uid
                                    ,cdate
                                    ,udate)
                                SELECT $2
                                    , title
                                    , $4
                                    , template_id
                                    , date_start
                                    , date_end
                                    , author
                                    , iconCls
                                    , details
                                    , private_for_user
                                    , $3
                                    , $3
                                    , CURRENT_TIMESTAMP
                                    , CURRENT_TIMESTAMP
                                FROM objects
                                WHERE id =$1',
                                array(
                                    $id
                                    ,$obj_id
                                    ,$_SESSION['user']['id']
                                    ,$newName
                                )
                            ) or die(DB\dbQueryError());

                            $duplicates = array(0 => 0);
                            $sql = 'SELECT id
                                     , pid
                                     , object_id
                                     , field_id
                                FROM objects_duplicates
                                WHERE object_id = $1
                                ORDER BY id';
                            $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError());
                            while ($r = $res->fetch_assoc()) {
                                DB\dbQuery(
                                    'INSERT INTO objects_duplicates(pid, object_id, field_id)
                                    VALUES($1
                                         , $2
                                         , $3)',
                                    array(
                                        $duplicates[$r['pid']]
                                        ,$r['object_id']
                                        ,$r['field_id']
                                    )
                                ) or die(DB\dbQueryError());
                                $duplicates[$r['id']] = DB\dbLastInsertId();
                            }
                            $res->close();

                            $sql = 'SELECT field_id
                                     , duplicate_id
                                     , `value`
                                     , info
                                     , files
                                     , private_for_user
                                FROM objects_data
                                WHERE object_id =$1';
                            $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError());
                            while ($r = $res->fetch_assoc()) {
                                DB\dbQuery(
                                    'INSERT INTO objects_data(
                                        object_id
                                        ,field_id
                                        ,duplicate_id
                                        ,`value`
                                        ,info
                                        ,files
                                        ,private_for_user)
                                    VALUES($1
                                         , $2
                                         , $3
                                         , $4
                                         , $5
                                         , $6
                                         , $7)',
                                    array(
                                        $obj_id
                                        ,$r['field_id']
                                        ,$duplicates[$r['duplicate_id']]
                                        ,$r['value']
                                        ,$r['info']
                                        ,$r['files']
                                        ,$r['private_for_user']
                                    )
                                ) or die(DB\dbQueryError());
                            }
                            $res->close();
                            break;

                        case 5://file
                            DB\dbQuery(
                                'INSERT INTO files(id, content_id, `date`, `name`, title, cid, uid, cdate, udate)
                                SELECT $2, content_id, `date`, $4, `title`, $3, $3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                                FROM files
                                WHERE id =$1',
                                array(
                                    $id
                                    ,$obj_id
                                    ,$_SESSION['user']['id']
                                    ,$newName
                                )
                            ) or die(DB\dbQueryError());
                            break;

                        case 6://task
                        case 7://event
                            DB\dbQuery(
                                'INSERT INTO tasks(
                                    id
                                    ,title
                                    ,date_start
                                    ,date_end
                                    ,`type`
                                    ,privacy
                                    ,responsible_party_id
                                    ,responsible_user_ids
                                    ,autoclose
                                    ,description
                                    ,parent_ids
                                    ,child_ids
                                    ,`time`
                                    ,reminds
                                    ,`status`
                                    ,missed
                                    ,completed
                                    ,cid
                                    ,uid
                                    ,cdate
                                    ,udate)
                                SELECT
                                    $2
                                    ,$4
                                    ,date_start
                                    ,date_end
                                    ,`type`
                                    ,privacy
                                    ,responsible_party_id
                                    ,responsible_user_ids
                                    ,autoclose
                                    ,description
                                    ,parent_ids
                                    ,child_ids
                                    ,`time`
                                    ,reminds
                                    ,`status`
                                    ,missed
                                    ,completed
                                    ,$3
                                    ,$3
                                    ,CURRENT_TIMESTAMP
                                    ,CURRENT_TIMESTAMP
                                FROM tasks
                                WHERE id =$1',
                                array(
                                    $id
                                    ,$obj_id
                                    ,$_SESSION['user']['id']
                                    ,$newName
                                )
                            ) or die(DB\dbQueryError());
                            break;

                        case 8://message
                            break;
                    }
                    if (!empty($existent_name)) {
                        DB\dbQuery(
                            'UPDATE tree
                            SET name = $2
                            WHERE id = $1',
                            array(
                                    $obj_id
                                    ,$this->getNewCopyName($p->pid, $existent_name)
                            )
                        ) or die(DB\dbQueryError());
                    }
                    Objects::updateCaseUpdateInfo($obj_id);
                }

                break;
            case 'move':
                foreach ($process_ids as $id) {
                    Objects::updateCaseUpdateInfo($id);
                }
                $res = DB\dbQuery(
                    'SELECT pid
                    FROM tree
                    WHERE id IN ('.implode(', ', $process_ids).')'
                ) or die(DB\dbQueryError());

                while ($r = $res->fetch_row()) {
                    $modified_pids[] = intval($r[0]);
                }
                $res->close();

                DB\dbQuery(
                    'UPDATE tree
                    SET pid = $1
                            , updated = (updated | 1)
                    WHERE id IN ('.implode(', ', $process_ids).')',
                    $p->pid
                ) or die(DB\dbQueryError());

                foreach ($process_ids as $id) {
                    Objects::updateCaseUpdateInfo($id);
                }

                Security::calculateUpdatedSecuritySets();
                break;
            case 'shortcut':
                DB\dbQuery(
                    'INSERT INTO tree (pid, `system`, `type`, `subtype`, target_id, `name`, cid, updated)
                    SELECT $1
                         , 0
                         , 2
                         , 0
                         , id
                         , `name`
                         , $2
                         , 1
                    FROM tree
                    WHERE id IN ('.implode(', ', $process_ids).')',
                    array(
                        $p->pid
                        ,$_SESSION['user']['id']
                    )
                ) or die(DB\dbQueryError());
                Objects::updateCaseUpdateInfo(DB\dbLastInsertId());
                break;
        }
        /*updating renamed document into solr directly (before runing background cron)
            so that it'll be displayed with new name without delay*/
        $solrClient = new Solr\Client();
        foreach ($process_ids as $id) {
            $solrClient->updateTree(array('id' => $id));
        }

        //running background cron to index other nodes
        $solrClient->runBackgroundCron();

        return array('success' => true, 'pids' => $modified_pids);
    }

    public function getNewCopyName($pid, $name, $excludeExtension = false)
    {
        $ext = '';
        if ($excludeExtension) {
            $a = explode('.', $name);
            if (sizeof($a) > 1) {
                $ext = '.'.array_pop($a);
            }
            $name = implode('.', $a);
        }

        $id = null;
        $i = 0;
        $newName = '';
        do {
            $newName = L\CopyOf.' '.$name.( ($i > 0) ? ' ('.$i.')' : '').$ext;
            $sql = 'SELECT id FROM tree WHERE pid = $1 AND name = $2';
            $res = DB\dbQuery($sql, array($pid, $newName)) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $id = $r['id'];
            } else {
                $id = null;
            }
            $res->close();
            $i++;
        } while (!empty($id));

        return $newName;
    }

    public function saveFile($p)
    {
        if (!file_exists(FILES_INCOMMING_PATH)) {
            @mkdir(FILES_INCOMMING_PATH, 0777, true);
        }

        $files = new Files();

        /* clean previous unhandled uploads if any */
        $a = $files->getUploadParams();
        if (($a !== false) && !empty( $a['files'] )) {
            @unlink(FILES_INCOMMING_PATH.$_SESSION['key']);
            $files->removeIncomingFiles($a['files']);
        }
        /* end of clean previous unhandled uploads if any */

        $F = &$_FILES;
        if (empty($p['pid'])) {
            return array('success' => false, 'msg' => L\Error_uploading_file);
        }
        //TODO: SECURITY: check if current user has write access to folder

        if (empty($F)) { //update only file properties (no files were uploaded)
            $files->updateFileProperties($p);

            return array( 'success' => true );
        } else {
            foreach ($F as $k => $v) {
                $F[$k]['name'] = strip_tags(@$F[$k]['name']);
            }
        }

        //if( !$files->fileExists($p['pid']) ) return array('success' => false, 'msg' => L\TargetFolderDoesNotExist);
        $res = DB\dbQuery(
            'SELECT id FROM tree WHERE id = $1',
            $p['pid']
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {

        } else {
            return array('success' => false, 'msg' => L\TargetFolderDoesNotExist);
        }
        $res->close();

        /*checking if there is no upload error (for any type of upload: single, multiple, archive) */
        foreach ($F as $fn => $f) {
            if (!in_array($f['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
                return array('success' => false, 'msg' => L\Error_uploading_file .': '.$f['error']);
            }
        }

        /* retreiving files list  */
        switch (@$p['uploadType']) {
            case 'archive':
                $archiveFiles = array();
                foreach ($F as $fk => $f) {
                    $files->extractUploadedArchive($F[$fk]);
                    $archiveFiles = array_merge($archiveFiles, $F[$fk]);
                }
                $F = $archiveFiles;
                break;
            default:
                $files->moveUploadedFilesToIncomming($F) or die('cannot move file to incomming '.FILES_INCOMMING_PATH);
                break;
        }

        $p['existentFilenames'] = $files->getExistentFilenames($F, $p['pid']);
        $p['files'] = &$F;

        if (!empty($p['existentFilenames'])) {
            // store current state serialized in a local file in incomming folder
            $files->saveUploadParams($p);
            if (!empty($p['response'])) {
                //it is supposed to work only for single files upload
                return $this->confirmUploadRequest((object) $p);
            }

            $allow_new_version = false;
            foreach ($p['existentFilenames'] as $f) {
                $mfvc = Files::getMFVC($f['name']);
                if ($mfvc > 0) {
                    $allow_new_version = true;
                }
            }
            $rez = array(
                'success' => false
                ,'type' => 'filesexist'
                ,'allow_new_version' => $allow_new_version
                ,'count' => sizeof($p['existentFilenames'])
            );
            if ($rez['count'] == 1) {
                $rez['msg'] = empty($p['existentFilenames'][0]['msg']) ?
                    str_replace(
                        '{filename}',
                        '"'.$p['existentFilenames'][0]['name'].'"',
                        L\FilenameExistsInTarget
                    )
                    : $p['existentFilenames'][0]['msg'];
                //$rez['filename'] = $p['existentFilenames'][0]['name'];
                $rez['suggestedFilename'] = $p['existentFilenames'][0]['suggestedFilename'];
            } else {
                $rez['msg'] = L\SomeFilenamesExistsInTarget;
            }

            return $rez;
        }
        $files->storeFiles($p); //if everithing is ok then store files
        Solr\Client::runCron();
        $rez = array('success' => true, 'data' => array('pid' => $p['pid']));
        $files->attachPostUploadInfo($F, $rez);

        return $rez;
    }

    // called when user was asked about file(s) overwrite
    public function confirmUploadRequest($p)
    {
        //if cancel then delete all uploaded files from incomming
        $files = new Files();
        $a = $files->getUploadParams();
        $a['response'] = $p->response;
        switch ($p->response) {
            case 'rename':
                $a['newName'] = $p->newName;
                //check if the new name does not also exist
                if (empty($a['response'])) {
                    return array('success' => false, 'msg' => L\FilenameCannotBeEmpty);
                }
                reset($a['files']);
                $k = key($a['files']);
                $a['files'][$k]['name'] = $a['newName'];
                if ($files->fileExists($a['pid'], $a['newName'])) {
                    $files->saveUploadParams($a);

                    return array(
                        'success' => false
                        ,'type' => 'filesexist'
                        //,'filename' => $a['newName']
                        ,'allow_new_version' => (Files::getMFVC($a['newName']) > 0)
                        ,'suggestedFilename' => $files->getAutoRenameFilename($a['pid'], $a['newName'])
                        ,'msg' => str_replace('{filename}', '"'.$a['newName'].'"', L\FilenameExistsInTarget)
                    );
                }
                // $files->storeFiles($a);
                // break;
            case 'newversion':
            case 'replace':
            case 'autorename':
                $files->storeFiles($a);
                break;
            default: //cancel
                $files->removeIncomingFiles($a['files']);

                return array('success' => true, 'data' => array() );
                break;
        }
        Solr\Client::runCron();
        $rez = array('success' => true, 'data' => array('pid' => $a['pid']));
        $files->attachPostUploadInfo($a['files'], $rez);

        return $rez;
    }

    public function uploadNewVersion($p)
    {
        $sql = 'select pid from tree where id = $1';
        $res = DB\dbQuery($sql, $p['id']) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $p['pid'] = $r['pid'];
        }
        $res->close();

        $rez = array('success' => true
            ,'data' => array('id' => $p['id']
            ,'pid' => $p['pid'])
        );

        $f = $_FILES['file'];
        if ($f['error'] == UPLOAD_ERR_NO_FILE) {
            DB\dbQuery(
                'UPDATE files
                SET `title` = $2
                            , `date` = $3
                WHERE id = $1',
                array(
                    $p['id']
                    ,$p['title']
                    ,Util\dateISOToMysql($p['date'])
                )
            ) or die(DB\dbQueryError());

            return $rez;
        }
        if ($f['error'] != UPLOAD_ERR_OK) {
            return array(
                'success' => false
                ,'msg' => L\Error_uploading_file .': '.$f['error']
            );
        }

        $p['files'] = &$_FILES;
        $p['response'] = 'overwrite';
        $files = new Files();
        $files->storeFiles($p);
        Solr\Client::runCron();

        return $rez;
    }

    public function toggleFavorite($p)
    {
        $favoriteFolderId = $this->getFavoriteFolderId();
        $p->pid = $favoriteFolderId;
        $sql = 'SELECT id
            FROM tree
            WHERE pid = $1
                AND `type` = 2
                AND target_id = $2';
        $res = DB\dbQuery($sql, array($favoriteFolderId, $p->id)) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            DB\dbQuery(
                'DELETE FROM tree WHERE id = $1',
                $r[0]
            ) or die(DB\dbQueryError());
            $res->close();
            $p->favorite = 0;
        } else {
            $res->close();
            /* get objects name */
            $name = 'Llink';
            $sql = 'select name from tree where id = $1';
            $res = DB\dbQuery($sql, array($p->id)) or die(DB\dbQueryError());
            if ($r = $res->fetch_row()) {
                $name = $r[0];
            }
            $res->close();
            /* end of get objects name */
            DB\dbQuery(
                'INSERT INTO tree (pid, user_id, `type`, name, target_id)
                VALUES($1
                     , $2
                     , 2
                     , $3
                     , $4)',
                array(
                    $favoriteFolderId
                    ,$_SESSION['user']['id']
                    ,$name
                    ,$p->id
                )
            ) or die(DB\dbQueryError());
            $p->favorite = 1;
        }

        return array('success' => true, 'data' => $p,);
    }

    public function takeOwnership($ids)
    {
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = array_filter($ids, 'is_numeric');
        $rez = array('success' => true, 'data' => $ids);
        if (empty($ids)) {
            return $rez;
        }
        $ids = implode(',', $ids);
        DB\dbQuery(
            'UPDATE tree
            SET oid = $1
                    , uid = $1
            WHERE id IN ('.$ids.')
                AND `system` = 0',
            $_SESSION['user']['id']
        ) or die(DB\dbQueryError());
        //TODO: view if needed to mark all childs as updated, for security to be changed ....
        Solr\Client::runCron();

        return $rez;
    }

    public function isChildOf($id, $pid)
    {
        $rez = false;
        $res = DB\dbQuery('SELECT pids from tree_info where id = $1', $id) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $r = ','.$r['pids'].',';
            $rez = ( strpos($r, ",$pid,") !== false );
        }
        $res->close();

        return $rez;
    }

    public static function checkRootFolder()
    {
        $id = null;
        $sql = 'SELECT id
            FROM tree
            WHERE pid IS NULL
                AND `system` = 1
                AND `is_main` = 1';
        $res = DB\dbQuery($sql) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $id = $r[0];
        }
        $res->close();

        /* create root folder */
        if ($id == null) {
            DB\dbQuery(
                'INSERT INTO tree (`system`, `name`, is_main, updated, template_id)
                VALUES (1
                    ,\'root\'
                    ,1
                    ,1
                    ,$1)',
                CONFIG\DEFAULT_FOLDER_TEMPLATE
            ) or die( DB\dbQueryError() );

            $id = DB\dbLastInsertId();

            // assign full control for "system" group
            DB\dbQuery(
                'INSERT INTO tree_acl (node_id, user_group_id, allow, deny)
                VALUES ($1
                      , $2
                      , 4095
                      , 0) ON duplicate KEY
                UPDATE allow = 4095
                     , deny = 0',
                array(
                    $id
                    ,Security::SystemGroupId()
                )
            ) or die( DB\dbQueryError() );

            Solr\Client::runCron();
        }

        return $id;
    }

    public static function getRootFolderId()
    {
        if (defined('CB\\ROOT_FOLDER_ID')) {
            return constant('CB\\ROOT_FOLDER_ID');
        }

        $id = null;
        $sql = 'SELECT id
            FROM tree
            WHERE pid IS NULL
                AND `system` = 1
                AND `is_main` = 1';
        $res = DB\dbQuery($sql) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $id = $r[0];
        }
        $res->close();

        if ($id == null) {
            Browser::checkRootFolder();

            return Browser::getRootFolderId();
        }
        define('CB\\ROOT_FOLDER_ID', $id);

        return $id;
    }

    public function getRootProperties($id)
    {
        $rez = array('success' => true, 'data' => array());
        $sql = 'SELECT t.id `nid`
                ,t.`system`
                ,t.`type`
                ,t.`subtype`
                ,t.`name`
                ,t.`cfg`
                ,ti.acl_count
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            WHERE t.id = $1';
        $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            if (empty($r['cfg'])) {
                unset($r['cfg']);
            } else {
                $r['cfg'] = json_decode($r['cfg']);
            }

            $rez['data'] = array($r);
            $this->updateLabels($rez['data']);
            $rez['data'] = $rez['data'][0];
        }
        $res->close();

        return $rez;
    }

    public static function getFavoriteFolderId()
    {
        $id = null;
        $sql = 'SELECT id
            FROM tree
            WHERE pid IS NULL
                AND `system` = 1
                AND `type` = 1
                AND subtype = 2
                AND user_id = $1';
        $res = DB\dbQuery($sql, array($_SESSION['user']['id'])) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $id = $r[0];
        }
        $res->close();

        return $id;
    }

    public function prepareResults(&$data)
    {
        if (empty($data) || !is_array($data)) {
            return;
        }
        for ($i=0; $i < sizeof($data); $i++) {
            $d = &$data[$i];
            if (isset($d['id']) && empty($d['nid'])) {
                $d['nid'] = $d['id'];
                unset($d['id']);
            }
            if (!isset($d['loaded'])) {
                $sql = 'SELECT count(*)
                    FROM tree
                    WHERE pid = $1
                        AND dstatus = 0'.
                    ( empty($this->showFoldersContent) ?
                        ' AND `template_id` IN (0'.implode(',', $GLOBALS['folder_templates']).')'
                        : ''
                    );
                $res = DB\dbQuery($sql, $d['nid']) or die(DB\dbQueryError());
                if ($r = $res->fetch_row()) {
                    $d['loaded'] = empty($r[0]);
                }
                $res->close();
            }
        }

    }
    public function updateLabels(&$data)
    {
        for ($i=0; $i < sizeof($data); $i++) {
            $d = &$data[$i];
            unset($d['iconCls']);
            //@$d['nid'] = intval($d['nid']);
            @$d['system'] = intval($d['system']);
            @$d['type'] = intval($d['type']);
            @$d['subtype'] = intval($d['subtype']);

            if ($d['system']) {
                if ((substr($d['name'], 0, 1) == '[') &&
                    (substr($d['name'], -1, 1) == ']')) {
                    $var_name = substr($d['name'], 1, strlen($d['name']) -2);
                    if (defined('CB\\L\\'.$var_name)) {
                        $d['name'] = L\get($var_name);
                    }
                }

            }
            /* next switch should/will be excluded: */
            switch ($d['type']) {
                case 0:
                    break;
                case 1:
                    switch ($d['subtype']) {
                        case 1:
                            if ((substr($d['name'], 0, 1) == '[') &&
                                (substr($d['name'], -1, 1) == ']')) {
                                $d['name'] = L\get(
                                    substr(
                                        $d['name'],
                                        1,
                                        strlen($d['name']) -2
                                    )
                                );
                            }
                            break;
                        case 2:
                            $d['name'] = L\MyCaseBox;
                            break;
                        case 3:
                            $d['name'] = L\MyDocuments;
                            break;
                        case 4:
                            $d['name'] = L\Cases;
                            break;
                        case 5:
                            $d['name'] = L\Tasks;
                            break;
                        case 6:
                            $d['name'] = L\Messages;
                            break;
                        //case 7:
                        //  $d['name'] = L\RecycleBin;
                        //  break;
                        case 8:
                            break;
                        case 9:
                            break;
                        case 10:
                            $d['name'] = L\PublicFolder;
                            break;
                        default:
                            break;
                    }
                    break;
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
            }
        }

        return $data;
    }

    public static function getIcon(&$data)
    {
        if (!isset($data['type'])) {
            return '';
        }

        switch (intval($data['type'])) {
            case 0:
                return Util\coalesce($data['iconCls'], 'icon-folder');
                break;
            case 1:
                switch (intval(@$data['subtype'])) {
                    case 1:
                        break;
                    case 2:
                        return 'icon-home';
                        break;
                    case 3:
                        return 'icon-blue-folder';
                        break;
                    case 4:
                        return 'icon-briefcase';
                        break;
                    case 5:
                        return 'icon-calendar-small';
                        break;
                    case 6:
                        return 'icon-mail-medium';
                        break;
                    case 7:
                        return 'icon-blue-folder-stamp';
                        break;
                    case 8:
                        return 'icon-folder';
                        break;
                    case 9:
                        return 'icon-blue-folder';
                        break;
                    case 10:
                        return 'icon-blue-folder-share';
                        break;
                    default:
                        return @Util\coalesce($data['iconCls'], 'icon-folder');
                        break;
                }
                break;
            case 2:
                return 'icon-shortcut';//case
                break;
            case 3:
                return 'icon-briefcase';//case
                break;
            case 4: //case object
                if (!empty($data['cfg']) && !empty($data['cfg']->iconCls)) {
                    return $data['cfg']->iconCls;
                }
                if (!empty($data['template_id'])) {
                    return Templates::getIcon($data['template_id']);
                }

                return 'icon-none';
                break;
            case 5: //file

                return Files::getIcon($data['name']);
                break;
            case 6:
                if (@$d['status'] == 3) {
                    return 'icon-task-completed';
                }

                return 'icon-task';//task
                break;
            case 7:
                return 'icon-event';//Event
            case 8:
                return 'icon-mail';//Message (email)
                break;
        }
    }
}
