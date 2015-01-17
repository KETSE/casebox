<?php

namespace CB\WebDAV;

use
    Sabre\DAV,
    Sabre\HTTP\URLUtil;

/**
 * Base node-class
 *
 * The node class implements the method used by both the File and the Directory classes
 *
 * @copyright Copyright (C) 2014 KETSE (https://www.ketse.com/).
 * @author Oleg Burlaca (http://www.burlaca.com/)
 * @license https://www.casebox.org/license/ AGPLv3
 */
abstract class Node implements DAV\INode {

    /**
     * The path to the current node
     *
     * @var string
     */
    protected $path;

    /**
     * The CB nodeId
     *
     * @var string
     */
    public $nodeId;

    /**
     * Sets up the node, expects a full path name
     *
     * @param string $path
     */
    public function __construct($path) {

        $this->path = $path;

    }



    /**
     * Returns the name of the node
     *
     * @return string
     */
    public function getName() {

        list(, $name)  = URLUtil::splitPath($this->path);
        return $name;

    }

    /**
     * Renames the node
     *
     * @param string $name The new name
     * @return void
     */
    public function setName($name) {

        list($parentPath, ) = URLUtil::splitPath($this->path);
        list(, $newName) = URLUtil::splitPath($name);

        $newPath = $parentPath . '/' . $newName;

        Utils::renameObject($this->nodeId, $name);
        // rename($this->path,$newPath);

        $this->path = $newPath;

    }



    /**
     * Returns the last modification time, as a unix timestamp
     *
     * @return int
     */
    public function getLastModified() {
        $t = empty($this->cbNode['udate'])
                 ? $this->cbNode['cdate']     // Creation date
                 : $this->cbNode['udate'];    // Update date

        $dttm = new \DateTime($t);
        // error_log("getLastModified: " . $dttm->getTimestamp());

        return $dttm->getTimestamp();

        // return filemtime($this->path);
    }

}

