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

        $prez = parent::getData($id);
        if (empty($prez)) {
            return $rez;
        }

        $params = array(
            'pid' => $this->id
            ,'fq' => array(
                '(template_type:task) OR (target_type:task)'
            )
            ,'fl' => 'id,pid,name,template_id,date,date_end,cid,cdate,status'
            ,'sort' => 'cdate'
            ,'dir' => 'desc'
        );

        $s = new \CB\Search();
        $sr = $s->query($params);
        foreach ($sr['data'] as $d) {
            $d['ago_text'] = @Util\formatDateTimePeriod($d['date'], null, @$_SESSION['user']['cfg']['timezone']);
            $d['user'] = User::getDisplayName($d['cid'], true);

            \CB\Tasks::setTaskActionFlags($d);

            $rez['data'][] = $d;
        }

        return $rez;
    }
}
