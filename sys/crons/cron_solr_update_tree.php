<?php
namespace CB;

require_once 'init.php';

$cron_id = 'solr_update_tree';
$execution_timeout = 60; //default is 60 seconds

$cd = prepareCron($cron_id, $execution_timeout);
if (!$cd['success']) {
    echo "\nerror preparing cron\n";
    exit(1);
}

$last_action_sql = 'UPDATE crons
SET last_action = CURRENT_TIMESTAMP
WHERE cron_id = $1';

$solr = new Solr\Client;

try {
    if (@$argv[2] == 'all') {
        echo "deleting all\n";
        $solr->deleteByQuery('*:*');
        DB\dbQuery($last_action_sql, $cron_id) or die('error updating crons last action');
        echo "updating tree\n";
        $solr->updateTree(array( 'all' => true, 'cron_id' => $cron_id));
        DB\dbQuery($last_action_sql, $cron_id) or die('error updating crons last action');
        echo "optimizing\n";
        $solr->optimize();
        DB\dbQuery($last_action_sql, $cron_id) or die('error updating crons last action');
    } else {
        $solr->updateTree();
    }

} catch (\Exception $e) {
    $msg = 'CaseBox cron execution exception ('.CORENAME."):<br />\n".
        $e->getMessage()."<br />\n".
        $e->getTraceAsString();
    echo $msg;
    notifyAdmin('CaseBox cron execution exception ('.CORENAME.')', $msg);
}

unset($solr);

DB\dbQuery(
    'UPDATE crons
    SET last_end_time = CURRENT_TIMESTAMP, execution_info = \'ok\'
    WHERE cron_id = $1',
    $cron_id
) or die( DB\dbQueryError() );
