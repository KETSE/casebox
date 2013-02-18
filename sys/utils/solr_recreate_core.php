#!/usr/bin/php
<?php
/* 
	Recreate solr core
	this script can be called with parametter - <core_name> (without prefix)
	if no argument is specified or argument = "all", then all solr cores will be recreated
	
	example: php -f solr_recreate_core.php dev
*/
if(PHP_OS == 'WINNT'){
  echo exec('net stop jetty');
}else echo exec('service jetty stop');

define('SOLR_DATA_PATH', realpath('../../data/solr/data').DIRECTORY_SEPARATOR);
$dir = SOLR_DATA_PATH;
$sleep = 30;
if(!empty($argv[1]) && ( $argv[1] !== 'all')){
	$dir.= $argv[1];
	if(!file_exists($dir)) die('core not found');
	$sleep = 10;
}

remove_indexes($dir);

if(PHP_OS == 'WINNT'){
  echo exec('net start jetty');
}else echo exec('service jetty start');

echo "\nwaiting $sleep seconds for solr to recreate indexes .... \n";
sleep($sleep);
include 'solr_reindex_core.php';

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