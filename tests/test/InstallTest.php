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

    public function testCreateSolrCore()
    {

    }
}
