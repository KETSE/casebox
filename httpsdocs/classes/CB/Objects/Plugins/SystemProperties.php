<?php

namespace CB\Objects\Plugins;

use CB\DB;
use CB\User;
use CB\Util;

class SystemProperties extends Base
{

    public function getData($id = false)
    {

        $rez = array(
            'success' => true
        );
        parent::getData($id);

        $res = DB\dbQuery(
            'SELECT
                t.id
                ,ti.path
                ,t.template_id
                ,tt.name `template_name`
                ,t.cid
                ,t.cdate
                ,t.uid
                ,t.udate
                ,t.dstatus
                ,t.did
                ,t.ddate
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            LEFT JOIN tree tt on t.template_id = tt.id
            where t.id = $1',
            $this->id
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $r['cid_text'] = User::getDisplayName($r['cid']);

            $r['cdate_text'] = Util\formatAgoTime($r['cdate']);
            $r['cdate'] = Util\dateMysqlToISO($r['cdate']);
            $r['udate'] = Util\dateMysqlToISO($r['udate']);

            $r['uid_text'] = User::getDisplayName($r['uid']);
            $r['udate_text'] = Util\formatAgoTime($r['udate']);

            if (!empty($r['dstatus'])) {
                $r['did_text'] = User::getDisplayName($r['did']);
                $r['ddate_text'] = Util\formatAgoTime($r['ddate']);
            }

            $rez['data'] = $r;
        }
        $res->close();

        return $rez;
    }
}
