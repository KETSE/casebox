<?php
namespace Demosrc\TreeNode;

// - Cases by role / status  (update all cases, case.status = Active)
use CB\DB;
use CB\Templates;
use CB\DataModel as DM;

class Offices extends \CB\TreeNode\Base
{
    protected function acceptedPath()
    {
        $p = &$this->path;

        if ((sizeof($p) > 1) &&
            (get_class($this->lastNode) != get_class($this))
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

        if (sizeof($pathArray) < 2) {
            $rez = $this->getRootNodes();
        }

        return $rez;
    }

    public function getName($id = false)
    {
        if ($id == false) {
            $id = $this->id;
        }
        $rez = 'no name';
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
        $fq = $this->fq;
        $rez = array('data' => array());

        //filter only programs where current user is manager
        $s = new \CB\Search();
        $sr = $s->query(
            array(
                'fl' => 'id'
                ,'fq' => array(
                    'template_id:24484'
                    ,'user_ids:'.$_SESSION['user']['id']
                )
            )
        );

        $programs = array();
        if (empty($sr['data'])) {
            return $rez;
        } else {
            foreach ($sr['data'] as $pr) {
                $programs[] = $pr['id'];
            }
            // $fq = 'category_id:('.implode(' OR ', $programs).')';
        }

        // $sr = $s->query(
        //     array(
        //         'rows' => 0
        //         ,'fq' => $fq
        //         ,'facet' => true
        //         ,'facet.field' => array(
        //             '{!ex=category_id key=category_id}category_id'
        //         )
        //     )
        // );
        // if (!empty($sr['facets']->facet_fields->{'category_id'})) {
        //     foreach ($sr['facets']->facet_fields->{'category_id'} as $program_id => $count) {
        //         if (!in_array($program_id, $programs)) {
        //             continue;
        //         }
        //         $rez['data'][] = array(
        //             'name' => $this->getName($program_id)
        //             ,'id' => $this->getId($program_id)
        //             ,'iconCls' => 'i-building'
        //             ,'has_childs' => true
        //         );
        //     }
        // }
        return $rez;
    }
}
