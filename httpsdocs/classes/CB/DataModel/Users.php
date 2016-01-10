<?php

namespace CB\DataModel;

use CB\DB;
use CB\Util;

class Users extends UsersGroups
{
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

        $p['type'] = static::$type;

        $rez = parent::create($p);

        return $rez;
    }

    public static function getCreateSqlParams($p)
    {
        $rez = parent::getCreateSqlParams($p);

        $passIdx = array_search('password', $rez['fields']);
        if ($passIdx !== false) {
            $rez['params'][$passIdx] = 'MD5(CONCAT(\'aero\', ' . $rez['params'][$passIdx] . '))';
        }

        return $rez;
    }

    /**
     * update a record
     * @param  array   $p array with properties (id field is required for update)
     * @return boolean
     */
    public static function update($p)
    {
        $p['type'] = static::$type;

        $rez = parent::update($p);

        return $rez;
    }

    public static function getUpdateSqlParams($p)
    {
        $rez = parent::getUpdateSqlParams($p);

        $passIdx = array_search('password', $rez['fields']);
        if ($passIdx !== false) {
            $a = explode('=', $rez['assignments'][$passIdx]);
            $rez['assignments'][$passIdx] = $a[0] . '= MD5(CONCAT(\'aero\', ' . $a[1] . '))';
        }

        return $rez;
    }

    /**
     * update a record by username param
     * @param  array   $p array with properties
     * @return boolean
     */
    public static function updateByName($p)
    {
        \CB\raiseErrorIf(
            empty($p['name']),
            'ErroneousInputData' //' no username specified for updateByName function'
        );

        $p['id'] = static::toId($p['name']);

        return static::update($p);
    }

    /**
     * delete a record by its id
     * @param  []int   $ids
     * @return boolean
     */
    public static function delete($ids)
    {
        $sql = 'DELETE from ' . static::getTableName() .
            ' WHERE `type` = $1 and id';

        if (is_scalar($ids)) {
            static::validateParamTypes(array('id' => $ids));

            DB\dbQuery($sql . ' = $2', array(static::$type, $ids));

        } else {
            $ids = Util\toNumericArray($ids);

            if (!empty($ids)) {
                DB\dbQuery(
                    $sql . ' IN (' . implode(',', $ids) . ')',
                    static::$type
                );
            }
        }

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
            FROM `' . static::getTableName() . '`
            WHERE id = $1  AND `type` = $2' .
            ($onlyActive
                ? ' AND enabled = 1'
                : ''
            );

        $res = DB\dbQuery(
            $sql,
            array($id, static::$type)
        );

        if ($res->fetch_assoc()) {
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
            FROM `' . static::getTableName() . '`
            WHERE name = $1  AND `type` = $2' .
            ($onlyActive
                ? ' AND enabled = 1'
                : ''
            );

        $res = DB\dbQuery(
            $sql,
            array($username, static::$type)
        );

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
                AND enabled = 1
                AND did IS NULL',
            "%$email%"
        );

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
        );

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
        );

        if ($r = $res->fetch_assoc()) {
            $rez = $r['cid'];
        }
        $res->close();

        return $rez;
    }

    public static function auth($login, $pass, $info)
    {
        $rez = false;

        $res = DB\dbQuery(
            'CALL p_user_login($1, $2, $3)',
            array($login, $pass, $info)
        );

        if (($r = $res->fetch_assoc()) && ($r['status'] == 1)) {
            $rez = $r['user_id'];
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
        );

        if ($r = $res->fetch_assoc()) {
            $rez = true;
        }
        $res->close();

        return $rez;
    }
}
