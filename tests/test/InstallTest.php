<?php
namespace CB\UNITTESTS;
/**
 * Description of InstallTest
 *
 * @author ghindows
 */

class InstallTest extends \PHPUnit_Framework_TestCase
{
    
   public function testdefineBackupDir() {

        $cfg = \CB\Config::loadConfigFile(HELPERS\getCBPath().'/config.ini');

        $this->assertEquals(CB_ROOT_PATH.'/backup/', \CB\INSTALL\defineBackupDir($cfg));

    }

    public function test_createSolrCore() {
        
    }

}