<?php
//namespace UnitTest\Oauth2;

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\Google as GoogleProvider;
use CB;
use CB\DataModel as DM;

class Oauth2UtilsTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;
    protected $email = 'test@test.com';

    protected function setUp()
    {
        $this->provider = new GoogleProvider(
            array(
                'clientId' => 'mock_client_id',
                'clientSecret' => 'mock_secret',
                'redirectUri' => 'none',
                'hostedDomain' => 'mock_domain',
                'accessType' => 'mock_access_type'
            )
        );

        DM\Users::updateByName(
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

    public function testGetLoginUrl()
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
     * @depends testGetLoginUrl
     */
    public function testCheckLogined()
    {

        unset($_SESSION['key']);
        $this->assertFalse(\CB\User::isLoged(), 'ERROR checkLogined \CB\Users::isLoged = true');

        $url = $this->getUrl();

        $this->assertTrue(isset($url), 'ERROR checkLogined getGoogleLoginUrl '.$url);

        $uri = parse_url($url);
        $Oauth2Query = [];
        parse_str($uri['query'], $Oauth2Query);
        $_GET = $Oauth2Query;

        $state          = \CB\Oauth2Utils::decodeState($Oauth2Query['state']);
        $state['email'] = $this->email;
        $_GET['state']  = \CB\Oauth2Utils::encodeState($state);

        $check = \CB\Oauth2Utils::checkLogined();

        $this->assertTrue($check['success'], '\CB\Oauth2Utils::checkLogined() return success false');

        $this->assertTrue($check['user_id'] == 1, '\CB\Oauth2Utils::checkLogined() WRONG USER ID');

        $this->assertTrue($check['session_id']  == $state['state'], '\CB\Oauth2Utils::checkLogined() WRONG SESSION ID');

        $r = \CB\User::setAsLoged($check['user_id'], $check['session_id']);

        $this->assertTrue($r['success'], ' User can\'t be set as logined');

    }
}
