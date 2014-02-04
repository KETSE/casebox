<?php

namespace CB\Facets;

use CB\TreeNode;

class ObjectsFacet extends StringsFacet
{

    public function getClientData()
    {
        $rez = array(
            'f' => $this->field
            ,'title' => $this->getTitle()
            ,'items' => array()
        );

        $dbnode = new TreeNode\Dbnode();

        foreach ($this->solrData as $k => $v) {
            $rez['items'][$k] = array(
                'name' => $dbnode->getName($k)
                ,'count' => $v
            );
        }

        return $rez;
    }
}
