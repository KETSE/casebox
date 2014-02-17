<?php
namespace Utils;

use CB\L as L;
use CB\DB as DB;

/**
 * script for updating cfg for template field from templates_structure table
 *
 * params:
 *     core_name
 */

// check params
if (empty($argv[1])) {
    die('Please specify a core as first argument');
}
$_SERVER['SERVER_NAME'] = $argv[1].'.casebox.local';
$_SESSION['user']['id'] = 1;

require_once '../crons/init.php';

L\initTranslations();

$res = DB\dbQuery(
    'SELECT id
    FROM templates_structure'
) or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    echo $r['id']." ";
    $obj = \CB\Objects::getCustomClassByObjectId($r['id']);
    if (is_null($obj)) {
        continue;
    }
    $data = $obj->load();
    $data['data']['cfg'] = json_encode($data['cfg'], JSON_UNESCAPED_UNICODE);
    $obj->setData($data);
    $obj->update();
}
$res->close();
echo "\n";

echo "Updating solr ... \n";
$solrClient = new \CB\Solr\Client();
$solrClient->updateTree();
echo "Done";
