<?php
namespace CB\Objects\Plugins;

use CB\User;

class TimeTracking extends Base
{

    public function getData($id = false)
    {
        $rez = array(
            'success' => true,
            'data' => []
        );

        $prez = parent::getData($id);
        if (empty($prez)) {
            return $rez;
        }

        $params = array(
            'pid' => $this->id
            ,'fq' => array(
                '(template_type:time_tracking)'
            )
            ,'fl' => 'id,pid,name,template_id,date,cdate,cid,time_spent_i,time_spent_money_f'
            ,'sort' => 'date asc, cdate asc'
        );

        $s = new \CB\Search();
        $sr = $s->query($params);

        foreach ($sr['data'] as $d) {
            $d['user'] = @User::getDisplayName($d['cid']);
            $d['time'] = gmdate("G\h i\m", $d['time_spent_i']);
            $d['cost'] = '$' . number_format(@$d['time_spent_money_f'], 2);
            $rez['data'][] = $d;
        }

        return $rez;
    }
}
