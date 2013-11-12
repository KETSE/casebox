<?php
namespace CB;

/**
 * script for creating templates in tree
 *
 * params:
 *     core_name
 *     target_id - folder where templates structure should be created.
 *                 If no target id is specified then new templates
 *                 will be created in /Templates folder
 *
 * Note: We'll use here following abreviations:
 *     "tT" for "templatesTemplate"
 *     "fT" for "fieldTemplate"
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

require_once '../crons/init.php';

$GLOBALS['DFT'] = getOption('DEFAULT_FOLDER_TEMPLATE');
// check $pid existance and create Templates Folder if needed
if (is_null($pid)) {
    $pid = Browser::getRootFolderId();
    $res = DB\dbQuery(
        'SELECT id
        FROM tree
        WHERE pid = $1
            AND name = $2
            AND dstatus = 0',
        array(
            $pid
            ,'Templates'
        )
    ) or die(DB\dbQueryError());

    if ($r = $res->fetch_assoc()) {
        $pid = $r['id'];
    } else {
        $folderObj = new Objects\Object();
        $pid = $folderObj->create(
            array(
                'pid' => $pid
                ,'name' => 'Templates'
                ,'template_id' => $GLOBALS['DFT']
            )
        ) or die('Error creating Templates folder');
    }
    $res->close();

} else {
    if (!Objects::idExists($pid)) {
        die('Specified target id does not exist');
    }
}

DB\startTransaction();
// before start we'll execute a special procedure that will clear all lost objects.
// These objects can couse errors on sync templates with tree
DB\dbQuery('CALL p_clear_lost_objects()') or die(DB\dbQueryError());

// define template configs
$tTConfig = array(
    'pid' => $pid
    ,'name' => 'TemplatesTemplate'
    ,'title' => 'Templates template'
    ,'custom_title' => null
    ,'l1' => 'Template for Templates'
    ,'l2' => 'Template for Templates'
    ,'l3' => 'Template for Templates'
    ,'l4' => 'Template for Templates'
    ,'template_id' => null //will be set later, after creation
    ,'iconCls' => 'icon-template'
    ,'type' => 'template'
    ,'fields' => array(
        array(
            'name' => '_title'
            ,'l1' => 'Name'
            ,'l2' => 'Name'
            ,'l3' => 'Name'
            ,'l4' => 'Name'
            ,'type' => 'varchar'
            ,'order' => 0
            ,'cfg' => array(
                'showIn' => 'top'
                ,'readOnly' => true
            )
        )
        ,array(
            'name' => 'type'
            ,'l1' => 'Type'
            ,'l2' => 'Type'
            ,'l3' => 'Type'
            ,'l4' => 'Type'
            ,'type' => '_templateTypesCombo'
            ,'order' => 5
        )
        ,array(
            'name' => 'visible'
            ,'l1' => 'Active'
            ,'l2' => 'Active'
            ,'l3' => 'Active'
            ,'l4' => 'Active'
            ,'type' => 'checkbox'
            ,'order' => 6
            ,'cfg' => array(
                'showIn' => 'top'
            )
        )
        ,array(
            'name' => 'iconCls'
            ,'l1' => 'Icon class'
            ,'l2' => 'Icon class'
            ,'l3' => 'Icon class'
            ,'l4' => 'Icon class'
            ,'type' => 'iconcombo'
            ,'order' => 7
        )
        ,array(
            'name' => 'cfg'
            ,'l1' => 'Config'
            ,'l2' => 'Config'
            ,'l3' => 'Config'
            ,'l4' => 'Config'
            ,'type' => 'text'
            ,'order' => 8
            ,'cfg' => array(
                'height' => 100
            )
        )
        ,array(
            'name' => 'title_template'
            ,'l1' => 'Title template'
            ,'l2' => 'Title template'
            ,'l3' => 'Title template'
            ,'l4' => 'Title template'
            ,'type' => 'text'
            ,'order' => 9
            ,'cfg' => array(
                'height' => 50
            )
        )
        ,array(
            'name' => 'info_template'
            ,'l1' => 'Info template'
            ,'l2' => 'Info template'
            ,'l3' => 'Info template'
            ,'l4' => 'Info template'
            ,'type' => 'text'
            ,'order' => 10
            ,'cfg' => array(
                'height' => 50
            )
        )
    )
);

$fTConfig = array(
    'pid' => $pid
    ,'name' => 'FieldsTemplate'
    ,'title' => 'Fields template'
    ,'custom_title' => null
    ,'l1' => 'Template for Fields'
    ,'l2' => 'Template for Fields'
    ,'l3' => 'Template for Fields'
    ,'l4' => 'Template for Fields'
    ,'template_id' => null //will be set later, after creation
    ,'iconCls' => 'icon-snippet'
    ,'type' => 'field'
    ,'fields' => array(
        array(
            'name' => '_title'
            ,'l1' => 'Name'
            ,'l2' => 'Name'
            ,'l3' => 'Name'
            ,'l4' => 'Name'
            ,'type' => 'varchar'
            ,'order' => 0
            ,'cfg' => array(
                'showIn' => 'top'
                ,'readOnly' => true
            )
        )
        ,array(
            'name' => 'type'
            ,'l1' => 'Type'
            ,'l2' => 'Type'
            ,'l3' => 'Type'
            ,'l4' => 'Type'
            ,'type' => '_fieldTypesCombo'
            ,'order' => 5
        )
        ,array(
            'name' => 'order'
            ,'l1' => 'Order'
            ,'l2' => 'Order'
            ,'l3' => 'Order'
            ,'l4' => 'Order'
            ,'type' => 'int'
            ,'order' => 6
        )
        ,array(
            'name' => 'cfg'
            ,'l1' => 'Config'
            ,'l2' => 'Config'
            ,'l3' => 'Config'
            ,'l4' => 'Config'
            ,'type' => 'text'
            ,'order' => 7
            ,'cfg' => array(
                'height' => 100
            )
        )
        ,array(
            'name' => 'solr_column_name'
            ,'l1' => 'Solr column name'
            ,'l2' => 'Solr column name'
            ,'l3' => 'Solr column name'
            ,'l4' => 'Solr column name'
            ,'type' => 'varchar'
            ,'order' => 8
        )
    )
);
$i = 1;
foreach ($GLOBALS['languages'] as $language) {
    $field = array(
        'name' => 'l'.$i
        ,'l1' => 'Title ('.$language.')'
        ,'l2' => 'Title ('.$language.')'
        ,'l3' => 'Title ('.$language.')'
        ,'l4' => 'Title ('.$language.')'
        ,'type' => 'varchar'
        ,'order' => $i
    );
    // $tTConfig['fields'][] = $field;
    $fTConfig['fields'][] = $field;
    $i++;
}

/* create templates template and fields template */
$fTId = processTemplate($fTConfig, $pid);
$fTObject = new Objects\Template($fTId);
$fTObject->load();

$tTId = processTemplate($tTConfig, $pid);
$tTObject = new Objects\Template($tTId);
$tTObject->load();

// now that we've checked/created basic template, add this item to be available in target folders menu
$menuId = null;
$menu = '';

$res = DB\dbQuery(
    'SELECT id, menu
    FROM menu
    WHERE node_ids = $1',
    $pid
) or die(DB\dbQueryError());

if ($r = $res->fetch_assoc()) {
    $menuId = $r['id'];
    $menu = $r['menu'];
}
$res->close();

if (strpos(','.$menu.',', ','.$tTId.',') === false) {
    if (!empty($menu)) {
        $menu .= ',';
    }
    $menu .= $tTId;
}

DB\dbQuery(
    'INSERT INTO menu (
        id
        ,node_ids
        ,menu)
    VALUES ($1, $2, $3)
    ON DUPLICATE KEY UPDATE
    menu = $3',
    array(
        $menuId
        ,$pid
        ,$menu
    )
) or die(DB\dbQueryError());

// also set menu to add fields when under a template
$menuId = null;
$menu = '';

$res = DB\dbQuery(
    'SELECT id, menu
    FROM menu
    WHERE node_template_ids = $1',
    $tTId.','.$fTId
) or die(DB\dbQueryError());

if ($r = $res->fetch_assoc()) {
    $menuId = $r['id'];
    $menu = $r['menu'];
}
$res->close();

if (strpos(','.$menu.',', ','.$fTId.',') === false) {
    if (!empty($menu)) {
        $menu .= ',';
    }
    $menu .= $fTId;
}

DB\dbQuery(
    'INSERT INTO menu (
        id
        ,node_template_ids
        ,menu)
    VALUES ($1, $2, $3)
    ON DUPLICATE KEY UPDATE
    menu = $3',
    array(
        $menuId
        ,$tTId.','.$fTId
        ,$menu
    )
) or die(DB\dbQueryError());

//iterate templates and create them in tree if not already created
// keep directory structure

iterateTemplates($pid);

echo "Updating menu and config ... \n";
updateMenuAndConfig();

DB\commitTransaction();

echo "Updating solr ... \n";

$solrClient = new Solr\Client();
$solrClient->updateTree(array('all' => true));

echo "Done";

//-------------------------------------------------------------------------------------------------------------------

function iterateTemplates($treePid, $templatesPid = null)
{
    global $tTId, $fTId, $tTObject, $fTObject;

    $templateConfigs = array();

    $folderObj = new Objects\Object();

    $res = DB\dbQuery(
        'SELECT *
        FROM templates
        WHERE ((pid = $1) OR ( ($1 is null) AND (pid is null) ) )
            AND  id not in ('.$tTId.','.$fTId.')
        ',
        $templatesPid
    ) or die(DB\dbQueryError());

    while ($r = $res->fetch_assoc()) {

        // check if it's a folder
        if ($r['is_folder'] == 1) {
            /** check if exists */
            $folderId = null;
            $fres = DB\dbQuery(
                'SELECT id
                FROM tree
                WHERE id = $1
                    and pid = $2
                    AND template_id = $3
                    AND name = $4
                    AND dstatus = 0',
                array(
                    $r['id']
                    ,$treePid
                    ,$GLOBALS['DFT']
                    ,$r['name']
                )
            ) or die(DB\dbQueryError());

            if ($fr = $fres->fetch_assoc()) {
                $folderId = $fr['id'];
            } else {
                $folderId = $folderObj->create(
                    array(
                        'pid' => $treePid
                        ,'name' => $r['name']
                        ,'template_id' => $GLOBALS['DFT']
                    )
                ) or die('Error creating Templates folder');

                DB\dbQuery(
                    'UPDATE templates
                    SET id = $2
                    WHERE id = $1',
                    array(
                        $r['id']
                        ,$folderId
                    )
                ) or die(DB\dbQueryError());

                DB\dbQuery(
                    'UPDATE templates
                    SET pid = $2
                    WHERE pid = $1',
                    array(
                        $r['id']
                        ,$folderId
                    )
                ) or die(DB\dbQueryError());
                $fr['id'] = $folderId;
            }
            $fres->close();

            iterateTemplates($folderId, $fr['id']);
            continue;
        }

        /* set grid values */
        $r['data'] = array(
            $tTObject->getField('_title')['name'] => $r['name']
            // ,$tTObject->getField('l1')['name'] => $r['l1']
            // ,$tTObject->getField('l2')['name'] => $r['l2']
            // ,$tTObject->getField('l3')['name'] => $r['l3']
            // ,$tTObject->getField('l4')['name'] => $r['l4']
            ,$tTObject->getField('type')['name'] => $r['type']
            ,$tTObject->getField('visible')['name'] => $r['visible']
            ,$tTObject->getField('iconCls')['name'] => $r['iconCls']
            ,$tTObject->getField('cfg')['name'] => $r['cfg']
            ,$tTObject->getField('title_template')['name'] => $r['title_template']
            ,$tTObject->getField('info_template')['name'] => $r['info_template']
        );
        /* end of grid values */

        // now loading fields
        $fres = DB\dbQuery(
            'SELECT *
            FROM templates_structure
            WHERE template_id = $1',
            $r['id']
        ) or die(DB\dbQueryError());

        while ($fr = $fres->fetch_assoc()) {
            $fr['cfg'] = Util\toJSONArray($fr['cfg']);
            $r['fields'][] = $fr;
        }
        $fres->close();

        $r['pid'] = $treePid;
        $r['template_id'] = $tTId;
        if (empty($r['name'])) {
            $r['name'] = $r['l1'];
        }
        $r['title'] = $r['l1'];
        $templateConfigs[] = $r;
    }
    $res->close();

    foreach ($templateConfigs as $tC) {
        processTemplate($tC, $treePid);
    }
}

function processTemplate($p, $pid)
{
    global $tTId, $fTId;

    echo "Processing template ".$p['name']." (".@$p['id'].")\n";
    $simpleObject = new Objects\Object();
    $tTObject = new Objects\Template();

    // check  template existance and create it if needed. Also keep its id for further usage
    $tId = null;
    if (!empty($p['id']) && is_numeric($p['id'])) {
        $tId = $p['id'];
    } else {
        $res = DB\dbQuery(
            'SELECT id
            FROM templates
            WHERE
                `type` = $1
                and name = $2',
            array(
                $p['type']
                ,$p['name']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $tId = $r['id'];
        }
        $res->close();
    }

    // create template if does not exist
    if (empty($tId)) {
        $tId = $tTObject->create($p) or die('Cannot create '.$p['name']);
        $p['id'] = $tId;
        if (empty($p['template_id'])) {
            $p['template_id'] = $tId;
        }

        echo "Created ".$p['name']." with id: $tId\n";

    } else {
        //check if template exists in tree
        echo "Found template ".$p['name']." in templates with id: $tId\n";
        //create object in tree if does not exist
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE id = $1
                '.(is_null($p['template_id'])
                    ? ''
                    : 'AND template_id = $2'
                ).'
                AND dstatus = 0',
            array(
                $tId
                ,$p['template_id']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            echo "Found template in tree with id = ".$r['id']."\n";
        } else {
            //create just node in tree and objects data
            echo "Creating template node in tree .. ";
            $tTNewId = $simpleObject->create(
                $p
            ) or die('Cannot create '.$p['name']);
            echo "id = $tTNewId\n";
            /* update template id to new id from tree */
            DB\dbQuery(
                'UPDATE templates
                SET id = $2
                WHERE id = $1 ',
                array(
                    $tId
                    ,$tTNewId
                )
            ) or die(DB\dbQueryError());

            DB\dbQuery(
                'UPDATE templates
                SET pid = $2
                WHERE pid = $1 ',
                array(
                    $tId
                    ,$tTNewId
                )
            ) or die(DB\dbQueryError());

            $GLOBALS['replacedTemplates'][$tId] = $tTNewId;
            // check if folder templates changed
            if ($GLOBALS['DFT'] == $tId) {
                $GLOBALS['DFT'] = $tTNewId;
            }

            $tId = $tTNewId;
        }

        $p['id'] = $tId;
        if (empty($p['template_id'])) {
            $p['template_id'] = $tId;
        }
    }
    echo "Updating ..\n";
    $tTObject->update($p) or die('Cannot update '.$p['name']);
    if ($p['name'] == 'TemplatesTemplate') {
        $tTId = $tId;
    } elseif ($p['name'] == 'FieldsTemplate') {
        $fTId = $tId;
    }

    echo "processing fields\n";
    $tTObject->load();
    $data = $tTObject->getData();
    echo "Loaded template data for fields processing:\n";
    // var_dump($data);
    processFields($data, $p['id']);

    return $tId;
}

function processFields(&$templateConfig, $treePid, $fieldsPid = '')
{
    global $tTId, $fTId, $tTObject, $fTObject;

    $simpleObject = new Objects\Object();

    foreach ($templateConfig['fields'] as $field) {
        // echo " compare ".$field['pid']." with ".$fieldsPid." \n";
        if ((!empty($field['pid']) || !empty($fieldsPid)) &&
            (empty($field['pid']) || !empty($fieldsPid) || ($field['pid'] != $templateConfig['id'])) &&
            ($field['pid'] != $fieldsPid)
        ) {
            continue;
        }
        // in some cores, there fields without name (not good).
        if (empty($field['name'])) {
            $field['name'] = '{unnamed}';
        }
        echo "  ".$field['name'];
        $field['pid'] = $treePid;
        $field['template_id'] = $fTId;
        $field['custom_title'] = $field['name'];
        // check field existance in tree
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE pid = $1
                '.(is_null($fTId)
                    ? ''
                    : 'AND template_id = $2'
                ).'
                AND name = $3
                AND dstatus = 0',
            array(
                $treePid
                ,$fTId
                ,$field['name']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {

        } else {
            //create just node in tree and objects data
            $fNewId = $simpleObject->create(
                $field
            ) or die('Can\'t create field '.$field['name']);

            /* update template id to new id from tree */
            DB\dbQuery(
                'UPDATE templates_structure
                SET id = $2
                WHERE id = $1 ',
                array(
                    $field['id']
                    ,$fNewId
                )
            ) or die(DB\dbQueryError());
            // change pids in config to new pid
            foreach ($templateConfig['fields'] as &$sf) {
                if ($sf['pid'] == $field['id']) {
                    $sf['pid'] = $fNewId;
                }
            }

            $field['id'] = $fNewId;
        }

        /* set grid values */
        if (!empty($fTObject)) {
            $field['data'] = array(
                $fTObject->getField('_title')['name'] => $field['name']
                ,$fTObject->getField('l1')['name'] => $field['l1']
                ,$fTObject->getField('l2')['name'] => $field['l2']
                ,$fTObject->getField('l3')['name'] => $field['l3']
                ,$fTObject->getField('l4')['name'] => $field['l4']
                ,$fTObject->getField('type')['name'] => $field['type']
                ,$fTObject->getField('order')['name'] => $field['order']
                ,$fTObject->getField('cfg')['name'] => json_encode($field['cfg'])
                ,$fTObject->getField('solr_column_name')['name'] => $field['solr_column_name']
            );
        }
        /* end of grid values */
        $fieldObjects = new Objects\TemplateField();
        $fieldObjects->update($field) or die('Cannot update '.$field['name']);

        processFields($templateConfig, $field['id'], $field['id']);
    }
    if (!empty($templateConfig['fields'])) {
        echo "\n";
    }

    return $tTId;
}

// ----------------------------------------------- config functions
function updateMenuAndConfig()
{
    if (empty($GLOBALS['replacedTemplates'])) {
        return;
    }
    $replacements = &$GLOBALS['replacedTemplates'];

    $config = array();
    $res = DB\dbQuery(
        'SELECT id, `value`
         FROM config
        WHERE param LIKE \'%template%\''
    ) or die(DB\dbQueryError());

    while ($r = $res->fetch_assoc()) {
        $config[] = $r;
    }
    $res->close();

    foreach ($replacements as $fromId => $toId) {
        if (empty($fromId)) {
            continue;
        }
        foreach ($config as &$row) {
            $row['value'] = trim(str_replace(','.$fromId.',', ','.$toId.',', ','.$row['value'].','), ',');
        }
    }

    foreach ($config as &$row) {
        DB\dbQuery(
            'UPDATE config
            SET `value` = $2
            WHERE id = $1',
            array(
                $row['id']
                ,$row['value']
            )
        ) or die(DB\dbQueryError());
    }

    // now update menu
    $menu = array();
    $res = DB\dbQuery(
        'SELECT id, `menu`
         FROM menu'
    ) or die(DB\dbQueryError());

    while ($r = $res->fetch_assoc()) {
        $menu[] = $r;
    }
    $res->close();

    foreach ($replacements as $fromId => $toId) {
        if (empty($fromId)) {
            continue;
        }
        foreach ($menu as &$row) {
            $row['menu'] = trim(str_replace(','.$fromId.',', ','.$toId.',', ','.$row['menu'].','), ',');
        }
    }

    foreach ($menu as &$row) {
        DB\dbQuery(
            'UPDATE menu
            SET `menu` = $2
            WHERE id = $1',
            array(
                $row['id']
                ,$row['menu']
            )
        ) or die(DB\dbQueryError());
    }
}
