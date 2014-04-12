<?php
namespace CB\TreeNode;

use CB\L;
use CB\Templates;

class MyCalendar extends Base
{
    protected function acceptedPath()
    {
        $p = &$this->path;

        $lastId = 0;
        if (!empty($p)) {
            $lastId = $this->lastNode->id;
        }

        $ourPid = @intval($this->config['pid']);
        if ($this->lastNode instanceof Dbnode) {
            if ($ourPid != $lastId) {
                return false;
            }
        } elseif (!empty($this->lastNode) && (get_class($this->lastNode) != get_class($this))) {
            return false;
        }

        return true;

    }

    protected function createDefaultFilter()
    {
        $this->fq = array();

        //select only task templates
        $taskTemplates = Templates::getIdsByType('task');
        if (!empty($taskTemplates)) {
            $this->fq[] = 'template_id:('.implode(' OR ', $taskTemplates).')';
        }

    }

    public function getChildren(&$pathArray, $requestParams)
    {

        $this->path = $pathArray;
        $this->lastNode = @$pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;

        if (!$this->acceptedPath()) {
            return;
        }

        $ourPid = @intval($this->config['pid']);

        $this->createDefaultFilter();

        if (empty($this->lastNode) ||
            (($this->lastNode->id == $ourPid) && (get_class($this->lastNode) != get_class($this)))
        ) {
            $rez = $this->getRootNodes();
        } else {
            $rez = $this->getChildrenTasks();
        }

        return $rez;
    }

    public function getName($id = false)
    {
        if ($id === false) {
            $id = $this->id;
        }
        switch ($id) {
            case 1:
                return L\MyCalendar;
        }

        return 'none';
    }

    protected function getRootNodes()
    {
        return array(
            'data' => array(
                array(
                    'name' => $this->getName(1)
                    ,'id' => $this->getId(1)
                    ,'iconCls' => 'icon-calendar'
                    ,'view' => 'calendar'
                )
            )
        );
    }

    protected function getChildrenTasks()
    {
        $p = $this->requestParams;

        if (@$p['from'] == 'tree') {
            return array();
        }

        $p['fq'] = $this->fq;
        $p['fq'][] = 'user_ids:'.$_SESSION['user']['id'];
        $p['fq'][] = 'status:[0 TO 2]';

        $s = new \CB\Search();
        $rez = $s->query($p);

        return $rez;
    }
}
