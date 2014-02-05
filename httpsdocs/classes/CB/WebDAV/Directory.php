<?php
namespace CB\WebDAV;

class Directory extends \Sabre\DAV\FS\Node implements \Sabre\DAV\ICollection{

    private $myPath;

    public $id;
    public $content;
    private $object;
    private $only;

    function __construct($myPath, $parent = null, $only = null) {
        $this->only = $only;

        if($myPath == "Home"){
            $this->id = 1;
        }else{
            foreach($parent->content as $node){
                if($node['path'] == $myPath){
                    $this->id = $node['id'];
                    break;
                }
            }
        }

        $this->myPath = $myPath;
        $this->content = Utils::getNodeContent($this->id, $this->myPath);
        $this->object = Utils::getNodeById($this->id);
    }

    function getChildren() {
        $children = array();
        // Loop through the directory, and create objects for each node

        foreach($this->content as $node) {
            if($this->only == null)
                $children[] = $this->getChild($node['name']);
            else
                if($this->only[0] == $node['id'])
                    $children[] = $this->getChild($node['name']);
        }
        return $children;
    }

    function getChild($name) {
        $path = $this->myPath . WEBDAV_PATH_DELIMITER . $name;
        foreach($this->content as $item){
            if($item['name'] == $name){
                if($item['template_id'] != \CB\CONFIG\DEFAULT_FILE_TEMPLATE){
                    return new Directory($path, $this);
                }else{
                    return new File($path, $item['id'], $this);
                }
            }
        }

        throw new \Sabre\DAV\Exception\NotFound('The file with name: ' . $name . ' could not be found');
    }

    function childExists($name) {
        foreach($this->content as $item){
            if($item['name'] == $name){
                return true;
            }
        }
        return false;
    }

    function getName() {
        return basename($this->myPath);
    }

    function setName($name) {
        Utils::renameObject($this->id, $name);
    }

    function getLastModified(){
        return strtotime((is_null($this->object['udate'])) ? $this->object['cdate'] : $this->object['udate']);
    }

    function createDirectory($name){
        return Utils::createDirectory($this->id, $name);
    }

    function createFile($name, $data = null){
        Utils::createCaseboxFile($this->id, $name, $data);
    }

    function delete(){
        Utils::deleteObject($this->id);
    }
}

