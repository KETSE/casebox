<?php

namespace CB\Facets;

use CB\TreeNode;
use CB\Util;

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

        //check if have default sorting set in cofig
        if (!empty($this->config['sort'])) {
            $sp = $this->getSortParams();

            Util\sortRecordsArray(
                $rez['items'],
                $sp['property'],
                $sp['direction'],
                $sp['type'],
                true
            );

            //add sort param for client side
            $rez['sort'] = $sp;
        }

        return $rez;
    }
}
