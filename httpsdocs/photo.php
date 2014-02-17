<?php
namespace CB;

if (empty($_GET['f'])) {
    exit(0);
}

require_once 'config.php';
$f = $_GET['f'];
$f = explode('_', $f);
$id = array_shift($f);

$path = DOC_ROOT.'css/i/ico/32/';
$filename = 'user-male.png';

require_once 'lib/DB.php';
DB\connect();

$res = DB\dbQuery(
    'SELECT photo
        ,sex
    FROM users_groups
    WHERE id = $1',
    $id
) or die(DB\dbQueryError());

if ($r = $res->fetch_assoc()) {
    if (!empty($r['photo']) && file_exists(PHOTOS_PATH.$r['photo'])) {
        $path = PHOTOS_PATH;
        $filename = $r['photo'];
    } elseif ($r['sex'] == 'f') {
        $filename = 'user-female.png';
    }
}
$res->close();
// seconds, minutes, hours, days
$expires = 60*60*24*14;
header('Content-Type: image; charset=UTF-8');
header('Content-Transfer-Encoding: binary');
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
header('Cache-Control: must-revalidate');
header('Pragma: public');
readfile($path.$filename);
