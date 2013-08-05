#!/usr/bin/php
<?php
namespace CB;

$cron_id = 'check_deadlines';
$execution_timeout = 60; //default is 60 seconds

include 'init.php';

/* try to preapare cron */
$cd = prepareCron($cron_id, $execution_timeout);
if (!$cd['success']) {
    echo "\nerror preparing cron\n";
    exit(1);
}

L\initTranslations();

/* iterate through active tasks with deadlines and check each task on expiration */
$sql = 'SELECT id
     , `title`
     , cid
     , responsible_user_ids
     , CASE
           WHEN allday=1 THEN date_end < CURRENT_DATE
           ELSE date_end <= date_end
       END `expired`
FROM tasks
WHERE `type` = 6
    AND `status` IN (2, 4)
    AND has_deadline = 1';

$res = DB\mysqli_query_params($sql) or die(DB\mysqli_query_error());
while ($r = $res->fetch_assoc()) {
    echo " task ".$r['id'].': '.$r['expired'];
    if ($r['expired'] == 1) {
        // update task as expired
        DB\mysqli_query_params('UPDATE tasks SET status = 1 WHERE id = $1', $r['id']) or die(DB\mysqli_query_error());
        Log::add(
            array(
                'action_type' => 28
                ,'task_id' => $r['id']
                ,'remind_users' => $r['cid'].','.$r['responsible_user_ids']
                ,'info' => 'title: '.$r['title']
            )
        );
    }
    //update cron last_action status
    DB\mysqli_query_params('UPDATE crons SET last_action = CURRENT_TIMESTAMP WHERE cron_id = $1', $cron_id) or die('error updating crons last action');
}
$res->close();

// writing cron execution info
DB\mysqli_query_params(
    'UPDATE crons SET last_end_time = CURRENT_TIMESTAMP, execution_info = $2 WHERE cron_id = $1',
    array($cron_id, 'ok')
) or die(DB\mysqli_query_error());

//Starting reindexing cron to update changes into solr
SolrClient::runCron();
