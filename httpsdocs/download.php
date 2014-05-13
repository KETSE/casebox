<?php
namespace CB;

use CB\L;

header('Content-Type: text/plain; charset=UTF-8');

if (empty($_GET['id'])) {
    exit(0);
}

require_once 'init.php';

$user = array();
/* check if public user is given */
if (isset($_GET['u']) && is_numeric($_GET['u'])) {
    $res = DB\dbQuery(
        'SELECT id
            ,name
            ,cfg
        FROM users_groups
        WHERE id = $1',
        $_GET['u']
    ) or die( DB\dbQueryError() );

    if ($r = $res->fetch_assoc()) {
        $r['cfg'] = Util\toJSONArray($r['cfg']);
        $user = $r;
    }
    $res->close();
    if (empty($user['cfg']['public_access'])) {
        exit(0);
    }
} else {
    if (!User::isLoged()) {
        exit(0);
    }
    $user = &$_SESSION['user'];
}
/* end of check if public user is given */

$ids = explode(',', $_GET['id']);
$ids = array_filter($ids, 'is_numeric');
if (empty($ids)) {
    exit(0);
}

$sql = 'SELECT f.id
             , f.content_id
             , c.path
             , f.name
             , c.`type`
             , c.size
        FROM files f
        LEFT JOIN files_content c ON f.content_id = c.id
        WHERE f.id IN ('.implode(', ', $ids).')';

if (!empty($_GET['v']) && is_numeric($_GET['v'])) {
    $sql = 'SELECT '.intval($ids[0]).' `id`
            ,f.id `version_id`
            ,f.content_id
            ,c.path
            ,f.name
            ,c.`type`
            ,c.size
        FROM files_versions f
        LEFT JOIN files_content c ON f.content_id = c.id
        WHERE f.id ='.intval($_GET['v']);
}

if (empty($_GET['z']) || ($_GET['z'] != 1)) {
    // single file download
    $res = DB\dbQuery($sql) or die( DB\dbQueryError() );
    if ($r = $res->fetch_assoc()) {
        //check if can download file
        if (!Security::canDownload($r['id'], $user['id'])) {
            die( L\Access_denied.'!!!'.$r['id']);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: '.$r['type'].'; charset=UTF-8');
        if (!isset($_GET['pw'])) {
            header('Content-Disposition: attachment; filename="'.$r['name'].'"');
        }
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.$r['size']);
        @readfile(FILES_DIR.$r['path'].DIRECTORY_SEPARATOR.$r['content_id']);
        Log::add(array('action_type' => 14, 'file_id' => $r['id']));
    }
    $res->close();
    exit(0);
} else {
    //archive download
    $archive_name = $_SERVER['SERVER_NAME'].'_'.date('Y-m-d_Hi').'.zip';
    $files = array();
    if (!empty($ids)) {
        $res = DB\dbQuery($sql) or die( DB\dbQueryError() );
        while ($r = $res->fetch_assoc()) {
            //check if can download file
            if (Security::canDownload($r['id'], $user['id'])) {
                $files[] = $r;
            }

        }
        $res->close();
        if (empty($files)) {
            exit(0);
        }
        if (sizeof($files) == 1) {
            $archive_name = $files[0]['name'].'_'.date('Y-m-d_Hi').'.zip';
        }

        $zip = new \ZipArchive();
        $tmp_name = tempnam(sys_get_temp_dir(), 'cb_arch');
        if ($zip->open($tmp_name, \ZIPARCHIVE::CREATE) !== true) {
            exit("cannot create archive\n");
        }
        foreach ($files as $f) {
            $zip->addFile(FILES_DIR.$f['path'].DIRECTORY_SEPARATOR.$f['content_id'], $f['name']);
        }
        $zip->close();
        header('Content-Type: application/zip; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$archive_name.'"');
        header('Content-Length: '.filesize($tmp_name));
        @readfile($tmp_name);
        exit(0);
    }
}
header('Location: '.CORE_URL);
