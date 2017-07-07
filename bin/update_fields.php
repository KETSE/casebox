#!/usr/bin/php
<?php
/*
 Updates objects field names

 When template fields are modified and their names are changed, objects
 based on that template are not automatically updated to take up the
 new field names. This script should be used after a template field name
 has been modified in order to update objects.

 Options:
  -c: core
  -t: comma separated list of template ids (optional)
  -f: field names to update, a comma separated list of
  field name changes where each item is a pair of the old and
  new field name separated by a full colon (:)
  -h --help: prints usage information

 Note! if no template id is specifed the updated will be made to ALL objects
 
 Examples:

 	php update_fields.php -c mycore -t 1123,230 -f age:victim_age,sex:gender
	php update_fields.php -c mycore -f name:first_name

 After running this script, you should update the solr prepared date and reindex the solr core.
	

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

run();

function run () {
	$opts = getopt('c:f:t:h');
	var_dump($opts);
	if (isset($opts['h'])) {
		printUsage();
		return;
	}

	if (!isset($opts['f']) || empty($opts['f'])) {
		println('You have not specified fields to update.');
		printUsage();
		return;
	}
	$ts = null;
	if (!isset($opts['t']) || empty($opts['t'])) {
		$ans = readline('No templates specified. Do you want to update ALL templates? (y/n) ');
		if ($ans != 'y') {
			println('Aborted.');
			return;
		}
	}
	else {
		$ts = parseTemplates($opts['t']);
	}
	$fs = parseFields($opts['f']);
	println("Updating objects...");
	$updater = new FieldUpdater($fs, $ts);
	$updater->updateObjects();
	println('Done');
	println("Now update solr prepared data and reindex the solr core");
}


class FieldUpdater {

	private $templateIds;
	private $fields;

	/**
	 * @param array $fs mapping of old field names to new names
	 * @param array $ts list of template ids
	 */
	function __construct ($fs, $ts=null) {
		$this->templateIds = $ts;
		$this->fields = $fs;
	}

	/**
	 * Fetches and updates objects
	 * based on the set template ids and field
	 * names to udpate
	 */
	public function updateObjects () {
		$res = $this->fetchObjectIds();
		while ($row = $res->fetch_assoc()) {
			$this->updateObject($row['id']);
		}
	}

	/**
	 * update the object with the specified id
	 * @param mixed $id
	 */
	public function updateObject ($id) {
		$o = Objects::getCustomClassByObjectId($id);
		$o->load();
		$data = $o->getData();
		foreach ($this->fields as $old=>$new) {
			if (array_key_exists($old, $data['data'])) {
				$data['data'][$new] = $data['data'][$old];
				unset($data['data'][$old]);
			}
		}
		$o->update($data);
	}

	/**
	 * fetch object ids from the db
	 * @return resource the db cursor
	 */
	private function fetchObjectIds () {
		$q = $this->buildQuery();
		return DB\dbQuery($q);
	}

	/**
	 * builds sql query to use for fetching
	 * objects based on the specified templates
	 * @return string
	 */
	private function buildQuery () {
		$q = "SELECT o.id
			  FROM objects o";
		if (!empty($this->templateIds)) {
			$ids = implode(',', $this->templateIds);
			$q .= " JOIN tree t on o.id=t.id AND t.template_id in (".$ids.")";
		}
		return $q;
	}
}

/**
 * parses the templates string arg and returns
 * an array of template ids
 * @param string $t comma separated list of template
 * ids: id1,id2,id3
 * @return array
 */
function parseTemplates ($t) {
	return explode(',', $t);
}

/**
 * parses the fields string arg and returns
 * an array mapping old fields to new fields
 * @param string $f string-encoded list old to new fields
 * mapping in the form oldName1:newName1,oldName2:newName2
 * @return array associative array mapping old field names
 * to new names
 */
function parseFields ($f) {
	$pairs = explode(',', $f);
	return array_reduce($pairs, function($res, $item) {
		$oldNew =  explode(':', $item);
		$res[$oldNew[0]] = $oldNew[1];
		return $res;
	}, []);
}

/**
 * prints helpful usage info
 */
function printUsage() {
	println('php update_fields.php -c <core> -f <fields> -t <templates>');
	println('Options:');
	println('-c : the Casebox core name without the cb_ prefix');
	println('-f : list of fields to updated in the form  oldName1:newName1,oldName2:newName2');
	println('-t (optional): comma-separated list of template ids');
	println('Note: If option -t is provided, only objects from those templates will be updated,'
		.' otherwise ALL objects will be updated.');
	println();
	println("Example:");
	println("php update_fields.php -c demo -f age:victim_age,sex:gender -t 3849,1234");
}

/**
 * ouputs the specified string followed by a new line
 * @param string $s
 */
function println ($s='') {
	echo "$s\n";
}
