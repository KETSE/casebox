<?php

namespace CB\DataModel;

use CB\Config;
use CB\DB;

class GUID extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'guids';

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
        ,'name' => 'varchar'
    );

    /**
     * add a record
     * @param  array $p associative array with table field values
     * @return int   created id
     */
    public static function create($p)
    {
        \CB\raiseErrorIf(
            empty($p['name']),
            'ErroneousInputData' //' Empty name for GUID.'
        );

        return parent::create($p);
    }

    /**
     * read recods in bulk for given names
     * @param  array       $names
     * @return associative array ('name' => id)
     */
    public static function readNames($names)
    {
        $rez = array();
        $params = array();

        for ($i = 1; $i <= sizeof($names); $i++) {
            $params[] = '$' . $i;
        }
        $sql = 'SELECT id, name
            FROM ' . static::getTableName() . '
            WHERE name in (' . implode(',', $params). ')';

        $res = DB\dbQuery($sql, $names) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez[$r['name']] = $r['id'];
        }
        $res->close();

        return $rez;
    }

    public static function getTableName()
    {
        $dbName = Config::get('prefix') . '__casebox';

        return "`$dbName`.`" . static::$tableName . '`';
    }
}
