<?php
namespace CB\UNITTESTS;

/**
 * Description of SearchTest
 *
 * @author ghindows
 */
class SearchTest extends \PHPUnit_Framework_TestCase
{

    public function testSearch()
    {
       $src = new \CB\Search();
       $this->assertTrue($src->ping()>0);

        $src_response = $src->search('test',0,10,[]);

        $this->assertEquals('OK',$src_response->getHttpStatusMessage(), $src_response->getHttpStatusMessage() );


    }

    public function testReindexSolr() {

        
        /*$argv[1] = '-c';
        $argv[2] = 'test';
        $argv[3] = '-a';
        $argv[4] = '-l'; */

        $content = \CB\UNITTESTS\HELPERS\get_include_contents(\CB\BIN_DIR. 'solr_reindex_core.php');

        $this->assertEquals('no core specified or invalid options set.',$content);
     
    }

}