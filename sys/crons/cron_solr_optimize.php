<?php
namespace CB;

require_once 'init.php';

require_once SOLR_CLIENT;

echo "\nOptimizing core \"".CONFIG\SOLR_CORE."\"...";

$solr = new Solr\Client();

$solr->optimize();

unset($solr);
