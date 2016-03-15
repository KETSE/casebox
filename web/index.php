<?php

use Symfony\Component\HttpFoundation\Request;

ini_set('error_reporting', -1);
ini_set('display_errors', 1);

date_default_timezone_set('UTC');

ini_set('max_execution_time', 500);
ini_set('short_open_tag', 'off');
ini_set('upload_max_filesize', '200M');
ini_set('post_max_size', '200M');
ini_set('max_file_uploads', '20');
ini_set('memory_limit', '400M');

mb_internal_encoding("UTF-8");
mb_detect_order('UTF-8,UTF-7,ASCII,EUC-JP,SJIS,eucJP-win,SJIS-win,JIS,ISO-2022-JP,WINDOWS-1251,WINDOWS-1250');
mb_substitute_character("none");

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';

$kernel = new AppKernel(AppEnv::getRequestEnvironment(), true);
$kernel->loadClassCache();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
