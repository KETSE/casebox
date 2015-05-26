<?php
namespace CB\UNITTESTS;

/**
 * Description of SolrTest
 *
 * @author ghindows
 */
class SolrTest extends \PHPUnit_Framework_TestCase
{
   private $SOLR;

    public function setUp() {

        $this->SOLR = new \CB\Solr\Client();

    }
    
    public function testConnection() {

          $this->assertTrue($this->SOLR->ping() > 0);
          
    }
    
}
