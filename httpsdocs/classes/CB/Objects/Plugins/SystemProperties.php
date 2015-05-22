<?php

namespace CB\Objects\Plugins;

use CB\User;
use CB\Util;
use CB\Search;

class SystemProperties extends Base
{

    public function getData($id = false)
    {
        $rez = array(
            'success' => true
            ,'data' => array()
        );

        parent::getData($id);

        $obj = $this->getObjectClass();

        $data = $obj->getData();

        $rez['data'] = array_intersect_key(
            $data,
            array(
                'id' => 1
                ,'template_id' => 1
                ,'cid' => 1
                ,'cdate' => 1
                ,'uid' => 1
                ,'udate' => 1
                ,'dstatus' => 1
                ,'did' => 1
                ,'size' => 1
            )
        );
        $d = &$rez['data'];

        $pids = Util\toNumericArray($data['pids']);
        array_pop($pids);
        $d['path'] = implode('/', $pids);

        $arr = array(&$d);
        Search::setPaths($arr);

        $d['template_name'] = Search::getObjectNames($d['template_id'])[$d['template_id']];

        $sd = $obj->getSysData();
        $userId = User::getId();

        $d['subscription'] = 'ignore';
        if (!empty($sd['fu']) && in_array($userId, $sd['fu'])) {
            $d['subscription'] = 'follow';
        } if (!empty($sd['wu']) && in_array($userId, $sd['wu'])) {
            $d['subscription'] = 'watch';
        }

        $d['cid_text'] = User::getDisplayName($d['cid']);

        $d['cdate_text'] = Util\formatAgoTime($d['cdate']);
        $d['cdate'] = Util\dateMysqlToISO($d['cdate']);
        $d['udate'] = Util\dateMysqlToISO($d['udate']);

        $d['uid_text'] = User::getDisplayName($d['uid']);
        $d['udate_text'] = Util\formatAgoTime($d['udate']);

        if (!empty($d['dstatus'])) {
            $d['did_text'] = User::getDisplayName($d['did']);
            $d['ddate_text'] = Util\formatAgoTime($d['ddate']);
        }

        return $rez;
    }
}
