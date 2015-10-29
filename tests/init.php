<?php

/**
 * use it as bootstrap for PHPUnit
 * include things
 *
 * @author ghindows
 */

include __DIR__.'/config.php';

include realpath( TEST_PATH . '/../').'/vendor/autoload.php';

include CB_DOC_ROOT . 'classes/UnitTest/Helpers.php';

require_once CB_DOC_ROOT . 'lib/install_functions.php';

UnitTest\Helpers::init();

error_reporting(E_ALL);
ini_set('display_errors', 1);




