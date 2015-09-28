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

        $cp = static::getCreateSqlParams($p);

        //prepare sql
        $sql = 'INSERT INTO ' . static::getTableName() . ' (`' .
            implode('`,`', $cp['fields']) .
            '`) VALUES (' .
            implode(',', $cp['params']) .
            ')';

        //add database record
        DB\dbQuery($sql, $cp['values']) or die(DB\dbQueryError());

        $rez = DB\dbLastInsertId();

        return $rez;
    }

    /**
     * get params for record creation
     * @param  array  $p associative array with table field values
     * @return array(
     *         array $fields
     *         array $params
     *         array $values
     *         )
     */
    public static function getCreateSqlParams($p)
    {
        $p = array_intersect_key($p, static::$tableFields);

        $fields = array_keys($p);
        $values = array_values($p);

        //prepare params
        $params = array_keys($values);
        $params[] = sizeof($params);
        array_shift($params);

        for ($i=0; $i < sizeof($fields); $i++) {
            $params[$i] = '$' . $params[$i];
        }

        return array(
            'fields' => $fields
            ,'params' => $params
            ,'values' => $values
        );
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
            FROM ' . static::getTableName() . '
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
        \CB\raiseErrorIf(
            empty($p['id']),
            'ErroneousInputData' //' no id given for update method
        );

        static::validateParamTypes($p);

        $up = static::getUpdateSqlParams($p);

        //prepare sql
        $sql = 'UPDATE ' . static::getTableName() .
            ' SET ' . implode(',', $up['assignments']) .
            ' WHERE id = $1';

        //add database record
        DB\dbQuery($sql, $up['values']) or die(DB\dbQueryError());

        $rez = (DB\dbAffectedRows() > 0);

        return $rez;
    }

    /**
     * get params for record update
     * @param  array  $p associative array with table field values
     * @return array(
     *         array $fields
     *         array $assignments
     *         array $values
     *         )
     */
    public static function getUpdateSqlParams($p)
    {
        $p = array_intersect_key($p, static::$tableFields);

        $fields = array_values(array_diff(array_keys($p), array('id')));
        $assignments = array();
        $values = array($p['id']);

        $i = 2;

        foreach ($p as $k => $v) {
            if ($k !== 'id') {
                $assignments[] = "`$k` = \$" . $i++;
                $values[] = $v;
            }
        }

        return array(
            'fields' => $fields
            ,'assignments' => $assignments
            ,'values' => $values
        );
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
            'DELETE from ' . static::getTableName() .
            ' WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        $rez = (DB\dbAffectedRows() > 0);

        return $rez;
    }

    /**
     * check if a record exists by its id or name field
     * @param  varchar $idOrName
     * @return boolean
     */
    public static function exists($idOrName)
    {
        $rez = false;
        try {
            $rez = static::read(static::toId($idOrName));
        } catch (\Exception $e) {

        }

        return !empty($rez);
    }

    /**
     * get name for given id or return same result if numeric
     * @param  varchar $idOrName
     * @return int     | null
     */
    public static function toId($idOrName, $nameField = 'name')
    {
        if (!is_numeric($idOrName)) {

            $res = DB\dbQuery(
                'SELECT id
                FROM ' . static::getTableName() .
                ' WHERE ' . $nameField . ' = $1',
                $idOrName
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $idOrName = $r['id'];
            }
            $res->close();
        }

        return $idOrName;
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
                case 'smallint':
                case 'float':
                    $valid = is_numeric($p[$fn]);

                    break;

                // case 'bool':
                //     $valid = is_bool($p[$fn]);

                    break;

                case 'char':
                    $valid = is_string($p[$fn]) && (mb_strlen($p[$fn]) < 2);

                    break;

                case 'enum':
                case 'varchar':
                case 'text':
                    $valid = is_scalar($p[$fn]);

                    break;

                case 'time':
                case 'timestamp':
                case 'date':
                    $dt = explode(' ', $p[$fn]);

                    $valid = sizeof($dt) < 3;

                    if ($valid) {
                        $d = explode('-', $dt[0]);
                        $valid = (sizeof($d) == 3);

                        if ($valid) {
                            $valid = is_numeric($d[0]) &&
                                is_numeric($d[1]) &&
                                is_numeric($d[2]);
                        }
                    }

                    if ($valid && !empty($dt[1])) {
                        $t = explode(':', $dt[1]);
                        $valid = (sizeof($t) < 4);

                        if ($valid) {
                            $valid = is_numeric($t[0]) &&
                                is_numeric($t[1]) &&
                                (empty($t[2]) || is_numeric($t[2]));
                        }
                    }

                    break;
            }

            \CB\raiseErrorIf(
                !$valid,
                'ErroneousInputData' //' Invalid value for field "' . $fn . '"'
            );
        }
    }

    /**
     * get table name that current class operates with
     * @return [type] [description]
     */
    public static function getTableName()
    {
        return static::$tableName;
    }
}
