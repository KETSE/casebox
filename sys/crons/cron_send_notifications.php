<?php
namespace CB;

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
        'UPDATE notifications SET email_sent = -1 WHERE email_sent = 0'
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
$sql = 'SELECT
    n.id
    ,n.object_id
    ,n.action_type
    ,n.user_id `to_user_id`
    ,l.object_pid
    ,l.user_id `from_user_id`
    ,l.action_time
    ,l.data
    ,l.activity_data_db
FROM notifications n
    JOIN action_log l
        ON n.action_id = l.id
WHERE n.email_sent = 0
ORDER BY n.user_id
   ,l.`action_time` DESC';

$res = DB\dbQuery($sql) or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    $r['data'] = Util\jsonDecode($r['data']);
    $r['activity_data_db'] = Util\jsonDecode($r['activity_data_db']);

    $uid = $r['to_user_id'];
    if (!isset($users[$uid])) {
        $users[$uid] = User::getPreferences($uid);
    }

    $users[$uid]['mails'][$r['id']] = $r;
}
$res->close();

//iterate mails for each user and send them
foreach ($users as $u) {
    if (empty($u['email'])) {
        continue;
    }

    $lang = $languages[$u['language_id']-1];

    if (filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
        foreach ($u['mails'] as $notificationId => $action) {
            //[$core #$nodeId] $action_type $template_name: $object_title
            $templateId = Objects::getTemplateId($action['object_id']);
            $templateName = Objects::getName($templateId);

            $subject = '[' . $coreName . ' #' . $action['object_id'] . '] ' .
                Notifications::getActionDeclination($action['action_type'], $lang) . ' ' .
                $templateName . '"' . $action['data']['name'] . '"';

            //skip sending notifications from devel server to other emails than Admin
            if (!$sendNotificationMails && ($u['email'] !== $adminEmail)) {
                echo 'Devel skip: '.$u['email'] . ': ' . $subject . "\n";

            } else {
                echo $u['email'].': ' . $subject  . "\n";

                $message = Notifications::getMailBodyForAction($action, $u);
                $sender = Notifications::getSender($action['from_user_id']);

                file_put_contents(TEMP_DIR . $action['id'].'.html', "$sender<br />\n<h1>$subject<h1>" . $message);
                //  COMMENTED FOR TEST
                if (!mail(
                    $u['email'],
                    $subject,
                    $message,
                    "Content-type: text/html; charset=utf-8\r\nFrom: ". $sender . "\r\n"
                )) {
                    $markNotificationAsSent = false;

                    System::notifyAdmin(
                        'CaseBox cron notification: Cant send notification (' . $notificationId . ') mail to "'. $u['email'] . '"',
                        $message
                    );

                } else {
                    DB\dbQuery(
                        'UPDATE notifications
                        SET email_sent = 1
                        WHERE id = $1',
                        $notificationId
                    ) or die(DB\dbQueryError());
                }
            }
        }
    }

    DB\dbQuery(
        'UPDATE crons
        SET last_action = CURRENT_TIMESTAMP
        WHERE cron_id = $1',
        $cron_id
    ) or die('error updating crons last action');
}
