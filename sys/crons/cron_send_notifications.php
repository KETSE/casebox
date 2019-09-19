<?php
namespace CB;

use CB\DataModel as DM;

$cron_id = 'send_notifications';
$execution_timeout = 60; //default is 60 seconds

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'init.php';

$cd = prepareCron($cron_id, $execution_timeout);

if (!$cd['success']) {
    echo "\nerror preparing cron\n";
    exit(1);
}

//dont try to send any notification if the script starts for the first time
if (empty($cd['last_start_time'])) {
    DB\dbQuery(
        'UPDATE notifications SET seen = 1 WHERE seen = 0'
    );

    exit();
}

L\initTranslations();

//send notification mails only if not in dev mode or _dev_sent_mails not set to 0
if ((Config::get('_dev_mode', 0) == 1) &&
    (Config::get('_dev_send_mail', 1) == 0)
) {
    return;
}

//collect notifications to be sent
$recs = DM\Notifications::getUnseen();

$users = groupPerUsers($recs);

//iterate mails for each user and send them
foreach ($users as $uid => $ud) {
    sendUserMails($ud);

    DB\dbQuery(
        'UPDATE crons
        SET last_action = CURRENT_TIMESTAMP
        WHERE cron_id = $1',
        $cron_id
    ) or die('error updating crons last action');
}

//--------------------------------------------------------------------------

/**
 * group given notification records per user
 * @param  array &$recs
 * @return array
 */
function groupPerUsers(&$recs)
{
    $rez = array();
    foreach ($recs as $r) {
        $uid = $r['to_user_id'];
        if (!isset($rez[$uid])) {
            $rez[$uid] = User::getPreferences($uid);
        }

        $rez[$uid]['mails'][$r['id']] = $r;
    }

    return $rez;
}

function sendUserMails($u)
{
    $uid = $u['id'];

    if (empty($u['email'])) {
        return;
    }

    $sendType = User::canSendNotifications($uid);

    if ($sendType == false) {
        return;
    }

    $coreName = Config::get('core_name');
    // $coreUrl = Config::get('core_url');

    $languages = Config::get('languages');

    $lang = $languages[$u['language_id']-1];

    if (filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
        //group mails into digest and separate ones (where user is mentioned)
        $mails = array(
            'digest' => array()
            ,'separate' => array()
        );

        foreach ($u['mails'] as $notificationId => $notification) {
            //[$core #$nodeId] $action_type $template_name: $object_title
            $templateId = Objects::getTemplateId($notification['object_id']);
            $templateName = Objects::getName($templateId);

            $subject = '[' . $coreName . ' #' . $notification['object_id'] . '] ' .
                Notifications::getActionDeclination($notification['action_type'], $lang) . ' ' .
                $templateName . ' "' . htmlspecialchars_decode($notification['data']['name']) . '"';

            $sender = Notifications::getSender($notification['from_user_id']);

            //divide notification into separate number of actions it consist of
            $actions = getNotificationActions($notification);

            for ($i=0; $i < sizeof($actions); $i++) {
                $a = $actions[$i];

                $message = Notifications::getMailBodyForAction($a, $u);

                $isMentioned = (
                    !empty($a['data']['mentioned']) &&
                    in_array($uid, $a['data']['mentioned'])
                );

                $mails[$isMentioned ? 'separate' : 'digest'][] = array(
                    'subject' => $subject,
                    'body' => $message,
                    'sender' => $sender,
                    'nId' => $notificationId
                );
            }
        }

        //merge digest emails group into one email and put it into separate group
        if (sizeof($mails['digest']) == 1) {
            $mails['separate'][] = $mails['digest'][0];

        } elseif (!empty($mails['digest'])) {
            $mail = array();
            $ids = array();
            $sender = '';

            foreach ($mails['digest'] as $m) {
                $mail[] = $m['body'];
                $sender = $m['sender'];
                $ids[] = $m['nId'];
            }

            $mails['separate'][] = array(
                'subject' => '[' . $coreName . '] Notifications digest'
                ,'body' => implode('<hr />', $mail)
                ,'sender' => $sender
                ,'nId' => $ids
            );
        }

        foreach ($mails['separate'] as $mail) {
            echo $u['email'].': ' . $mail['subject']  . "\n";

            if (!mail(
                $u['email'],
                $mail['subject'],
                $mail['body'],
                "Content-type: text/html; charset=utf-8\r\nFrom: ". $mail['sender'] . "\r\n"
            )) {
                System::notifyAdmin(
                    'CaseBox cron notification: Cant send notification (' . $mail['nId'] . ') mail to "'. $u['email'] . '"',
                    $mail['body']
                );
            } else {
                DM\Notifications::markAsSeen($mail['nId'], $uid);
            }
        }

        if (!empty($mails['digest'])) {
            User::setUserConfigParam('lastNotifyTime', Util\dateISOToMysql('now'), $uid);
        }
    }
}

/**
 * split notification into number of actions it consist of
 * @param  array $notification
 * @return array
 */
function getNotificationActions($notification)
{
    $rez = array();

    $actionIds = Util\toNumericArray($notification['action_ids']);

    $rez[sizeof($actionIds) - 1] = $notification;

    //remove last action id (already loaded)
    array_shift($actionIds);

    //add all actions if multiple
    if (!empty($actionIds)) {
        $actionIds = array_reverse($actionIds);

        $recs = DM\Log::getRecords($actionIds);
        $actionIds = array_flip($actionIds);

        foreach ($recs as $r) {
            $notification['object_pid'] = $r['object_pid'];
            $notification['action_time'] = $r['action_time'];
            $notification['data'] = Util\jsonDecode($r['data']);
            $notification['activity_data_db'] = Util\jsonDecode($r['activity_data_db']);

            $rez[$actionIds[$r['id']]] = $notification;
        }
    }

    return $rez;
}
