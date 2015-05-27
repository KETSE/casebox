<?php

/**
 * use it as bootstrap for PHPUnit
 * include things
 *
 * @author ghindows
 */

define('CB_ROOT_PATH',realpath(__DIR__.'/../'));

define('TEST_PATH', __DIR__);
define('TEST_PATH_TEMP', __DIR__.'/tmp');

    if (!file_exists(TEST_PATH_TEMP)) {
        mkdir(TEST_PATH_TEMP, 0755, true);
    }



define('DEFAULT_TEST_CBPREFIX', 'cbtest');
define('DEFAULT_TEST_CORENAME', 'test');
define('DEFAULT_TEST_USERNAME', 'root');
define('DEFAULT_TEST_USERPASS', 'devel');
define('DEFAULT_TEST_CONFIG', TEST_PATH.'/src/config/install_config.ini');


include realpath(__DIR__.'/src').'/helpers.php';
include realpath(__DIR__.'/src').'/data_providers.php';

\CB\UNITTESTS\HELPERS\prepareInstance();
\CB\UNITTESTS\HELPERS\init();

include TEST_PATH.'/vendor/autoload.php';



