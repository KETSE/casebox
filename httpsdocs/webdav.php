<?php
    namespace CB;

    prepare_environment();
    require_once '../../casebox/httpsdocs/init.php';
    require_once 'libx/SabreDAV/vendor/autoload.php';

    $auth = new WebDAV\Auth();
    $only = null;

    if(preg_match('@^/edit/(\d{1,})@',$_SERVER['REQUEST_URI'],$matches)) {

        $path = WebDAV\Utils::getPathFromId($matches[1]);
        $path = ($path == '') ? WEBDAV_PATH_DELIMITER : $path;

        $_SERVER['REQUEST_URI']  = preg_replace('@^\/edit\/\d{1,}@',$path, $_SERVER['REQUEST_URI']);

        $object = WebDAV\Utils::getNodeById($matches[1]);
        $only = array_slice(explode(',', $object['pids']), 1);
    }

    $rootDirectory = new WebDAV\Directory("Home", null, $only);

    $server = new \Sabre\DAV\Server($rootDirectory);
    $lockBackend = new \Sabre\DAV\Locks\Backend\File(TEMP_DIR.CORE_NAME.DIRECTORY_SEPARATOR.'locks');
    $lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);
    $browsePlugin = new \Sabre\DAV\Browser\Plugin();
    $tempFilesPlugin = new \Sabre\DAV\TemporaryFileFilterPlugin(TEMP_DIR.CORE_NAME.DIRECTORY_SEPARATOR);

    // todo Remove after LibreOffice fix bug with locking
    if($_SERVER['HTTP_USER_AGENT'] != 'LibreOffice') $server->addPlugin($lockPlugin);

    $server->addPlugin($tempFilesPlugin);
    $server->addPlugin($lockPlugin);
    $server->setBaseUri('/');
    $server->exec();

    // --- Additional ---

    function prepare_environment(){
        session_start();
        define('WEBDAV_PATH_DELIMITER', '/');

        if(!is_file(TEMP_DIR.CORE_NAME.DIRECTORY_SEPARATOR.'locks'))
            file_put_contents(TEMP_DIR.CORE_NAME.DIRECTORY_SEPARATOR.'locks','');

        $_SERVER['REQUEST_URI']  = $_SERVER['REDIRECT_URL'];

        $temp = explode(WEBDAV_PATH_DELIMITER, trim($_SERVER['REQUEST_URI'], WEBDAV_PATH_DELIMITER));

        $uri = array();
        foreach ($temp as $u) if($u != '') $uri[] = $u;

        if(count($uri) == 0) die();

        if($uri[0] == 'edit'){
            $core = $uri[1];
            unset($uri[1]);
        }else{
            $core = $uri[0];
            unset($uri[0]);
        }

        if(isset($_SERVER['HTTP_DESTINATION'])){
            $url = parse_url($_SERVER['HTTP_DESTINATION']);
            $_SERVER['HTTP_DESTINATION'] = preg_replace('@/'.$core.'/@','/', $url['path']);
        }

        $_SERVER['SERVER_NAME'] = $core.'.casebox.org';
        $_SERVER['REQUEST_URI'] = WEBDAV_PATH_DELIMITER.implode(WEBDAV_PATH_DELIMITER, $uri);
    }
