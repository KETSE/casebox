<?php
namespace Demosrc\TreeNode;

// - Cases by role / status  (update all cases, case.status = Active)
use CB\Templates;
use CB\DB;
use CB\L;
use CB\DataModel as DM;

class OfficeUsers extends \CB\TreeNode\Base
{
    protected function acceptedPath()
    {
        $p = &$this->path;
        if (empty($p)) {
            return false;
        }

        if ((get_class($this->lastNode) != 'Demosrc\\TreeNode\\Offices') &&
            ((get_class($this->lastNode) != 'Demosrc\\TreeNode\\OfficeUsers') ||
                ($this->lastNode->id != 'users')
            )
        ) {
            return false;
        }

        return true;
    }

    protected function createDefaultFilter()
    {

        $this->fq = array();

        //select only task templates
        $taskTemplates = DM\Templates::getIdsByType('task');
        if (!empty($taskTemplates)) {
            $this->fq[] = 'template_id:('.implode(' OR ', $taskTemplates).')';
        }

        // add office filter
        if (get_class($this->lastNode) == 'Demosrc\\TreeNode\\OfficeUsers') {
            $this->office_id = $this->lastNode->parent->id;
        } else {
            $this->office_id = $this->lastNode->id;
        }

        // $this->fq[] = 'category_id:'.$this->office_id;

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

        if (get_class($this->lastNode) == 'Demosrc\\TreeNode\\Offices') {
            $rez = $this->getRootNodes();
        } else {
            $rez = $this->getDepth1();
        }

        return $rez;
    }

    public function getName($id = false)
    {
        if ($id == false) {
            $id = $this->id;
        }
        $rez = 'no name';
        switch ($id) {
            case 'users':
                $rez = L\get('Users');
                break;
            default:
                $rez = \CB\User::getDisplayName($id);
        }

        return $rez;
    }

    protected function getRootNodes()
    {
        $rez = array(
            'data' => array(
                array(
                    'name' => $this->getName('users')
                    ,'id' => $this->getId('users')
                    ,'iconCls' => 'icon-folder'
                    ,'has_childs' => true
                )
            )
        );

        return $rez;
    }

    protected function getDepth1()
    {
        $rez = array();
        $users = array();

        $office = new \CB\Objects\Object($this->office_id);
        $od  = $office->load();
        if (empty($od['data']['security_group'])) {
            return $rez;
        }

        $res = DB\dbQuery(
            'SELECT
                ug.id
                ,ug.name
                ,ug.first_name
                ,ug.last_name
                ,ug.sex
            FROM
            `users_groups_association` uga
            JOIN users_groups ug ON uga.user_id = ug.id
            WHERE uga.`group_id` = $1
            ORDER BY ug.first_name, ug.last_name, ug.name',
            $od['data']['security_group']
        );
        while ($r = $res->fetch_assoc()) {
            $name = trim($r['first_name'].' '.$r['last_name']);
            if (empty($name)) {
                $name = $r['name'];
            }
            $users[$r['id']] = array(
                'id' => $this->getId($r['id'])
                ,'name' => $name
                ,'iconCls' => 'icon-user-'.$r['sex']
                ,'has_childs' => true
            );
        }
        $res->close();

        $fq = $this->fq;
        $s = new \CB\Search();

        if (@$this->requestParams['from'] == 'tree') {
            $rez['data'] = array_values($users);

            return $rez;
        }

        $sr = $s->query(
            array(
                'fq' => $fq
            )
        );

        return $sr;
    }
}
