<?php namespace UnitTest;

use \GuzzleHttp\Client;

error_reporting(E_ALL);
ini_set('display_errors', 1);


if (!is_file($autoloadFile = __DIR__ . '/../../vendor/autoload.php')) {
    echo 'Could not find "vendor/autoload.php". Did you forget to run "composer install --dev"?' . PHP_EOL;
    exit(1);
}

require_once $autoloadFile;
