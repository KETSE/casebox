<?php

namespace CB\DataModel;

use CB\DB;

class UsersGroups extends Base
{

    protected static $tableName = 'users_groups';

    protected static $tableFields = array(
        'id' => 'int'
        ,'type' => 'int' //strict value
        ,'system' => 'int' //0, 1
        ,'name' => 'varchar'
        ,'first_name' => 'varchar'
        ,'last_name' => 'varchar'
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

    protected static $decodeJsonFields = array('cfg', 'data');

    protected static $allowReadAll = true;

    /**
     * method to get available user groups
     * @return array associative array: id => array(id, name, title, iconCls)
     */
    public static function getAvailableGroups()
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT id
                ,name
                ,COALESCE(first_name, name) `title`
                ,`system`
                ,`enabled`
            FROM users_groups
            WHERE TYPE = 1
            ORDER BY 3'
        );

        while ($r = $res->fetch_assoc()) {
            $rez[] = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * get available users with some basic data
     * @return array
     */
    public static function getAvailableUsers()
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT
                id
                ,name
                ,first_name
                ,last_name
                ,concat(\'icon-user-\', coalesce(sex, \'\')) `iconCls`
                ,photo
            FROM users_groups
            WHERE `type` = 2
                AND did IS NULL
            ORDER BY 2'
        );

        while ($r = $res->fetch_assoc()) {
            $rez[] = $r;
        }
        $res->close();

        return $rez;
    }

     /**
     * get associated group ids for given user(group) id
     * @param  int   $id
     * @return array
     */
    public static function getMemberGroupIds($id)
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT DISTINCT group_id
            FROM users_groups_association
            WHERE user_id = $1',
            $id
        );

        while ($r = $res->fetch_assoc()) {
            $rez[] = $r['group_id'];
        }
        $res->close();

        return $rez;
    }

     /**
      * get associated user ids for given group id
      * @param  int $id
      * @return array
      */
    public static function getGroupUserIds($id)
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT user_id
            FROM users_groups_association
            WHERE group_id = $1',
            $id
        );

        while ($r = $res->fetch_assoc()) {
            $rez[] = $r['user_id'];
        }
        $res->close();

        return $rez;
    }

    /**
     * method to get users and groups display data in bulk manner (for rendering)
     * @return array associative array: id => array(id, name, title, iconCls)
     */
    public static function getDisplayData()
    {
        $rez = array();

        $sql = 'SELECT id
            ,name
            ,trim( CONCAT(coalesce(first_name, \'\'), \' \', coalesce(last_name, \'\')) ) `title`
            ,CASE WHEN (`type` = 1) THEN \'icon-users\' ELSE CONCAT(\'icon-user-\', coalesce(sex, \'\') ) END `iconCls`
            FROM users_groups';

        $res = DB\dbQuery($sql);

        while ($r = $res->fetch_assoc()) {
            $rez[$r['id']] = $r;
        }
        $res->close();

        return $rez;
    }
}
