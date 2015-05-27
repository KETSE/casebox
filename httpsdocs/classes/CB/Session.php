<?php

namespace CB;

/**
 * CaseBox database session handling class
 *
 * requires the following database table:
 * CREATE TABLE `sessions` (
 *  `id` varbinary(50) NOT NULL,
 *  `pid` varbinary(50) DEFAULT NULL COMMENT 'parrent session id',
 *  `last_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 *  `expires` timestamp NULL DEFAULT NULL COMMENT 'expire could be null for non expiring sessions',
 *  `user_id` int(10) unsigned NOT NULL,
 *  `data` text,
 *  PRIMARY KEY (`id`),
 *  KEY `idx_expires` (`expires`),
 *  KEY `idx_last_action` (`last_action`),
 *  KEY `idx_pid` (`pid`)
 *) ENGINE=MyISAM DEFAULT CHARSET=utf8
 */

use CB\DB;

class Session implements \SessionHandlerInterface
{
    private $lifetime = 0;

    /**
     * lifetime for previous sessions.
     *
     * We give them a timeout becouse client side can send requests
     * with parent/old session key, until result is received from current executing script.
     *
     * @var integer number of seconds
     */
    private $lifetime_pid_sessions = 3;

    /**
     * on session id regeneration the primary session id is saved in this variable
     * @var varchar
     */
    private $previous_session_id = null;

    /**
     * session close
     * @return bool
     */
    public function close()
    {
        $this->gc($this->lifetime);
        // close database-connection
        return @mysqli_close($GLOBALS['dbh']);
    }

    /**
     * destroy session
     * @param  varchar session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        $res = DB\dbQuery(
            'DELETE FROM sessions WHERE id = $1',
            $session_id
        ) or die(DB\dbQueryError());

        return (DB\dbAffectedRows() > 0);
    }

    /**
     * garbage collector
     * @param  varchar $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        if ($maxlifetime == 0) {
            $maxlifetime = null;
        }
        /* delete expired sessions or/and unlimited sessions older than 3 days. */
        $res = DB\dbQuery(
            'DELETE
            FROM sessions
            WHERE (expires < CURRENT_TIMESTAMP)
                OR (last_action < TIMESTAMPADD( DAY, -3, CURRENT_TIMESTAMP))'
        ) or die(DB\dbQueryError());

        return (DB\dbAffectedRows() > 0);
    }

    /**
     * open session
     * @param  varchar $save_path
     * @param  varchar $name      session name
     * @return bool
     */
    public function open($save_path, $name)
    {
        $this->lifetime = ini_get('session.cookie_lifetime');

        return true;
    }

    /**
     * read session data
     * @param  varchar $session_id
     * @return string
     */
    public function read($session_id)
    {
        $rez = '';
        $res = DB\dbQuery(
            'SELECT data
            FROM sessions
            WHERE id = $1
                AND (
                    (expires > CURRENT_TIMESTAMP)
                    OR (expires IS NULL)
                )',
            $session_id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['data'];
        }
        $res->close();

        $this->previous_session_id = $session_id;

        return $rez;
    }

    /**
     * write session data
     * @param  varchar $session_id
     * @param  varchar $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $lifetime = ini_get('session.cookie_lifetime');
        $lifetime = empty($this->lifetime) ? null : $this->lifetime;

        /* when updating/creating a new session
        then parent session and all other child sessions shoould be marked as
        expiring in corresponding timeout */
        if (!empty($this->previous_session_id)) {
            DB\dbQuery(
                'UPDATE sessions
                SET expires = TIMESTAMPADD(SECOND, $3, CURRENT_TIMESTAMP)
                WHERE (
                    (id = $2) OR
                    (pid = $2)
                    ) and id <> $1',
                array(
                    $session_id
                    ,$this->previous_session_id
                    ,$this->lifetime_pid_sessions
                )
            ) or die(DB\dbQueryError());
        }

        $res = DB\dbQuery(
            'INSERT INTO sessions
            (id, pid, expires, user_id, data)
            VALUES($1, $2, TIMESTAMPADD(SECOND, $3, CURRENT_TIMESTAMP), $4, $5)
            ON DUPLICATE KEY UPDATE
            expires = TIMESTAMPADD(SECOND, $3, CURRENT_TIMESTAMP)
            ,last_action = CURRENT_TIMESTAMP
            ,user_id = $4
            ,data = $5',
            array(
                $session_id
                ,$this->previous_session_id
                ,$lifetime
                ,'0'.@$_SESSION['user']['id']
                ,$session_data
            )
        ) or die(DB\dbQueryError());

        return (DB\dbAffectedRows() > 0);
    }

    /**
     * clear user sessions
     * @param  int     $userId
     * @return boolean
     */
    public static function clearUserSessions($userId)
    {
        if (!Security::canEditUser($userId)) {
            return false;
        }
        DB\dbQuery(
            'DELETE FROM sessions WHERE user_id = $1',
            $userId
        ) or die(DB\dbQueryError());

        return true;
    }
}
