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
        $this->assertTrue(true);
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