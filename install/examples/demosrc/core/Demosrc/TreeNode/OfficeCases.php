<?php
namespace Demosrc\TreeNode;

// - Cases by role / status  (update all cases, case.status = Active)
use CB\Templates;
use CB\DB;
use CB\L;
use CB\DataModel as DM;

class OfficeCases extends \CB\TreeNode\Base
{
    protected function acceptedPath()
    {
        $p = &$this->path;
        if (empty($p)) {
            return false;
        }

        if (((get_class($this->lastNode) != 'Demosrc\\TreeNode\\OfficeUsers') ||
                !is_numeric($this->lastNode->id)
            ) &&
            (get_class($this->lastNode) != 'Demosrc\\TreeNode\\Offices') &&
            (get_class($this->lastNode) != 'Demosrc\\TreeNode\\OfficeCases')
        ) {
            return false;
        }

        return true;
    }

    protected function createDefaultFilter()
    {

        $this->fq = array();

        //select only case templates
        $templates = DM\Templates::getIdsByType('case');
        if (!empty($templates)) {
            $this->fq[] = 'template_id:('.implode(' OR ', $templates).')';
        }

        $this->user_id = $_SESSION['user']['id'];
        // add office filter

        $parentNode = null;
        switch (get_class($this->lastNode)) {
            case 'Demosrc\\TreeNode\\OfficeCases':
                if (is_numeric($this->lastNode->id)) {
                    $parentNode = $this->lastNode->parent->parent;
                    // $this->fq[] = 'category_id:'.$this->lastNode->parent->parent->id;
                    $this->fq[] = 'status:'.$this->lastNode->id;
                } else {
                    $parentNode = $this->lastNode->parent;
                    // $this->fq[] = 'category_id:'.$this->lastNode->parent->id;
                }
                break;
            default: $parentNode = $this->lastNode;
        }

        switch (get_class($parentNode)) {
            case 'Demosrc\\TreeNode\\OfficeUsers':
                $this->fq[] = '(role_ids2:'.$parentNode->id.' OR role_ids3:'.$parentNode->id.')';
                // $this->fq[] = 'category_id:'.$parentNode->parent->parent->id;
                break;
            case 'Demosrc\\TreeNode\\Offices':
                // $this->fq[] = 'category_id:'.$parentNode->id;
                break;
        }

    }

    public function getChildren(&$pathArray, $requestParams)
    {

        $rez = array();
        $this->path = $pathArray;
        $this->lastNode = @$pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;

        if (!$this->acceptedPath()) {
            return;
        }

        $this->createDefaultFilter();

        $rez = array();
        switch (get_class($this->lastNode)) {
            case 'Demosrc\\TreeNode\\OfficeUsers':
            case 'Demosrc\\TreeNode\\Offices':
                $rez = $this->getRootNodes();
                break;
            case 'Demosrc\\TreeNode\\OfficeCases':
                if (is_numeric($this->lastNode->id)) {
                    $rez = $this->getCases();
                } else {
                    $rez = $this->getCasesStatuses();
                }
                break;
        }

        return $rez;
    }

    public function getName($id = false)
    {
        if ($id == false) {
            $id = $this->id;
        }
        $rez = 'no name';

        if ($id == 'cases') {
            return L\get('Cases');
        }

        $res = DB\dbQuery(
            'SELECT name FROM tree WHERE id = $1',
            $id
        );
        if ($r = $res->fetch_assoc()) {
            $rez = $r['name'];
        }
        $res->close();

        return $rez;
    }

    protected function getRootNodes()
    {
        $rez = array(
            'data' => array(
                array(
                    'name' => $this->getName('cases')
                    ,'id' => $this->getId('cases')
                    ,'iconCls' => 'icon-folder'
                    ,'has_childs' => true
                )
            )
        );

        return $rez;
    }

    protected function getCasesStatuses()
    {
        $fq = $this->fq;
        if (@$this->requestParams['from'] == 'tree') {
            $s = new \CB\Search();
            $sr = $s->query(
                array(
                    'rows' => 0
                    ,'fq' => $fq
                    ,'facet' => true
                    ,'facet.field' => array(
                        'status'
                    )
                )
            );
            $rez = array('data' => array());
            if (!empty($sr['facets']->facet_fields->{'status'})) {
                foreach ($sr['facets']->facet_fields->{'status'} as $k => $v) {
                    $rez['data'][] = array(
                        'name' => $this->getName($k).' ('.$v.')'
                        ,'id' => $this->getId($k)
                        ,'iconCls' => 'icon-folder'
                        ,'has_childs' => true
                    );
                }
            }

            return $rez;
        }

        // for other views
        $s = new \CB\Search();
        $rez = $s->query(array('fq' => $fq));

        return $rez;
    }

    protected function getCases()
    {
        $s = new \CB\Search();
        $rez = $s->query(array('fq' => $this->fq));

        return $rez;
    }
}
