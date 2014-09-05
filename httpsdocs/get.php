<?php
namespace CB;

require_once 'init.php';

$coreDir = Config::get('core_dir');

if (is_file($coreDir.DIRECTORY_SEPARATOR.'get.php')) {
    include $coreDir.DIRECTORY_SEPARATOR.'get.php';
} else {
    if (!empty($_REQUEST['export'])) {
        $p = json_decode($_REQUEST['export'], true);
        $export = new \Export\Instance();
        $export->getHTML($p);
    }
}
