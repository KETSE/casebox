<?php
namespace CB\TreeNode;

use CB\L;
use CB\Templates;

class Tasks extends Base
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

    }

    public function getChildren(&$pathArray, $requestParams)
    {

        $this->path = $pathArray;
        $this->lastNode = @$pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;
        $this->rootId = \CB\Browser::getRootFolderId();

        if (!$this->acceptedPath()) {
            return;
        }

        $ourPid = @intval($this->config['pid']);

        $this->createDefaultFilter();
        if (empty($this->lastNode) ||
            (($this->lastNode->id == $ourPid) && (get_class($this->lastNode) != get_class($this))) ||
            (\CB\Objects::getType($this->lastNode->id) == 'case')
        ) {
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

    public function getName($id = false)
    {
        if ($id === false) {
            $id = $this->id;
        }
        switch ($id) {
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
            case 'assignee':
                return lcfirst(L\Assignee);
            default:
                if (substr($id, 0, 3) == 'au_') {
                    return \CB\User::getDisplayName(substr($id, 3));
                }
        }

        return 'none';
    }

    protected function getRootNodes()
    {
        $p = $this->requestParams;
        $p['fq'] = $this->fq;
        $p['fq'][] = '(user_ids:'.$_SESSION['user']['id'].' OR cid:'.$_SESSION['user']['id'].')';
        $p['fq'][] = 'status:(1 OR 2)';
        $p['rows'] = 0;

        $s = new \CB\Search();
        $rez = $s->query($p);
        $count = '';
        if (!empty($rez['total'])) {
            $count = ' ('.$rez['total'].')';
        }

        return array(
            'data' => array(
                array(
                    'name' => L\Tasks.$count
                    ,'id' => $this->getId(1)
                    ,'iconCls' => 'icon-task'
                    ,'has_childs' => true
                )
            )
        );
    }

    protected function getDepthChildren2()
    {
        $p = $this->requestParams;
        $p['fq'] = $this->fq;
        $p['fq'][] = '(user_ids:'.$_SESSION['user']['id'].' OR cid:'.$_SESSION['user']['id'].')';
        $p['fq'][] = 'status:(1 OR 2)';

        if (@$this->requestParams['from'] == 'tree') {
            $s = new \CB\Search();
            $p['rows'] = 0;
            $p['facet'] = true;
            $p['facet.field'] = array(
                '{!ex=user_ids key=1assigned}user_ids'
                ,'{!ex=cid key=2cid}cid'
            );
            $sr = $s->query($p);
            $rez = array('data' => array());
            if (!empty($sr['facets']->facet_fields->{'1assigned'}->{$_SESSION['user']['id']})) {
                $rez['data'][] = array(
                    'name' => L\AssignedToMe.' ('.$sr['facets']->facet_fields->{'1assigned'}->{$_SESSION['user']['id']}.')'
                    ,'id' => $this->getId(2)
                    ,'iconCls' => 'icon-task'
                    ,'has_childs' => true
                );
            }
            if (!empty($sr['facets']->facet_fields->{'2cid'}->{$_SESSION['user']['id']})) {
                $rez['data'][] = array(
                    'name' => L\Created.' ('.$sr['facets']->facet_fields->{'2cid'}->{$_SESSION['user']['id']}.')'
                    ,'id' => $this->getId(3)
                    ,'iconCls' => 'icon-task'
                    ,'has_childs' => true
                );
            }

            return $rez;
        }

        // for other views
        $s = new \CB\Search();
        $rez = $s->query($p);

        return $rez;
    }

    protected function getDepthChildren3()
    {
        $p = $this->requestParams;
        $p['fq'] = $this->fq;

        if ($this->lastNode->id == 2) {
            $p['fq'][] = 'user_ids:'.$_SESSION['user']['id'];
        } else {
            $p['fq'][] = 'cid:'.$_SESSION['user']['id'];
        }

        $rez = array();

        if (@$this->requestParams['from'] == 'tree') {
            $s = new \CB\Search();

            $sr = $s->query(
                array(
                    'rows' => 0
                    ,'fq' => $p['fq']
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
                    ,'iconCls' => 'icon-task'
                );
            }
            if (!empty($sr['facets']->facet_fields->{'0status'}->{'2'})) {
                $rez['data'][] = array(
                    'name' => lcfirst(L\Ongoing).' ('.$sr['facets']->facet_fields->{'0status'}->{'2'}.')'
                    ,'id' => $this->getId(5)
                    ,'iconCls' => 'icon-task'
                );
            }
            if (!empty($sr['facets']->facet_fields->{'0status'}->{'3'})) {
                $rez['data'][] = array(
                    'name' => lcfirst(L\Closed).' ('.$sr['facets']->facet_fields->{'0status'}->{'3'}.')'
                    ,'id' => $this->getId(6)
                    ,'iconCls' => 'icon-task'
                );
            }
            // Add assignee node if there are any created tasks already added to result
            if (($this->lastNode->id == 3) && !empty($rez['data'])) {
                $rez['data'][] = array(
                    'name' => lcfirst(L\Assignee)
                    ,'id' => $this->getId('assignee')
                    ,'iconCls' => 'icon-task'
                    ,'has_childs' => true
                );
            }
        } else {

            $p['fq'][] = 'status:(1 OR 2)';

            $s = new \CB\Search();
            $rez = $s->query($p);
            foreach ($rez['data'] as &$n) {
                $n['has_childs'] = true;
            }
        }

        return $rez;
    }

    protected function getChildrenTasks()
    {
        $p = $this->requestParams;
        $p['fq'] = $this->fq;

        $parent = $this->lastNode->parent;

        if ($parent->id == 2) {
            $p['fq'][] = 'user_ids:'.$_SESSION['user']['id'];
        } else {
            $p['fq'][] = 'cid:'.$_SESSION['user']['id'];
        }

        switch ($this->lastNode->id) {
            case 4:
                $p['fq'][] = 'status:1';
                break;
            case 5:
                $p['fq'][] = 'status:2';
                break;
            case 6:
                $p['fq'][] = 'status:3';
                break;
            case 'assignee':
                return $this->getAssigneeUsers();
                break;
            default:
                if (substr($this->lastNode->id, 0, 3) == 'au_') {
                    return $this->getAssigneeTasks();
                }
        }

        $s = new \CB\Search();
        $rez = $s->query($p);

        return $rez;
    }

    protected function getAssigneeUsers()
    {
        $p = $this->requestParams;
        $p['fq'] = $this->fq;

        $p['fq'][] = 'cid:'.$_SESSION['user']['id'];
        $p['fq'][] = 'status:[1 TO 2]';

        $p['rows'] = 0;
        $p['facet'] = true;
        $p['facet.field'] = array(
            '{!ex=user_ids key=user_ids}user_ids'
        );
        $rez = array();

        $s = new \CB\Search();

        $sr = $s->query($p);

        $rez = array('data' => array());
        if (!empty($sr['facets']->facet_fields->{'user_ids'})) {
            foreach ($sr['facets']->facet_fields->{'user_ids'} as $k => $v) {
                $k = 'au_'.$k;
                $r = array(
                    'name' => $this->getName($k).' ('.$v.')'
                    ,'id' => $this->getId($k)
                    ,'iconCls' => 'icon-user'
                );

                if (!empty($p['showFoldersContent']) ||
                    (@$this->requestParams['from'] != 'tree')
                ) {
                    $r['has_childs'] = true;
                }
                $rez['data'][] = $r;
            }
        }

        return $rez;
    }

    protected function getAssigneeTasks()
    {
        $p = $this->requestParams;
        $p['fq'] = $this->fq;

        $p['fq'][] = 'cid:'.$_SESSION['user']['id'];
        $p['fq'][] = 'status:[1 TO 2]';

        $user_id = substr($this->lastNode->id, 3);
        $p['fq'][] = 'user_ids:'.$user_id;

        $s = new \CB\Search();

        $sr = $s->query($p);

        return $sr;
    }
}
