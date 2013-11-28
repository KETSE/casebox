<?php
namespace CB;

/**
 * script for reindexing a node by it's id
 * used for testing purposes
 *
 * params:
 *     core_name
 *     node_id
 */

// check params
if (empty($argv[1])) {
    die('Please specify a core as first argument');
}
if (empty($argv[2]) || !is_numeric($argv[2])) {
    die('Invalid node id');
}
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = $argv[1].'.casebox.local';
$_SESSION['user']['id'] = 1;

require_once '../../httpsdocs/config.php';
require_once '../../httpsdocs/language.php';
require_once '../../httpsdocs/lib/Util.php';
L\initTranslations();

echo "Reindexing document ... \n";

$solrClient = new Solr\Client();
$solrClient->updateTree(
    array(
        "id" => $argv[2]
    )
);

echo "\nDone.";
