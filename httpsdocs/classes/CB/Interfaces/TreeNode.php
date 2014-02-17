<?php
namespace CB\Interfaces;

/**
 * Interface for tree nodes
 */

interface TreeNode
{
    /**
     * return child nodes
     * @return array an array of treeNodes object instances
     */
    public function getChildren(&$pathArray, $requestParams);

    /**
     * get node name
     * @return varchar
     */
    public function getName();

    /**
     * get node configuration
     * @return array set of properties
     */
    public function getConfig();

    /**
     * get parent node
     * @return object | null
     */
    public function getParent();

    /**
     * get depth
     * @return int
     */
    public function getDepth();

    /**
     * check if a node has children
     * @return int
     */
    public function hasChildren();
}
