<?php
namespace ExtDirect;

use CB\Config;
use CB\Util;

// define function if undefinded

if (!function_exists('\\ExtDirect\\extDirectShutdownFunction')) {

    /**
     * catch server side errors and return json encoded exception
     * @return void
     */
    function extDirectShutdownFunction()
    {
        $data = \CB\Cache::get('ExtDirectData');

        $error = error_get_last();

        if (in_array($error['type'], array(1, 4))) {
            $data['type']   = 'exception';
            $data['result'] = array('success' => false);
            $data['msg']    = 'Internal server error.';

            if (\CB\IS_DEBUG_HOST) {
                $data['msg']   = $error['message'];
                $data['where'] = print_r(debug_backtrace(false), true);
            }

            //notify admin
            if (!(php_sapi_name() == "cli")) {
                @mail(
                    Config::get('ADMIN_EMAIL'),
                    'Remote router error on '.Config::get('core_url'),
                    var_export($data, true),
                    'From: '.Config::get('SENDER_EMAIL')."\r\n"
                );

            }

            echo Util\jsonEncode($data);
        }

        if (\CB\User::isLoged()) {
            \CB\User::updateLastActionTime();
        }
    }
}

register_shutdown_function('\\ExtDirect\\extDirectShutdownFunction');

$path = dirname(__FILE__) . DIRECTORY_SEPARATOR;

require_once dirname($path) . DIRECTORY_SEPARATOR . 'init.php';
require $path . 'config.php';

require_once \CB\LIB_DIR.'router_functions.php';

$isForm = false;
$isUpload = false;

if (!(php_sapi_name() == "cli")) {
    header('Content-Type: application/json; charset=UTF-8');
}

if (isset($_POST['extAction'])) {
    // form post
    $isForm   = true;
    $isUpload = ($_POST['extUpload'] == 'true');
    $data     = array(
        'action' => $_POST['extAction']
        , 'method' => $_POST['extMethod']
        , 'tid' => isset($_POST['extTID']) ? intval($_POST['extTID']) : null // not set for upload
        , 'data' => array($_POST, $_FILES)
    );
} elseif (isset($postdata)) {
    $data = Util\jsonDecode($postdata);
} else {

    $postdata = file_get_contents("php://input");

    if (!empty($postdata)) {
        $data = Util\jsonDecode($postdata);
    }
}

if (empty($data)) {
    die('Invalid request.');
}

\CB\Cache::set('ExtDirectData', $data);

$response = null;
if (empty($data['action'])) {
    $response = array();
    foreach ($data as $d) {
        $response[] = doRpc(sanitizeParams($d));
    }
} else {
    $response = doRpc(sanitizeParams($data));
}

if ($isForm && $isUpload) {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<html><body><textarea>';
    echo Util\jsonEncode($response);
    echo '</textarea></body></html>';
} else {

    if (!(php_sapi_name() == "cli")) {
        header('X-Frame-Options: deny');
    }

    echo Util\jsonEncode($response);
}
