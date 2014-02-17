<?php
    namespace CB;

    $env = prepare_environment();

    require_once '../../casebox/httpsdocs/init.php';
    require_once 'libx/SabreDAV/vendor/autoload.php';

    $auth = new WebDAV\Auth();
    $only = null;

    // check direct link edit
    if($env['action'] == 'edit'){
        // get path to requested object ID
        $path = WebDAV\Utils::getPathFromId($env['id']);
        $path = ($path == '') ? WEBDAV_PATH_DELIMITER : $path;

        // patch request for sabredav
        $_SERVER['REQUEST_URI'] = $path.$env['request'];

        // prepare only needed objects
        $object = WebDAV\Utils::getNodeById($env['id']);
        $only = array_slice(explode(',', $object['pids']), 1);
    }

    $rootDirectory = new WebDAV\Directory("Home", null, $only);
    $server = new \Sabre\DAV\Server($rootDirectory);

    // if there is no locking file for this core, create one
    if(!is_file(TEMP_DIR.CORE_NAME.DIRECTORY_SEPARATOR.'locks'))
        file_put_contents(TEMP_DIR.CORE_NAME.DIRECTORY_SEPARATOR.'locks','');

    $tempFilesPlugin = new \Sabre\DAV\TemporaryFileFilterPlugin(TEMP_DIR.CORE_NAME.DIRECTORY_SEPARATOR);

    // todo Remove after LibreOffice fix bug with locking
    // LibreOffice dont remove lock when working with files, so disable locking with hope for the future
    if($_SERVER['HTTP_USER_AGENT'] != 'LibreOffice') {
        $lockBackend = new \Sabre\DAV\Locks\Backend\File(TEMP_DIR.CORE_NAME.DIRECTORY_SEPARATOR.'locks');
        $lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);
        $server->addPlugin($lockPlugin);
    }

    $server->addPlugin($tempFilesPlugin);
    $server->setBaseUri('/');
    $server->exec();

    // --- Additional ---

    function prepare_environment(){
        $result = array();

        session_start();
        define('WEBDAV_PATH_DELIMITER', '/');

        $_SERVER['REQUEST_URI']  = $_SERVER['REDIRECT_URL'];

        $url_parts = explode('/', trim($_SERVER['REQUEST_URI'],'/'));

        if(count($url_parts)<1) return;

        // prepare env
        if(preg_match('#^/?dav-(.*)$#',$url_parts[0], $matches)){
            // ordinary webdav request
            $result['core'] = $matches[1];
            $result['action'] = 'request';
            $result['request'] = implode('/', array_slice($url_parts,1));
        }else if($url_parts[0] =='edit'){
            // direct link webdav request
            $result['core'] = $url_parts[1];
            $result['action'] = 'edit';
            $result['request'] = implode('/', array_slice($url_parts,3));

            // get object id
            if(isset($url_parts[2]))
                if(preg_match('@(\d{1,})@', $url_parts[2], $matches))
                    $result['id'] = $matches[1];
        }

        // core defined, bring it to casebox
        if(isset($result['core'])){
            $sn = explode('.', $_SERVER['SERVER_NAME']);
            $index = in_array($sn[0], array('www','ww2')) ? 1 : 0;
            $sn[$index] = $result['core'];

            // bring
            $_SERVER['SERVER_NAME'] = implode('.',$sn);
        }

        // bring request to webdav server
        if(isset($result['request']))
            $_SERVER['REQUEST_URI'] = WEBDAV_PATH_DELIMITER.$result['request'];

        // HTTP_DESTINATION is in charge for destination of file
        // Patch this var, so we can process MOVE requsts (Word cant save without it)
        if(isset($_SERVER['HTTP_DESTINATION'])){
            $url = parse_url($_SERVER['HTTP_DESTINATION']);
            $_SERVER['HTTP_DESTINATION'] = preg_replace('@^/dav-'.$result['core'].'/@','/', $url['path']);
        }

        return $result;
    }
