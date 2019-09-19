<?php
namespace CB;

use CB\DataModel as DM;

class BrowserTree extends Browser
{
    public function getChildren($p)
    {
        $p['from'] = 'tree';

        $rez = parent::getChildren($p);

        //collect resulting record ids and get their children
        $ids = array();
        foreach ($rez['data'] as &$d) {
            $ids[] = $d['nid'];
        }

        $children = DM\Tree::getChildCount($ids);

        foreach ($rez['data'] as &$d) {
            if (!isset($d['loaded'])) {
                if (!isset($d['has_childs'])) {
                    $d['has_childs'] = !empty($children[$d['nid']]);
                }
                $d['loaded'] = empty($d['has_childs']);
            }
        }

        return $rez['data'];
    }
}
