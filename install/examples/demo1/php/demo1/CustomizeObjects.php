<?php
namespace demo1;

use CB\DB as DB;

class CustomizeObjects
{
    public function getCustomInfo($p)
    {
        $rez = array('success' => true, 'data' => 'remote customInfo');
        if (is_numeric($p)) {
            $sql = 'select name from tree where id = $1';
            $res = DB\dbQuery($sql, array($p)) or die(DB\dbQueryError() );
            if ($r = $res->fetch_assoc()) {
                $rez['data'] .= ': '.$r['name'];
            }
            $res->close();
        }

        return $rez;
    }
}
