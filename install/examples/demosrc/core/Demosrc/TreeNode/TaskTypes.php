<?php
namespace Demosrc\TreeNode;

// - Cases by role / status  (update all cases, case.status = Active)
use CB\Templates;
use CB\DB;
use CB\DataModel as DM;

class TaskTypes extends \CB\TreeNode\Base
{
    protected function acceptedPath()
    {
        $p = &$this->path;
        if (empty($p)) {
            return false;
        }

        // echo get_class($this->lastNode);
        if (((get_class($this->lastNode) != 'Demosrc\\TreeNode\\OfficeUsers') ||
                !is_numeric($this->lastNode->id)
            ) &&
            (get_class($this->lastNode) != 'Demosrc\\TreeNode\\Offices')
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

        switch (get_class($this->lastNode)) {
            case 'Demosrc\\TreeNode\\OfficeUsers':
                $this->fq[] = 'user_ids:'.$this->lastNode->id;
                break;
            case 'Demosrc\\TreeNode\\Offices':
                // $this->fq[] = 'category_id:'.$this->lastNode->id;
                break;
        }
        if (get_class($this->lastNode) == 'Demosrc\\TreeNode\\OfficeUsers') {
            // $this->fq[] = 'category_id:'.$this->lastNode->parent->parent->id;
            $this->fq[] = 'user_ids:'.$this->lastNode->id;
        } else {
            // $this->fq[] = 'category_id:'.$this->lastNode->id;
        }

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

        return $this->getRootNodes();
    }

    public function getName($id = false)
    {
        if ($id == false) {
            $id = $this->id;
        }
        $rez = 'no name';
        $res = DB\dbQuery(
            'SELECT name, iconCls FROM templates WHERE id = $1',
            $id
        );
        if ($r = $res->fetch_assoc()) {
            $rez = $r['name'];
            $this->iconCls = $r['iconCls'];
        }
        $res->close();

        return $rez;
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
                        '{!ex=template_id key=template_id}template_id'
                    )
                )
            );
            $rez = array('data' => array());
            if (!empty($sr['facets']->facet_fields->{'template_id'})) {
                foreach ($sr['facets']->facet_fields->{'template_id'} as $template_id => $count) {
                    $rez['data'][] = array(
                        'name' => $this->getName($template_id) . ' ('.$count.')'
                        ,'id' => $this->getId($template_id)
                        ,'iconCls' => $this->iconCls
                        ,'has_childs' => true
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
}
