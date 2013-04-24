#!/usr/bin/php
<?php
namespace CB;

require_once 'init.php';

require_once SOLR_CLIENT;

echo "\nOptimizing core \"".config\solr_core."\"...";

$solr = new SolrClien();
$solr->optimize();
unset($solr);
