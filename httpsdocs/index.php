<?php
namespace CB;

if (empty($_GET['uri'])) {
    die('Error, invalid url.');
}

$uri = explode('/', $_GET['uri']);
$uri = array_filter($uri, 'strlen');



$coreName = array_shift($uri);

         if( $coreName == 'oauth2callback') {
                include 'oauth2callback.php';
                return 0;
         }

$_GET['core'] = $coreName;

$command = array_shift($uri);
$_GET['command'] = $command;

$subcommand = array_shift($uri);
$_GET['subcommand'] = $subcommand;

switch ($command) {
    case 'login':
        switch ($subcommand) {
            case 'auth':
                include 'auth.php';
                break;

            default:
                include 'login.php';
        }

        break;
    case 'recover':
        include 'recover.php';
        break;

    case 'photo':
        require_once 'init.php';

        $f = basename($subcommand);
        $f = explode('_', $f);
        $id = array_shift($f);
        $id = intval($id);

        User::OutputPhoto(
            $id,
            isset($_GET['32'])
        );
        break;

    case 'view':
        include 'preview.php';
        break;

    case 'get':
        require_once 'init.php';

        $coreDir = Config::get('core_dir');

        if (!empty($_REQUEST['export'])) {
            $p = Util\jsonDecode($_REQUEST['export']);
            $export = new \Export\Instance();
            $export->getHTML($p);
        } elseif (is_file($coreDir.DIRECTORY_SEPARATOR.'get.php')) {
            include $coreDir.DIRECTORY_SEPARATOR.'get.php';

        } else {

        }
        break;

    case 'download':
        $id = $subcommand;
        $versionId = array_shift($uri);
        $userId = false;

        if (empty($id) || !is_numeric($id)) {
            break;
        }

        //check if version specified
        if (!empty($versionId)) {
            if ($versionId[0] == 'v') {
                $versionId = substr($versionId, 1);
                if (!is_numeric($versionId)) {
                    $versionId = null;
                }
            } elseif (!is_numeric($versionId)) {
                $versionId = null;
            }
        }

        require_once 'init.php';

        /* check if public user is given */
        if (isset($_GET['u']) && is_numeric($_GET['u'])) {
            $userId = $_GET['u'];
            if (!User::isPublic($userId)) {
                exit(0);
            }
        } else {
            if (!User::isLoged()) {
                exit(0);
            }
            $userId = $_SESSION['user']['id'];
        }
        /* end of check if public user is given */

        Files::download($id, $versionId, !isset($_GET['pw']), $userId);

        break;

    case 'upload':
        require_once 'init.php';

        $result = array(
            'success' => false
        );

        if (isset($_SERVER['HTTP_X_FILE_OPTIONS'])) {
            // AJAX call
            $file = Util\jsonDecode($_SERVER['HTTP_X_FILE_OPTIONS']);
            $file['error'] = UPLOAD_ERR_OK;
            $file['tmp_name'] = tempnam(Config::get('incomming_files_dir'), 'cbup');
            $file['name'] = urldecode($file['name']);

            if (empty($file['content_id'])) {
                Util\bufferedSaveFile(
                    'php://input',
                    $file['tmp_name']
                );
            }

            $_FILES = array('file' => $file);
            $browser = new Browser();

            $result = $browser->saveFile(
                array(
                    'pid' => @$file['pid']
                    ,'draftPid' => @$file['draftPid']
                    ,'response' => @$file['response']
                )
            );
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo Util\jsonEncode($result);

        break;

    case 'logo.png':
        require_once 'config.php';

        $logo = DOC_ROOT . 'css/i/casebox-logo-small.png';
        $coreLogo = Config::get('files_dir') . 'logo.png';

        if (file_exists($coreLogo)) {
            $logo = $coreLogo;
        }

        header('Content-Type: image; charset=UTF-8');
        readfile($logo);
        break;

    default:
        include 'main.php';
        break;
}
