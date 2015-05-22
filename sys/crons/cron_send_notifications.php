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
    exit();
}

L\initTranslations();

$users = array();

$coreName = Config::get('core_name');
$coreUrl = Config::get('core_url');
$languages = Config::get('languages');
$sender = Notifications::getSender();
$adminEmail = Config::get('ADMIN_EMAIL');

//send notification mails only if not in dev mode or _dev_sent_mails not set to 0
$sendNotificationMails = (
    (Config::get('_dev_mode', 0) == 0) ||
    (Config::get('_dev_send_mail', 1) == 1)
);

//collect notifications to be sent
$sql = 'SELECT id
    ,object_id
    ,object_pid
    ,user_id
    ,action_type
    ,action_time
    ,data
    ,activity_data_db
FROM action_log
WHERE action_time > $1
ORDER BY user_id
   ,`action_time` DESC';

$res = DB\dbQuery($sql, $cd['last_start_time']) or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    $r['data'] = Util\jsonDecode($r['data']);
    $r['activity_data_db'] = Util\jsonDecode($r['activity_data_db']);

    if (!empty($r['activity_data_db']['fu'])) {
        foreach ($r['activity_data_db']['fu'] as $uid) {
            if (!isset($users[$uid])) {
                $users[$uid] = User::getPreferences($uid);
            }

            $users[$uid]['mails'][$r['id']] = $r;
        }
    }

}
$res->close();

//iterate mails for each user and send them
foreach ($users as $u) {
    if (empty($u['email'])) {
        continue;
    }

    $lang = $languages[$u['language_id']-1];

    if (filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
        foreach ($u['mails'] as $actionLogId => $action) {

            $subject = '[' . $coreName . ' #' . $action['object_id'] . '] ' . L\get('SubscriptionNotification', $lang) . ' "' . $action['data']['name'] . '"';

            //skip sending notifications from devel server to other emails than Admin
            if (!$sendNotificationMails && ($u['email'] !== $adminEmail)) {
                echo 'Devel skip: '.$u['email'] . ': ' . $subject . "\n";

            } else {
                echo $u['email'].': ' . $subject  . "\n";

                $message = Notifications::getMailBodyForAction($action, $u);

                file_put_contents('c:/'.$action['id'].'.html', "<h1>$subject<h1>" . $message);
                /*  COMMENTED FOR TEST
                if (!mail(
                    $u['email'],
                    $subject,
                    $message,
                    "Content-type: text/html; charset=utf-8\r\nFrom: ". $sender . "\r\n"
                )) {
                    $markNotificationAsSent = false;

                    System::notifyAdmin(
                        'CaseBox cron notification: Cant send notification (' . $actionLogId . ') mail to "'. $u['email'] . '"',
                        $message
                    );
                }/**/
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
