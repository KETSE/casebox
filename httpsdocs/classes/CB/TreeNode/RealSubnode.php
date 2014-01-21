<?php
namespace CB\TreeNode;

use CB\DB;

class RealSubnode extends Base
{
    protected function acceptedPath()
    {
        $p = &$this->path;
        if (empty($p)) {
            return false;
        }

        if (($this->lastNode->getId() != $this->config['pid']) ||
            (get_class($this->lastNode) == get_class($this))
        ) {
            return false;
        }

        return true;
    }

    public function getChildren(&$pathArray, $requestParams)
    {
        $rez = array();
        $this->path = $pathArray;
        $this->lastNode = $pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;

        if (!$this->acceptedPath()) {
            return;
        }
        /* should start with path check and see if child request is for a real db node*/

        $rez = array(
            'data' => array(
                array(
                    'name' => $this->config['title']
                    ,'id' => $this->getId($this->config['realNodeId'])
                    ,'iconCls' => 'icon-folder'
                    ,'has_childs' => true
                )
            )
        );

        return $rez;
    }

    // public function getId($id = null)
    // {
    //     if (is_null($id)) {
    //         $id = $this->id;
    //     }

    //     return $id;
    // }

    public function getName()
    {
        return $this->config['title'];
    }
}
