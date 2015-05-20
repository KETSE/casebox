<?php

/**
 * use it as bootstrap for PHPUnit
 * include things
 *
 * @author ghindows
 */

define('TEST_PATH', __DIR__);
define('TEST_PATH_TEMP', __DIR__.'/temp');

include TEST_PATH.'/vendor/autoload.php';

define('DEFAULT_TEST_CORENAME', 'phpunittest');
define('DEFAULT_TEST_USERNAME', 'root');
define('DEFAULT_TEST_USERPASS', 'devel');


include realpath(__DIR__.'/src').'/helpers.php';


