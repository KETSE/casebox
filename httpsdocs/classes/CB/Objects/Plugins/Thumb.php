<?php

namespace CB\Objects\Plugins;

class Thumb extends Base
{
    public function getData($id = false)
    {
        parent::getData($id);

        $o = $this->getObjectClass();
        if (empty($o)) {
            return null;
        }
        $data = $o->getData();

        return array(
            'success' => true
            ,'data' => array(
                'cls' => 'pr-th-'.\CB\Files::getExtension($data['name'])
            )
        );
    }
}
