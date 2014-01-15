<?php
namespace Utils;

use CB\L;
use CB\DB;
use CB\Solr;

/**
 * script for creating templates in tree
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

$taskTemplates = array();
/* selecting templates that are of type task */
echo "select task templates\n";
$res = DB\dbQuery(
    'SELECT id
    FROM templates
    WHERE type = \'task\'',
    array()
) or die(DB\dbQueryError());
while ($r = $res->fetch_assoc()) {
    $taskTemplates[] = $r['id'];
}
$res->close();

$fieldsTemplateId = \Util\Templates::getTemplateId(
    array(
        'name' => 'FieldsTemplate'
    )
);

DB\startTransaction();

$tfObject = new \CB\Objects\TemplateField();
echo "creating template fields ..\n";
foreach ($taskTemplates as $templateId) {
    $data = array(
        'id' => null
        ,'pid' => $templateId
        ,'template_id' => $fieldsTemplateId
        ,'name' => '_title'
        ,'data' => array(
            '_title' => '_title'
            ,'type' => 'varchar'
            ,'order' => 1
            ,'cfg' => '{"showIn": "top"}'
        )
    );
    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Title';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $templateId
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'allday'
        ,'data' => array(
            '_title' => 'allday'
            ,'type' => 'checkbox'
            ,'order' => 2
            ,'cfg' => '{"showIn": "top", "value": 1}'
        )
    );
    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'All day';
    }
    $pid = $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $pid
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'date_start'
        ,'data' => array(
            '_title' => 'date_start'
            ,'type' => 'date'
            ,'order' => 3
            ,'cfg' => '{"dependency": {"pidValues": [1]}, "value": "now"}'
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Start';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $pid
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'date_end'
        ,'data' => array(
            '_title' => 'date_end'
            ,'type' => 'date'
            ,'order' => 4
            ,'cfg' => '{"dependency": {"pidValues": [1]} }'
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'End';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $pid
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'datetime_start'
        ,'data' => array(
            '_title' => 'datetime_start'
            ,'type' => 'datetime'
            ,'order' => 5
            ,'cfg' => '{"dependency": {"pidValues": [-1]}, "value": "now"}'
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Start';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $pid
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'datetime_end'
        ,'data' => array(
            '_title' => 'datetime_end'
            ,'type' => 'datetime'
            ,'order' => 6
            ,'cfg' => '{"dependency": {"pidValues": [-1]}}'
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'End';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $templateId
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'assigned'
        ,'data' => array(
            '_title' => 'assigned'
            ,'type' => '_objects'
            ,'order' => 7
            ,'cfg' => '{
                "editor": "form"
                ,"source": "users"
                ,"renderer": "listObjIcons"
                ,"autoLoad": true
                ,"multiValued": true
            }'
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Assigned';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $templateId
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'importance'
        ,'data' => array(
            '_title' => 'importance'
            ,'type' => 'importance'
            ,'order' => 8
            ,'cfg' => '{
                "value": 1
            }'
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Importance';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $templateId
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'category'
        ,'data' => array(
            '_title' => 'category'
            ,'type' => '_objects'
            ,'order' => 9
            ,'cfg' => '{
                "source": "tree"
                ,"renderer": "listObjIcons"
                ,"autoLoad": true
                ,"scope": '.\CB\getOption('task_categories').'
                ,"value": '.\CB\getOption('default_task_category').'
            }'
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Category';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $templateId
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'description'
        ,'data' => array(
            '_title' => 'description'
            ,'type' => 'memo'
            ,'order' => 10
            ,'cfg' => '{
                "height": 100
            }'
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Description';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $templateId
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'reminders'
        ,'data' => array(
            '_title' => 'reminders'
            ,'type' => 'H'
            ,'order' => 11
            ,'cfg' => '{
                "maxInstances": 5
            }'
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Reminders';
    }
    $pid = $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $pid
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'count'
        ,'data' => array(
            '_title' => 'count'
            ,'type' => 'int'
            ,'order' => 12
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Count';
    }
    $tfObject->create($data);

    $data = array(
        'id' => null
        ,'pid' => $pid
        ,'template_id' => $fieldsTemplateId
        ,'name' => 'units'
        ,'data' => array(
            '_title' => 'units'
            ,'type' => 'timeunits'
            ,'order' => 13
        )
    );

    foreach ($GLOBALS['languages'] as $language) {
        $data['data'][$language] = 'Units';
    }
    $pid = $tfObject->create($data);
}

echo "Commiting transaction ..\n";
DB\commitTransaction();

echo "Updating solr ... \n";

$solrClient = new Solr\Client();
$solrClient->updateTree();

echo "Done.";
