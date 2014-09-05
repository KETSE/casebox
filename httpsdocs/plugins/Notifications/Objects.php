<?php
namespace Notifications;

use CB\Config;
use CB\DB;
use CB\L;
use CB\User;

class Objects
{
    /**
     * add notifications for item subscriptions
     * @param  array $p params passed to log
     * @return void
     */
    public static function addNotifications(&$p)
    {

        $obj = empty($p['new'])
            ? $p['old']
            : $p['new'];

        $data = $obj->getData();
        $id = $data['id'];

        $coreName = Config::get('core_name');
        $coreUrl = Config::get('core_url');

        $sender = static::getSender();

        $usersToNotify = static::getUsersToNotify($id, $p['logData']['pids']);

        //exclude current user from notified users
        $usersToNotify = array_diff($usersToNotify, array($_SESSION['user']['id']));

        foreach ($usersToNotify as $userId) {
            $u = User::getPreferences($userId);
            $l = $u['language'];
            $subject = '['.$coreName.' #' . $id . '] ' . L\get('SubscriptionNotification', $l). ' "'.$data['name'].'"';

            $notifyData = array(
                'sender' => $sender
                ,'subject' => $subject
                ,'body' => 'Item <a href="' . $coreUrl . 'v-' . $id . '">' . $data['name'] . ' suffered following action: ' . $p['type'] . '.'
            );

            // insert notification into myslq
            DB\dbQuery(
                'INSERT INTO notifications (
                    action_type
                    ,object_id
                    ,object_pid
                    ,user_id
                    ,data
                )
                VALUES (
                    $1
                    ,$2
                    ,$3
                    ,$4
                    ,$5
                )
                ON DUPLICATE KEY UPDATE
                object_pid = $3
                ,data = $4
                ,action_time = CURRENT_TIMESTAMP',
                array(
                    $p['type']
                    ,$id
                    ,@$data['pid']
                    ,$userId
                    ,json_encode($notifyData, JSON_UNESCAPED_UNICODE)
                )
            ) or die(DB\dbQueryError());
        }

    }

    /**
     * get the sender formated string
     * @return varchar
     */
    protected static function getSender()
    {
        $coreName = Config::get('core_name');

        $commentsConfig = Config::get('comments_config');

        $senderMail = empty($commentsConfig['email'])
            ? Config::get('sender_email')
            : $commentsConfig['email'];

        $rez = mb_encode_mimeheader(User::getDisplayName(), 'UTF-8')
            ." (" . mb_encode_mimeheader($coreName, 'UTF-8') . ")"
            ." <" . $senderMail . '>';

        return $rez;
    }

    /**
     * get the list of users to be notified
     * @param  int   $id
     * @param  array $pids
     * @return array
     */
    private static function getUsersToNotify($id, $pids)
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT DISTINCT user_id
            FROM user_subscriptions
            WHERE object_id = $1

            UNION

            SELECT DISTINCT user_id
            FROM user_subscriptions
            WHERE object_id IN (0' . implode(',', $pids). ')
                AND recursive = 1',
            $id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez[] = $r['user_id'];
        }
        $res->close();

        return array_unique($rez);
    }
}
