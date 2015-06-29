<?php
namespace UnitTest;

use CB\Solr\Client;

class SolrTest extends \PHPUnit_Framework_TestCase
{
    private $SOLR;

    public function setUp()
    {
        $this->SOLR = new Client();

    }

    public function testConnection()
    {
        $this->assertTrue($this->SOLR->ping() > 0);
    }

    public function testReindexing()
    {
        try {
            $this->SOLR->updateTree(array('all' => true));

            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false, 'Error full reindexing');
        }
    }

    public function tearDown()
    {
        unset($this->SOLR);

    }
}
