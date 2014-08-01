<?php
namespace CB;

if (empty($_GET['f'])) {
    exit(0);
}

require_once 'init.php';

$f = basename($_GET['f']);
$f = explode('_', $f);
$id = array_shift($f);
$id = intval($id);

$photoFile = User::getPhotoFilename(
    $id,
    isset($_GET['32'])
);

$expires = 60*60*24*14;
header('Content-Type: image; charset=UTF-8');
header('Content-Transfer-Encoding: binary');
header("Cache-Control: maxage=" . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
header('Cache-Control: must-revalidate');
header('Pragma: public');
readfile($photoFile);
