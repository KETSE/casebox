<?php

namespace CB;

/**
 * Script to add time_tracking template to core
 *
 * Note: check core_init.php description for params
 */

include 'core_init.php';

$class = new Import\TimeTrackingModel($importConfig);

$class->import();

echo "Done\n";
