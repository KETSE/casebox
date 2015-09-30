<?php
namespace CB\TreeNode;

use CB\Config;
use CB\Util;
use CB\Search;

class Query extends Base
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
        $fq = empty($this->config['fq'])
            ? array()
            : $this->config['fq'];

        $this->replaceFilterVars($fq);

        $s = new \CB\Search();
        $rez = $s->query(
            array(
                'fq' => $fq
            )
        );

        return $rez;
    }
}
