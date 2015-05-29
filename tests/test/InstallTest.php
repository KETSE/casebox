<?php
namespace CB\UNITTESTS;

/**
 * Description of InstallTest
 *
 * @author ghindows
 */

class InstallTest extends \PHPUnit_Framework_TestCase
{

    public function testdefineBackupDir()
    {
        $dc = DIRECTORY_SEPARATOR;

        $cfg = \CB\Config::loadConfigFile(HELPERS\getCBPath() . $dc . 'config.ini');

        $this->assertEquals(CB_ROOT_PATH . $dc . 'backup' . $dc, \CB\INSTALL\defineBackupDir($cfg));

    }

    public function testcreateSolrConfigsetsSymlinks()
    {

        $dc = DIRECTORY_SEPARATOR;

        $cfg = \CB\Config::loadConfigFile(HELPERS\getCBPath() . $dc . 'config.ini');

        $result = \CB\INSTALL\createSolrConfigsetsSymlinks($cfg);

        $this->assertTrue($result['success'], ' creates symplink return errors');

        $this->assertTrue( file_exists($result['links']['log']), 'solr logs configset symlink not created : ' . $result['links']['log'] );
        $this->assertTrue( file_exists($result['links']['default']), 'solr default configset symlink not created : ' .$result['links']['default'] );

    }
    
}
