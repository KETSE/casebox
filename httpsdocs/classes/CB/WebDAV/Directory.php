<?php

namespace CB\WebDAV;
use Sabre\DAV;

/**
 * Directory class
 *
 * @copyright Copyright (C) 2014 KETSE (https://www.ketse.com/).
 * @author Oleg Burlaca (http://www.burlaca.com/)
 * @license https://www.casebox.org/license/ AGPLv3
 */
class Directory extends Node implements DAV\ICollection, DAV\IQuota {

    public $parentDir = null;
    public $content = null;    // an array of children nodes
    protected $path;    // the path to current node
    // public $nodeId;   // the ID of the node
    public $cbNode;   // CB Object of Directory
    private $env;



    /**
     *  $p = [
     *    'nodeId' => 1,           // the ID of the Directory
     *    'parentDir' => null,     // the reference to the parentDirectory object
     *    'level' => 0..n
     *   ];
     *
     *   $env = [
     *       'mode'  => edit | browse
     *       'nodeId' => 18232  // directly edit this node if mode=edit
     *   ]
     */

    public function __construct($path, $p = null, $env = null) {

        $this->path = $path;
        $this->nodeId = $p['nodeId'];
        $this->parentDir = $p['parentDir'];
        $this->env = $env;

        // a direct request for a File?
        $fileId = $env['mode'] == 'edit' ? $fileId = $env['nodeId']
                                         : $fileId = null;

        $this->content = Utils::getChildren($this->nodeId, $this->path, $env, $fileId);


        // fully loading the CB Node for Directory
        $this->cbNode = Utils::getNodeById($this->nodeId);

        // error_log("WebDAV/Directory.construct(" . $path . ")");
    }



    /**
     * Creates a new file in the directory
     *
     * Data will either be supplied as a stream resource, or in certain cases
     * as a string. Keep in mind that you may have to support either.
     *
     * After successful creation of the file, you may choose to return the ETag
     * of the new file here.
     *
     * The returned ETag must be surrounded by double-quotes (The quotes should
     * be part of the actual string).
     *
     * If you cannot accurately determine the ETag, you should not return it.
     * If you don't store the file exactly as-is (you're transforming it
     * somehow) you should also not return an ETag.
     *
     * This means that if a subsequent GET to this new file does not exactly
     * return the same contents of what was submitted here, you are strongly
     * recommended to omit the ETag.
     *
     * @param string $name Name of the file
     * @param resource|string $data Initial payload
     * @return null|string
     */
    public function createFile($name, $data = null) {

        Utils::createCaseboxFile($this->nodeId, $name, $data);
        // $newPath = $this->path . '/' . $name;
        // file_put_contents($newPath,$data);
    }


    /**
     * Creates a new subdirectory
     *
     * @param string $name
     * @return void
     */
    public function createDirectory($name) {
        $env = $this->env;
        if ($env['mode'] == 'edit') {
            return null;  // no folders when in Edit mode
        }

        return Utils::createDirectory($this->nodeId, $name);
    }


    /**
     * Returns a specific child node, referenced by its name
     *
     * This method must throw DAV\Exception\NotFound if the node does not
     * exist.
     *
     * @param string $name
     * @throws DAV\Exception\NotFound
     * @return DAV\INode
     */
    public function getChild($name) {
        // error_log("WebDAV/Directory.getChild(" . $name . ")");

        $childPath = $this->path . '/' . $name;
        $fileTmpl =  \CB\Config::get('default_file_template');

        foreach ($this->content as $item) {
            if ($item['name'] == $name) {
                $p = [
                    'nodeId'    => $item['id'],
                    'parentDir' => $this
                ];

                if ($item['template_id'] != $fileTmpl) {
                    return new Directory($childPath, $p, $this->env);
                } else {
                    return new File($childPath, $p, $this->env);
                }
            }
        }

        throw new \Sabre\DAV\Exception\NotFound('File with name: ' . $childPath. ' could not be found');
    }


    /**
     * Returns an array with all the child nodes
     *
     * @return DAV\INode[]
     */
    public function getChildren() {
        // error_log("WebDAV/Directory.getChildren()");

        $children = array();

        // Loop through the directory, and create objects for each node
        // NOTE: can be refactored a bit
        foreach ($this->content as $node) {
            $children[] = $this->getChild($node['name']);
        }

        return $children;
    }


    /**
     * Checks if a child exists.
     *
     * @param string $name
     * @return bool
     */
    public function childExists($name) {

       foreach ($this->content as $item) {
            if ($item['name'] == $name) {
                return true;
            }
        }

        return false;
    }


    /**
     * Deletes all files in this directory, and then itself
     *
     * @return void
     */
    public function delete() {

        // don't delete folders when in 'edit' mode
        if ($this->env['mode'] == 'edit') {
            return ;
        }

        Utils::deleteObject($this->nodeId);

    }

    /**
     * Returns available diskspace information
     *
     * @return array
     */
    public function getQuotaInfo() {

        // NOTE: to replace by CB calculation of free space for {core}
        return array(
            disk_total_space($this->path)-disk_free_space($this->path),
            disk_free_space($this->path)
            );

    }

}

