<?php

namespace CB\Facets;

class IntFacet extends StringsFacet
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
                'name' => $k
                ,'count' => $v
            );
        }

        return $rez;
    }
}
