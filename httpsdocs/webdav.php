<?php

namespace CB;

/**
 * webdav router
 *
 * @copyright Copyright (C) 2014 KETSE (https://www.ketse.com/).
 * @author Oleg Burlaca (http://www.burlaca.com/)
 * @license https://www.casebox.org/license/ AGPLv3
 */

// server urls 'https://casebox.org/dav/{core}/edit-{id}/'
define('URIPREFIX', 'dav');

// small hack for init.php: allowing it to work without being authenticated
$webDAVMode = 1;

# set the CORE and check if it's Browse or Edit mode
$env = initEnv();
// error_log("ENV: " . print_r($env, true));

require_once 'init.php';

// NOTE: should change it later
error_reporting(E_ALL & ~E_WARNING);
// error_reporting(E_ERROR | E_PARSE);

require_once 'libx/SabreDAV/vendor/autoload.php';

date_default_timezone_set('UTC');




// error_log("env: " . print_r($env, true));

// Make sure there is a directory in your current directory named 'public'. We will be exposing that directory to WebDAV
$p = [
    'nodeId' => 1,
    'parentDir' => null,
];

// the root folder = parentNode fo the file, if mode == 'edit'
if ($env['mode'] == 'edit') {
    $p['nodeId'] = WebDAV\Utils::getParentNodeId($env['nodeId']);
}

$rootNode = new WebDAV\Directory($env['rootFolder'], $p, $env);
// error_log('webdav.php: ' . print_r($p, true));

// The rootNode needs to be passed to the server object.
$server = new \Sabre\DAV\Server($rootNode);


// If you want to run the SabreDAV server in a custom location (using mod_rewrite for instance)
// You can override the baseUri here.
$baseUri = '/' . URIPREFIX . '/' . $env['core'];

// for EDIT mode, the root will start directly on /dav/{core}/edit-{nodeId}/
if ($env['mode'] == 'edit') {
    $baseUri .= '/' . $env['editFolder'];
    // $baseUri .= '/' . $uriPrefix . '/' . $env['editFolder'];
}

$server->setBaseUri($baseUri);


// Authentication
$authBackend = new WebDAV\Auth();
$authPlugin = new \Sabre\DAV\Auth\Plugin($authBackend, 'SabreDAV');


$server->addPlugin($authPlugin);


// where to store temp files: LOCK files and files created by TemporaryFileFilterPlugin
$tmpDir = TEMP_DIR . Config::get('core_name') . '/';
$lockFile = $tmpDir . 'locks';

// if there is no locking file for this core, create one
if (! is_file($lockFile)) {
    file_put_contents($lockFile, '');
}


// this plugin filters temp files
$tffp = new \Sabre\DAV\TemporaryFileFilterPlugin($tmpDir);
$server->addPlugin($tffp);


// LibreOffice will NOT remove LOCK after closing the file
// in EDIT mode disable locking, so everyone can save the file
//if ($_SERVER['HTTP_USER_AGENT'] != 'LibreOffice') {   // WORD requires locking. and ($env['mode'] != 'edit')
    $lockBackend = new \Sabre\DAV\Locks\Backend\File($lockFile);
    $lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);

    // http://sabre.io/dav/clients/msoffice/
    // certain versions of Office break if the {DAV:}lockdiscovery property
    // but Word 2013 doesn't like this setting, on save it shows:
    // "Showing a dialog: files was changed by another author, combine results?"
    //
    // \Sabre\DAV\Property\LockDiscovery::$hideLockRoot = true;

    $server->addPlugin($lockPlugin);
//}

// Adding 'creationdate' property
$storageBackend = new WebDAV\PropertyStorageBackend();
$propertyStorage = new \Sabre\DAV\PropertyStorage\Plugin($storageBackend);
$server->addPlugin($propertyStorage);

$cbLockPlugin = new WebDAV\LockPlugin();
$server->addPlugin($cbLockPlugin);

// And off we go!
$server->exec();

//------------------------------------------------------------------------------
function initEnv()
{
    $ary = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
    // error_log("initEnv: " . $_SERVER['REQUEST_URI']);
    // echo(print_r($ary, true));

    // remove URIPREFIX
    array_shift($ary);

    $r = [];

    $r['core'] = $ary[0];

    # current version
    # /dav/{core}/edit-{nodeId}/{filename}
    #
    # version history
    # /dav/{core}/edit-{nodeId}-{versionId}/{filename}
    # /dav/{core}/edit-{nodeId}-{versionId}/
    # also support a direct folder request /edit-{nodeId}
    if (((count($ary) == 2) or (count($ary) == 3)) && (preg_match('/^edit-(\d+)/', $ary[1], $m))) {
        $r['mode'] = 'edit';

        $r['nodeId'] = $m[1];

        // /{core}/edit-{nodeId}-{versionId}/
        $r['editFolder'] = $ary[1];

        # /edit-{nodeId}-{versionId}  ?
        if (preg_match('/^edit-(\d+)-(\d+)\//', $ary[1], $m)) {
            $r['versionId'] = $m[2];
        }

        // {core}/edit-{nodeId}-{versionId}/{filename}
        // only if filename is specified
        if (count($ary) == 3) {
            $r['filename'] = $ary[2];
        }

        // root Sabredav folder, serve all requests from here: /dav/{core}/edit-{nnn}
        // i.e. dav client should not try to get out of this folder
        $r['rootFolder'] = '/' . $r['editFolder'];

        // } elseif (preg_match('/^dav-(.*)$/', $ary[0], $m)) {    # /dav-{core}/
    } else {
        // $r['core'] = $m[1];
        $r['mode'] = 'browse';
        $r['rootFolder'] = '';
    }

    $_GET['core'] = $r['core'];

    return $r;
}
