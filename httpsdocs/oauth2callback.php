<?php

namespace CB;

if (isset($_GET['state'])) {
    $state_json = base64_decode(strtr($_GET['state'], '-_,', '+/='));
    $state      = json_decode($state_json, true);
    if (isset($state['core'])) {
        $_GET['core'] = $state['core'];
    } else {
        die('error');
    }
}

$oauthMode = 1;

require_once 'init.php';

$useGoogleOauth2_json = Config::get('googleapis_credentials');

if ($useGoogleOauth2_json && $GPlus = Util\jsonDecode($useGoogleOauth2_json, true)) {

    if (isset($GPlus['web']) && isset($GPlus['web']['client_id']) && isset($GPlus['web']['client_secret']) && isset($GPlus['web']['redirect_uris'])
        && count($GPlus['web']['redirect_uris'])) {

        require_once realpath(__DIR__.'/../vendor/').'/autoload.php';

        $provider = new \League\OAuth2\Client\Provider\Google([
            'clientId' => $GPlus['web']['client_id'],
            'clientSecret' => $GPlus['web']['client_secret'],
            'redirectUri' => $GPlus['web']['redirect_uris'][0],
            'hostedDomain' => $_SERVER['SERVER_NAME']
        ]);

       if ( empty($_GET['state']) ) {

            // State is invalid, possible CSRF attack in progress
            // $state = $_SESSION['oauth2state'];
            // unset($_SESSION['oauth2state']);
            // print_r($_SESSION);
            session_destroy();
            exit('Invalid state: '.$state);
        } else {

            // Try to get an access token (using the authorization code grant)
            try {
            $token = $provider->getAccessToken('authorization_code', [ 'code' => $_GET['code'] ]);
            } catch (Exception $e) {

                // Failed to get user details
                trigger_error('Something went wrong: '.$e->getMessage(),E_USER_ERROR);
            }


            // Optional: Now you have a token you can look up a users profile data
            try {

                // We got an access token, let's now get the owner details
                $ownerDetails = $provider->getResourceOwner($token);

                // Use these details to create a new profile
                printf('Hello %s!<br>', $ownerDetails->getFirstName());
                printf('Your email\'s is %s!<br>', $ownerDetails->getEmail());

                if ($ownerDetails->getEmail()) {
                    $coreUrl = Config::get('core_url');
                    $state['email'] = $ownerDetails->getEmail();
                    header('Location: '.$coreUrl.'login/auth/?state='.strtr(base64_encode(json_encode($state)), '+/=', '-_,') );
                }
                
            } catch (Exception $e) {
            
                // Failed to get user details
                trigger_error('Something went wrong: '.$e->getMessage(),E_USER_ERROR);
            }

            // Use this to interact with an API on the users behalf
            echo $token->accessToken;

            // Use this to get a new access token if the old one expires
            echo $token->refreshToken;

            // Number of seconds until the access token will expire, and need refreshing
            echo $token->expires;

        }
    }
}

