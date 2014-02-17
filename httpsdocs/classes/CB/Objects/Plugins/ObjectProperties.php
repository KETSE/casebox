<?php

namespace CB\Objects\Plugins;

class ObjectProperties extends Base
{
    public function getData($id = false)
    {
        $rez = array(
            'success' => true
        );
        parent::getData($id);

        $data = \CB\Objects::getPreview($this->id);
        if (!empty($data)) {
            $rez['data'] = array(
                'html' => $data
            );
        }

        return $rez;
    }
}
