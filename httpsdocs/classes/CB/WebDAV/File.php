<?php
namespace CB\WebDAV;

class File extends \Sabre\DAV\FS\Node implements \Sabre\DAV\IFile
{
    private $id;
    private $myPath;
    private $parent;
    private $objectData;
    // private $realPath;

    public function __construct($myPath, $id, &$parent = null, $objectData = null)
    {
        $this->id = $id;
        $this->myPath = $myPath;
        $this->parent = $parent;

        // $this->object = Utils::getFileById($id);
        $this->objectData = is_null($objectData)
            ? Utils::getFileById($id)
            : $objectData;

        // $this->realPath = \CB\FILES_DIR.$this->object['content_path'] . '/' . $this->object['content_id'];
    }

    public function getName()
    {
        return basename($this->myPath);
    }

    public function setName($name)
    {
        Utils::renameObject($this->id, $name);
    }

    public function delete()
    {
        Utils::deleteObject($this->id);
    }

    public function put($data)
    {
        Utils::createCaseboxFile($this->parent->id, $this->objectData['name'], $data);
    }

    public function get()
    {
        return fopen($this->objectData['content_path'], 'r');
        // return fopen($this->realPath, 'r');
    }

    public function getSize()
    {
        return $this->objectData['size'];
        // return filesize($this->realPath);
    }

    public function getETag()
    {
        return $this->objectData['md5'];
        // return md5_file($this->realPath);
    }

    public function getLastModified()
    {
        return ( empty($this->objectData['udate'])
            ? $this->objectData['cdate']
            : $this->objectData['udate']
        );
        // return is_null($this->object['udate']) ? $this->object['cdate'] : $this->object['udate'];
    }

    public function getContentType()
    {
    }
}
