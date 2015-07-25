<?php

namespace CB;

/**
 * Script to upgrade menu model from separate table in DB into tree
 *
 * Note: check core_update_init.php description for params
 */

include 'core_update_init.php';

$class = new Import\UpgradeMenuModel($importConfig);

$class->import();

echo "Done\n";
