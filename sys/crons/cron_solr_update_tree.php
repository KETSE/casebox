<?php
namespace CB;

$cron_id = 'solr_update_tree';
$execution_timeout = 60; //default is 60 seconds

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'init.php';

$coreName = Config::get('core_name');

$cd = prepareCron($cron_id, $execution_timeout);

if (!$cd['success']) {
    echo "\nerror preparing cron\n";
    exit(1);
}

$last_action_sql =
    'UPDATE crons
    SET last_action = CURRENT_TIMESTAMP
    WHERE cron_id = $1';

$solr = new Solr\Client;

try {
    $all = !empty($scriptOptions['all']);
    $nolimit = !empty($scriptOptions['nolimit']);

    if ($all) {
        //mark all tree nodes as updated
        DB\dbQuery('UPDATE tree SET updated = 1', $cron_id) or die('error updating tree nodes');

        DB\dbQuery($last_action_sql, $cron_id) or die('error updating crons last action');

        echo "updating tree\n";
        $solr->updateTree(
            array(
                'all' => true
                ,'cron_id' => $cron_id
                ,'nolimit' => $nolimit
            )
        );
        DB\dbQuery($last_action_sql, $cron_id) or die('error updating crons last action');

        echo "optimizing\n";
        $solr->optimize();
        DB\dbQuery($last_action_sql, $cron_id) or die('error updating crons last action');

    } else {
        $solr->updateTree(
            array(
                'cron_id' => $cron_id
                ,'nolimit' => $nolimit
            )
        );
    }

} catch (\Exception $e) {
    $msg = 'CaseBox cron execution exception ('.$coreName."):<br />\n".
        $e->getMessage()."<br />\n".
        $e->getTraceAsString();
    echo $msg;
    System::notifyAdmin('CaseBox cron execution exception ('.$coreName.')', $msg);
}

unset($solr);

// closeCron($cron_id);
