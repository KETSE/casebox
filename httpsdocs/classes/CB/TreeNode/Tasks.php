<?php
namespace CB\TreeNode;

use CB\L;
use CB\Templates;

class Tasks extends Base
{
    public function getChildren(&$pathArray, $requestParams)
    {
        $this->depth = sizeof($pathArray);
        //show only under root node
        if (empty($pathArray)) {
            return;
        }

        $node = $pathArray[sizeof($pathArray) - 1];

        if (($this->depth > 1) && !($node instanceof Tasks)) {
            return;
        }

        $this->path = $pathArray;

        $this->requestParams = $requestParams;

        switch ($this->depth) {
            case 1:
                $rez = $this->getRootNodes();
                break;
            case 2:
                $rez = $this->getDepthChildren2();
                break;
            case 3:
                $rez = $this->getDepthChildren3();
                break;
            default:
                $rez = $this->getChildrenTasks();
        }

        return $rez;
    }

    public function getName()
    {
        switch ($this->id) {
            case 1:
                return L\Tasks;
            case 2:
                return L\AssignedToMe;
            case 3:
                return L\Created;
            case 4:
                return lcfirst(L\Overdue);
            case 5:
                return lcfirst(L\Ongoing);
            case 6:
                return lcfirst(L\Closed);
        }

        return 'none';
    }

    protected function getRootNodes()
    {
        return array(
            'data' => array(
                array(
                    'name' => L\Tasks
                    ,'id' => $this->getId(1)
                    ,'iconCls' => 'i-flag'
                    ,'has_childs' => true
                )
            )
        );
    }

    protected function getDepthChildren2()
    {
        return array(
            'data' => array(
                array(
                    'name' => L\AssignedToMe
                    ,'id' => $this->getId(2)
                    ,'iconCls' => 'is-flag'
                    ,'has_childs' => true
                )
                ,array(
                    'name' => L\Created
                    ,'id' => $this->getId(3)
                    ,'iconCls' => 'is-flag'
                    ,'has_childs' => true
                )
            )
        );
    }

    protected function getDepthChildren3()
    {
        $rez = array(
            'data' => array(
                array(
                    'name' => lcfirst(L\Overdue)
                    ,'id' => $this->getId(4)
                    ,'iconCls' => 'is-flag'
                )
                ,array(
                    'name' => lcfirst(L\Ongoing)
                    ,'id' => $this->getId(5)
                    ,'iconCls' => 'is-flag'
                )
                ,array(
                    'name' => lcfirst(L\Closed)
                    ,'id' => $this->getId(6)
                    ,'iconCls' => 'is-flag'
                )
            )
        );
        if (@$this->requestParams['from'] != 'tree') {
            foreach ($rez['data'] as &$n) {
                $n['has_childs'] = true;
            }
        }

        return $rez;
    }

    protected function getChildrenTasks()
    {
        $fq = array();
        //select only task templates
        $taskTemplates = Templates::getIdsByType('task');
        if (!empty($taskTemplates)) {
            $fq[] = 'template_id:('.implode(' OR ', $taskTemplates).')';
        }

        $node = $this->path[sizeof($this->path) - 1];
        $parent = $node->parent;
        if ($parent->id == 2) {
            $fq[] = 'user_ids:'.$_SESSION['user']['id'];
        } else {
            $fq[] = 'cid:'.$_SESSION['user']['id'];
        }

        switch ($node->id) {
            case 4:
                $fq[] = 'status:1';
                break;
            case 5:
                $fq[] = 'status:2';
                break;
            case 6:
                $fq[] = 'status:3';
                break;
        }

        $s = new \CB\Search();
        $rez = $s->query(array('fq' => $fq));

        return $rez;
    }
}
