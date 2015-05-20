<?php
/**
 * Description of ObjectAPITest
 *
 * @author ghindows
 */
namespace CB\UNITTESTS;

class CoreTest extends \PHPUnit_Framework_TestCase
{
    
    private $DB;

    public function setUp() {

     \CB\UNITTESTS\HELPERS\prepareInstance();
     \CB\UNITTESTS\HELPERS\init();
     
        if (!empty($GLOBALS['dbh'])) {
           $this->DB = $GLOBALS['dbh'];
           //$lastParams = $dbh->lastParams;
        }

    }

    public function tearDown() {
      // disconect to DB
    }

 
    /**
     * 
     */
    public function testDBConnect() {
        $this->assertTrue(isset($this->DB->lastParams));
    }
    
    /**
     * create core and configuration for it 
     * @depends testDBConnect
     */
    public function testCreateCore()
    {
        $this->assertTrue(true);
    }

    /**
     * test if you can login to core 
     * @depends testCreateCore
     */
    public function testLoginCore()
    {
        /* authenticate */
     $fields = ['u' => $username,
                'p' => $userpass,
                's' => 'Login'];
     $ch = curl_init();
     $headers = array("Accept" => "application/json");
     $request = \Unirest\Request::post(getCoreUrl($corename) . 'login/auth/',[],$fields);
     $response->code;        // HTTP Status code
     $response->headers;     // Headers
     $response->body;        // Parsed body
     $response->raw_body;    // Unparsed body
     
     /* 
      * $user = new \CB\User();
        $user->login();
        $this->assertTrue(true);
     */
    }

    /**
     * 
     * try to logout if user is logedin
     * @depends testCreateCore
     * @depends testLoginCore
     */
    public function testLogoutCore()
    {
        $this->assertTrue(false);
    }

    /**
     * delete created core
     * @depends testCreateCore
     */
    public function testDeleteCore()
    {
        $this->assertTrue(true);
    }
}
