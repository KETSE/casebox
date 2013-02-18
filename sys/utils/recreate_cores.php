#!/usr/bin/php
<?php
/* recreate all solr cores */
if(PHP_OS == 'WINNT'){
  echo exec('net stop jetty');
}else echo exec('service jetty stop');

remove_indexes('data');
/*if(PHP_OS == 'WINNT'){
  echo exec('net stop jetty');
  echo exec('net start jetty');
}else echo exec('service jetty restart');
/**/
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