<?php
namespace CB\TreeNode;

use CB\L;
use CB\Templates;

class Tasks extends Base
{
    protected function acceptedPath()
    {
        $p = &$this->path;
        if (empty($p)) {
            return false;
        }

        // echo '!'.get_class($this->lastNode);
        if ($this->lastNode instanceof Dbnode) {
            if ($this->lastNode->id <> $this->rootId) {
                return false;
            }
        } elseif (get_class($this->lastNode) != get_class($this)) {
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
        $this->fq[] = '(user_ids:'.$_SESSION['user']['id'].' OR cid:'.$_SESSION['user']['id'].')';

    }

    public function getChildren(&$pathArray, $requestParams)
    {

        $this->path = $pathArray;
        $this->lastNode = $pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;
        $this->rootId = \CB\Browser::getRootFolderId();

        if (!$this->acceptedPath()) {
            return;
        }

        $this->createDefaultFilter();
        // echo "in ".get_class($this);
        // var_dump($this->fq);

        if ($this->lastNode instanceof Dbnode) {
            // var_dump($this->lastNode);
            // var_dump($this->path);
            $rez = $this->getRootNodes();
        } else {
            switch ($this->lastNode->id) {
                case 1:
                    $rez = $this->getDepthChildren2();
                    break;
                case 2:
                case 3:
                    $rez = $this->getDepthChildren3();
                    break;
                default:
                    $rez = $this->getChildrenTasks();
            }
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
        $fq = $this->fq;
        $fq[] = 'status:(1 OR 2)';
        $s = new \CB\Search();
        $rez = $s->query(
            array(
                'rows' => 0
                ,'fq' => $fq
            )
        );
        $count = '';
        if (!empty($rez['total'])) {
            $count = ' ('.$rez['total'].')';
        }

        return array(
            'data' => array(
                array(
                    'name' => L\Tasks.$count
                    ,'id' => $this->getId(1)
                    ,'iconCls' => 'i-flag'
                    ,'has_childs' => true
                )
            )
        );
    }

    protected function getDepthChildren2()
    {
        $fq = $this->fq;
        $fq[] = 'status:(1 OR 2)';

        if (@$this->requestParams['from'] == 'tree') {
            $s = new \CB\Search();
            $sr = $s->query(
                array(
                    'rows' => 0
                    ,'fq' => $fq
                    ,'facet' => true
                    ,'facet.field' => array(
                        '{!ex=user_ids key=1assigned}user_ids'
                        ,'{!ex=cid key=2cid}cid'
                    )
                )
            );
            $rez = array('data' => array());
            if (!empty($sr['facets']->facet_fields->{'1assigned'}->{$_SESSION['user']['id']})) {
                $rez['data'][] = array(
                    'name' => L\AssignedToMe.' ('.$sr['facets']->facet_fields->{'1assigned'}->{$_SESSION['user']['id']}.')'
                    ,'id' => $this->getId(2)
                    ,'iconCls' => 'is-flag'
                    ,'has_childs' => true
                );
            }
            if (!empty($sr['facets']->facet_fields->{'2cid'}->{$_SESSION['user']['id']})) {
                $rez['data'][] = array(
                    'name' => L\Created.' ('.$sr['facets']->facet_fields->{'2cid'}->{$_SESSION['user']['id']}.')'
                    ,'id' => $this->getId(3)
                    ,'iconCls' => 'is-flag'
                    ,'has_childs' => true
                );
            }

            return $rez;
        }

        // for other views
        $s = new \CB\Search();
        $rez = $s->query(array('fq' => $fq));

        return $rez;
    }

    protected function getDepthChildren3()
    {
        $fq = $this->fq;

        if ($this->lastNode->id == 2) {
            $fq[] = 'user_ids:'.$_SESSION['user']['id'];
        } else {
            $fq[] = 'cid:'.$_SESSION['user']['id'];
        }

        $rez = array();

        if (@$this->requestParams['from'] == 'tree') {
            $s = new \CB\Search();
            $sr = $s->query(
                array(
                    'rows' => 0
                    ,'fq' => $fq
                    ,'facet' => true
                    ,'facet.field' => array(
                        '{!ex=status key=0status}status'
                    )
                )
            );
            $rez = array('data' => array());
            if (!empty($sr['facets']->facet_fields->{'0status'}->{'1'})) {
                $rez['data'][] = array(
                    'name' => lcfirst(L\Overdue).' ('.$sr['facets']->facet_fields->{'0status'}->{'1'}.')'
                    ,'id' => $this->getId(4)
                    ,'iconCls' => 'is-flag'
                );
            }
            if (!empty($sr['facets']->facet_fields->{'0status'}->{'2'})) {
                $rez['data'][] = array(
                    'name' => lcfirst(L\Ongoing).' ('.$sr['facets']->facet_fields->{'0status'}->{'2'}.')'
                    ,'id' => $this->getId(5)
                    ,'iconCls' => 'is-flag'
                );
            }
            if (!empty($sr['facets']->facet_fields->{'0status'}->{'3'})) {
                $rez['data'][] = array(
                    'name' => lcfirst(L\Closed).' ('.$sr['facets']->facet_fields->{'0status'}->{'3'}.')'
                    ,'id' => $this->getId(6)
                    ,'iconCls' => 'is-flag'
                );
            }
        } else {
            $s = new \CB\Search();
            $rez = $s->query(array('fq' => $fq));
            foreach ($rez['data'] as &$n) {
                $n['has_childs'] = true;
            }
        }

        return $rez;
    }

    protected function getChildrenTasks()
    {
        $fq = $this->fq;

        $parent = $this->lastNode->parent;

        if ($parent->id == 2) {
            $fq[] = 'user_ids:'.$_SESSION['user']['id'];
        } else {
            $fq[] = 'cid:'.$_SESSION['user']['id'];
        }

        switch ($this->lastNode->id) {
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
