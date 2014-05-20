<?php
namespace CB;

require_once 'init.php';

$coreDir = Config::get('core_dir');

if (is_file($coreDir.DIRECTORY_SEPARATOR.'get.php')) {
    include $coreDir.DIRECTORY_SEPARATOR.'get.php';
}
