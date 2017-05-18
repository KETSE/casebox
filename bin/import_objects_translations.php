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

// include cron init script
// sets up the environment and also
// the DB based on the selected core
$cronPath = realpath(
    dirname(__FILE__) . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    'sys' . DIRECTORY_SEPARATOR .
    'crons' . DIRECTORY_SEPARATOR
) . DIRECTORY_SEPARATOR;

$cron_id = 'dummy';
include $cronPath.'init.php';

\CB\Config::setFlag('disableActivityLog', true);


// run the script
run();


/**
 * runs the import script based on cmd option
 */
function run () {
	$options = getopt('c:f:');
	// the `c` (core) param is already checked by the cron init script
	if(!array_key_exists('f', $options)) {
		printLine("Error: please specify filename");
		printUsage();
		return;
	}
	$filename = $options['f'];
	printLine("Opening $filename...");
	$file = fopen($filename, 'r');
	if(!$file) {
		printLine("Error: could not open file");
		return;
	}

	printLine("Importing translations...");
	importTranslationsFromFile($file);

	fclose($file);
	printLine("Done!");
}


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
		if(!$row) continue; // skip empty lines
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
	if(!$obj) {
		printLine("Object #".$id." not found");
		return;
	}
	// load data from DB first in order not to overwrite/lose
	// existing data that's not part of the update
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
	$langs = array_map(trim, array_slice($header, 1));
	return $langs;
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
 * @return array|null associative array with keys id and
 * translations, where translations is an associative
 * array mapping languages to translations for the given object id
 * returns null if the line is empty
 */
function parseNextTranslations ($file, $langs) {
	$row = fgetcsv($file);
	if (empty($row)) {
		// return null on empty lines
		return null;
	}
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

/**
 * helper to echo logs
 * @param string $str
 */
function printLine ($str) {
	echo $str."\n";
}

/**
 * prints usage example and instructions
 */
function printUsage () {
	printLine("USAGE:");
	printLine("php import_objects_translations -c corename -f filename");
}