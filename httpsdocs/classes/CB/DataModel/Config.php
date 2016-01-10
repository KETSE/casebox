<?php
namespace CB\DataModel;

use CB\DB;

class Config extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'config';

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
        ,'pid' => 'int'
        ,'param' => 'varchar'
        ,'value' => 'text'
        ,'order' => 'int'
    );

    protected static $allowReadAll = true;

    public static function readAll()
    {
        $rez = array();

        $sql = 'SELECT *
            FROM ' . static::getTableName() . '
            ORDER BY pid';

        $res = DB\dbQuery($sql . ', `order`'); //order by 'order' field also

        //backward compatibility
        if (empty($res)) {
            $res = DB\dbQuery($sql);
        }

        while ($r = $res->fetch_assoc()) {
            $rez[] = $r;
        }
        $res->close();

        return $rez;
    }
}
