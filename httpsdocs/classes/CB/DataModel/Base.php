<?php

namespace CB\DataModel;

use CB\DB;

class Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'table_name';

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
    );

    /**
     * add a record
     * @param  array $p associative array with table field values
     * @return int   created id
     */
    public static function create($p)
    {
        static::validateParamTypes($p);

        return null;
    }

    /**
     * read a record by id
     * @param  int   $id
     * @return array | null
     */
    public static function read($id)
    {
        $rez = null;

        static::validateParamTypes(array('id' => $id));

        //read
        $res = DB\dbQuery(
            'SELECT *
            FROM `' . static::$tableName. '`
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * update a record
     * @param  array   $p array with table properties
     * @return boolean
     */
    public static function update($p)
    {
        if (empty($p['id'])) {
            trigger_error(L\get('ErroneousInputData') . ' no id given for update method', E_USER_ERROR);
        }

        static::validateParamTypes($p);

        return null;
    }

    /**
     * delete a record by its id
     * @param  int     $id
     * @return boolean
     */
    public static function delete($id)
    {
        static::validateParamTypes(array('id' => $id));

        DB\dbQuery(
            'DELETE from `' . static::$tableName . '` ' .
            'WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        $rez = (DB\dbAffectedRows() > 0);

        return $rez;
    }

    /**
     * validate param types
     * @param  array $p
     * @param  array $fields - default is $tableFields
     * @return void  |  throws an exception on error
     */
    protected static function validateParamTypes($p, $fields = false)
    {
        if ($fields === false) {
            $fields = static::$tableFields;
        }

        foreach ($fields as $fn => $ft) {
            $valid = true;

            if (!isset($p[$fn]) || is_null($p[$fn])) {
                continue;
            }

            switch ($ft) {
                case 'int':
                case 'float':
                    $valid = is_numeric($p[$fn]);

                    break;

                case 'bool':
                    $valid = is_bool($p[$fn]);

                    break;

                case 'char':
                    $valid = is_string($p[$fn]) && (mb_strlen($p[$fn]) < 2);

                    break;

                case 'enum':
                case 'varchar':
                case 'text':
                    $valid = is_string($p[$fn]);

                    break;

                case 'time':
                case 'timestamp':
                case 'date':
                    $dt = explode(' ', $p[$fn]);
                    $valid = sizeof($dt) < 3;

                    if ($valid) {
                        $d = explode('-', $dt[0]);
                        $valid = (sizeof($dt) == 3);

                        if ($valid) {
                            $valid = is_numeric($d[0]) &&
                                is_numeric($d[1]) &&
                                is_numeric($d[2]);
                        }
                    }

                    if ($valid && !empty($dt[1])) {
                        $t = explode(':', $dt[1]);
                        $valid = (sizeof($dt) < 4);

                        if ($valid) {
                            $valid = is_numeric($t[0]) &&
                                is_numeric($t[1]) &&
                                (empty($t[2]) || is_numeric($t[2]));
                        }
                    }

                    break;
            }

            if (!$valid) {
                trigger_error(L\get('ErroneousInputData') . ' Invalid value for field "' . $fn . '"', E_USER_ERROR);
            }
        }
    }
}
