<?php
namespace CB;

class Browser
{

    protected $path = [];
    protected $treeNodeConfigs = array();
    protected $treeNodeGUIDConfigs = array();
    protected $treeNodeClasses = array();

    public function getChildren($p)
    {
        $rez = array();

        /* prepare params */
        $path = '/';
        if (!isset($p['path']) || (strlen($p['path']) < 1)) {
            if (!empty($p['pid'])) {
                $path = $p['pid'];
            }
        } else {
            $path = $p['path'];
        }
        $p['path'] = $path;

        //the navigation goes from search results. We should get the real path of the node
        if (!empty($p['lastQuery']) && empty($p['query'])) {
            $a = Util\toNumericArray($p['path'], '/');
            $p['path'] = Path::getPath(array_pop($a))['path'];
        }

        $this->showFoldersContent = isset($p['showFoldersContent'])
            ? $p['showFoldersContent']
            : false;

        $this->requestParams = $p;
        Cache::set('requestParams', $p);

        /* end of prepare params */

        /* we should:
            1. load available plugins for the tree with their configs
            2. fire the on treeInitialize event
            3. call each plugin with received params
            4. join and sort received data
        */

        //detect tree nodes config,
        //but leave only SearchResults plugin when searching
        $this->treeNodeConfigs = empty($p['search'])
            ? Config::get('treeNodes', array('Dbnode' => array()))
            : array('SearchResults' => array());

        $params = array(
            'params' => &$p,
            'plugins' => &$this->treeNodeConfigs
        );

        fireEvent('treeInitialize', $params);

        $this->initNodeClasses();
        $this->createNodesPath();
        Cache::set('current_path', $this->path);
        $this->collectAllChildren();

        $this->prepareResults($this->data);

        $rez = array(
            'success' => true
            ,'pathtext' => $this->getPathText($p)
            ,'folderProperties' => $this->getPathProperties($p)
            ,'data' => $this->data
            ,'total' => $this->total
        );

        if (!empty($this->facets)) {
            $rez['facets'] = &$this->facets;
        }
        if (!empty($this->pivot)) {
            $rez['pivot'] = &$this->pivot;
        }
        if (!empty($this->search)) {
            $rez['search'] = &$this->search;
        }
        if (!empty($this->DC)) {
            $rez['DC'] = &$this->DC[0];
        }

        return $rez;

    }

    public function initNodeClasses()
    {
        $this->treeNodeClasses = array();
        foreach ($this->treeNodeConfigs as $p => $cfg) {
            $class = empty($cfg['class']) ? '\\CB\\TreeNode\\'.$p : $cfg['class'];
            $cfg['guid'] = $this->getGUID($p);
            $cfg['class'] = $class;

            try {
                $class = new $class($cfg);
                $this->treeNodeGUIDConfigs[$cfg['guid']] = $cfg;
                $this->treeNodeClasses[$cfg['guid']] = $class;
            } catch (\Exception $e) {
                debug('error creating class '.$class);
            }
        }
    }

    protected function createNodesPath()
    {

        $this->path = array();
        $path = explode('/', @$this->requestParams['path']);
        while (!empty($path)) {
            $npid = null;
            $nodeId = null;

            $el = array_shift($path);
            if (strlen($el) < 1) {
                continue;
            }

            $el = explode('-', $el);
            if (sizeof($el) > 1) {
                $npid = $el[0];
                $nodeId = $el[1];
            } else {
                $npid = $this->getGUID('Dbnode');
                $nodeId = $el[0];
            }

            $cfg = empty($this->treeNodeGUIDConfigs[$npid])
                ? array( 'class' => 'CB\TreeNode\\Dbnode', 'guid' => $npid)
                : $this->treeNodeGUIDConfigs[$npid];

            $class = new $cfg['class']($cfg, $nodeId);
            //set parent node
            if (!empty($this->path)) {
                $class->parent = $this->path[sizeof($this->path) - 1];
            }

            array_push(
                $this->path,
                $class
            );
        }
    }

    protected function getPathText()
    {
        $rez = array();
        if (empty($this->path)) {
            return '/';
        }

        foreach ($this->path as $n) {
            $rez[] = $n->getName();
        }

        return implode('/', $rez);
    }

    protected function getPathProperties()
    {
        $rez = array();
        if (empty($this->path)) {
            $rez['path'] = '/';
        } else {
            $rez = $this->path[sizeof($this->path) - 1]->getData();

            $idsPath = array();
            foreach ($this->path as $n) {
                $idsPath[] = $n->getId();
            }

            $rez['path'] = '/'.implode('/', $idsPath);
        }

        return $rez;
    }

    protected function collectAllChildren()
    {

        $this->data = array();
        $this->facets = array();
        $this->pivot = array();
        $this->total = 0;
        $this->search = array();
        $this->DC = array();
        foreach ($this->treeNodeClasses as $class) {
            $rez = $class->getChildren($this->path, $this->requestParams);
            if (!empty($rez['data'])) {
                $this->data = array_merge($this->data, $rez['data']);
            }
            if (!empty($rez['facets'])) {
                $this->facets = $rez['facets'];
            }
            if (!empty($rez['pivot'])) {
                $this->pivot = $rez['pivot'];
            }

            if (isset($rez['total'])) {
                $this->total += $rez['total'];
            } elseif (!empty($rez['data'])) {
                $this->total += sizeof($rez['data']);
            }

            if (isset($rez['search'])) {
                $this->search[] = $rez['search'];
            }

            if (isset($rez['DC'])) {
                $this->DC[] = $rez['DC'];
            }
        }
    }

    protected function sortResult()
    {
        //sorting nodes;
    }

    public static function getGUID($name)
    {
        $rez = null;
        $res = DB\dbQuery(
            'SELECT id FROM `casebox`.guids WHERE name = $1',
            $name
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        } else {
            DB\dbQuery(
                'INSERT INTO `casebox`.guids
                (`name`)
                VALUES ($1)',
                $name
            ) or die(DB\dbQueryError());
            $rez = DB\dbLastInsertId();
        }
        $res->close();

        return $rez;
    }

    /**
     * return records for an objects field based on its config
     * @param  array $p
     * @return json  repsponce
     */
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

        if (!empty($p['source'])) {
            if (is_array($p['source'])) { // a custom source
                $rez = array();

                if (empty($p['fieldId'])) {
                    return $rez;
                }

                //get custom method from database
                $cfg = [];
                $res = DB\dbQuery(
                    'SELECT cfg from templates_structure where id = $1',
                    $p['fieldId']
                ) or die(DB\dbQueryError());
                if ($r = $res->fetch_assoc()) {
                    $cfg = json_decode($r['cfg'], true);
                }
                $res->close();
                if (empty($cfg['source']['fn'])) {
                    return $rez;
                }

                $method = explode('.', $cfg['source']['fn']);
                $class = new $method[0]();
                $rez = $class->$method[1]($p);
                if (!empty($rez)) {
                    return $rez;
                }
            }

            switch ($p['source']) {
                case 'field':

                    $ids = array();

                    switch ($p['scope']) {
                        case 'project':
                            $ids = $this->getCaseId(Path::detectRealTargetId($p['path']));
                            break;

                        case 'parent':
                            $ids = Path::detectRealTargetId($p['path']);
                            break;

                        default:
                            if (empty($p['pidValue']) || empty($p['field'])) {
                                break 2;
                            }
                            $ids = $p['pidValue'];

                    }
                    $ids = Util\toNumericArray($ids);

                    if (empty($ids)) {
                        break;
                    }

                    /*get distinct target field values for selected objects in parent field */
                    $obj = new Objects\Object();
                    $values = array();
                    foreach ($ids as $id) {
                        $obj->load($id);
                        $fv = $obj->getFieldValue($p['field'], 0);
                        $fv = Util\toNumericArray(@$fv['value']);
                        $values = array_merge($values, $fv);
                    }
                    $values = array_unique($values);

                    if (empty($values)) {
                        return array('success' => true, 'data' => array() );
                    }

                    $p['ids'] = $values;
                    break;
            }
        }

        $pids = false;
        if (!empty($p['scope'])) {
            switch ($p['scope']) {
                case 'project': /* limiting pid to project. If not in a project then to parent directory */
                    if (!empty($p['objectId']) && is_numeric($p['objectId'])) {
                        $pids = $this->getCaseId($p['objectId']);
                    } elseif (!empty($p['path'])) {
                        $pids = $this->getCaseId(Path::detectRealTargetId($p['path']));
                    }
                    break;
                case 'parent':
                    if (!empty($p['objectId']) && is_numeric($p['objectId'])) {
                        $p['pids'] = $this->getPid($p['objectId']);
                    } elseif (!empty($p['path'])) {
                        $pids = Path::detectRealTargetId($p['path']);
                    }

                    break;
                case 'self':
                    if (!empty($p['objectId']) && is_numeric($p['objectId'])) {
                        $p['pids'] = $r['objectId'];
                    } elseif (!empty($p['path'])) {
                        $pids = Path::detectRealTargetId($p['path']);
                    }
                    break;
                case 'variable':
                    $pids = empty($p['pidValue'])
                        ? Path::detectRealTargetId($p['path'])
                        : Util\toNumericArray($p['pidValue']);
                    break;
                default:
                    $pids = Util\toNumericArray($p['scope']);
                    break;
            }
        }
        if (!empty($pids)) {
            if (empty($p['descendants'])) {
                $p['pid'] = $pids;
            } elseif (@$p['source'] !== 'field') {
                $p['pids'] = $pids;
            }
        }

        $p['fl'] = 'id,name,type,subtype,template_id,status';
        if (!empty($p['fields'])) {
            if (!is_array($p['fields'])) {
                $p['fields'] = explode(',', $p['fields']);
            }
            for ($i=0; $i < sizeof($p['fields']); $i++) {
                $fieldName = trim($p['fields'][$i]);
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
                    $p['fl'] .= ','.$fieldName;
                }
            }
        }

        $search = new Search();
        $rez = $search->query($p);

        foreach ($rez['data'] as &$doc) {
            $res = DB\dbQuery(
                'SELECT cfg FROM tree WHERE id = $1 AND cfg IS NOT NULL',
                $doc['id']
            ) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                if (!empty($r['cfg'])) {
                    $cfg = Util\toJSONArray($r['cfg']);
                    if (!empty($cfg['iconCls'])) {
                        $doc['iconCls'] = $cfg['iconCls'];
                    }
                }
            }
            $res->close();
        }

        return $rez;
    }

    public static function getCaseId($objectId)
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT coalesce(ti.case_id, t.pid) `pid`
            FROM tree t
            JOIN tree_info ti ON t.id = ti.id
            WHERE t.id = $1',
            $objectId
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['pid'];
        }
        $res->close();

        return $rez;
    }

    public static function getPId($objectId)
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT pid FROM tree WHERE id = $1',
            $objectId
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['pid'];
        }
        $res->close();

        return $rez;
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

        while ($r = $res->fetch_assoc()) {
            $existing_names[] = $r['name'];
        }
        $res->close();
        $i = 1;
        while (in_array($newFolderName, $existing_names)) {
            $newFolderName = L\NewFolder.' ('.$i.')';
            $i++;
        }
        /* end of find default folder name */

        DB\dbQuery(
            'INSERT INTO tree
                (pid
                ,user_id
                ,`type`
                ,`name`
                ,cid
                ,uid
                ,template_id)
            VALUES ($1
                ,$2
                ,$3
                ,$4
                ,$2
                ,$2
                ,$3)',
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

        foreach ($ids as $id) {
            $obj = Objects::getCustomClassByObjectId($id);
            $obj->delete();
        }

        Solr\Client::runCron();

        return array('success' => true, 'ids' => $ids);
    }

    public function rename($p)
    {
        $id = explode('/', $p['path']);
        $id = array_pop($id);
        $p['name'] = trim($p['name']);

        if (!is_numeric($id) || empty($p['name'])) {
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
                $p['name']
                ,$id
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'UPDATE files
            SET name = $1
            WHERE id = $2',
            array(
                $p['name']
                ,$id
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'UPDATE tasks
            SET title = $1
            WHERE id = $2',
            array(
                $p['name']
                ,$id
            )
        ) or die(DB\dbQueryError());

        /*updating renamed document into solr directly (before runing background cron)
            so that it'll be displayed with new name without delay*/
        $solrClient = new Solr\Client();
        $solrClient->updateTree(array('id' => $id));

        //running background cron to index other nodes
        $solrClient->runBackgroundCron();

        return array('success' => true, 'data' => array( 'id' => $id, 'newName' => $p['name']) );
    }

    /**
     * generate a name for for a new copy of an object
     *
     * This function is used to generate a new name lyke "Copy of <old file_name> (1).ext".
     * Usually used when copy/pasting objects and pasted object should receive a new name.
     *
     * @param  int     $pid              parent object/folder id
     * @param  varchar $name             old/existing object name
     * @param  boolean $excludeExtension if true then characters after last "." will remain unchanged
     * @return varchar new name
     */
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
            $res = DB\dbQuery(
                'SELECT id
                FROM tree
                WHERE pid = $1
                    AND name = $2',
                array($pid, $newName)
            ) or die(DB\dbQueryError());

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
        if (!file_exists(INCOMMING_FILES_DIR)) {
            @mkdir(INCOMMING_FILES_DIR, 0777, true);
        }

        $files = new Files();

        /* clean previous unhandled uploads if any */
        $a = $files->getUploadParams();
        if (($a !== false) && !empty( $a['files'] )) {
            @unlink(INCOMMING_FILES_DIR.$_SESSION['key']);
            $files->removeIncomingFiles($a['files']);
        }
        /* end of clean previous unhandled uploads if any */

        $F = &$_FILES;
        if (empty($p['pid'])) {
            return array('success' => false, 'msg' => L\Error_uploading_file);
        }
        $p['pid'] = Path::detectRealTargetId($p['pid']);

        //TODO: SECURITY: check if current user has write access to folder

        if (empty($F)) { //update only file properties (no files were uploaded)
            $files->updateFileProperties($p);

            return array( 'success' => true );
        } else {
            foreach ($F as $k => $v) {
                $F[$k]['name'] = strip_tags(@$F[$k]['name']);
            }
        }

        //if ( !$files->fileExists($p['pid']) ) {
        //  return array('success' => false, 'msg' => L\TargetFolderDoesNotExist);
        //  }
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
                $files->moveUploadedFilesToIncomming($F) or die('cannot move file to incomming '.INCOMMING_FILES_DIR);
                break;
        }

        $p['existentFilenames'] = $files->getExistentFilenames($F, $p['pid']);
        $p['files'] = &$F;

        if (!empty($p['existentFilenames'])) {
            // store current state serialized in a local file in incomming folder
            $files->saveUploadParams($p);
            if (!empty($p['response'])) {
                //it is supposed to work only for single files upload
                return $this->confirmUploadRequest($p);
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
        $a['response'] = $p['response'];
        switch ($p['response']) {
            case 'rename':
                $a['newName'] = $p['newName'];
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
                        ,'suggestedFilename' => Objects::getAvailableName($a['pid'], $a['newName'])
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

    /**
     * upload a new file version
     * @param  array $p params
     * @return json  responce
     */
    public function uploadNewVersion($p)
    {
        // get the pid and set it into params
        $res = DB\dbQuery(
            'SELECT pid FROM tree WHERE id = $1',
            $p['id']
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $p['pid'] = $r['pid'];
        }
        $res->close();

        $rez = array('success' => true
            ,'data' => array('id' => $p['id']
            ,'pid' => $p['pid'])
        );

        $f = &$_FILES['file'];
        // if no file is uploaded then just update file properties
        if ($f['error'] == UPLOAD_ERR_NO_FILE) {
            DB\dbQuery(
                'UPDATE files
                SET `title` = $2
                    ,`date` = $3
                WHERE id = $1',
                array(
                    $p['id']
                    ,$p['title']
                    ,Util\dateISOToMysql($p['date'])
                )
            ) or die(DB\dbQueryError());

            return $rez;
        }

        //check for upload error
        if ($f['error'] != UPLOAD_ERR_OK) {
            return array(
                'success' => false
                ,'msg' => L\Error_uploading_file .': '.$f['error']
            );
        }

        $p['files'] = &$_FILES;
        $p['response'] = 'newversion';
        $files = new Files();

        $files->storeFiles($p);

        Solr\Client::runCron();

        return $rez;
    }

    public function toggleFavorite($p)
    {
        $favoriteFolderId = $this->getFavoriteFolderId();
        $p['pid'] = $favoriteFolderId;
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE pid = $1
                AND `type` = 2
                AND target_id = $2',
            array($favoriteFolderId, $p['id'])
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            DB\dbQuery(
                'DELETE FROM tree WHERE id = $1',
                $r['id']
            ) or die(DB\dbQueryError());
            $res->close();
            $p['favorite'] = 0;
        } else {
            $res->close();
            /* get objects name */
            $name = 'Llink';
            $res = DB\dbQuery(
                'SELECT name FROM tree WHERE id = $1',
                $p['id']
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $name = $r['name'];
            }
            $res->close();
            /* end of get objects name */
            DB\dbQuery(
                'INSERT INTO tree
                    (pid
                    ,user_id
                    ,`type`
                    ,name
                    ,target_id)
                VALUES($1
                    ,$2
                    ,2
                    ,$3
                    ,$4)',
                array(
                    $favoriteFolderId
                    ,$_SESSION['user']['id']
                    ,$name
                    ,$p['id']
                )
            ) or die(DB\dbQueryError());
            $p['favorite'] = 1;
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
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE pid IS NULL
                AND `system` = 1
                AND `is_main` = 1'
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $id = $r['id'];
        }
        $res->close();

        /* create root folder */
        if ($id == null) {
            DB\dbQuery(
                'INSERT INTO tree
                    (`system`
                    , `name`
                    , is_main
                    , updated
                    , template_id)
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
                'INSERT INTO tree_acl
                    (node_id
                    , user_group_id
                    , allow
                    , deny)
                VALUES ($1
                    ,$2
                    ,4095
                    ,0) ON duplicate KEY
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
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE pid IS NULL
                AND `system` = 1
                AND `is_main` = 1'
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $id = $r['id'];
        }
        $res->close();

        if ($id == null) {
            Browser::checkRootFolder();

            return Browser::getRootFolderId();
        }
        define('CB\\ROOT_FOLDER_ID', $id);

        return $id;
    }

    public static function getRootProperties($id)
    {
        $rez = array('success' => true, 'data' => array());
        $res = DB\dbQuery(
            'SELECT t.id `nid`
                ,t.`system`
                ,t.`type`
                ,t.`subtype`
                ,t.`name`
                ,t.`cfg`
                ,ti.acl_count
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            WHERE t.id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $rez['data'] = array($r);
            Browser::updateLabels($rez['data']);
            $rez['data'] = $rez['data'][0];
        }
        $res->close();

        return $rez;
    }

    public static function getFavoriteFolderId()
    {
        $id = null;
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE pid IS NULL
                AND `system` = 1
                AND `type` = 1
                AND subtype = 2
                AND user_id = $1',
            $_SESSION['user']['id']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $id = $r['id'];
        }
        $res->close();

        return $id;
    }

    public function prepareResults(&$data)
    {
        if (empty($data) || !is_array($data)) {
            return;
        }

        //this->sortNodes();
        $sql = 'SELECT count(*) `has_childs`
            FROM tree
            WHERE pid = $1
                AND dstatus = 0'.
            ( empty($this->showFoldersContent) ?
                ' AND `template_id` IN (0'.implode(',', $GLOBALS['folder_templates']).')'
                : ''
            );

        for ($i=0; $i < sizeof($data); $i++) {
            $d = &$data[$i];
            if (isset($d['id']) && empty($d['nid'])) {
                $d['nid'] = $d['id'];
                unset($d['id']);
            }
            if (is_numeric($d['nid']) && !isset($d['loaded'])) {
                $res = DB\dbQuery($sql, $d['nid']) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $d['has_childs'] = !empty($r['has_childs']);
                }
                $res->close();
            }
        }

    }

    public static function updateLabels(&$data)
    {
        for ($i=0; $i < sizeof($data); $i++) {
            $d = &$data[$i];
            unset($d['iconCls']);
            //@$d['nid'] = intval($d['nid']);
            @$d['system'] = intval($d['system']);
            @$d['type'] = intval($d['type']);
            @$d['subtype'] = intval($d['subtype']);

            if ($d['system']) {
                $d['name'] = L\getTranslationIfPseudoValue($d['name']);
            }
            /* next switch should/will be excluded: */
            switch ($d['type']) {
                case 0:
                    break;
                case 1:
                    switch ($d['subtype']) {
                        case 1:
                            $d['name'] = L\getTranslationIfPseudoValue($d['name']);
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

    /**
     * detect object icon by analizing it's data
     *
     * object data could have set a custom iconCls in cfg property of the data,
     * otherwise the icon is determined from it's template
     * TODO: think about shortcuts
     * @param  array   $data object data
     * @return varchar iconCls
     */
    public static function getIcon(&$data)
    {

        if (!empty($data['cfg']) && !empty($data['cfg']['iconCls'])) {
            return $data['cfg']['iconCls'];
        }

        if (empty($data['template_id'])) {
            return 'icon-none';
        }

        $templates = Templates\SingletonCollection::getInstance();
        $templateData = $templates->getTemplate($data['template_id'])->getData();

        if (!empty($templateData['iconCls'])) {
            return $templateData['iconCls'];
        }

        switch ($templateData['type']) {
            case 'object':
                if (in_array($data['template_id'], $GLOBALS['folder_templates'])) {
                    return 'icon-folder';
                }
                break;
            case 2:
                return 'icon-shortcut';//case
                break;

            case 'file':
                return Files::getIcon($data['name']);
                break;
            case 'task':
                if (@$d['status'] == 3) {
                    return 'icon-task-completed';
                }

                return 'icon-task';//task
                break;
            case 'email':
                return 'icon-mail';//Message (email)
                break;
        }

        return 'icon-none';
    }
}
