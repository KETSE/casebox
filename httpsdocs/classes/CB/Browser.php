<?php
namespace CB;

use CB\Path;

class Browser
{
    protected $path = [];
    protected $treeNodeConfigs = array();
    protected $treeNodeGUIDConfigs = array();
    protected $treeNodeClasses = array();

    public function getChildren($p)
    {
        $rez = array();

        //unset restricted query params from user input
        unset($p['fq']);

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

        //check if user have changed the row limit in grid
        if (!empty($p['setMaxRows']) && !empty($p['rows'])) {
            User::setGridMaxRows($p['rows']);
        }

        //the navigation goes from search results. We should get the real path of the node
        if (!empty($p['lastQuery']) && empty($p['query'])) {
            while (substr($path, -1) == '/') {
                $path = substr($path, 0, strlen($path) -1);
            }
            $a = explode('/', $path);
            if (!empty($a) && is_numeric($a[sizeof($a)-1])) {
                $p['path'] = @Path::getPath(array_pop($a))['path'];
            }
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
        if (empty($p['search'])) {
            if (empty($p['query'])) {
                $this->treeNodeConfigs = Config::get('treeNodes');
            }
            if (empty($this->treeNodeConfigs)) {
                $this->treeNodeConfigs = array('Dbnode' => array());
            }

        } else {
            $this->treeNodeConfigs = array('SearchResults' => $p['search']);
            $path = Path::getGUID('SearchResults').'-';
        }

        $params = array(
            'params' => &$p,
            'plugins' => &$this->treeNodeConfigs
        );

        fireEvent('treeInitialize', $params);

        // array of all available classes defined in treeNodes
        // used to check if any class should add its nodes based
        // on last node from current path
        $this->treeNodeClasses = Path::getNodeClasses($this->treeNodeConfigs);

        foreach ($this->treeNodeClasses as &$nodeClass) {
            $cfg = $nodeClass->getConfig();
            $this->treeNodeGUIDConfigs[$cfg['guid']] = $cfg;
        }

        $this->path = Path::createNodesPath($path, $this->treeNodeGUIDConfigs);

        //set path and input params for last node
        //because iterating each class and requesting children can
        //invoke a search that will use last node to get facets and DC
        if (!empty($this->path)) {
            $lastNode = $this->path[sizeof($path) - 1];
            $lastNode->path = $this->path;
            $lastNode->requestParams = $this->requestParams;
        }

        Cache::set('current_path', $this->path);

        $this->collectAllChildren();

        $this->prepareResults($this->data);

        $rez = array(
            'success' => true
            ,'pathtext' => $this->getPathText($p)
            ,'folderProperties' => $this->getPathProperties($p)
            ,'data' => $this->data
            ,'total' => $this->total
            ,'page' => @$p['page']
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
        if (!empty($this->view)) {
            $rez['view'] = &$this->view;
        }
        if (!empty($this->DC)) {
            $rez['DC'] = &$this->DC[0];
        }
        if (!empty($this->sort)) {
            $rez['sort'] = &$this->sort;
        }
        if (!empty($this->group)) {
            $rez['group'] = &$this->group;
        }

        return $rez;

    }

    protected function getPathText()
    {
        $rez = array();
        if (empty($this->path)) {
            return '/';
        }

        foreach ($this->path as $n) {
            $rez[] = str_replace('/', '&#47;', $n->getName());
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

            $rez['name'] = @Util\adjustTextForDisplay($rez['name']);
            $rez['path'] = '/'.implode('/', $idsPath);
            $rez['menu'] = $this->path[sizeof($this->path) - 1]->getCreateMenu();

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

            //merging all returned records into a single array
            if (!empty($rez['data'])) {
                $this->data = array_merge($this->data, $rez['data']);

                //set display columns and sorting if present
                if (isset($rez['DC'])) {
                    $this->DC[] = $rez['DC'];
                }

                if (isset($rez['sort'])) {
                    $this->sort = $rez['sort'];
                }

                if (isset($rez['group'])) {
                    $this->group = $rez['group'];
                }
            }

            //set viewif present
            if (isset($rez['view'])) {
                $this->view = $rez['view'];
            }

            //calc totals accordingly
            if (isset($rez['total'])) {
                $this->total += $rez['total'];
            } elseif (!empty($rez['data'])) {
                $this->total += sizeof($rez['data']);
            }

            //return last facets and pivot if present
            if (!empty($rez['facets'])) {
                $this->facets = $rez['facets'];
            }
            if (!empty($rez['pivot'])) {
                $this->pivot = $rez['pivot'];
            }

            //if its debug host - search params will be also returned
            if (isset($rez['search'])) {
                $this->search[] = $rez['search'];
            }
        }
    }

    protected function sortResult()
    {
        //sorting nodes;
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

        //unset restricted query params from user input
        unset($p['fq']);

        $fieldConfig = array();
        // get field config from database
        if (!empty($p['fieldId']) && is_numeric($p['fieldId'])) {
            $res = DB\dbQuery(
                'SELECT cfg FROM templates_structure WHERE id = $1',
                $p['fieldId']
            ) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $fieldConfig = json_decode($r['cfg'], true);

                //set "fq" param from database (dont trust user imput)
                if (!empty($fieldConfig['fq'])) {
                    $p['fq'] = $fieldConfig['fq'];
                }
            }
            $res->close();
        }

        if (!empty($p['source'])) {
            if (is_array($p['source'])) { // a custom source
                $rez = array();

                if (empty($p['fieldId'])) {
                    return $rez;
                }

                //get custom method from config
                if (empty($fieldConfig['source']['fn'])) {
                    return $rez;
                }

                $method = explode('.', $fieldConfig['source']['fn']);
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
        if (!empty($fieldConfig['scope'])) {
            $scope = $fieldConfig['scope'];
            switch ($scope) {
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
                        $p['pids'] = $p['objectId'];
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
                    $pids = Util\toNumericArray($scope);
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

        $p['fl'] = 'id,name,type,template_id,status';
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

        //increase number of returned items
        if (empty($p['rows'])) {
            $p['rows'] = 50;
        }

        $search = new Search();

        // temporary: Don't use permissions for Objects fields
        // it can be later reinforced per field in config
        $p['skipSecurity'] = true;
        $rez = $search->query($p);

        foreach ($rez['data'] as &$doc) {
            $res = DB\dbQuery(
                'SELECT cfg
                FROM tree
                WHERE id = $1 AND
                    cfg IS NOT NULL',
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

        if (empty($rez['DC'])) {
            $rez['DC'] = array(
                'name' => array(
                    'solr_column_name' => "name"
                    ,'idx' => 0
                )
            );
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
            throw new \Exception(L\get('Access_denied'));
        }

        /* find default folder name */
        $newFolderName = L\get('NewFolder');
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
            $newFolderName = L\get('NewFolder').' ('.$i.')';
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
                ,Config::get('default_folder_template')
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
                throw new \Exception(L\get('Access_denied'));
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

    public function restore($paths)
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
                throw new \Exception(L\get('Access_denied'));
            }
            $ids[] = intval($id);
        }
        if (empty($ids)) {
            return array('success' => false);
        }

        /* before deleting we should check security for specified paths and all children */

        /* if access is granted then setting dstatus=0 for specified ids
        all their children /**/

        foreach ($ids as $id) {
            $obj = Objects::getCustomClassByObjectId($id);
            $obj->restore();
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
            throw new \Exception(L\get('Access_denied'));
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

        switch (Objects::getType($id)) {
            case 'file':
                $p['name']  = Purify::filename($p['name']);

                DB\dbQuery(
                    'UPDATE files
                    SET name = $1
                    WHERE id = $2',
                    array(
                        $p['name']
                        ,$id
                    )
                ) or die(DB\dbQueryError());

                break;

            case 'task':
                DB\dbQuery(
                    'UPDATE tasks
                    SET title = $1
                    WHERE id = $2',
                    array(
                        $p['name']
                        ,$id
                    )
                ) or die(DB\dbQueryError());

                break;
        }

        /*updating renamed document into solr directly (before runing background cron)
            so that it'll be displayed with new name without delay*/
        $solrClient = new Solr\Client();
        $solrClient->updateTree(array('id' => $id));

        //running background cron to index other nodes
        $solrClient->runBackgroundCron();

        $p['name'] = htmlspecialchars($p['name'], ENT_COMPAT);

        //get pid
        $pid = null;
        $res = DB\dbQuery(
            'SELECT pid FROM tree WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $pid = $r['pid'];
        }
        $res->close();

        return array(
            'success' => true
            ,'data' => array(
                'id' => $id
                ,'pid' => $pid
                ,'newName' => $p['name']
            )
        );
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
            $newName = L\get('CopyOf').' '.$name.( ($i > 0) ? ' ('.$i.')' : '').$ext;
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
        $incommingFilesDir = Config::get('incomming_files_dir');

        if (!file_exists($incommingFilesDir)) {
            @mkdir($incommingFilesDir, 0777, true);
        }

        $files = new Files();

        /* clean previous unhandled uploads if any */
        $a = $files->getUploadParams();
        if (($a !== false) && !empty( $a['files'] )) {
            @unlink($incommingFilesDir . $_SESSION['key']);
            $files->removeIncomingFiles($a['files']);
        }
        /* end of clean previous unhandled uploads if any */

        $F = &$_FILES;
        if (empty($p['pid'])) {
            return array('success' => false, 'msg' => L\get('Error_uploading_file'));
        }
        $p['pid'] = Path::detectRealTargetId($p['pid']);

        if (empty($F)) { //update only file properties (no files were uploaded)

            return $files->updateFileProperties($p);
        } else {
            foreach ($F as $k => $v) {
                $F[$k]['name'] = Purify::filename(@$F[$k]['name']);
            }
        }

        if (!Objects::idExists($p['pid'])) {
            return array(
                'success' => false,
                'msg' => L\get('TargetFolderDoesNotExist')
            );
        }

        /*checking if there is no upload error (for any type of upload: single, multiple, archive) */
        foreach ($F as $fn => $f) {
            if (!in_array($f['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
                return array(
                    'success' => false,
                    'msg' => L\get('Error_uploading_file') .': '.$f['error']
                );
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
                $files->moveUploadedFilesToIncomming($F) or die('cannot move file to incomming dir');
                break;
        }

        $p['existentFilenames'] = $files->getExistentFilenames($F, $p['pid']);
        $p['files'] = &$F;

        if (!empty($p['existentFilenames'])) {
            //check if can write target file
            if (!Security::canWrite($p['existentFilenames'][0]['existentFileId'])) {
                return array('success' => false, 'msg' => L\get('Access_denied'));
            }

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
                        L\get('FilenameExistsInTarget')
                    )
                    : $p['existentFilenames'][0]['msg'];
                //$rez['filename'] = $p['existentFilenames'][0]['name'];
                $rez['suggestedFilename'] = $p['existentFilenames'][0]['suggestedFilename'];
            } else {
                $rez['msg'] = L\get('SomeFilenamesExistsInTarget');
            }

            return $rez;

        } else {
            //check if can write in target folder
            if (!Security::canWrite($p['pid'])) {
                return array('success' => false, 'msg' => L\get('Access_denied'));
            }
        }

        $files->storeFiles($p); //if everithing is ok then store files
        Solr\Client::runCron();

        $rez = array(
            'success' => true,
            'data' => array('pid' => $p['pid'])
        );

        $files->attachPostUploadInfo($F, $rez);

        return $rez;
    }

    /**
     * subscribe to notifications for a tree item
     * @param  array      $p
     * @return Ext.Direct responce
     */
    public function subscribe($p)
    {
        $rez = array('success' => true);

        if (empty($p['id']) || !is_numeric($p['id'])) {
            return array('success' => false);
        }

        if (!Security::canRead($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        DB\dbQuery(
            'INSERT INTO user_subscriptions
            (user_id, object_id, recursive)
            VALUES ($1, $2, $3)
            ON DUPLICATE KEY UPDATE
            sdate = CURRENT_TIMESTAMP',
            array(
                $_SESSION['user']['id']
                ,$p['id']
                ,empty($p['recursive']) ? 0 : 1
            )
        ) or die(DB\dbQueryError());

        return $rez;
    }

    /**
     * unsubscribe from notifications for a tree item
     * @param  array      $p
     * @return Ext.Direct responce
     */
    public function unsubscribe($p)
    {
        $rez = array('success' => true);

        if (empty($p['id']) || !is_numeric($p['id'])) {
            return array('success' => false);
        }

        DB\dbQuery(
            'DELETE FROM  user_subscriptions
            WHERE user_id = $1 AND object_id = $2',
            array(
                $_SESSION['user']['id']
                ,$p['id']
            )
        ) or die(DB\dbQueryError());

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
                $a['newName'] = Purify::filename($p['newName']);
                //check if the new name does not also exist
                if (empty($a['response'])) {
                    return array('success' => false, 'msg' => L\get('FilenameCannotBeEmpty'));
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
                        ,'msg' => str_replace('{filename}', '"'.$a['newName'].'"', L\get('FilenameExistsInTarget'))
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
            $name = 'Link';

            $name = @Search::getObjectNames($p['id'])[$p['id']];

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
        $ids = Util\toNumericArray($ids);

        $rez = array('success' => true, 'data' => $ids);

        if (empty($ids)) {
            return $rez;
        }

        //check if user has rights to take ownership on each object
        foreach ($ids as $id) {
            if (!Security::canTakeOwnership($id)) {
                throw new \Exception(L\get('Access_denied'));
            }
        }

        //set the owner
        DB\dbQuery(
            'UPDATE tree
            SET oid = $1
                ,uid = $1
            WHERE id IN (' . implode(',', $ids) . ')
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
                Config::get('default_folder_template')
            ) or die( DB\dbQueryError() );

            $id = DB\dbLastInsertId();

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

        for ($i=0; $i < sizeof($data); $i++) {
            $d = &$data[$i];
            if (isset($d['id']) && empty($d['nid'])) {
                $d['nid'] = $d['id'];
                unset($d['id']);
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

            if ($d['system']) {
                $d['name'] = L\getTranslationIfPseudoValue($d['name']);
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
                if (in_array($data['template_id'], Config::get('folder_templates'))) {
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
