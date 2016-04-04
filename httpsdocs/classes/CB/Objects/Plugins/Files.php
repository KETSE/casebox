<?php

namespace CB\Objects\Plugins;

use CB\Util;

class Files extends Base
{

    public function getData($id = false)
    {

        $rez = array(
            'success' => true
            ,'data' => array()
        );

        $prez = parent::getData($id);
        if (empty($prez)) {
            return $this->isVisible()
                ? $rez
                : null;
        }

        $params = array(
            'pid' => $this->id
            ,'fq' => array(
                '(template_type:file) OR (target_type:file)'
            )
            ,'fl' => 'id,pid,name,template_id,size,cdate'
            ,'sort' => 'cdate'
            ,'dir' => 'desc'
        );

        $s = new \CB\Search();
        $sr = $s->query($params);
        foreach ($sr['data'] as $d) {
            $d['ago_text'] = Util\formatAgoTime($d['cdate']);
            $rez['data'][] = $d;
        }

        return $rez;
    }
}
