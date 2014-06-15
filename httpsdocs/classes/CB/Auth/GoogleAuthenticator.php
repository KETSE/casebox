<?php
namespace CB\Auth;

/**
 * Google authenticator CaseBox class
 */

class GoogleAuthenticator implements \CB\Interfaces\Auth
{
    /**
     * secret key length used
     * @var int
     */
    private $secretLength = 16;

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

    /**
     * @param array $p {
     *     @type int $secretLength - length of secret key
     * }
     * @return void
     */
    public function __construct($p = array(), $data = null)
    {
        if (isset($p['secretLength']) && is_numeric($p['secretLength'])) {
            $this->secretLength = $p['secretLength'];
        }
        if (!empty($data)) {
            $this->secretData = $data;
        }
        $this->instance = new \Auth_GoogleAuthenticator();
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
        return array('sk' => $this->instance->createSecret($this->secretLength));
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
        return $params;
    }

    /**
     * set secret params
     * @param  $secretData secret data used for code generation
     * @return void
     */
    public function setSecretData($secretData)
    {
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
        $key = $this->secretData['sk'];
        $spacedKey = '';
        while (!empty($key)) {
            $spacedKey .= substr($key, 0, 4).' ';
            $key = substr($key, 4);
        }

        $rez = array(
            'sd' => $spacedKey
            ,'url' => $this->instance->getQRCodeGoogleUrl(\CB\Config::get('core_url'), $this->secretData['sk'])
        );

        return $rez;
    }

    /**
     * return a generated code (otp). Used by Google Authenticatot for sending the code over sms.
     * @return varchar
     */
    public function getCode()
    {
        return $this->instance->getCode($this->secretData['sk']);
    }

    /**
     * verify a given code
     * @param  varchar $code one tyme password
     * @return bool
     */
    public function verifyCode($code)
    {
        return $this->instance->verifyCode($this->secretData['sk'], $code);
    }
}
