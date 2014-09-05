<?php
namespace CB\WebDAV;

class File extends \Sabre\DAV\FS\Node implements \Sabre\DAV\IFile
{
    private $id;
    private $myPath;
    private $parent;
    private $objectData;

    public function __construct($myPath, $id, &$parent = null, $objectData = null)
    {
        $this->id = $id;
        $this->myPath = $myPath;
        $this->parent = $parent;

        // $this->object = Utils::getFileById($id);
        $this->objectData = is_null($objectData)
            ? Utils::getFileById($id)
            : $objectData;
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
        return fopen(\CB\Config::get('files_dir') . $this->objectData['content_path'], 'r');
    }

    public function getSize()
    {
        return $this->objectData['size'];
    }

    public function getETag()
    {
        return $this->objectData['md5'];
    }

    public function getLastModified()
    {
        return ( empty($this->objectData['udate'])
            ? $this->objectData['cdate']
            : $this->objectData['udate']
        );
    }

    public function getCreationDate()
    {
        return $this->objectData['cdate'];
    }

    public function getContentType()
    {
        return $this->objectData['type'];
    }
}
