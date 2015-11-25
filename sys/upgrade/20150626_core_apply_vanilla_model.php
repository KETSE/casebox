<?php

namespace CB;

/**
 * Script for applying vanilla data model to an existing core.
 * Data model described in https://dev.casebox.org/dev/view/5916/
 *
 * Example usage : php -f core_apply_vanilla_model.php -- -c test_core_name
 *                 php core_apply_vanilla_model.php -c test_core_name -s
 *                 php core_apply_vanilla_model.php -c test_core_name -s /tmp/custom_core_sql_dump.sql
 */

include 'core_init.php';

$vanilla = new Import\VanillaModel($importConfig);

$vanilla->import();

echo "Done\n";
