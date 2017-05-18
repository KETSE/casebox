#!/usr/bin/php
<?php
/*
	Import translations for objects from CSV file

	The first column of the CSV file should be the object id,
	followed by a column for each language, each language
	should be specified by its 2-character ISO code.
	So each row contains translations for one object.

	Command parameters:
	-c : core
	-f : filename

	Example usage:

	php import_objects_translations -c mycore -f filename.csv
*/

namespace CB;

ini_set('max_execution_time', 0);

$cronPath = realpath(
    dirname(__FILE__) . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    'sys' . DIRECTORY_SEPARATOR .
    'crons' . DIRECTORY_SEPARATOR
) . DIRECTORY_SEPARATOR;

$cron_id = 'dummy';
include $cronPath.'init.php';

\CB\Config::setFlag('disableActivityLog', true);


// TESTING
$trans = [
	"bn"=> "পুরুষ ",
	"my"=> "အမျိုးသား "]; //male
$id = 1467;

echo "translating\n";
updateObjectTranslations($id, $trans);

/**
 * updates language translations of the object
 * with the specified id
 * @param number $id
 * @param array $translations associative array mapping
 * language codes and the corresponding text translations
 * for this object, e.g. ["en"=>"Cheeze","fr"=>"Fromage"]
 */
function updateObjectTranslations ($id, $translations) {
	$obj = Objects::getCustomClassByObjectId($id);
	$obj->load();
	$data = $obj->getData();
	foreach($translations as $lg=>$text) {
		$data['data']['title_'.$lg] = $text;
	}
	$obj->update($data);
}