<?php
namespace Demosrc\TreeNode;

use CB\L;

class ManagersCalendar extends \CB\TreeNode\MyCalendar
{
    protected function createDefaultFilter()
    {
        parent::createDefaultFilter();

        $programs = array();
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
        if (empty($sr['data'])) {
            $this->fq[] = 'id:0';
        } else {
            foreach ($sr['data'] as $pr) {
                $programs[] = $pr['id'];
            }
            $this->fq[] = 'category_id:('.implode(' OR ', $programs).')';
        }
    }

    public function getName($id = false)
    {
        if ($id === false) {
            $id = $this->id;
        }
        switch ($id) {
            case 1:
                return L\get('ManagersCalendar');
        }

        return 'none';
    }
}
