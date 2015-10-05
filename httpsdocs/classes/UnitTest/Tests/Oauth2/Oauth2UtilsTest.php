<?php
//namespace UnitTest\Oauth2;

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\Google as GoogleProvider;
use Mockery as m;
use CB;
use CB\DataModel as DM;

class Oauth2UtilsTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;
    protected $email = 'test@test.com';

    protected function setUp()
    {
        $this->provider = new GoogleProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'hostedDomain' => 'mock_domain',
            'accessType' => 'mock_access_type'
        ]);

        DM\User::updateByName(
            array(
                'name' => 'root',
                'email' => $this->email,
                'data' => '{"email": "'.$this->email.'"}'
            )
        );
    }

    protected function getUrl()
    {
        return \CB\Oauth2Utils::getLoginUrl($this->provider);
    }

    public function test_getLoginUrl()
    {

        $url = $this->getUrl();

        $this->assertTrue(isset($url), 'ERROR getGoogleLoginUrl '.$url);

        $uri = parse_url($url);

        parse_str($uri['query'], $Oauth2Query);

        $this->assertArrayHasKey('client_id', $Oauth2Query);
        $this->assertArrayHasKey('redirect_uri', $Oauth2Query);
        $this->assertArrayHasKey('state', $Oauth2Query);
        $this->assertArrayHasKey('scope', $Oauth2Query);
        $this->assertArrayHasKey('response_type', $Oauth2Query);
        $this->assertArrayHasKey('approval_prompt', $Oauth2Query);

        $this->assertArrayHasKey('access_type', $Oauth2Query);

        $this->assertEquals('mock_access_type', $Oauth2Query['access_type']);

        $this->assertContains('email', $Oauth2Query['scope']);
        $this->assertContains('profile', $Oauth2Query['scope']);
        $this->assertContains('openid', $Oauth2Query['scope']);
    }

    /**
     * @depends test_getLoginUrl
     */
    public function test_checkLogined()
    {

        unset($_SESSION['key']);
        $this->assertFalse(\CB\User::isLoged(), 'ERROR checkLogined \CB\User::isLoged = true');

        $url = $this->getUrl();

        $this->assertTrue(isset($url), 'ERROR checkLogined getGoogleLoginUrl '.$url);

        $uri = parse_url($url);

        parse_str($uri['query'], $Oauth2Query);
        $_GET = $Oauth2Query;

        $state          = \CB\Oauth2Utils::decodeState($Oauth2Query['state']);
        $state['email'] = $this->email;
        $_GET['state']  = \CB\Oauth2Utils::encodeState($state);

        \CB\Oauth2Utils::checkLogined();

        $this->assertTrue(\CB\User::isLoged(),
            'ERROR \CB\User::isLoged = false GET: '.print_r($_GET, true).' SESSIONS:'.print_r($_SESSION, true));
    }
}