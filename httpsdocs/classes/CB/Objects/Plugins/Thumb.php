<?php

namespace CB\Objects\Plugins;

use CB\Config;
use CB\Files;

class Thumb extends Base
{
    public function getData($id = false)
    {
        $rez = array('success'=> true, 'data' => array());
        parent::getData($id);

        $o = $this->getObjectClass();
        if (empty($o)) {
            return $rez;
        }
        $data = $o->getData();

        //dont display thumb for images less then 30kb
        if ((substr($data['type'], 0, 5) == 'image') && ($data['size'] < Config::get('images_display_size', 30 *1024))) {
            $preview = Files::generatePreview($data['id']);
            if (!empty($preview['filename'])) {
                $fn = Config::get('files_preview_dir') . $preview['filename'];
                $rez['data']['html'] = $fn;
                if (file_exists($fn)) {
                    $rez['data']['html'] = str_replace(
                        'fit-img',
                        'click fit-300',
                        file_get_contents($fn)
                    );
                }
            }
        } else {
            $rez['data']['cls'] = 'pr-th-'.\CB\Files::getExtension($data['name']);
        }

        return $rez;
    }
}
