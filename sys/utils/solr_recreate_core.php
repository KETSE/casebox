#!/usr/bin/php
<?php
/*
	Recreate solr core
	this script can be called with parametter - <core_name> (without prefix)
	if no argument is specified or argument = "all", then all solr cores will be recreated

	example: php -f solr_recreate_core.php dev
*/
error_reporting(E_ALL);

if(PHP_OS == 'WINNT'){
  shell_exec('net stop jetty');
}else shell_exec('service jetty stop > /dev/null 2>&1');

define('SOLR_DATA_PATH', realpath('../../data/solr/data').DIRECTORY_SEPARATOR);
$dir = SOLR_DATA_PATH;
$sleep = 15;
if(!empty($argv[1]) && ( $argv[1] !== 'all')){
	$dir.= $argv[1];
	if(!file_exists($dir)) die('core not found');
	$sleep = 5;
}

remove_indexes($dir);

if(PHP_OS == 'WINNT'){
  echo exec('net start jetty');
}else echo exec('service jetty start > /dev/null 2>&1');

echo "\nwaiting $sleep seconds for solr to recreate indexes .... \n";
sleep($sleep);

$path = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'crons'.DIRECTORY_SEPARATOR);
$cmd = 'php -f "'.$path.DIRECTORY_SEPARATOR.'run_cron.php" solr_update_tree '.@$argv[1].' all';
echo shell_exec($cmd);

function remove_indexes($dir) {
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($iterator as $path) {
		if ($path->isDir()){
			$dir = $path->__toString();
			if(substr($dir, -6) == DIRECTORY_SEPARATOR.'index')
				//echo "removing $dir\n";
				rmdir($dir);
		}else{
			//echo "deleting ".$path->__toString()."\n";
			unlink($path->__toString());
		}
	}
}
?>