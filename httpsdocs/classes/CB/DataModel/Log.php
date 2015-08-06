<?php

namespace CB\DataModel;

use CB\DB;
use CB\Util;

class Log extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'action_log';

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
        ,'object_id' => 'int'
        ,'object_pid' => 'int'
        ,'user_id' => 'int'
        ,'action_type' => 'enum'
        ,'action_time' => 'time'
        ,'data' => 'text'
        ,'activity_data_db' => 'text'
        ,'activity_data_solr' => 'text'
    );

    /**
     * update a record
     * @param  array   $p array with properties
     * @return boolean
     */
    public static function getRecords($ids)
    {
        $rez = array();
        $ids = Util\toNumericArray($ids);

        $res = DB\dbQuery(
            'SELECT *
            FROM `' . static::getTableName() . '`
            WHERE id in (' . implode(',', $ids). ')'
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $rez[] = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * update a record
     * @param  array   $p array with properties
     * @return boolean
     */
    public static function update($p)
    {
    }
}
