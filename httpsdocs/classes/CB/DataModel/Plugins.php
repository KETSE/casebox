<?php

namespace CB\DataModel;

class Plugins extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'plugins';

    protected static $tableFields = array(
        'id' => 'int'
        ,'name' => 'varchar'
        ,'cfg' => 'text'
        ,'active' => 'int'
        ,'order' => 'int'
    );

    protected static $decodeJsonFields = array('cfg');

    protected static $allowReadAll = true;

    public static function getTableName()
    {
        $dbName = \CB\PREFIX . '_casebox';

        return "`$dbName`.`" . static::$tableName . '`';
    }
}
