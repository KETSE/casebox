<?php

namespace CB;

/**
 * CaseBox database session handling class
 *
 */

use CB\DB;
use CB\DataModel as DM;

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
        $rez = true;
        $this->gc($this->lifetime);

        // close database-connection
        $rez = DB\close();

        return $rez;
    }

    /**
     * destroy session
     * @param  varchar $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        $rez = DM\Sessions::delete($sessionId);

        return $rez;
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

        return DM\Sessions::cleanExpired();
    }

    /**
     * open session
     * @param  varchar $save_path
     * @param  varchar $name      session name
     * @return bool
     */
    public function open($savePath, $name)
    {
        $this->lifetime = ini_get('session.cookie_lifetime');

        return true;
    }

    /**
     * read session data
     * @param  varchar $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        $rez = '';

        $r = DM\Sessions::read($sessionId);
        if (!empty($r)) {
            $rez = $r['data'];
        }

        $this->previous_session_id = $sessionId;

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
            DM\Sessions::updateExpiration(
                $session_id,
                $this->previous_session_id,
                $this->lifetime_pid_sessions
            );
        }

        $rez = DM\Sessions::replace(
            array(
                'id' => $session_id
                ,'pid' => $this->previous_session_id
                ,'lifetime' => $lifetime
                ,'user_id' => '0'.@$_SESSION['user']['id']
                ,'data' => $session_data
            )
        );

        return $rez;
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

        DM\Sessions::deleteByUserId($userId);

        return true;
    }
}
