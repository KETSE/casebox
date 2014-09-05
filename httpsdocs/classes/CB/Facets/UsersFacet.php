<?php

namespace CB\Facets;

use CB\User;

class UsersFacet extends StringsFacet
{

    public function getClientData()
    {
        $rez = array(
            'f' => $this->field
            ,'title' => $this->getTitle()
            ,'items' => array()
        );

        foreach ($this->solrData as $k => $v) {
            $rez['items'][$k] = array(
                'name' => User::getDisplayName($k)
                ,'count' => $v
            );
        }

        return $rez;
    }
}
