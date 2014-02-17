<?php

namespace Utils;

/**
 * script that will do three steps upgrade:
 *     - sync templates to tree
 *     - update old objects data to new format
 *     - sync tags to tree
 *     - sync config from template_fields to correponding field objects from tree
 *
 * IMPORTANT NOTE:
 *     Before runing this script make sure:
 *         - the database structure is fully updated (all structure updates applied)
 *         - update template field names from l1, l2 format to corresponding language abreviation
 *             Example of statements used for this approach:
 *                  UPDATE templates_structure
 *                   SET NAME = CASE NAME
 *                       WHEN 'l1' THEN 'en'
 *                       WHEN 'l2' THEN 'hy'
 *                       ELSE NAME
 *                       END;
 *
 *                    //Update Field titles if needed
 *                    UPDATE templates_structure
 *                    SET l1 = REPLACE(l1, '(fr)', '(hy)')
 *                    WHERE NAME = 'hy';
 *
 *                    UPDATE templates_structure
 *                    SET l2 = REPLACE(l2, '(fr)', '(hy)')
 *                    WHERE NAME = 'hy';
 *
 *                    //delete not needed title fields
 *                    DELETE FROM templates_structure WHERE NAME = 'l3';
 *
 *         - corect/update all title_templates from templates table.
 *             Replace ids with names
 *             For php title templates I suggest the following idea:
 *                 <?php
 *                    $title = '{en}';
 *                    if (empty($title)) {
 *                      $title = '{hy}';
 *                    }
 *                    if (empty($title)) {
 *                       $title = '{template_title}';
 *                    }
 *                 ?>
 */

use CB\L;
use CB\DB;

/**
 * params:
 *     core_name
 *     target_id - folder where templates structure should be created.
 *                 If no target id is specified then new templates
 *                 will be created in /Templates folder
 *
 */

// check params
if (empty($argv[1])) {
    die('Please specify a core as first argument');
}
$_SERVER['SERVER_NAME'] = $argv[1].'.casebox.local';
$_SESSION['user']['id'] = 1;
$pid = null;
if (!empty($argv[2]) && is_numeric($argv[2])) {
    $pid = $argv[2];
}

ini_set('max_execution_time', 0);
require_once '../crons/init.php';

L\initTranslations();

$templatesSyncClass =  new \Util\Templates\TreeSync($pid);
$templatesSyncClass->execute();

$jsonTransformClass =  new \Util\JSON\Transform();
$jsonTransformClass->execute();

$tagsSyncClass =  new \Util\Tags\TreeSync($pid);
$tagsSyncClass->execute();

echo "Sync template fields config:\n";
$res = DB\dbQuery(
    'SELECT id
    FROM templates_structure'
) or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    echo ".";
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
$solrClient->updateTree(
    array('all' => true)
);
echo "\nProcess completed.";
