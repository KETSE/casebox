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
//we should exclude template for fields from processing

$fieldTemplates = array();

DB\startTransaction();
$res = DB\dbQuery(
    'SELECT id FROM templates WHERE `type` = \'field\''
) or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    $fieldTemplates[] = $r['id'];
}
$res->close();
if (empty($fieldTemplates)) {
    $fieldTemplates = '';
} else {
    $fieldTemplates = ' WHERE id '.
        ((sizeof($fieldTemplates) == 1)
            ? '<>'.$fieldTemplates[0]
            : 'NOT IN ('.implode(',', $fieldTemplates).')'
        );
}

echo "Start processing objects :\n";

$res = DB\dbQuery('SELECT id FROM tree'.$fieldTemplates) or die(DB\dbQueryError());
while ($r = $res->fetch_assoc()) {
    echo $r['id'].' ';
    $obj = Objects::getCustomClassByObjectId($r['id']);
    $obj->load();
    $obj->update();
}
$res->close();

echo "\n Done\n\nProcessing Users data:\n";

$user = new User();
$res = DB\dbQuery('SELECT id FROM users_groups WHERE `type` = 2') or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    echo $r['id'].' ';
    @$data = $user->getProfileData($r['id']);
    @$user->saveProfileData($data);
}
$res->close();
echo "\n commiting transaction ... \n";
DB\commitTransaction();

echo "Updating solr ... \n";

$solrClient = new Solr\Client();
$solrClient->updateTree();

echo "\nDone.";
