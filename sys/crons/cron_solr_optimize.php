<?php
namespace CB;

$cron_id = null;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'init.php';

require_once SOLR_CLIENT;

echo "\nOptimizing core \"".CONFIG\SOLR_CORE."\"...";

$solr = new Solr\Client();

$solr->optimize();

unset($solr);
