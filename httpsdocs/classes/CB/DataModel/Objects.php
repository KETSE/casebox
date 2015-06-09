<?php

namespace CB\DataModel;

use CB\DB;
use CB\Util;

class Objects extends Base
{
    /**
     * read objects data in bulk manner
     * @param  array $ids
     * @return array
     */
    public static function read($ids)
    {
        $rez = array();
        $ids = Util\toNumericArray($ids);

        if (empty($ids)) {
            return $rez;
        }

        $sql = 'SELECT t.*
                ,ti.pids
                ,ti.path
                ,ti.case_id
                ,ti.acl_count
                ,ti.security_set_id
                ,o.data
                ,o.sys_data
            FROM tree t
            JOIN tree_info ti
                ON t.id = ti.id
            LEFT JOIN objects o
                ON t.id = o.id
            WHERE t.id in (' . implode(',', $ids) .')';

        $res = DB\dbQuery($sql) or die(DB\dbQueryError());

        // if ($rez = $res->fetch_all(MYSQLI_ASSOC)) {
            // foreach ($rez as &$r) {
        while ($r = $res->fetch_assoc()) {
            $r['data'] = Util\jsonDecode($r['data']);
            $r['sys_data'] = Util\jsonDecode($r['sys_data']);
            $rez[] = $r;
            // }
        }
        $res->close();

        return $rez;
    }
}
