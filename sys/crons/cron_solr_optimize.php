#!/usr/bin/php
<?php
require_once 'crons_init.php';
require_once CB_SOLR_CLIENT;
global $CB_cores;
foreach($CB_cores as $core){
	$solr = new Apache_Solr_Service('127.0.0.1', 8983, '/solr/'.$core['db_name']);
	if(! $solr->ping()) die('Can\'t connect to SOLR core: '.$core['db_name']);
	print "\nOptimizing core \"".$core['db_name']."\"...";
	$solr->optimize();
	unset($solr);
	print " Done";
}
?>