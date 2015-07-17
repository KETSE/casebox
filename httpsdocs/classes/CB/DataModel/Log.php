<?php

namespace CB\DataModel;

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
    public static function update($p)
    {
    }
}
