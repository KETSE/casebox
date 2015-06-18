<?php

namespace CB\DataModel;

use CB\DB;
use CB\L;

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
        parent::create($p);

        if (empty($p['name'])) {
            trigger_error(L\get('ErroneousInputData') . ' Empty name for GUID.', E_USER_ERROR);
        }
        //prepare params

        //add database record
        $sql = 'INSERT INTO ' . \CB\PREFIX . '_casebox.guids
                (`name`)
                VALUES ($1)';

        DB\dbQuery($sql, $p['name']) or die(DB\dbQueryError());

        $rez = DB\dbLastInsertId();

        return $rez;
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
            FROM ' . \CB\PREFIX . '_casebox.guids
            WHERE name in (' . implode(',', $params). ')';

        $res = DB\dbQuery($sql, $names) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez[$r['name']] = $r['id'];
        }
        $res->close();

        return $rez;
    }
}
