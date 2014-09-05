<?php

namespace CB\Objects\Plugins;

use CB\Objects;

class Meta extends ObjectProperties
{
    public function getData($id = false)
    {

        $rez = parent::getData($id);

        if (empty($rez['data']['html'])) {
            // $rez['data']['html'] = 'No metadata';
        }

        return $rez;
    }
}
