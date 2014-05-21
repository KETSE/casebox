<?php
namespace CommentNotifications;

use CB\Config;
use CB\DB;
use CB\Util;
use CB\User;

class Listeners
{
    /**
     * autoset fields
     * @param  object $o
     * @return void
     */
    public function onNodeDbCreate($o)
    {
        if (!is_object($o)) {
            return;
        }
        if ($o->getType() != 'comment') {
            return;
        }

        $coreName = Config::get('core_name');

        $objData = $o->getData();

        $notifiedUsers = $this->getNotifiedUsers($objData['pid']);

        if (!empty($notifiedUsers)) {
            $senderMail = Config::get('comments_email');
            if (empty($senderMail)) {
                $senderMail = Config::get('sender_email');
            }
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
                DB\dbQuery(
                    'INSERT INTO notifications (
                        action_type
                        ,object_id
                        ,sender
                        ,subject
                        ,message
                        ,time
                        ,user_id)
                    VALUES ($1
                        ,$2
                        ,$3
                        ,$4
                        ,$5
                        ,CURRENT_TIMESTAMP
                        ,$6
                        )',
                    array(
                        111
                        ,$objData['id']
                        ,$sender
                        ,$subject
                        ,$body
                        ,$userId
                    )
                ) or die(DB\dbQueryError());
            }
        }

        $this->addCurrentUserToNotifiedUsers($objData['pid']);
    }

    /**
     * get the list of users ids that should be notified on a new comment
     * Note: this function does not exclude current user from the list
     * @param  int   $objectId the id of the parent object
     * @return array
     */
    protected function getNotifiedUsers($objectId)
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

    protected function addCurrentUserToNotifiedUsers($objectId)
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
