<?php

namespace CB\WebDAV;

class Utils
{
    public function __construct()
    {

    }

    /**
     *  loads CB Object by Id
     *  @param  int $id nodeId
     *  @return CBObject
     */
    public static function getNodeById($id)
    {
        $o = new \CB\Objects\Object();

        return $o->load($id);
    }

    /**
     *  similar to getNodeById, returns a CBFile
     *  @param  int $id FileId
     *  @return CBFile
     */
    public static function getFileById($id)
    {
        $file = new \CB\Objects\File($id);

        return $file->load();
    }

    /**
     *  returns SOLR results: nodeId children
     *  @param  [type] $id [description]
     *  @return [type]     [description]
     */
    public static function solrGetChildren($id, $fileId = null)
    {
        $s = new \CB\Search();

        $query = 'pid: ' . $id;

        // fetch only a single file
        if (@$fileId) {
            $query = 'id: ' . $fileId;
        }

        $params = array(
            'fl' => 'id, name, template_id, date, cdate, uid, udate, size'
            ,'fq'=> array(
                'dstatus: 0'
                ,'system: [0 TO 1]'
            )
            ,'sort' => 'sort_name asc'
        );

        $data = $s->search(
            $query,
            0,
            1000,      // return maximum 1000 nodes
            $params
        );

        return $data;
    }

    /**
     *  returns CB nodes as simple array
     *  @param  int $id          [description]
     *  @param  string $path     [description]
     *  @param  string $level    depth
     *  @param  ary $env
     *  @return [type]           [description]
     */
    public static function getChildren($id, $path, $env, $fileId)
    {
        // error_log('WebDAV/Utils.getChildren(' . $id . ',' . $path . ')');
        $defaultFileTemplate = \CB\Config::get('default_file_template');

        $data = Utils::solrGetChildren($id, $fileId);

        // process SOLR results into a simple array
        $fileIds = array();
        $ary = array();
        foreach ($data->response->docs as $item) {
            $el = array(
                'id' => $item->id
                ,'name' => $item->name
                ,'template_id' => $item->template_id
                ,'size' => $item->size
                ,'cdate' => $item->cdate
                ,'uid' => $item->uid     // the last user that updated node
                ,'udate' => $item->udate
                ,'path' => $path . DIRECTORY_SEPARATOR . $item->name
            );

            // PropertyStorage will use filename as path, without the 'edit-{nodeId}' folder
            if ($env['mode'] == 'edit') {
                $el['path'] = $item->name;
            }

            // remember Files: more properties will be fetched below
            if ($item->template_id == $defaultFileTemplate) {
                $fileIds[] = $el['id'];
            }

            $ary[$el['id']] = $el;
        }

        // fetch additional info required for files
        // !!! to be refactored using CB API

        // are there any files in Directory?
        if (! empty($fileIds)) {
            $res = \CB\DB\dbQuery(
                'SELECT
                    f.id
                    ,CONCAT(c.path, \'/\', f.content_id) `content_path`
                    ,c.md5
                    ,c.type
                FROM files f
                LEFT JOIN files_content c ON f.content_id = c.id
                WHERE f.id in (' . implode(',', $fileIds) . ')'
            );

            // append additional file info (content_path, MD5, type)
            while ($r = $res->fetch_assoc()) {
                $ary[$r['id']] = array_merge($ary[$r['id']], $r);
            }
            $res->close();
        }

        // save the nodes in Cache for later use in WebDAV\PropertyStorage (creationdate and other props)
        Utils::cacheNodes($ary);

        return $ary;
    }

    /**
     *  stores loaded nodes in 'DAVNodes' ary,
     *  later it's used in PropertyStoragePlugin to get 'creationdate'
     *  @param  [type] $ary [description]
     *  @return [type]      [description]
     */
    public static function cacheNodes($ary)
    {
        // initialize DAVNodes cache
        if (! \CB\Cache::exist('DAVNodes')) {
            \CB\Cache::set('DAVNodes', []);
        }
        $cachedNodes = \CB\Cache::get('DAVNodes');

        // store nodes in cache
        foreach ($ary as $node) {

            // remove '/'
            $path = str_replace('\\', '/', $node['path']);
            $path = trim($path, '/');
            //  use only '/' as separator


            $cachedNodes[$path] = $node;
        }

        \CB\Cache::set('DAVNodes', $cachedNodes);
    }


    public static function getParentNodeId($id)
    {
        $node = Utils::getNodeById($id);

        if ((count($node) == 0) or ($node['dstatus'] != 0)) {
            return null;
        }
        $pids = explode(',', $node['pids']);

        // error_log("getParentNodeId: " . print_r($pids, true));

        $pid = array_pop($pids);  // pids contains the node itself
        $pid = array_pop($pids);

        return $pid;
    }


    public static function createDirectory($pid, $name)
    {
        $item = array(
            'pid' => $pid
            ,'name' => $name
            // date column is not present in template for folders
            // ,'date' => date('Y-m-d')
            ,'template_id' => \CB\Config::get('default_folder_template')
            ,'data' => array('_title'=>$name)
        );
        $temp = new \CB\Objects\Object();
        $temp = $temp->create($item);

        \CB\Solr\Client::runCron();

        return $temp;
    }

    public static function createCaseboxFile($pid, $name, $data = null)
    {
        $path = \CB\Config::get('incomming_files_dir') . $name;
        file_put_contents($path, $data);

        $action = 'newversion';
        //check if file exists and its size is 0
        $id = \CB\Files::getFileId($pid, $name);
        if (!empty($id)) {
            if (\CB\Files::getSize($id) <= 1) {
                $action = 'replace';
            }
        }

        $param = array(
            'pid' => $pid
            ,'title' => $name
            ,'localFile' => $path
            ,'owner' => $_SESSION['user']['id']
            ,'tmplId' => \CB\Config::get('default_file_template')
            ,'fileExistAction' => $action
        );
        $fl = new \CB\Api\Files();
        $fl->upload($param);

        \CB\Solr\Client::runCron();
    }

    /**
     *  updates the '_title' of a CB node
     *  @param  int $id   nodeId
     *  @param  string $name newTitle
     *  @return bool    should return true on succes
     */
    public static function renameObject($id, $name)
    {
        $file = new \CB\Objects\File();
        $data = $file->load($id);

        $data['name'] = $name;
        $data['data']['_title'] = $name;

        $file->setData($data);
        $file->update();

        \CB\Solr\Client::runCron();
    }

    public static function deleteObject($id)
    {
        $node = new \CB\Objects\Object($id);
        $node->delete();

        \CB\Solr\Client::runCron();
    }
}

/*
    public static function getPathFromId($id)
    {
        $object = Utils::getNodeById($id);
        if (count($object) == 0) {
            return null;
        }
        if ($object['dstatus'] != 0) {
            return null;
        }

        $pids = explode(',', $object['pids']);

        $array = array();
        foreach ($pids as $pid) {
            if ($id == $pid) {
                continue;
            }

            $temp = Utils::getNodeById($pid);
            if ($temp['id'] != 1) {
                $array[] = $temp['name'];
            }
        }

        $result = "/";
        foreach ($array as $item) {
            $result .= $item.'/';
        }

        return $result;
    }
*/
