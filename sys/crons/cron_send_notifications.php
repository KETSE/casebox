<?php
namespace CB;

$cron_id = 'send_notifications';
$execution_timeout = 60; //default is 60 seconds

require_once 'init.php';

$cd = prepareCron($cron_id, $execution_timeout);
if (!$cd['success']) {
    echo "\nerror preparing cron\n";
    exit(1);
}

$users = array();

L\initTranslations();

$languages = Config::get('languages');
$adminEmail = Config::get('ADMIN_EMAIL');
$senderEmail = Config::get('SENDER_EMAIL');

$sql = 'SELECT action_type
        ,object_id
        ,user_id
        ,data
    FROM notifications
    WHERE `action_time` < CURRENT_TIMESTAMP '.(empty($cd['last_start_time']) ? '' : '
        AND `action_time` > \''.$cd['last_start_time'].'\' ').'
        AND user_id IS NOT NULL
    ORDER BY user_id
           , `action_time` DESC';

$res = DB\dbQuery($sql) or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    $data = json_decode($r['data'], true);

    if ($data['body'] == '<generateTaskViewOnSend>') {
        $data['body'] = Tasks::getTaskInfoForEmail($r['object_id'], $r['user_id']);
    } else {
        $data['body'] = stripslashes($data['body']);
    }

    if (!isset($users[$r['user_id']])) {
        $users[$r['user_id']] = User::getPreferences($r['user_id']);
    }

    $users[$r['user_id']]['mails'][] = array($data['subject'], $data['body'], $data['sender']);
}
$res->close();

foreach ($users as $u) {
    if (empty($u['email'])) {
        continue;
    }
    $lang = $languages[$u['language_id']-1];
    if (filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
        foreach ($u['mails'] as $m) {
            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '.
                    '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
                '<html xmlns="http://www.w3.org/1999/xhtml" lang="'.$lang.'" xml:lang="'.$lang.'">'.
                '<head><title>CaseBox</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>'.
                '<body>'.$m[1].'</body></html>';
            //skip sending notifications from devel server to other emails than Admin
            if (isDevelServer() && ($u['email'] !== $adminEmail)) {
                echo 'Devel skip: '.$u['email'].': '.$m[0]."\n";
            } else {
                echo $u['email'].': '.$m[0]."\n";
                mail(
                    $u['email'],
                    $m[0],
                    $message,
                    "Content-type: text/html; charset=utf-8\r\nFrom: ".
                    (empty($m[2])
                        ? $senderEmail
                        : $m[2]
                    )."\r\n"
                );
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

closeCron($cron_id);
