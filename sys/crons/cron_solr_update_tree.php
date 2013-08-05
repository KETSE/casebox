#!/usr/bin/php
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

$solr = new SolrClient();
try {
    if (@$argv[2] == 'all') {
        echo "deleting all\n";
        $solr->deleteByQuery('*:*');
        DB\mysqli_query_params($last_action_sql, $cron_id) or die('error updating crons last action');
        echo "updating tree\n";
        $solr->updateTree(true, $cron_id);
        DB\mysqli_query_params($last_action_sql, $cron_id) or die('error updating crons last action');
        echo "optimizing\n";
        $solr->optimize();
        DB\mysqli_query_params($last_action_sql, $cron_id) or die('error updating crons last action');
    } else {
        $solr->updateTree();
    }

} catch (\Exception $e) {
    notifyAdmin('CaseBox cron execution exception ('.$solr->core.')', $e->getMessage());
}

unset($solr);

DB\mysqli_query_params(
    'UPDATE crons
    SET last_end_time = CURRENT_TIMESTAMP, execution_info = \'ok\'
    WHERE cron_id = $1',
    $cron_id
) or die( DB\mysqli_query_error() );
