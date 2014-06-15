<?php
namespace Notifications;

use CB\Config;
use CB\DB;
use CB\Util;
use CB\User;

class Comments
{
    /**
     * add notifications for tasks
     * @param  array $p params passed to log
     * @return void
     */
    public static function addNotifications(&$p)
    {

        $o = empty($p['new'])
            ? $p['old']
            : $p['new'];

        $p['type'] = 'comment';

        $coreName = Config::get('core_name');

        $objData = $o->getData();

        $notifiedUsers = static::getNotifiedUsers($objData['pid']);

        if (!empty($notifiedUsers)) {
            $commentsConfig = Config::get('comments_config');

            $senderMail = empty($commentsConfig['email'])
                ? Config::get('sender_email')
                : $commentsConfig['email'];

            $sender = User::getDisplayName(). " (".$coreName.") <".$senderMail.'>'; //<$UserName ($core)> $sender_email

            $o = \CB\Objects::getCachedObject($objData['pid']);
            $d = $o->getData();

            $subject = '['.$coreName.' #'.$d['id'].'] '.$d['name'].' ('.$d['path'].')';//[$coreName #$nodeId] Comment: $nodeTitle ($nodePath)
            $body  = nl2br(Util\adjustTextForDisplay($objData['data']['_title'])).
                '<br /><hr />'.
                'To add a comment, reply to this email.<br />
                <a href="#">Unsubscribe</a> (will not receive emails with new comments for “'.$d['name'].'”)';

            foreach ($notifiedUsers as $userId) {
                if ($userId == $_SESSION['user']['id']) {
                    continue;
                }

                $notifyData = array(
                    'sender' => $sender
                    ,'subject' => $subject
                    ,'body' => $body
                );

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
                        'comment'
                        ,$objData['id']
                        ,@$objData['pid']
                        ,$userId
                        ,json_encode($notifyData, JSON_UNESCAPED_UNICODE)
                    )
                ) or die(DB\dbQueryError());
            }
        }

        static::addCurrentUserToNotifiedUsers($objData['pid']);
    }

    /**
     * get the list of users ids that should be notified on a new comment
     * Note: this function does not exclude current user from the list
     * @param  int   $objectId the id of the parent object
     * @return array
     */
    protected static function getNotifiedUsers($objectId)
    {
        $o = \CB\Objects::getCachedObject($objectId);
        $d = $o->getData();

        $onUsers = @Util\toNumericArray($d['sys_data']['subscribers']['on']);
        $offUsers = @Util\toNumericArray($d['sys_data']['subscribers']['off']);

        $rez = $onUsers;

        if (!in_array($d['oid'], $offUsers)) {
            $rez[] = $d['oid'];
        }

        $assigned = @Util\toNumericArray($o->getFieldValue('assigned', 0)['value']);
        foreach ($assigned as $userId) {
            if (!in_array($userId, $offUsers)) {
                $rez[] = $userId;
            }
        }

        $rez = array_unique($rez);

        return $rez;
    }

    protected static function addCurrentUserToNotifiedUsers($objectId)
    {
        $o = \CB\Objects::getCachedObject($objectId);
        $d = $o->getData();

        $onUsers = @Util\toNumericArray($d['sys_data']['subscribers']['on']);
        $offUsers = @Util\toNumericArray($d['sys_data']['subscribers']['off']);

        if (in_array($_SESSION['user']['id'], $offUsers)) {
            return;
        }

        if (in_array($_SESSION['user']['id'], $onUsers)) {
            return;
        }

        $onUsers[] = $_SESSION['user']['id'];

        $d['sys_data']['subscribers'] = array(
            'on' => $onUsers
            ,'off' => $offUsers
        );
        DB\dbQuery(
            'INSERT INTO objects
            (id, sys_data)
            VALUES ($1, $2)
            ON DUPLICATE KEY
            UPDATE sys_data = $2',
            array(
                $objectId
                ,json_encode($d['sys_data'], JSON_UNESCAPED_UNICODE)
            )
        ) or die(DB\dbQueryError());
    }
}
