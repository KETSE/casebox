<?php

namespace CB\DataModel;

use CB\DB;
use CB\Util;
use CB\User;

class Notifications extends Base
{
    /**
     * available fields for notifications table
     *
     * associative array of fieldName => type
     *
     * @var array
     */
    protected static $tableFields = array(
        'id' => 'int'
        ,'object_id' => 'int'
        ,'action_id' => 'int'
        ,'action_type' => 'varchar'
        ,'from_user_id' => 'int'
        ,'user_id' => 'int'
        ,'email_sent' => 'int'
        ,'read' => 'int'
    );

    /**
     * add a notification record
     * its a bit different from standart create because it updated the record
     * on key dupliaction
     *
     * @param  array $p associative array with table field values
     * @return int   created id
     */
    public static function add($p)
    {
        static::validateParamTypes($p);

        //prepare params
        $params = array(
            empty($p['object_id']) ? null : $p['object_id']
            ,empty($p['action_id']) ? null : $p['action_id']
            ,empty($p['action_type']) ? null : $p['action_type']
            ,empty($p['from_user_id']) ? null : $p['from_user_id']
            ,empty($p['user_id']) ? null : $p['user_id']
            ,empty($p['email_sent']) ? 0 : $p['email_sent']
        );

        //add database record
        $sql = 'INSERT INTO notifications (
            object_id
            ,action_id
            ,action_ids
            ,action_type
            ,from_user_id
            ,user_id
            ,email_sent
            )
            VALUES($1, $2, $2, $3, $4, $5, $6)

            ON DUPLICATE KEY

            UPDATE
            action_id = $2
            ,action_ids = CASE WHEN `read` = 1 THEN $2 ELSE CONCAT($2, \',\', action_ids) END
            ,email_sent = $6
            ,`read` = 0';

        DB\dbQuery($sql, $params) or die(DB\dbQueryError());

        $rez = DB\dbLastInsertId();

        return $rez;
    }

    /**
     * get last notification records for a given user
     *
     * @param  int   $userId
     * @param  int   $limit  max number of records returned
     * @param  int   $fromId return only notifications newer than given id
     * @return array
     */
    public static function getLast($userId, $limit = 200, $fromId = false)
    {
        $rez = array();

        //validate params
        if (!is_numeric($userId) ||
            !is_numeric($limit) ||
            ($fromId !== false && !is_numeric($fromId))
        ) {
            trigger_error(L\get('ErroneousInputData'), E_USER_ERROR);
        }

        $sql = 'SELECT
            n.id
            ,l.object_id
            ,l.action_type
            ,n.read
            ,n.from_user_id
            ,n.user_id
            ,l.data
            ,l.action_time
        FROM notifications n
        JOIN action_log l
            ON n.action_id = l.id
        WHERE n.user_id = $1 '.
        (empty($fromId) ? '' : ' AND n.id > $2 ') .
        'ORDER BY l.action_time DESC, id DESC
        LIMIT ' . $limit;

        $res = DB\dbQuery(
            $sql,
            array(
                $userId
                ,$fromId
            )
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez[] = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * get notifications that was not sent by mail yet
     *
     * @param  int   $userId optional
     * @return array
     */
    public static function getUnsent($userId = false)
    {
        $rez = array();

        //validate params
        if (($userId !== false) && !is_numeric($userId)) {
            trigger_error(L\get('ErroneousInputData'), E_USER_ERROR);
        }

        $sql = 'SELECT
            n.id
            ,n.object_id
            ,n.action_type
            ,n.user_id `to_user_id`
            ,n.`from_user_id`
            ,l.object_pid
            ,l.action_time
            ,l.data
            ,l.activity_data_db
        FROM notifications n
            JOIN action_log l
                ON n.action_id = l.id
        WHERE n.email_sent = 0 '.
        (($userId == false)
            ? ''
            : ' AND user_id = $1 '
        ) .
        'ORDER BY n.user_id
           ,l.`action_time` DESC';

        $res = DB\dbQuery($sql, $userId) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['data'] = Util\jsonDecode($r['data']);
            $r['activity_data_db'] = Util\jsonDecode($r['activity_data_db']);
            $rez[] = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * get last notifications count
     *
     * @param  int   $userId
     * @param  int   $fromId return only notifications newer than given id
     * @return array
     */
    public static function getCount($userId, $fromId = false)
    {
        $rez = 0;

        //validate params
        if (!is_numeric($userId)) {
            trigger_error(L\get('ErroneousInputData'), E_USER_ERROR);
        }
        if (empty($fromId) || !is_numeric($fromId)) {
            $fromId = 0;
        }

        $sql = 'SELECT count(*) `count`
        FROM notifications
        WHERE user_id = $1 AND id > $2';

        $res = DB\dbQuery(
            $sql,
            array(
                $userId
                ,$fromId
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['count'];
        }
        $res->close();

        return $rez;

    }

    /**
     * mark user notifications as read
     * @param  int   $userId
     * @param  int[] $ids
     * @return void
     */
    public static function markAsRead($userId, $ids)
    {
        //validate params
        if (!is_numeric($userId)) {
            trigger_error(L\get('ErroneousInputData'), E_USER_ERROR);
        }

        $ids = Util\toNumericArray($ids);

        if (!empty($ids)) {
            DB\dbQuery(
                'UPDATE notifications
                SET `read` = 1
                WHERE user_id = $1 AND id IN (' . implode(',', $ids) .')',
                $userId
            ) or die(DB\dbQueryError());
        }
    }

    /**
     * mark all notifications as read for given user
     * @param  int  $userId
     * @return void
     */
    public static function markAllAsRead($userId)
    {
        //validate params
        if (!is_numeric($userId)) {
            trigger_error(L\get('ErroneousInputData'), E_USER_ERROR);
        }

        DB\dbQuery(
            'UPDATE notifications
            SET `read` = 1
            WHERE user_id = $1 AND `read` = 0',
            $userId
        ) or die(DB\dbQueryError());
    }
}
