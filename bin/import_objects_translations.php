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


/**
 * reads the speficied csv file handle
 * and imports translations for each row into the DB
 * for the object specified at that row
 * the csv file should have column header as the first line,
 * the first column should be for the object id and the remaining
 * columns for the languages to translate, each language should
 * be specifed by its language code in the header
 * @param handle $file
 */
function importTranslationsFromFile ($file) {
	$langs = parseHeader($file);
	while (!feof($file)) {
		$row = parseNextTranslations($file, $langs);
		updateObjectTranslations($row['id'], $row['translations']);
	}
}


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

/**
 * reads the next line of the specified csv
 * file handle in an attempt to get the languages
 * in the translation, this should be used to
 * read the first line of the file, before other
 * reads are done
 * @param handle $file
 * @return array array of language codes from the csv
 * header row
 */
function parseHeader ($file) {
	$header = fgetcsv($file);
	$langs = array_slice($header, 1);
	return $langs
}

/**
 * reads the next line of the specified csv file
 * handle and maps languages to translations of
 * the object at that row
 * the first column of the row is considered the object
 * id and the remaining columns as translations
 * the number of columns should therefore be 1 + the
 * size of the $langs array
 * @param handle $file
 * @param array $langs array of language codes
 * @return array associative array with keys id and
 * translations, where translations is an associative
 * array mapping languages to translations for the given object id
 */
function parseNextTranslations ($file, $langs) {
	$row = fgetcsv($file);
	$id = $row[0];
	// clean id if it starts with # character
	if($id[0] == '#') {
		$id = substr($id, 1);
	}
	$id = (int) $id;
	$values = array_slice($row, 1);
	$trans = array_combine($langs, $values);
	return [
		"id" => $id,
		"translations" => $trans
	];
}