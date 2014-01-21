<?php
namespace CB\TreeNode;

class Base implements \CB\Interfaces\TreeNode
{
    protected $config;
    public $guid = null;
    public $id = null;

    public function __construct ($config = array(), $id = null)
    {
        if (!empty($config['pid']) && ($config['pid'] == 'root')) {
            $config['pid'] = \CB\Browser::getRootFolderId();
        }

        if (!empty($config['realNodeId']) && ($config['realNodeId'] == 'root')) {
            $config['realNodeId'] = \CB\Browser::getRootFolderId();
        }

        $this->config = $config;
        $this->guid = $config['guid'];
        $this->id = $id;
    }

    public function getChildren(&$pathArray, $requestParams)
    {
        return array();
    }

    public function getId($id = null)
    {
        if (is_null($id)) {
            $id = $this->id;
        }
        if (!empty($this->guid)) {
            $id = $this->guid.'-'.$id;
        }

        return $id;
    }

    public function getName()
    {
        return 'no name';
    }

    public function getData()
    {
        return array();
    }

    /**
     * get node configuration
     * @return array set of properties
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * get parent node
     * @return object | null
     */
    public function getParent()
    {

    }

    /**
     * get depth
     * @return int
     */
    public function getDepth()
    {

    }

    /**
     * check if a node has children
     * @return int
     */
    public function hasChildren()
    {

    }
}
