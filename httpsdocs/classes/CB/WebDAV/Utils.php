<?php

namespace CB\WebDAV;

class Utils
{
    public function __construct()
    {

    }

    public static function getNodeById($id)
    {
        $o = new \CB\Objects\Object();

        return $o->load($id);
    }

    public static function getFileById($id)
    {
        $file = new \CB\Objects\File($id);

        return $file->load();
    }

    public static function getNodeContent($id, $myPath, $onlyFileId = null)
    {
        $s = new \CB\Search();

        $query = 'pid:'.$id;

        $params = array(
            'fl' => 'id,name,template_id,date,cdate,udate,size'
            ,'fq'=> array(
                'dstatus:0'
                ,'system:[0 TO 1]'
            )
            ,'sort' => 'sort_name asc'
        );

        if (is_array($onlyFileId)) {
            $params['fq'][] = 'id:('.implode(' OR ', $onlyFileId).')';
        }

        $data = $s->search(
            $query,
            0,
            9999,
            $params
        );

        $fileIds = array();
        $array = array();
        foreach ($data->response->docs as $item) {
            $el = array(
                'id' => $item->id
                ,'name' => $item->name
                ,'template_id' => $item->template_id
                ,'size' => $item->size
                ,'cdate' => $item->cdate
                ,'udate' => $item->udate
                ,'path' => $myPath . DIRECTORY_SEPARATOR . $item->name
            );

            if ($item->template_id != \CB\CONFIG\DEFAULT_FILE_TEMPLATE) {
                $el['path'] = $myPath .DIRECTORY_SEPARATOR. $item->name;
            } else {
                $fileIds[] = $el['id'];
            }
            $array[$el['id']] = $el;
        }

        /* select additional info required for files */
        if (!empty($fileIds)) {
            $res = \CB\DB\dbQuery(
                'SELECT
                    f.id
                    ,CONCAT(c.path, \'/\', f.content_id) `content_path`
                    ,c.md5
                    ,c.type
                FROM files f
                LEFT JOIN files_content c ON f.content_id = c.id
                WHERE f.id in (' . implode(',', $fileIds) . ')'
            ) or die(DB\dbQueryError());

            while ($r = $res->fetch_assoc()) {
                $array[$r['id']] = array_merge($array[$r['id']], $r);
            }
            $res->close();
        }

        return $array;
    }

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

    public static function createDirectory($pid, $name)
    {
        $item = array(
            'pid' => $pid
            ,'name' => $name
            // date column is not present in template for folders
            // ,'date' => date('Y-m-d')
            ,'template_id' => \CB\CONFIG\DEFAULT_FOLDER_TEMPLATE
            ,'data' => array('_title'=>$name)
        );
        $temp = new \CB\Objects\Object();
        $temp = $temp->create($item);

        \CB\Solr\Client::runCron();

        return $temp;
    }

    public static function createCaseboxFile($pid, $name, $data = null)
    {
        $path = \CB\INCOMMING_FILES_DIR.$name;
        file_put_contents($path, $data);

        $param = array(
            'pid' => $pid
            ,'title' => $name
            ,'localFile' => $path
            ,'owner' => $_SESSION['user']['id']
            ,'tmplId' => \CB\CONFIG\DEFAULT_FILE_TEMPLATE
            ,'fileExistAction' => 'newversion'
        );
        $fl = new \CB\Api\Files();
        $fl->upload($param);

        \CB\Solr\Client::runCron();
    }

    public static function log($name)
    {
        $f = fopen('webdav.log', 'a+');
        fwrite($f, $name."\n");
        fclose($f);
    }

    public static function renameObject($id, $name)
    {
        $file = new \CB\Objects\File();
        $data = $file->load($id);

        $data['name'] = $name;
        $data['data']['_title'] = $name;

        $file->setData($date);
        $file->update();

        \CB\Solr\Client::runCron();
    }

    public static function deleteObject($id)
    {
        $file = new \CB\Objects\Object($id);
        $file->delete();

        \CB\Solr\Client::runCron();
    }
}
