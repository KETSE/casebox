<?php
namespace CB;

class BrowserTree extends Browser
{
    public function getChildren($p)
    {
        $rez = parent::getChildren($p);
        foreach ($rez['data'] as &$node) {
            $node['loaded'] = empty($node['has_childs']);
        }

        return $rez['data'];
    }
}
