<?php

namespace UnitTest\Solr;

use CB\Search;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $solr;
    private $config = [
        'solr_host' => 'localhost',
        'solr_port' => 8983,
        'solr_core' => 'cbtest_test'
    ];

    public function setUp()
    {
        $config['solr_port'] = \CB\Config::get('solr_port');
        $config['solr_host'] = \CB\Config::get('solr_host');
        $config['solr_core'] = \CB\Config::get('prefix').'_'.\CB\Config::get('core_name');
        // print_r($config);
        $layer = new \Apache_Solr_Compatibility_Solr4CompatibilityLayer;
        $this->solr = new \Apache_Solr_Service($this->config['solr_host'], $this->config['solr_port'], "/solr/".$this->config['solr_core']."/", false, $layer);

    }

    public function testConnection()
    {
        $this->assertTrue($this->solr->ping()!==false);
    }

    public function testAddDocument()
    {
        $doc = new \Apache_Solr_Document();
        $doc->id = 9999;
        $doc->name="testAddDocument";
        $doc->dstatus = 0;
        $response = $this->solr->addDocument($doc);

        $this->assertTrue($response->getHttpStatus() == 200, "ERROR SOLR ADD DOCUMENT:" . print_r($response, true));

                $response = $this->solr->commit();

        $this->assertTrue($response->getHttpStatus() == 200, "ERROR SOLR COMMIT:".print_r($response, true));

    }

    /**
    * @depends testAddDocument
    */
    public function testQuery()
    {
        $response =  $this->solr->search("id:9999");
        $r = json_decode($response->getRawResponse(), true);
        $this->assertTrue(isset($r['response']['numFound']) && $r['response']['numFound'] >=0, print_r($r, true));
    }
}
