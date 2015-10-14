<?php

namespace CB\DataModel;

use CB\DB;
use CB\Util;

class Objects //extends Base
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

        if (!empty($ids)) {
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

            while ($r = $res->fetch_assoc()) {
                $r['data'] = Util\jsonDecode($r['data']);
                $r['sys_data'] = Util\jsonDecode($r['sys_data']);
                $rez[] = $r;
            }
            $res->close();
        }

        return $rez;
    }

    /**
     * check if the record with given id is marked as draft
     * @param  int     $id
     * @return boolean
     */
    public static function isDraft($id)
    {
        $rez = false;

        $r = static::read($id);

        if (!empty($r[0]['draft']) && ($r[0]['draft'] != 0)) {
            $rez = true;
        }

        return $rez;
    }
}
