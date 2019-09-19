<?php

namespace CB\Objects\Plugins;

use CB\Config;
use CB\Files as CBFiles;
use CB\Util;

class Thumb extends Base
{
    public function getData($id = false)
    {
        $rez = array('success'=> true, 'data' => array());

        $prez = parent::getData($id);
        if (empty($prez)) {
            return null;
        }

        $o = $this->getObjectClass();
        if (empty($o)) {
            return $rez;
        }
        $data = $o->getData();

        //dont display thumb for images less then 1MB
        $maxDisplaySize = Util\coalesce(Config::get('images_display_size'), 1024 * 1024);

        if (!empty($data['content_type']) && (substr($data['content_type'], 0, 5) == 'image') && ($data['size'] < $maxDisplaySize)) {
            $preview = CBFiles::generatePreview($data['id']);
            if (!empty($preview['filename'])) {
                $fn = Config::get('files_preview_dir') . $preview['filename'];
                $rez['data']['html'] = $fn;
                if (file_exists($fn)) {
                    $rez['data']['html'] = str_replace('fit-img', 'click fit-img', file_get_contents($fn));
                }
            }
        } else {
            $rez['data']['cls'] = 'pr-th-' . CBFiles::getExtension($data['name']);
        }

        return $rez;
    }

    protected function getObjectClass()
    {
        $rez = parent::getObjectClass();

        if (!empty($rez)) {
            $rez->load();
        }

        return $rez;
    }
}
