<?php
/**
 * This script will sort properties from cfg field of templates_structure table in desired order.
 *
 * Acceptable orders are: asc, sense
 * script should be called with 2 arguments: <core_name> <sort_order>
 */
namespace CB;

require_once '../crons/init.php';

/* define sorting arrays used for sorting */
$ascending_sort_array = array(
    "showIn" => 'grid' //tabsheet, top
    ,"readOnly" => false //true
    ,"value" => null
    // ~~~~~~~~~~~~~~~ FIELD FORMS autoLoad
    ,"autoLoad" => false //true
    ,"showDate" => null //"<column_name>"
    ,"fields" => "name" // date, path, project, size, cid, oid, cdate, udate
    // ~~~~~~~~~~~~~~~ GRID CONFIG
    ,"editor" => 'combo' //form
    ,"renderer" => 'string' //listGreenIcons, listObjIcons
    ,"maxInstances" => 1
    ,"dependency" => null
    // ~~~~~~~~~~~~~~~ VALUES SET CONFIG
    ,"multiValued" => false //true
    ,"source" => 'thesauri' //tree, related, field, users, groups, usersgroups, custom
    ,"thesauriId" =>  null //'variable', pid of thesauri set
    ,"scope" => 'tree' //project, parent, self, $node_id, variable    //,"parentIds" => [] //tree ids from which to fetch child elements
    ,"descendants" => false //true

    ,"field" => null //<field_name> //the fieldname of the parent object when source=='field'
    ,"url" => null //<url> //for custom type
    ,"fn" => null //<function_name> //for custom type

    /* filter used for objects */
    ,"tags" => array() //[]
    ,"types" => array() //[]
    ,"templates" => array() //[]
    ,"templateGroups" => array() //[]
);

/* detect required sorting array */
$sort_order = strtolower(trim(@$argv[2]));
if (!in_array($sort_order, array('asc', 'sense'))) {
        die('Unknown ordering');
}
/* end of detect required sorting array */

echo "Reordering field properties ...\n";
$res = DB\dbQuery('SELECT id, name, cfg FROM templates_structure WHERE cfg IS NOT NULL') or die( DB\dbQueryError() );
while ($r = $res->fetch_assoc()) {
    echo 'Field "'.$r['name'].'" ('.$r['id'].'):'."\n";
    $cfg = '';
    if (!empty($r['cfg'])) {
        // echo "  Old config:\n".$r['cfg']."\n";

        $r['cfg'] = json_decode($r['cfg'], true);
        if (is_null($r['cfg'])) {
            echo "  >>>> Error parsing JSON.\n";
            continue;
        }

        if ($sort_order == 'asc') {
            ksort($r['cfg'], SORT_STRING);
            $cfg = &$r['cfg'];
        } else {
            foreach ($ascending_sort_array as $k => $v) {
                if (isset($r['cfg'][$k])) {
                    $cfg[$k] = $r['cfg'][$k];
                    //unsetting processed properties so that remained properties will be appended at the end
                    unset($r['cfg'][$k]);
                }
            }
            // appending remained properties
            foreach ($r['cfg'] as $k => $v) {
                $cfg[$k] = $v;
            }
        }

        /* remove properties that are equal to default */
        foreach ($cfg as $k => $v) {
            if (isset($ascending_sort_array[$k]) && ($ascending_sort_array[$k] == $v)) {
                unset($cfg[$k]);
            }
        }

        /* encoding json and save to db */
        $cfg = json_encode($cfg, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
        // echo "  New Config:\n".$cfg."\n\n";
        DB\dbQuery('UPDATE templates_structure set cfg = $2 where id = $1', array($r['id'], $cfg)) or die(DB\dbQueryError());
    }
}
$res->close();

echo "Done";
