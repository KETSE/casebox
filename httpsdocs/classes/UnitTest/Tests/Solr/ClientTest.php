<?php
namespace UnitTest\Solr;

use CB\Solr\Client;
use CB\Search;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $solr;

    public function setUp()
    {
        $this->solr = new Client();

    }

    public function testConnection()
    {
        $this->assertTrue(is_numeric($this->solr->ping()));
    }

   public function testDeleteByQuery()
    {
        $this->solr->deleteByQuery('*:*');

        $search = new Search();
        $rez = $search->query(
            array(
                'rows' => 0
            )
        );

        $this->assertTrue($rez['total'] == 0, 'Delete all by query didnt clear the solr instance.');
    }

    public function testReindexing()
    {
        try {
            $this->solr->updateTree(array('all' => true));

            $this->solr->optimize();

            $this->assertTrue(true);

        } catch (\Exception $e) {
            $this->assertTrue(false, 'Error full reindexing');
        }
    }
    
    public function tearDown()
    {
        unset($this->solr);

    }
}
