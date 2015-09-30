<?php

/**
 * use it as bootstrap for PHPUnit
 * include things
 *
 * @author ghindows
 */

//define constants
define('CB_ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('CB_DOC_ROOT', CB_ROOT_PATH . 'httpsdocs' . DIRECTORY_SEPARATOR);

define('TEST_PATH', __DIR__ . DIRECTORY_SEPARATOR);

define('TEST_PATH_TEMP', TEST_PATH . 'tmp' . DIRECTORY_SEPARATOR);

define('DEFAULT_TEST_CBPREFIX', 'cbtest');
define('DEFAULT_TEST_CORENAME', 'test');
define('DEFAULT_TEST_USERNAME', 'root');
define('DEFAULT_TEST_USERPASS', 'devel');

//create tmp if doesnt exist
if (!file_exists(TEST_PATH_TEMP)) {
    mkdir(TEST_PATH_TEMP, 0755, true);
}