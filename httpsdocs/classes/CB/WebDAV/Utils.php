<?php

namespace CB\WebDAV;

class Utils{
    function __construct(){}

    static public function getNodeById($id){
        $o = new \CB\Objects\Object();
        return $o->load($id);
    }

    static public function getFileById($id){
        $file = new \CB\Objects\File($id);
        return $file->load();
    }


    public static function  getNodeContent($id, $myPath, $only = null){
        $s = new \CB\Search();
        $data = $s->search('pid:'.$id, 0, 9999, array('fq'=> array('dstatus:0'), 'sort'=> 'sort_name asc'));

        $array = array();
        foreach($data->response->docs as $item){
            if($item->template_id != \CB\CONFIG\DEFAULT_FILE_TEMPLATE)
                $array[] = array('id' => $item->id, 'name' => $item->name, 'template_id' => $item->template_id , 'path' => $myPath .WEBDAV_PATH_DELIMITER. $item->name) ;
        }

        foreach($data->response->docs as $item){
            if($item->template_id == \CB\CONFIG\DEFAULT_FILE_TEMPLATE)
                $array[] = array('id' => $item->id, 'name' => $item->name, 'template_id' => $item->template_id) ;
        }

        if($only != null){
            foreach($array as $k=>$item)
                if($item['id']!=$only) unset($array[$k]);
        }
        return $array;
    }

    static public function getPathFromId($id){
        $object = Utils::getNodeById($id);
        if(count($object) == 0) return null;
        if($object['dstatus'] != 0) return null;

        $pids = explode(',',$object['pids']);

        $array = array();
        foreach($pids as $pid){
            if($id == $pid) { continue; }

            $temp = Utils::getNodeById($pid);
            if($temp['id']!=1)
                $array[] = $temp['name'];
        }

        $result = "/";
        foreach($array as $item){
            $result .= $item.'/';
        }
        return $result;
    }

    static public function createDirectory($pid, $name){
        $item = array(
            'pid' => $pid
            ,'name' => $name
            ,'date' => date('Y-m-d')
            ,'cdate' => date('Y-m-d')."T00:00:00Z"
            ,'template_id' => \CB\CONFIG\DEFAULT_FOLDER_TEMPLATE
            ,'data' => array('_title'=>$name)
        );
        $temp = new \CB\Objects\Object();
        $temp = $temp->create($item);

        \CB\Solr\Client::runCron();

        return $temp;
    }

    static public function createCaseboxFile($pid, $name, $data = null){
        $path = \CB\INCOMMING_FILES_DIR.$name;
        file_put_contents($path, $data);

        $param = array(
            'pid' => $pid
            ,'title' => $name
            ,'localFile' => $path
            ,'owner' => $_SESSION['user']['id']
            ,'tmplId' => \CB\CONFIG\DEFAULT_FILE_TEMPLATE
            ,'date' =>  date('Y-m-d')."T00:00:00Z"
            ,'fileExistAction' => 'replace'
        );
        $fl = new \CB\Api\Files();
        $fl->upload($param);

        \CB\Solr\Client::runCron();
    }

    static public function log($name){
        $f = fopen('webdav.log','a');
        fwrite($f, $name."\n");
        fclose($f);
    }

    static public function renameObject($id, $name){
        $file = new \CB\Objects\File();
        $file->load($id);
        $file->setData(array('name'=> $name));
        $file->update();

        \CB\Solr\Client::runCron();
    }

    static public function deleteObject($id){
        $file = new \CB\Objects\Object($id);
        $file->delete();

        \CB\Solr\Client::runCron();
    }

}