<?php

namespace examples;

/**
 * Objects data is an array with common properties (from tree + tree_info tables) and some custom data.
 * Custom data is specific per object type:
 *     Object - some columns from objects table
 *     File - some columns from files +file_version tables
 *     Task -  some columns from tasks table, reminders
 * Also object properties array will contain a property named "data" that will contain data from its template defined fields.
 *
 * For file uploads you can use for now  Api\Files\upload($p) method, it contains description.
 */
if (empty($argv[1])) {
    die('Please specify a core as first argument');
}
$_GET['core'] = $argv[1];
$_SERVER['SERVER_NAME'] = 'local.casebox.org';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SESSION['user']['id'] = 1;

require_once '../../httpsdocs/config.php';
require_once '../../httpsdocs/lib/Util.php';
require_once '../../httpsdocs/language.php';
/*
$newObjectData = array(
    'pid' => \CB\Browser::getRootFolderId()
    ,'name' => 'Custom object'
    ,'template_id' => \CB\getOption('default_folder_template')
);

$object = new \CB\Objects\Object();

//you can create the object by passing params to create method:
$objectId = $object->create($newObjectData);

//or by setting data an then call create
$newObjectData['pid'] = $objectId;
$newObjectData['id'] = null;
$object->setData($newObjectData);
$objectId2 = $object->create();

//loading and updating an object
$object->load($objectId);
$objectData = $object->getData();
$objectData['name'] = 'Renamed object';
$objectData['data']['_title'] = 'Renamed object';

// you can setData and then call update or just call update with data param
$object->setData($objectData);
$object->update();

// delete method will be available with next commit
// It will have a param to just mark the object as deleted in tree or completely delete object from database

// index in solr
\CB\Solr\Client::runCron();
/**/
$object = new \CB\Objects\Object(2197);
$object->load(2197);
