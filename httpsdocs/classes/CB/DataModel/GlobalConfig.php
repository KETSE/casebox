<?php
namespace CB\DataModel;

class GlobalConfig extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'config';

    protected static $tableFields = array(
        'id' => 'int'
        ,'pid' => 'int'
        ,'param' => 'varchar'
        ,'value' => 'text'
    );

    protected static $allowReadAll = true;

    public static function getTableName()
    {
        //\CB\Config::get('prefix')
        //we cannot use Config get because this class is used to initialize ocnfig
        $dbName = \CB\PREFIX . '_casebox';

        return "`$dbName`.`" . static::$tableName . '`';
    }
}
