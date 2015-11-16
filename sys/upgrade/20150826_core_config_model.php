<?php

namespace CB;

/**
 * Script to upgrade core config options to be editable from the tree
 *
 * Note: check core_init.php description for params
 */

include 'core_init.php';

$class = new Import\UpgradeConfigModel($importConfig);

$class->import();

echo "Done\n";
