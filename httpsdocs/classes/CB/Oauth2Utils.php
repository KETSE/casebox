<?php

namespace CB;

class Oauth2Utils
{

    /**
     *
     * @param array $GPlus
     * @return \League\OAuth2\Client\Provider\Google
     */
    static function getGoogleProvider($GPlus = null)
    {

        if (empty($GPlus)) {
            $GPlus = static::getGoogleConfig();
        }

        $provider = null;
        if (isset($GPlus['web']) && isset($GPlus['web']['client_id']) && isset($GPlus['web']['client_secret']) && isset($GPlus['web']['redirect_uris'])
            && count($GPlus['web']['redirect_uris'])) {
            require_once realpath(__DIR__.'/../../../vendor/').'/autoload.php';

            $provider = new \League\OAuth2\Client\Provider\Google([
                'clientId' => $GPlus['web']['client_id'],
                'clientSecret' => $GPlus['web']['client_secret'],
                'redirectUri' => $GPlus['web']['redirect_uris'][0],
            ]);
        }
        return $provider;
    }

    /**
     *
     * @return array with google credentials
     */
    static function getGoogleConfig()
    {
        $useGoogleOauth2_json = Config::get('googleapis_credentials');
        if ($useGoogleOauth2_json && $GPlus                = Util\jsonDecode($useGoogleOauth2_json, true)) {
            return $GPlus;
        }
        return null;
    }

    /**
     *
     * @param type $provider
     * @return string url to login 
     */
    static function getLoginUrl($provider = null)
    {

        $authUrl = null;

        if (isset($provider)) {

            $generator    = $provider->getRandomFactory()->getMediumStrengthGenerator();
            $random_state = $generator->generateString(32);

            $state = [
                'core' => Config::get('core_name'),
                'state' => $random_state
            ];

            $authUrl = $provider->getAuthorizationUrl(['state' => strtr(base64_encode(json_encode($state)), '+/=', '-_,')]);

            $_SESSION['oauth2state'] = $provider->getState();
        }

        return $authUrl;
    }

    /**
     * return true if current request is for oaut2callback script
     * @return boolean
     */
    static function isOauth2Login()
    {
        return isset($_GET['state']) && isset($_SESSION['oauth2state']);
    }

    /**
     *
     * @return array
     */
    static function checkLogined()
    {
        $result = [ 'success' => true];
        if (static::isOauth2Login()) {
            $state_json         = base64_decode(strtr($_GET['state'], '-_,', '+/='));
            $state              = json_decode($state_json, true);
            $session_state_json = base64_decode(strtr($_SESSION['oauth2state'], '-_,', '+/='));
            $session_state      = json_decode($session_state_json, true);
            if (isset($session_state['state']) && isset($state['state']) && isset($state['email'])) {

                DB\connect();

                $QueryUser = 'select id,enabled from users_groups where email like  $1 ';

                $res = DB\dbQuery(
                    $QueryUser, array($state['email'])
                    ) or die(DB\dbQueryError());

                if (($r = $res->fetch_assoc()) && ($r['enabled'] == 1)) {
                    $user_id = $r['id'];
                } else {
                    return [ 'success' => false, 'message' => 'Email '.$state['email'].' not authorized for this core. '.L\get('Specify_username')];
                }

                $res->close();

                //echo '<pre>'.print_r($session_state, true).'</pre>';
                //echo '<pre>'.print_r($state, true).'</pre>';
                if ($user_id > 0) {

                    $r = User::setAsLoged($user_id, $session_state['state']);


                    if ($r['success'] == false) {
                        $errors[] = L\get('Auth_fail');
                    } else {
                        $cfg = User::getTSVConfig();
                        if (!empty($cfg['method'])) {
                            $_SESSION['check_TSV'] = time();
                        } else {
                            $_SESSION['user']['TSV_checked'] = true;
                        }
                    }

                    // die('<pre>'.print_r($_SESSION, true).'</pre>');

                    return [ 'success' => true];
                }
            } else {
                return [ 'success' => false, 'message' => 'WRONG STATE!!!'];
            }
        } else {
            return [ 'success' => false, 'message' => 'Is not Oauth login'];
        }
    }

    /**
     *
     */
    static function getToken($provider, $state, $code)
    {
        $token = null;

        if (isset($provider)) {

            if (empty($state)) {
                // session_destroy();
                //  die('Invalid state: '.$state);
                trigger_error('Oauth2Utils Invalid state: '.$state, E_USER_WARNING);
            } else {

                // Try to get an access token (using the authorization code grant)
                try {
                    $token = $provider->getAccessToken('authorization_code', [ 'code' => $code]);
                } catch (Exception $e) {

                    // Failed to get user details
                    trigger_error('Something went wrong: '.$e->getMessage(), E_USER_ERROR);
                }

                // Use this to interact with an API on the users behalf
                // echo $token->accessToken;
                // Use this to get a new access token if the old one expires
                // echo $token->refreshToken;
                // Number of seconds until the access token will expire, and need refreshing
                // echo $token->expires;
            }
        }

        return $token;
    }

    /**
     *
     */
    static function getOwner($provider, $token)
    {

        $ownerDetails = null;
        // Optional: Now you have a token you can look up a users profile data
        try {

            // We got an access token, let's now get the owner details
            $ownerDetails = $provider->getResourceOwner($token);
            // Use these details to create a new profile
            // printf('Hello %s!<br>', $ownerDetails->getFirstName());
            // printf('Your email\'s is %s!<br>', $ownerDetails->getEmail());
        } catch (Exception $e) {

            // Failed to get user details
            trigger_error('Something went wrong: '.$e->getMessage(), E_USER_ERROR);
        }
        return $ownerDetails;
    }

    /**
     *
     * @param type $state
     * @return string
     */
    static function encodeState($state)
    {
        return strtr(base64_encode(json_encode($state)), '+/=', '-_,');
    }

    /**
     * 
     * @param type $encodedState
     * @return type
     */
    static function decodeState($encodedState)
    {
        $state_json = base64_decode(strtr($encodedState, '-_,', '+/='));
        $state      = json_decode($state_json, true);
        return $state;
    }

    /**
     *
     * @param type $provider
     * @param type $encodedState
     * @param type $code
     * @return provider
     */
    static function getLocalState($provider, $encodedState, $code)
    {

        $updateEncodedState = null;
        $token              = static::getToken($provider, $encodedState, $code);

        if (isset($token)) {

            $ownerDetails = static::getOwner($provider, $token);
            $state        = static::decodeState($encodedState);
            if ($ownerDetails->getEmail()) {
                $state['email']     = $ownerDetails->getEmail();
                $updateEncodedState = static::encodeState($state);
            }
        }
        return $updateEncodedState;
    }
}