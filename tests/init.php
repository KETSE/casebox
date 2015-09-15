<?php

/**
 * use it as bootstrap for PHPUnit
 * include things
 *
 * @author ghindows
 */

include __DIR__.'/config.php';

include TEST_PATH . '../vendor/autoload.php';

include CB_DOC_ROOT . 'classes/UnitTest/Helpers.php';

UnitTest\Helpers::init();

ini_set('display_errors', 1);
