<?php

namespace CB\Facets;

use CB\User;
use CB\Users;

class UsersFacet extends StringsFacet
{

    public function getClientData($options = array())
    {
        $rez = array(
            'f' => $this->field
            ,'title' => $this->getTitle()
            ,'items' => array()
        );

        // $colors = empty($options['colors'])
        //     ? array()
        //     : Users::getColors();

        foreach ($this->solrData as $k => $v) {
            $rez['items'][$k] = array(
                'name' => User::getDisplayName($k)
                ,'count' => $v
            );

            if (!empty($options['colors'])) {
                $rez['items'][$k]['cls'] = 'user-color-' . $k;
            }
        }

        return $rez;
    }
}
