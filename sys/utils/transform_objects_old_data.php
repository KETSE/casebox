<?php
namespace CB;

/**
 * script for converting old objects data format to new JSON format
 *
 * params:
 *     core_name
 */

// check params
if (empty($argv[1])) {
    die('Please specify a core as first argument');
}
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = $argv[1].'.casebox.local';
$_SESSION['user']['id'] = 1;

require_once '../../httpsdocs/config.php';
require_once '../../httpsdocs/language.php';
require_once '../../httpsdocs/lib/Util.php';
L\initTranslations();

$jsonTransformClass =  new \Util\JSON\Transform();
$jsonTransformClass->execute();

echo "Updating solr ... \n";

$solrClient = new Solr\Client();
$solrClient->updateTree();

echo "\nDone.";
