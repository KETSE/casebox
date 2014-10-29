<?php
namespace Notifications;

use CB\Config;
use CB\DB;
use CB\Util;
use CB\User;

class Comments extends Objects
{
    /**
     * set last comment id, user_id and date in in sys_data of the parent object
     * @param  array $p params passed to log
     * @return void
     */
    public static function setParentLastCommentData(&$p)
    {
        $o = empty($p['new'])
            ? $p['old']
            : $p['new'];

        $coreName = Config::get('core_name');
        $coreUrl = Config::get('core_url');

        $objData = $o->getData();

        $o = \CB\Objects::getCachedObject($objData['pid']);
        $d = $o->getData();

        $date = $objData['cdate'].'Z';
        $date[10] = 'T';

        $d['sys_data']['lastComment'] = array(
            'id' => $objData['id']
            ,'user_id' => $objData['cid']
            ,'date' => $date
        );

        $o->update($d);
    }

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
        $coreUrl = Config::get('core_url');

        $objData = $o->getData();

        $message  = nl2br(Util\adjustTextForDisplay($objData['data']['_title']));

        static::subscribeMessageUsers($objData['pid'], $message);

        $message = \CB\Objects\Comment::processAndFormatMessage($message);

        $sender = static::getSender();

        $o = \CB\Objects::getCachedObject($objData['pid']);
        $d = $o->getData();

        $subject = '['.$coreName.' #'.$d['id'].'] '.$d['name'].' ('.$d['path'].')';//[$coreName #$nodeId] Comment: $nodeTitle ($nodePath)

        $body  = '<h3><a href="' . $coreUrl . 'v-' . $objData['pid'] . '/">' . \CB\Objects::getName($objData['pid']) . '</a></h3>'.
            $message.
            '<br /><hr />'.
            'To add a comment, reply to this email.<br />
            <a href="#">Unsubscribe</a> (will not receive emails with new comments for “'.$d['name'].'”)';

        $notifiedUsers = static::getNotifiedUsers($objData['pid']);

        //exclude the comment owner from notified users
        $notifiedUsers = array_diff($notifiedUsers, array($objData['cid']));

        if (!empty($notifiedUsers)) {
            foreach ($notifiedUsers as $userId) {
                // if ($userId == $_SESSION['user']['id']) {
                //     continue;
                // }

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

        //add current user to notified users for parent object
        static::addUserToNotifiedUsers($objData['pid'], $objData['cid']);
    }

    /**
     * subscribe users referred in message
     * @param int     $objectId
     * @param varchar $message
     */
    protected static function subscribeMessageUsers($objectId, $message)
    {
        if (preg_match_all('/@([^@\s,]+)/', $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $userId = User::exists($match[1]);
                if (is_numeric($userId)) {
                    static::addUserToNotifiedUsers($objectId, $userId);
                }
            }
        }
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

    protected static function addUserToNotifiedUsers($objectId, $userId = false)
    {
        if ($userId === false) {
            $userId = $_SESSION['user']['id'];
        }
        $o = \CB\Objects::getCachedObject($objectId);
        $d = $o->getData();

        $onUsers = @Util\toNumericArray($d['sys_data']['subscribers']['on']);
        $offUsers = @Util\toNumericArray($d['sys_data']['subscribers']['off']);

        if (in_array($userId, $offUsers)) {
            return;
        }

        if (in_array($userId, $onUsers)) {
            return;
        }

        $onUsers[] = $userId;

        $d['sys_data']['subscribers'] = array(
            'on' => $onUsers
            ,'off' => $offUsers
        );

        $o->setData($d);

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
