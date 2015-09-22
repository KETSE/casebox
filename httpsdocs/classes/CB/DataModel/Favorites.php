<?php

namespace CB\DataModel;

use CB\DB;
use CB\Util;
use CB\User;

class Favorites extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'favorites';

    /**
     * available table fields
     *
     * associative array of fieldName => type
     * that is also used for trivial validation of input values
     *
     * @var array
     */
    protected static $tableFields = array(
        'id' => 'int'
        ,'user_id' => 'int'
        ,'node_id' => 'varchar'
        ,'data' => 'text'
    );

    public static function readAll($userId)
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT *
            FROM ' . static::getTableName() .
            ' WHERE user_id = $1',
            $userId
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['data'] = Util\toJSONArray($r['data']);
            $rez[] = $r;
        }
        $res->close();

        return $rez;
    }

    public static function deleteByNodeId($nodeId, $userId = false)
    {
        if ($userId == false) {
            $userId = User::getId();
        }

        DB\dbQuery(
            'DELETE FROM ' . static::getTableName() .
            ' WHERE user_id = $1 AND node_id = $2',
            array(
                $userId
                ,$nodeId
            )
        ) or die(DB\dbQueryError());

        $rez = (DB\dbAffectedRows() > 0);

        return $rez;
    }
}
