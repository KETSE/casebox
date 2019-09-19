<?php
namespace CB\DataModel;

use CB\DB;

class Sessions extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'sessions';

    protected static $tableFields = array(
        'id' => 'varchar'
        ,'pid' => 'varchar'
        ,'last_action' => 'datetime'
        ,'expires' => 'datetime'
        ,'user_id' => 'int'
        ,'data' => 'text'
    );

    public static function read($id)
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT *
            FROM ' . static::getTableName() . '
            WHERE id = $1
                AND (
                    (expires > CURRENT_TIMESTAMP)
                    OR (expires IS NULL)
                )',
            $id
        );

        if ($r = $res->fetch_assoc()) {
            $rez = $r;
        }
        $res->close();

        return $rez;
    }

    public static function updateExpiration($id, $pid, $lifetime)
    {
        DB\dbQuery(
            'UPDATE ' . static::getTableName() . '
            SET expires = TIMESTAMPADD(SECOND, $3, CURRENT_TIMESTAMP)
            WHERE (
                (id = $2) OR
                (pid = $2)
                ) and id <> $1',
            array(
                $id
                ,$pid
                ,$lifetime
            )
        );

        return (DB\dbAffectedRows() > 0);
    }

    public static function replace($data)
    {
        DB\dbQuery(
            'INSERT INTO sessions
            (id, pid, expires, user_id, data)
            VALUES($1, $2, TIMESTAMPADD(SECOND, $3, CURRENT_TIMESTAMP), $4, $5)
            ON DUPLICATE KEY UPDATE
                expires = TIMESTAMPADD(SECOND, $3, CURRENT_TIMESTAMP)
                ,last_action = CURRENT_TIMESTAMP
                ,user_id = $4
                ,data = $5',
            array(
                $data['id']
                ,$data['pid']
                ,$data['lifetime']
                ,$data['user_id']
                ,$data['data']
            )
        );

        return (DB\dbAffectedRows() > 0);
    }
    /**
     * delete expired sessions or/and unlimited sessions older than 3 days.
     * @return boolean
     */
    public static function cleanExpired()
    {
        DB\dbQuery(
            'DELETE
            FROM sessions
            WHERE (expires < CURRENT_TIMESTAMP)
                OR (last_action < TIMESTAMPADD( DAY, -3, CURRENT_TIMESTAMP))'
        );

        return (DB\dbAffectedRows() > 0);
    }

    public static function deleteByUserId($userId)
    {
        DB\dbQuery(
            'DELETE FROM sessions WHERE user_id = $1',
            $userId
        );

        return (DB\dbAffectedRows() > 0);
    }
}
