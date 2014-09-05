<?php
namespace CB\Interfaces;

/**
 * Auth interface for distinct authentication mechannisms like
 * Google Authenticator, Ybikey
 */

interface Auth
{
    /**
     * method for preparing secret data creation
     * For example google authenticator will generate secret key
     * $params {
     *     email
     *     code
     * }
     * @return array associative array of secret params
     */
    public function prepareSecretDataCreation();

    /**
     * create secret data
     *
     * $params {
     *     email
     *     code
     * }
     * @return array associative array of secret params
     */
    public function createSecretData($params = null);

    /**
     * set secret params
     * @param  $secretData secret data used for code generation
     * @return void
     */
    public function setSecretData($secretData);

    /**
     * return the secret params
     * @return scalar | array
     */
    public function getSecretData();

    /**
     * method for returning data used for message displaying to user when enabling TSV.
     * @param  $secretData secret data used for code generation
     * @return array
     */
    public function getTemplateData();

    /**
     * return a generated code (otp). Used by Google Authenticatot for sending the code over sms.
     * @param  $secretData secret data used for code generation
     * @return varchar
     */
    public function getCode();

    /**
     * verify a given code
     * @param  varchar $code one tyme password
     * @return bool
     */
    public function verifyCode($code);
}
