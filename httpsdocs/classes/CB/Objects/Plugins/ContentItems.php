<?php

namespace CB\Objects\Plugins;

use CB\User;
use CB\Util;

class ContentItems extends Base
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
                'template_type:object'
            )
            ,'fl' => 'id,pid,name,template_id,cdate,cid'
            ,'sort' => 'cdate'
            ,'dir' => 'desc'
        );

        $folderTemplates = \CB\Config::get('folder_templates');
        if (!empty($folderTemplates)) {
            $params['fq'][] = '!template_id:('.implode(' OR ', Util\toNumericArray($folderTemplates)).')';
        }

        $s = new \CB\Search();
        $sr = $s->query($params);
        foreach ($sr['data'] as $d) {
            $d['ago_text'] = Util\formatAgoTime($d['cdate']);
            $d['user'] = @User::getDisplayName($d['cid']);
            $rez['data'][] = $d;
        }

        return $rez;
    }
}
