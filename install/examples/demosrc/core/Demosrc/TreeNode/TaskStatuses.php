<?php
namespace Demosrc\TreeNode;

// - Cases by role / status  (update all cases, case.status = Active)
use CB\L;
use CB\Templates;

class TaskStatuses extends \CB\TreeNode\Base
{
    protected function acceptedPath()
    {
        $p = &$this->path;
        if (empty($p)) {
            return false;
        }

        // echo get_class($this->lastNode);
        if ((get_class($this->lastNode) != 'Demosrc\\TreeNode\\TaskTypes') &&
            (get_class($this->lastNode) != 'Demosrc\\TreeNode\\TaskStatuses')
        ) {
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

        // add office filter

        switch (get_class($this->lastNode)) {
            case 'Demosrc\\TreeNode\\TaskTypes':
                $this->fq[] = 'template_id:'.$this->lastNode->id;
                break;
            case 'Demosrc\\TreeNode\\TaskStatuses':
                $this->fq[] = 'template_id:'.$this->lastNode->parent->id;
                $this->fq[] = 'status:'.$this->lastNode->id;
                break;
        }
        //get program filter
        $pnode = $this->lastNode->parent;
        while (!empty($pnode) && (get_class($pnode) !== 'Demosrc\\TreeNode\\Offices')) {
            // echo get_class($pnode)."\n";
            $pnode = @$pnode->parent;
        }
        if ((!empty($pnode) && (get_class($pnode) == 'Demosrc\\TreeNode\\Offices'))) {
            $this->fq[] = 'category_id:'.$pnode->id;
        }
        // if (get_class($this->lastNode) == 'Demosrc\\TreeNode\\OfficeUsers') {
        //     $this->fq[] = 'category_id:'.$this->lastNode->parent->parent->id;
        //     $this->fq[] = 'user_ids:'.$this->lastNode->id;
        // } else {
        //     $this->fq[] = 'category_id:'.$this->lastNode->id;
        // }

        $this->user_id = $_SESSION['user']['id'];
    }

    public function getChildren(&$pathArray, $requestParams)
    {

        $rez = array();
        $this->path = $pathArray;
        $this->lastNode = @$pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;
        $this->rootId = \CB\Browser::getRootFolderId();
        if (!$this->acceptedPath()) {
            return;
        }

        $this->createDefaultFilter();

        if ((get_class($this->lastNode) == 'Demosrc\\TreeNode\\TaskTypes')) {
            $rez = $this->getRootNodes();
        } else {
            $rez = $this->getTasks();
        }

        return $rez;
    }

    public function getName($id = false)
    {
        if ($id == false) {
            $id = $this->id;
        }

        switch ($id) {
            case 1:
                return lcfirst(L\get('Overdue'));
            case 2:
                return lcfirst(L\get('Ongoing'));
            case 3:
                return lcfirst(L\get('Closed'));
        }

        return 'none';
    }

    protected function getRootNodes()
    {
        $fq = $this->fq;
        $s = new \CB\Search();

        if (@$this->requestParams['from'] == 'tree') {
            $sr = $s->query(
                array(
                    'rows' => 0
                    ,'fq' => $fq
                    ,'facet' => true
                    ,'facet.field' => array(
                        '{!ex=status key=status}status'
                    )
                )
            );

            $rez = array('data' => array());
            if (!empty($sr['facets']->facet_fields->{'status'})) {
                foreach ($sr['facets']->facet_fields->{'status'} as $status => $count) {
                    $rez['data'][] = array(
                        'name' => $this->getName($status) . ' ('.$count.')'
                        ,'id' => $this->getId($status)
                        ,'iconCls' => 'icon-task'
                        ,'has_childs' => false
                    );
                }
            }

            return $rez;
        }

        $sr = $s->query(
            array(
                'fq' => $fq
            )
        );

        return $sr;
    }

    protected function getTasks()
    {
        $fq = $this->fq;
        $s = new \CB\Search();

        $sr = $s->query(
            array(
                'fq' => $fq
            )
        );

        return $sr;
    }
}
