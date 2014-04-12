<?php
namespace CB;

use CB\WebDAV\Utils;

$env = prepareEnvironment();

require_once 'init.php';
require_once 'libx/SabreDAV/vendor/autoload.php';

error_reporting(0);

$auth = new WebDAV\Auth();

// check direct link edit
if ($env['action'] == 'edit') {
    // get path to requested object ID
    $path = WebDAV\Utils::getPathFromId($env['id']);
    $path = ($path == '') ? DIRECTORY_SEPARATOR : $path;

    // patch request for sabredav
    $_SERVER['REQUEST_URI'] = '/'.
        'dav-' . $env['core'] . DIRECTORY_SEPARATOR.
        $env['core'] . DIRECTORY_SEPARATOR.
        $path . $env['request'];

    // prepare only needed objects
    $object = WebDAV\Utils::getNodeById($env['id']);
    $env['onlyFile'] = array_slice(explode(',', $object['pids']), 1);
    Utils::log(json_encode($env['onlyFile']));
}

$rootDirectory = new \Sabre\DAV\SimpleCollection(
    'root',
    array(
        new \Sabre\DAV\SimpleCollection(
            'dav-'.$env['core'],
            array(
                new WebDAV\Directory($env['core'], null, $env['onlyFile'], $env['core'])
            )
        )
    )
);
$server = new \Sabre\DAV\Server($rootDirectory);

// if there is no locking file for this core, create one
if (!is_file(TEMP_DIR . CORE_NAME . DIRECTORY_SEPARATOR.'locks')) {
    file_put_contents(TEMP_DIR . CORE_NAME . DIRECTORY_SEPARATOR.'locks', '');
}

$tempFilesPlugin = new \Sabre\DAV\TemporaryFileFilterPlugin(TEMP_DIR . CORE_NAME . DIRECTORY_SEPARATOR);

// todo Remove after LibreOffice fix bug with locking
// LibreOffice dont remove lock when working with files, so disable locking with hope for the future
if ($_SERVER['HTTP_USER_AGENT'] != 'LibreOffice') {
    $lockBackend = new \Sabre\DAV\Locks\Backend\File(TEMP_DIR . CORE_NAME . DIRECTORY_SEPARATOR.'locks');
    $lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);
    $server->addPlugin($lockPlugin);
}

$server->addPlugin($tempFilesPlugin);
$server->addPlugin(new WebDAV\CustomPropertiesPlugin());
$server->setBaseUri('/');
$server->exec();

// --- Additional ---

function prepareEnvironment()
{
    $result = array('onlyFile' => null);
    $url_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

    if (count($url_parts)<1) {
        return;
    }

    // prepare env
    if (preg_match('#^/?dav-(.*)$#', $url_parts[0], $matches)) {
        // ordinary webdav request
        $result['core'] = $matches[1];
        $result['action'] = 'request';
    } elseif ($url_parts[0] =='edit') {
        // direct link webdav request
        $result['core'] = $url_parts[1];
        $result['action'] = 'edit';
        $result['request'] = implode('/', array_slice($url_parts, 3));

        // get object id
        if (isset($url_parts[2])) {
            if (preg_match('@(\d{1,})@', $url_parts[2], $matches)) {
                $result['id'] = $matches[1];
            }
        }
    }

    // core defined, bring it to casebox
    if (isset($result['core'])) {
        $_GET['core'] = $result['core'];
        // $sn = explode('.', $_SERVER['SERVER_NAME']);
        // $index = in_array($sn[0], array('www','ww2')) ? 1 : 0;
        // $sn[$index] = $result['core'];

        // // bring
        // $_SERVER['SERVER_NAME'] = implode('.',$sn);
    }

    return $result;
}
