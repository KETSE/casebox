<?php

namespace CB\DataModel;

use CB\DB;
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

    protected static $decodeJsonFields = array('data');

    public static function readAll()
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT *
            FROM ' . static::getTableName() .
            ' WHERE user_id = $1',
            User::getId()
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            static::decodeJsonFields($r);
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
