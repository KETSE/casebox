<?php
namespace CB;

use CB\DataModel as DM;

class Oauth2Utils
{

    /**
     *
     * @param  array                                 $GPlus
     * @return \League\OAuth2\Client\Provider\Google
     */
    public static function getGoogleProvider($GPlus = null)
    {

        if (empty($GPlus)) {
            $GPlus = static::getGoogleConfig();
        }

        $provider = null;
        if (isset($GPlus['web']) && isset($GPlus['web']['client_id']) && isset($GPlus['web']['client_secret']) && isset($GPlus['web']['redirect_uris'])
            && count($GPlus['web']['redirect_uris'])) {
            require_once realpath(__DIR__.'/../../../vendor/').'/autoload.php';

            $provider = new \League\OAuth2\Client\Provider\Google(
                array(
                    'clientId' => $GPlus['web']['client_id'],
                    'clientSecret' => $GPlus['web']['client_secret'],
                    'redirectUri' => $GPlus['web']['redirect_uris'][0],
                )
            );
        }

        return $provider;
    }

    /**
     *
     * @return array with google credentials
     */
    public static function getGoogleConfig()
    {
        $useGoogleOauth2_json = Config::get('oauth2_credentials_google');
        if ($useGoogleOauth2_json && $GPlus                = Util\jsonDecode($useGoogleOauth2_json, true)) {
            return $GPlus;
        }

        return null;
    }

    /**
     *
     * @param  type   $provider
     * @return string url to login
     */
    public static function getLoginUrl($provider = null)
    {

        $authUrl = null;

        if (isset($provider)) {

            $generator    = $provider->getRandomFactory()->getMediumStrengthGenerator();
            // $random_state = $generator->generateString(32);

            $state = [
                'core' => Config::get('core_name'),
                'state' => session_id()
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
    public static function isOauth2Login()
    {
        return isset($_GET['state']) && isset($_SESSION['oauth2state']);
    }

    /**
     *
     * @return array
     */
    public static function checkLogined()
    {
        $result = array(
            'success' => false
        );

        if (static::isOauth2Login()) {
            $state = self::decodeState($_GET['state']);

            $session_state = self::decodeState($_SESSION['oauth2state']);

            if (isset($session_state['state']) &&
                isset($state['state']) &&
                $session_state['state'] == $state['state'] &&
                isset($state['email'])
            ) {
                $userId = DM\Users::getIdByEmail($state['email']);

                if (empty($userId)) {
                    $result['message'] = 'Email ' . $state['email'] .
                        ' not authorized for this core. ' .
                        L\get('Specify_username') . ' ';

                } else {
                    $result = array(
                        'success' => true,
                        'user_id' => $userId,
                        'session_id' => $session_state['state']
                    );
                }

            } else {
                $result['message'] = 'WRONG STATE!!!';
            }

        } else {
            $result['message'] = 'Is not Oauth login';
        }

        return $result;
    }

    /**
     *
     */
    public static function getToken($provider, $state, $code)
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
    public static function getOwner($provider, $token)
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
     * @param  type   $state
     * @return string
     */
    public static function encodeState($state)
    {
        return strtr(base64_encode(json_encode($state)), '+/=', '-_,');
    }

    /**
     *
     * @param  type $encodedState
     * @return type
     */
    public static function decodeState($encodedState)
    {
        $state_json = base64_decode(strtr($encodedState, '-_,', '+/='));
        $state      = json_decode($state_json, true);

        return $state;
    }

    /**
     *
     * @param  type     $provider
     * @param  type     $encodedState
     * @param  type     $code
     * @return provider
     */
    public static function getLocalState($provider, $encodedState, $code)
    {

        $updateEncodedState = null;
        $token              = static::getToken($provider, $encodedState, $code);

        // save token for futher
        // $_SESSION['oauth2_token'] = $token;

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
