<?php
namespace CB;

/*
selecting node properties from the tree
comparing last preview access time with node update time. Generate preview if needed and store it in cache
checking if preview is available and return it
 */
if (empty($_GET['f'])) {
    exit(0);
}
require_once 'init.php';

if (!User::isLoged()) {
    echo 'Session expired. Please login.';
    exit(0);
}

$f = $_GET['f'];
$f = explode('.', $f);
$a = array_shift($f);
@list($id, $version_id) = explode('_', $a);
$ext = array_pop($f);

$filesPreviewDir = Config\get('files_preview_dir');

//TODO: check access with security model
if ($ext !== 'html') {//this will provide other files (images, swfs)
    $f = realpath($filesPreviewDir . $_GET['f']);
    if (file_exists($f)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        header('Content-type: '.finfo_file($finfo, $f));
        echo file_get_contents($f);
    }
    exit(0);
}
if (!is_numeric($id)) {
    exit(0);
}

$res = DB\dbQuery(
    'SELECT t.id
        ,t.pid
        ,te.type
        ,t.subtype
        ,t.name
        ,t.updated
    FROM tree t
    JOIN templates te on t.template_id = te.id
    WHERE t.id = $1',
    $id
) or die(DB\dbQueryError());

if ($r = $res->fetch_assoc()) {
    $f = $r;
}
$res->close();
if (!is_array($f) || empty($f)) {
    exit(0); //tree element does not exist
}
$preview = array();
switch ($f['type']) {
    case 'case':
    case 'object':
    case 'template':
    case 'field':
    case 'email':
    case 'search':
        $o = new Objects();
        echo $o->getPreview($id);
        break;
    case 'file':
        $sql = 'SELECT p.filename
            FROM files f
            JOIN file_previews p ON f.content_id = p.id
            WHERE f.id = $1';

        if (!empty($version_id)) {
            $sql = 'SELECT p.filename
                FROM files_versions f
                JOIN file_previews p ON f.content_id = p.id
                WHERE f.file_id = $1
                    AND f.id = $2';
        }
        $res = DB\dbQuery($sql, array($id, $version_id)) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            if (!empty($r['filename']) && file_exists($filesPreviewDir . $r['filename'])) {
                $preview = $r;
            }
        }
        $res->close();

        if (empty($preview)) {
            $preview = Files::generatePreview($id, $version_id);
        }
        if (!empty($preview['processing'])) {
            echo '&#160';
        } else {
            $top = '';
            $tmp = Tasks::getActiveTasksBlockForPreview($id);
            if (!empty($tmp)) {
                $top = '<div class="obj-preview-h pt10">'.L\get('ActiveTasks').'</div>'.$tmp;
            }
            if (!empty($top)) {
                echo //'<div class="p10">'.
                $top.
                // '</div>'.
                '<hr />';
            }

            if (!empty($preview['filename'])) {
                $fn = $filesPreviewDir . $preview['filename'];
                if (file_exists($fn)) {
                    echo file_get_contents($fn);
                    $res = DB\dbQuery(
                        'UPDATE file_previews
                        SET ladate = CURRENT_TIMESTAMP
                        WHERE id = $1',
                        $id
                    ) or die(DB\dbQueryError());
                }
            } elseif (!empty($preview['html'])) {
                echo $preview['html'];
            }
            $dbNode = new TreeNode\Dbnode();
            // echo '<!-- NodeName:'.$dbNode->getName($id).' -->';
        }
        break;
    case 'task':
        $o = new Tasks();
        echo $o->getPreview($id);
        break;
}
