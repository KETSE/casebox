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
        $this->assertTrue(is_numeric($this->solr->ping())&&$this->solr->ping()>0,"test ping solr not pass");
    }

   public function testAddDocument() {

       // add documents
         $data = [
                       'name' => 'testDeleteByQuery',
                       'pid' => 1,
                       'template_id' => 5,
                       'data' => [
                           '_title' => 'testDeleteByQuery'
                       ]
                   ];

                // try to add one folder to root tree
        $obj = new \CB\Objects\Object();

        // first create object
        $data['id'] = $obj->create($data);

       $search = new Search();
       $rez = $search->query(
            array(
                'fq' => [ 'name:testDeleteByQuery'],
                'rows' => 1
            )
        );

        // select document from solr
        //  $rez = $this->solr->search('name:testDeleteByQuery', 0, 10, []);
       $this->assertTrue($rez['total'] >= 1,"query result:".print_r($rez, true));


   }
   /**
    * @depends testAddDocument
    */
   public function testDeleteByQuery()
    {

      $search = new Search();
       $rez = $search->query(
            array(
                'fq' => [ 'name:testDeleteByQuery'],
                'rows' => 1
            )
        );

       $this->assertTrue($rez['total'] >= 1, 'Delete all by query didnt clear the solr instance.');

      $this->solr->deleteByQuery('name:testDeleteByQuery');


        $rez = $search->query(
            array(
                'fq' => [ 'name:testDeleteByQuery'],
                'rows' => 1
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
