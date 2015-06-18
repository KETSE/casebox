<?php

namespace CB\DataModel;

use CB\DB;
use CB\Util;

class User extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'users_groups';

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
        // type //strict value
        ,'system' => 'int' //0, 1
        ,'name' => 'varchar'
        ,'first_name' => 'varchar'
        ,'last_name' => 'varchar'
        ,'l1' => 'varchar'
        ,'l2' => 'varchar'
        ,'l3' => 'varchar'
        ,'l4' => 'varchar'
        ,'sex' => 'char'
        ,'email' => 'varchar'
        ,'photo' => 'varchar'
        ,'password' => 'varchar'
        ,'recover_hash' => 'varchar'
        ,'language_id' => 'int'
        ,'cfg' => 'text'
        ,'data' => 'text'
        ,'last_action_time' => 'time'
        ,'enabled' => 'int'
        ,'cid' => 'int'
        ,'uid' => 'int'
        ,'did' => 'int'
        ,'ddate' => 'timestamp'
    );

    /**
     * db value for type field
     * @var integer
     */
    protected static $type = 2;

    /**
     * add a record
     * @param  array $p associative array with table field values
     * @return int   created id
     */
    public static function create($p)
    {
        parent::create($p);

        $p = array_intersect_key($p, static::$tableFields);

        $p['type'] = static::$type;

        $fields = array_keys($p);
        $values = array_values($p);

        //prepare params
        $params = array_keys($values);
        $params[] = sizeof($params);
        array_shift($params);

        for ($i=0; $i < sizeof($fields); $i++) {
            $params[$i] = ($fields[$i] == 'password')
                ?'MD5(CONCAT(\'aero\', $' . $params[$i] . '))'
                : '$' . $params[$i];
        }

        //prepare sql
        $sql = 'INSERT INTO `' . static::$tableName . '` (`' .
            implode('`,`', $fields) .
            '`) VALUES (' .
            implode(',', $params) .
            ')';

        //add database record
        DB\dbQuery($sql, $values) or die(DB\dbQueryError());

        $rez = DB\dbLastInsertId();

        return $rez;
    }

    /**
     * update a record
     * @param  array   $p array with properties (id field is required for update)
     * @return boolean
     */
    public static function update($p)
    {
        parent::update($p);

        $p = array_intersect_key($p, static::$tableFields);

        $assignments = array();
        $values = array($p['id']);

        $p['type'] = static::$type;
        $i = 2;

        foreach ($p as $k => $v) {
            switch ($k) {
                case 'id':
                    break;

                case 'password':
                    $assignments[] = "`$k` = MD5(CONCAT('aero', \$" . $i++ . "))" ;
                    $values[] = $v;
                    break;

                default:
                    $assignments[] = "`$k` = \$" . $i++;
                    $values[] = $v;
                    break;
            }
        }

        //prepare sql
        $sql = 'UPDATE `' . static::$tableName . '` ' .
            ' SET ' . implode(',', $assignments) .
            ' WHERE id = $1';

        //add database record
        DB\dbQuery($sql, $values) or die(DB\dbQueryError());

        $rez = (DB\dbAffectedRows() > 0);

        return $rez;
    }

    /**
     * update a record by username param
     * @param  array   $p array with properties
     * @return boolean
     */
    public static function updateByName($p)
    {
        if (empty($p['name'])) {
            trigger_error(L\get('ErroneousInputData') . ' no username specified for updateByName function', E_USER_ERROR);
        }

        $p['id'] = static::getIdByName($p['name']);

        return static::update($p);
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
            'WHERE id = $1 AND `type` = $2',
            array($id, static::$type)
        ) or die(DB\dbQueryError());

        $rez = (DB\dbAffectedRows() > 0);

        return $rez;
    }

    /**
     * check if a given user id exists
     * @param  int     $id
     * @param  int     $onlyActive
     * @return boolean
     */
    public static function idExists($id, $onlyActive = true)
    {
        $rez = false;

        $sql = 'SELECT id
            FROM `' . static::$tableName . '`
            WHERE id = $1  AND `type` = $2' .
            ($onlyActive
                ? ' AND enabled = 1'
                : ''
            );

        $res = DB\dbQuery(
            $sql,
            array($id, static::$type)
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = true;
        }
        $res->close();

        return $rez;
    }

    /**
     * get user id by username
     * @param  varchar $username
     * @param  int     $onlyActive
     * @return int
     */
    public static function getIdByName($username, $onlyActive = true)
    {
        $rez = null;

        $sql = 'SELECT id
            FROM `' . static::$tableName . '`
            WHERE name = $1  AND `type` = $2' .
            ($onlyActive
                ? ' AND enabled = 1'
                : ''
            );

        $res = DB\dbQuery(
            $sql,
            array($username, static::$type)
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        }
        $res->close();

        return $rez;
    }

    /**
     * get user id by email
     * @param  varchar $email
     * @return int     | null
     */
    public static function getIdByEmail($email)
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT id
                ,email
            FROM users_groups
            WHERE email LIKE $1
                AND enabled = 1',
            "%$email%"
        ) or die(DB\dbQueryError());

        while (($r = $res->fetch_assoc()) && empty($rez)) {
            $mails = Util\toTrimmedArray($r['email']);

            for ($i=0; $i < sizeof($mails); $i++) {
                if (mb_strtolower($mails[$i]) == $email) {
                    $rez = $r['id'];
                }
            }
        }

        $res->close();

        return $rez;
    }

    /**
     * get user id by recovery hash
     * @param  varchar $hash
     * @return int     | null
     */
    public static function getIdByRecoveryHash($hash)
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT id
            FROM users_groups
            WHERE recover_hash = $1',
            $hash
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        }
        $res->close();

        return $rez;
    }

    /**
     * get user owner id
     * @param  int $userId
     * @return int
     */
    public static function getOwnerId($userId)
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT cid
            FROM users_groups
            WHERE id = $1',
            $userId
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['cid'];
        }
        $res->close();

        return $rez;
    }

    /**
     * get user owner id
     * @param  int     $userId
     * @param  varchar $pass
     * @return boolean
     */
    public static function verifyPassword($userId, $pass)
    {
        $rez = false;

        $res = DB\dbQuery(
            'SELECT id
            FROM users_groups
            WHERE id = $1
                AND `password`= md5($2)',
            array(
                $userId
                ,'aero'.$pass
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = true;
        }
        $res->close();

        return $rez;
    }
}
