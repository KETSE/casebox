<?php
namespace CB\TreeNode;

use CB\Config;
use CB\Util;
use CB\Search;

class Dashboard extends Base
{

    public function getChildren(&$pathArray, $requestParams)
    {
        $this->path = $pathArray;
        $this->lastNode = @$pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;

        if (!$this->acceptedPath($pathArray, $requestParams)) {
            return;
        }

        $this->lastNodeDepth = $this->lastNode->getClassDepth();

        if (empty($this->lastNode) || ($this->lastNode->guid != $this->guid)) {
            $rez = $this->getRootNode();
        } else {
            $rez = $this->getChildNodes();
        }

        return $rez;
    }

    protected function getRootNode()
    {
        return array(
            'data' => array(
                array(
                    'name' => $this->getName('root')
                    ,'id' => $this->getId('root')
                    ,'iconCls' => Util\coalesce(@$this->config['iconCls'], 'icon-folder')
                    ,'cls' => 'tree-header'
                    ,'has_childs' => false
                )
            )
        );
    }

    /**
     * getChildNodes description
     * @return json responce
     */
    protected function getChildNodes()
    {
        $p = $this->requestParams;
        if ($p['view']['type'] != 'dashboard') {
            return array();
        }
        $rez = array(
            'data' => array()
        );

        $vc = $p['view'];
        $rp = $p;

        foreach ($this->subClasses as $k => &$class) {
            $path = array(&$class);

            if (isset($vc['items'][$k])) {
                $rp['view'] = $vc['items'][$k];
                $rp['facets'] = $class->getFacets($rp);
            }

            $rez['blockData'][$k] = $class->getChildren($path, $rp);

            unset($rez['blockData'][$k]['search']);
        }

        return $rez;
    }
}
