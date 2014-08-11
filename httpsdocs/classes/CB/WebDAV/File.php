<?php

namespace CB\WebDAV;

use Sabre\DAV;

/**
 * File class
 *
 * @copyright Copyright (C) 2014 KETSE (https://www.ketse.com/).
 * @author Oleg Burlaca (http://www.burlaca.com/)
 * @license https://www.casebox.org/license/ AGPLv3
 */
class File extends Node implements DAV\IFile {

    // private $nodeId;
    private $parentDir;


    public function __construct($path, $p = null, $env = null)
    {
        $this->nodeId = $p['nodeId'];
        $this->path = $path;
        $this->parentDir = $p['parentDir'];


        // fully loading the CB Node as File
        $this->cbNode = Utils::getFileById($this->nodeId);

        // error_log("WebDAV/File.construct: " . $path); // print_r($this->cbNode, true)
    }



    /**
     * Updates the data
     *
     * @param resource $data
     * @return void
     */
    public function put($data) {

        // create a 'new' file in parentDir, it will overwrite existing file
        Utils::createCaseboxFile($this->parentDir->nodeId, $this->cbNode['name'], $data);
    }

    /**
     * Returns the data
     *
     * @return string
     */
    public function get() {
        $filename = \CB\Config::get('files_dir') .
                    $this->cbNode['content_path'] . '/' .
                    $this->cbNode['content_id'];

        // error_log("get file: " . $filename);

        return fopen($filename, 'r');
    }

    /**
     * Delete the current file
     *
     * @return void
     */
    public function delete() {

        // unlink($this->path);
        Utils::deleteObject($this->nodeId);

    }

    /**
     * Returns the size of the node, in bytes
     *
     * @return int
     */
    public function getSize() {

        return $this->cbNode['size'];

    }

    /**
     * Returns the ETag for a file
     *
     * An ETag is a unique identifier representing the current version of the file. If the file changes, the ETag MUST change.
     * The ETag is an arbitrary string, but MUST be surrounded by double-quotes.
     *
     * Return null if the ETag can not effectively be determined
     *
     * @return mixed
     */
    public function getETag() {

        // MD5: this approach doesn't work well with Word at least
        // $etag = '"' . $this->cbNode['md5'] . '"';
        // return $etag;

        return null;
    }

    public function setName($name) {
        Utils::renameObject($this->nodeId, $name);
    }


    /**
     * Returns the mime-type for a file
     *
     * If null is returned, we'll assume application/octet-stream
     *
     * @return mixed
     */
    public function getContentType() {

        return $this->cbNode['type'];

    }

}

