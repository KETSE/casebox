<?php

namespace CB\Objects\Plugins;

use CB\Objects;

class Meta extends ObjectProperties
{
    public function getData($id = false)
    {

        $rez = parent::getData($id);

        if (empty($rez['data']['preview'])) {
            unset($rez['data']);
        } else {
            $preview = implode('', $rez['data']['preview']);
            if (empty($preview)) {
                unset($rez['data']);
            }
        }

        return $rez;
    }
}
