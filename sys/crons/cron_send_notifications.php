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
// $log_groups = array(
// 		1 => 'activity' //login
// 		,2 => 'activity' //logout
// 		,3 => 'Cases'// Add case
// 		,4 => 'Cases' // update case
// 		,5 => 'Cases' // delete case
// 		,6 => 'Cases'// open case
// 		,7 => 'Cases'// close case
// 		,8 => 'Cases objects'// add object
// 		,9 => 'Cases objects'// update object
// 		,10 => 'Cases objects'// delete object
// 		,11 => 'Cases objects'// open object
// 		,12 => 'Cases objects'// get objects info
// 		,13 => 'Cases files'// add file
// 		,14 => 'Cases files'// download file
// 		,15 => 'Cases files'// delete file
// 		,16 => 'Cases'// Update case security roghts
// 		,17 => 'Cases access'// add access to case
// 		,18 => 'Cases access'// remove access from case
// 		,19 => 'Cases access'// grant access to case
// 		,20 => 'Cases access'// close access to case
// 		,21 => 'Deadlines'// add deadline
// 		,22 => 'Deadlines'// update deadline
// 		,23 => 'Deadlines'// complete deadline
// 		,24 => 'Deadlines'// remove deadline
// 		,25 => 'Deadlines'// remove deadline
// 		,26 => 'Deadlines'// remove deadline
// 		,27 => 'Deadlines'// remove deadline
// );

L\initTranslations();

$sql = 'SELECT action_type
        ,task_id
        ,user_id
        ,sender
        ,subject
        ,message
    FROM notifications
    WHERE `time` < CURRENT_TIMESTAMP '.(empty($cd['last_start_time']) ? '' : '
        AND `time` > \''.$cd['last_start_time'].'\' ').'
        AND user_id IS NOT NULL
    ORDER BY user_id
           , `time` DESC';
$res = DB\dbQuery($sql) or die(DB\dbQueryError());
while ($r = $res->fetch_assoc()) {
    if ($r['message'] == '<generateTaskViewOnSend>') {
        $r['message'] = Tasks::getTaskInfoForEmail($r['task_id'], $r['user_id']);
    } else {
        $r['message'] = stripslashes($r['message']);
    }
    if (!isset($users[$r['user_id']])) {
        $users[$r['user_id']] = User::getPreferences($r['user_id']);
    }
    $users[$r['user_id']]['mails'][] = array($r['subject'], $r['message'], $r['sender']);
}
$res->close();
foreach ($users as $u) {
    if (empty($u['email'])) {
        continue;
    }
    $lang = $GLOBALS['languages'][$u['language_id']-1];
    if (filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
        foreach ($u['mails'] as $m) {
            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '.
                    '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
                '<html xmlns="http://www.w3.org/1999/xhtml" lang="'.$lang.'" xml:lang="'.$lang.'">'.
                '<head><title>CaseBox</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>'.
                '<body style="font: normal 11px tahoma,arial,helvetica,sans-serif; line-height: 18px">'.$m[1].'</body></html>';
            //skip sending notifications from devel server to other emails than Admin
            if (isDevelServer() && ($u['email'] !== CONFIG\ADMIN_EMAIL)) {
                echo 'Devel skip: '.$u['email'].': '.$m[0]."\n";
            } else {
                echo $u['email'].': '.$m[0]."\n";
                mail(
                    $u['email'],
                    $m[0],
                    $message,
                    "Content-type: text/html; charset=utf-8\r\nFrom: ".
                    (empty($m[2])
                        ? CONFIG\SENDER_EMAIL
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
DB\dbQuery(
    'UPDATE crons
    SET last_end_time = CURRENT_TIMESTAMP, execution_info = $2
    WHERE cron_id = $1',
    array(
        $cron_id
        ,'ok'
    )
) or die(DB\dbQueryError());
