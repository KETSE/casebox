<?php
namespace CB;

require_once '../crons/init.php';

echo "Fill tree info table ...\n";
$res = DB\dbQuery('CALL p_update_tree_info()') or die( DB\dbQueryError() );

echo "Calculating security sets ...\n";
Security::calculateUpdatedSecuritySets();

echo "Updating solr ...\n";
echo shell_exec('php -f solr_reindex_core.php '.@$argv[1]);

echo "Done";
