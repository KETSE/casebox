<?php
namespace CB\WebDAV;

class File extends \Sabre\DAV\FS\Node implements \Sabre\DAV\IFile {

    private $id;
    private $myPath;
    private $parent;
    private $object;
    private $realPath;

    function __construct($myPath, $id, &$parent = null) {

        $this->id = $id;
        $this->myPath = $myPath;
        $this->parent = $parent;
        $this->object = Utils::getFileById($id);
        $this->realPath = \CB\FILES_DIR.$this->object['content_path'].'/'.$this->object['content_id'];
    }

    function getName() {
        return basename($this->myPath);
    }

    function setName($name) {
        Utils::renameObject($this->id, $name);
    }

    function delete(){
        Utils::deleteObject($this->id);
    }

    function put($data) {
        Utils::createCaseboxFile($this->parent->id, $this->object['name'], $data);
    }

    function get() {
        return fopen($this->realPath,'r');
    }

    function getSize() {
        return filesize($this->realPath);
    }

    function getETag() {
        return md5_file($this->realPath);
    }

    function getLastModified(){
        return is_null($this->object['udate']) ? $this->object['cdate'] : $this->object['udate'];
    }

    function getContentType(){

    }
}

