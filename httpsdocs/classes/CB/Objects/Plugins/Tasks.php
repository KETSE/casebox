<?php

namespace CB\Objects\Plugins;

use CB\User;
use CB\Util;

class Tasks extends Base
{

    public function getData($id = false)
    {

        $rez = array(
            'success' => true
        );
        parent::getData($id);

        $params = array(
            'pid' => $this->id
            ,'fq' => array(
                'template_type:task'
            )
            ,'fl' => 'id,pid,name,template_id,date,date_end,cid'
            ,'sort' => 'cdate'
            ,'dir' => 'desc'
        );

        $s = new \CB\Search();
        $sr = $s->query($params);
        foreach ($sr['data'] as $d) {
            $d['ago_text'] = Util\formatDateTimePeriod($d['date'], $d['date_end'], @$_SESSION['user']['cfg']['TZ']);
            $d['user'] = User::getDisplayName($d['cid']);
            $rez['data'][] = $d;
        }

        return $rez;
    }
}
