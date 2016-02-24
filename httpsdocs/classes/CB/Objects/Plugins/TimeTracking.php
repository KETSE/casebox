<?php
namespace CB\Objects\Plugins;

use CB\User;
use CB\Util;

class TimeTracking extends Base
{

    public function getData($id = false)
    {
        $rez = array(
            'success' => true
        );

        if (empty(parent::getData($id))) {
            return $rez;
        }

        $params = array(
            'pid' => $this->id
            ,'fq' => array(
                '(template_type:time_tracking)'
            )
            ,'fl' => 'id,pid,name,template_id,cdate,cid'
            ,'sort' => 'cdate'
            ,'dir' => 'asc'
        );

        $s = new \CB\Search();
        $sr = $s->query($params);

        foreach ($sr['data'] as $d) {
            // $d['ago_text'] = Util\formatAgoTime($d['cdate']);
            // $d['user'] = @User::getDisplayName($d['cid']);
            $rez['data'][] = $d;
        }

        return $rez;
    }
}
