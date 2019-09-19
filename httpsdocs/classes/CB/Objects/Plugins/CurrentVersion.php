<?php

namespace CB\Objects\Plugins;

use CB\Objects;
use CB\Util;
use CB\User;

class CurrentVersion extends Base
{

    public function getData($id = false)
    {

        $rez = array(
            'success' => true
        );

        parent::getData($id);

        $o = Objects::getCachedObject($this->id);
        if (!empty($o)) {
            $data = $o->getData();

            //show current version only if have more other versions
            if (!empty($data['versions'])) {
                $data['ago_text'] = Util\formatAgoTime(Util\coalesce($data['udate'], $data['cdate']));
                $data['user'] = User::getDisplayName(Util\coalesce($data['uid'], $data['oid'], $data['cid']), true);
                $data['cls'] = 'sel';

                $rez['data'] = array($data);
            }
        }

        return $rez;
    }
}
