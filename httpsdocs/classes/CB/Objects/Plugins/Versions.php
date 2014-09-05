<?php

namespace CB\Objects\Plugins;

use CB\Objects;
use CB\Util;
use CB\User;

class Versions extends Base
{
    public function getData($id = false)
    {

        $rez = array(
            'success' => true
        );

        parent::getData($id);

        // echo $this->id.'!!';
        $o = Objects::getCachedObject($this->id);
        $data = $o->getData();

        if (!empty($data['versions'])) {
            $rez['data'] = $data['versions'];
            foreach ($rez['data'] as &$version) {
                $version['ago_text'] = Util\formatAgoTime($version['cdate']);
                $version['user'] = User::getDisplayName($version['cid'], true);
            }
        }

        return $rez;
    }
}
