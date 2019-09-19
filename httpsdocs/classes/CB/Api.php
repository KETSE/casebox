<?php
namespace CB;

class Api
{
    /**
     * methods association table
     *
     * this associated methods are accessible only through http api requiest
     *
     * @var array
     */
    private $methods = array(
        'cb.objects.create' => 'Objects.create'

        ,'cb.objects.permissions.getDirectRules' => 'Security.getNodeDirectAcl'
        ,'cb.objects.permissions.addRule'        => 'Security.updateNodeAccess'
        ,'cb.objects.permissions.updateRule'     => 'Security.updateNodeAccess'
        ,'cb.objects.permissions.removeRule'     => 'Security.deleteNodeAccess'
        ,'cb.objects.permissions.setInheritance' => 'Security.setInheritance'

        ,'cb.files.get'         => 'Files.get'
        ,'cb.files.download'    => 'Files.download'
        ,'cb.files.view'        => 'Files.view'
        ,'cb.files.upload'      => 'Files.upload'

    );

    /**
     * process remote request
     * @return Api\Request
     */
    public function processRequest()
    {
        // get our verb
        $request_method = strtolower($_SERVER['REQUEST_METHOD']);

        $return_obj     = new Api\Request();

        // we'll store our data here
        $data           = array();

        switch ($request_method) {
            // gets are easy...
            case 'get':
                $data = $_GET;
                break;
            // so are posts
            case 'post':
                $data = $_POST;
                break;
            // here's the tricky bit...
            case 'put':
                // basically, we read a string from PHP's special input location,
                // and then parse it out into an array via parse_str... per the PHP docs:
                // Parses str  as if it were the query string passed via a URL and sets
                // variables in the current scope.
                parse_str(file_get_contents('php://input'), $put_vars);
                $data = $put_vars;
                break;
        }

        /* if there is accessed a method from association table then convert it to target class.method */
        if (!empty($data['method']) && isset($this->methods[$data['method']])) {
            $tmp = explode('.', $this->methods[$data['method']]);
            $data['action'] = $tmp[0];
            $data['method'] = $tmp[1];
        }
        /* end of if there is accessed a method from association table then convert it to target class.method */


        // store the method
        $return_obj->setMethod($request_method);

        // set the raw data, so we can access it if needed (there may be
        // other pieces to your requests)
        $return_obj->setRequestVars($data);

        if (isset($data['data'])) {
            // translate the JSON to an Object for use however you want
            $return_obj->setData($data['data']);
        }

        return $return_obj;
    }

    public function sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        $status_header = 'HTTP/1.1 '.$status.' '.$this->getStatusCodeMessage($status);
        // set the status
        header($status_header);
        // set the content type
        header('Content-type: '.$content_type);

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
            exit;
        } else { // we need to create the body if none is passed
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '')
                ? $_SERVER['SERVER_SOFTWARE'] .
                    ' Server at ' .
                    $_SERVER['SERVER_NAME'] .
                    ' Port ' .
                    $_SERVER['SERVER_PORT']
                : $_SERVER['SERVER_SIGNATURE'];

            // this should be templatized in a real-world solution
            $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                        <html>
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                                <title>'.$status.' '.$this->getStatusCodeMessage($status).'</title>
                            </head>
                            <body>
                                <h1>'.$this->getStatusCodeMessage($status).'</h1>
                                <p>'.$message.'</p>
                                <hr />
                                <address>'.$signature.'</address>
                            </body>
                        </html>';

            echo $body;
            exit;
        }
    }

    public static function getStatusCodeMessage($status)
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }
}
