<?php
namespace \CB\UNITTESTS;

/**
 * Description of ObjectsTest
 *
 * @author ghindows
 */
class ObjectsTest extends \PHPUnit_Framework_TestCase
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
    
    public function testCreate()
    {
        
    }

    /**
     * @depends testCreate
     * @param type $param
     */
    public function testRead()
    {
        
    }
    
    /**
     * @depends testCreate
     * @depends testRead
     */
    public function testUpdate()
    {
        
    }
    
    /**
     * @depends testCreate
     * @depends testRead
     * @depends testUpdate
     */
    public function testDelete()
    {
        
    }

    
}
