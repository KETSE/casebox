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
    ) or die(DB\dbQueryError());

    exit();
}

L\initTranslations();

$users = array();

$coreName = Config::get('core_name');
$coreUrl = Config::get('core_url');
$languages = Config::get('languages');
$adminEmail = Config::get('ADMIN_EMAIL');

//send notification mails only if not in dev mode or _dev_sent_mails not set to 0
$sendNotificationMails = (
    (Config::get('_dev_mode', 0) == 0) ||
    (Config::get('_dev_send_mail', 1) == 1)
);

//collect notifications to be sent
$recs = DM\Notifications::getUnseen();

foreach ($recs as $r) {
    $uid = $r['to_user_id'];
    if (!isset($users[$uid])) {
        $users[$uid] = User::getPreferences($uid);
    }

    $users[$uid]['mails'][$r['id']] = $r;
}

//iterate mails for each user and send them
foreach ($users as $uid => $u) {
    if (empty($u['email'])) {
        continue;
    }

    $sendType = User::canSendNotifications($uid);

    if ($sendType == false) {
        continue;
    }

    $lang = $languages[$u['language_id']-1];

    if (filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
        //group mails into digest and separate ones (where user is mentioned)
        $mails = array(
            'digest' => array()
            ,'separate' => array()
        );

        foreach ($u['mails'] as $notificationId => $action) {
            //[$core #$nodeId] $action_type $template_name: $object_title
            $templateId = Objects::getTemplateId($action['object_id']);
            $templateName = Objects::getName($templateId);

            $subject = '[' . $coreName . ' #' . $action['object_id'] . '] ' .
                Notifications::getActionDeclination($action['action_type'], $lang) . ' ' .
                $templateName . ' "' . htmlspecialchars_decode($action['data']['name']) . '"';

            //skip sending notifications from devel server to other emails than Admin
            if (!$sendNotificationMails && ($u['email'] !== $adminEmail)) {
                echo 'Devel skip: '.$u['email'] . ': ' . $subject . "\n";

            } else {
                // echo $u['email'].': ' . $subject  . "\n";

                $sender = Notifications::getSender($action['from_user_id']);

                $actionIds = Util\toNumericArray($action['action_ids']);

                $actions = array();
                $actions[sizeof($actionIds) - 1] = $action;

                //remove last action id (already loaded)
                array_shift($actionIds);

                //add all actions if multiple
                if (!empty($actionIds)) {
                    $actionIds = array_reverse($actionIds);

                    $recs = DM\Log::getRecords($actionIds);
                    $actionIds = array_flip($actionIds);

                    foreach ($recs as $r) {
                        $action['object_pid'] = $r['object_pid'];
                        $action['action_time'] = $r['action_time'];
                        $action['data'] = Util\jsonDecode($r['data']);
                        $action['activity_data_db'] = Util\jsonDecode($r['activity_data_db']);

                        $actions[$actionIds[$r['id']]] = $action;
                    }
                }

                for ($i=0; $i < sizeof($actions); $i++) {
                    $a = $actions[$i];

                    $message = Notifications::getMailBodyForAction($a, $u);

                    $isMentioned = (
                        !empty($a['data']['mentioned']) &&
                        in_array($uid, $a['data']['mentioned'])
                    );

                    if ($isMentioned) {
                        $mails['separate'][] = array($subject, $message, $sender, $notificationId);
                    } elseif ($sendType == 'all') {
                        $mails['digest'][] = array($subject, $message, $sender, $notificationId);
                    }
                }
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
                $mail[] = $m[1];
                $sender = $m[2];
                $ids[] = $m[3];
            }
            $mails['separate'][] = array(
                '[' . $coreName . '] Notifications digest'
                ,implode('<hr />', $mail)
                ,$sender
                ,$ids
            );
        }

        $seenMaxId = 0;
        foreach ($mails['separate'] as $mail) {
            //send separate emails group
            // file_put_contents(TEMP_DIR . $mail[3].'.html', $mail[2]."<br />\n<h1>" . $mail[0]. "<h1>" . $mail[1]);
             // COMMENTED FOR TEST
            // echo $u['email'], "\n" . $mail[0] . "\n".$mail[1]."\n\n";
            echo $u['email'].': ' . $mail[0]  . "\n";

            if (!mail(
                $u['email'],
                $mail[0],
                $mail[1],
                "Content-type: text/html; charset=utf-8\r\nFrom: ". $mail[2] . "\r\n"
            )) {
                System::notifyAdmin(
                    'CaseBox cron notification: Cant send notification (' . $mail[3] . ') mail to "'. $u['email'] . '"',
                    $mail[1]
                );
            } else {
                if (is_array($mail[3])) {
                    foreach ($mail[3] as $id) {
                        $seenMaxId = max($seenMaxId, $id);
                    }
                } else {
                    $seenMaxId = max($seenMaxId, $mail[3]);
                }
            }
        }

        if (!empty($seenMaxId)) {
            Notifications::updateLastSeenId($seenMaxId, $uid);
        }

        if ($sendType == 'all') {
            User::setUserConfigParam('lastNotifyTime', Util\dateISOToMysql('now'), $uid);
        }
    }

    DB\dbQuery(
        'UPDATE crons
        SET last_action = CURRENT_TIMESTAMP
        WHERE cron_id = $1',
        $cron_id
    ) or die('error updating crons last action');
}
