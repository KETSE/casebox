<?php
namespace CB\Auth;

/**
 * Yubikey authenticator CaseBox class
 */

class Yubikey implements \CB\Interfaces\Auth
{
    /**
     * url used to create new secred data
     * this url is used for posting with params: email, otp
     * parsed result should contain new client id and secret key
     * @var string
     */
    private $secretDataCreationURL = 'https://upgrade.yubico.com/getapikey/';

    /**
     * secret data
     * @var sacalar | array
     */
    private $secretData = null;

    /**
     * google authenticator library class instance
     * @var object
     */
    private $instance = null;

    public function __construct($p = array())
    {
        if (isset($p['clientId']) && isset($p['sk'])) {
            $this->instance = new \Auth_Yubico(
                $p['clientId'],
                '', //$p['sk'],
                1
            );

            $this->secretData = $p;
        }
    }

    /**
     * method for preparing secret data creation
     * For example google authenticator will generate secret key
     * $params {
     *     email
     *     code
     * }
     * @return array associative array of secret params
     */
    public function prepareSecretDataCreation()
    {
    }

    /**
     * create secret data
     * $params {
     *     email
     *     code
     * }
     * @return varchar | array secret data
     */
    public function createSecretData($params = null)
    {
        if (!empty($params['clientId']) && !empty($params['sk'])) {
            return $params;
        }

        if (empty($params['email'])) {
            throw new \Exception('Yubico error: Email not specified for secret data creation.', 1);
        }
        if (empty($params['code'])) {
            throw new \Exception('Yubico error: OTP not specified for secret data creation.', 1);
        }

        $data = array(
            'email' => $params['email']
            ,'otp' => $params['code']
        );

        $data = http_build_query($data, '', '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->secretDataCreationURL);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:text/html; Content-Length:".strlen($data)));
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        $rez = curl_exec($ch);

        file_put_contents(\CB\Config::get('debug_log').'_yubikey', $rez);

        if (curl_errno($ch)) {
            throw new \Exception("curl_error:" . curl_error($ch), 1);
        }

        $rez = strip_tags($rez);

        preg_match_all('/client id:[\s]*([\d]+)\s+secret key:[\s]*([^\s]+)/i', $rez, $matches);

        if (empty($matches[1][0]) || empty($matches[2][0])) {
            throw new \Exception('Cannot find Client ID and Secret key on Yubiko response for getting api key.', 1);
        }

        $params['clientId'] = $matches[1][0];
        $params['sk'] = $matches[2][0];

        return $params;
    }

    /**
     * set secret params
     * @param  $secretData secret data used for code generation
     * @return void
     */
    public function setSecretData($secretData)
    {
        if (empty($this->secretData)
            || ($secretData['clientId'] != $this->secretData['clientId'])
            || ($secretData['sk'] != $this->secretData['sk'])
        ) {
            $this->instance = new \Auth_Yubico(
                $secretData['clientId'],
                '', //$secretData['sk'],
                1
            );
        }
        $this->secretData = $secretData;
    }

    /**
     * return the secret params
     * @return scalar | array
     */
    public function getSecretData()
    {
        return $this->secretData;
    }

    /**
     * function to return data used for message displayed to user when enabling TSV.
     * @return array
     */
    public function getTemplateData()
    {
        $rez = array('email' => @$_SESSION['user']['email']);

        return $rez;
    }

    /**
     * return a generated code (otp). Used by Google Authenticatot for sending the code over sms.
     * @return varchar
     */
    public function getCode()
    {
        return null;
    }

    /**
     * verify a given code
     * @param  varchar $code one tyme password
     * @return bool
     */
    public function verifyCode($code)
    {
        $rez = true;

        if (substr($this->secretData['code'], 0, 12) != substr($code, 0, 12)) {
            $rez = false;
        } else {
            try {
                $this->instance->verify($code);
            } catch (\Exception $e) {
                $rez = false;
                \CB\debug($e->getMessage());
            }
        }

        return $rez;
    }
}
