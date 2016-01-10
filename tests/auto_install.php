<?php
/**
 * before testing fisrt try to install CaseBox
 *
 * @author ghindows
 */

include __DIR__ . '/config.php';

include CB_DOC_ROOT . 'classes/UnitTest/Helpers.php';

UnitTest\Helpers::prepareInstance();

echo "Instance for testing prepared, try to init Casebox and start testing" . PHP_EOL;
