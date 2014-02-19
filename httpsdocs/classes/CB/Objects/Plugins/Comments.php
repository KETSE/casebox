<?php

namespace CB\Objects\Plugins;

use CB\User;
use CB\Util;

class Comments extends Base
{

    public function getData($id = false)
    {

        $rez = array(
            'success' => true
            ,'data' => array()
        );
        parent::getData($id);

        $commentTemplateIds = \CB\Templates::getIdsByType('comment');
        if (empty($commentTemplateIds)) {
            return $rez;
        }

        $params = array(
            'pid' => $this->id
            ,'system' => '[0 TO 2]'
            ,'fq' => array(
                'template_id:('.implode(' OR ', $commentTemplateIds).')'
            )
            ,'fl' => 'id,pid,name,template_id,cid,cdate,content'
            ,'sort' => 'cdate'
            ,'rows' => 10
            ,'dir' => 'desc'
        );

        $s = new \CB\Search();
        $sr = $s->query($params);
        $rez['total'] = $sr['total'];
        foreach ($sr['data'] as $d) {
            $d['cdate_text'] = Util\formatAgoTime($d['cdate']);
            $d['user'] = User::getDisplayName($d['cid'], true);
            array_unshift($rez['data'], $d);
        }

        return $rez;
    }
}
