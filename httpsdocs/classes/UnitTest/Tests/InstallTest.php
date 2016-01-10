<?php namespace UnitTest;

/**
 * Description of InstallTest
 *
 * @author ghindows
 */
class InstallTest extends \PHPUnit_Framework_TestCase
{

    public function testDefineBackupDir()
    {
        $cfg = \CB\Config::loadConfigFile(\CB_DOC_ROOT . 'config.ini');

        $this->assertEquals(
            CB_ROOT_PATH . 'backup' . DIRECTORY_SEPARATOR,
            \CB\Install\defineBackupDir($cfg)
        );
    }

    public function testReindexSolr()
    {
        /*
          $argv[1] = '-c';
          $argv[2] = 'test';
          $argv[3] = '-a';
          $argv[4] = '-l'; */

        $options = array(
        );
        $content = Helpers::getIncludeContents(\CB\BIN_DIR . 'solr_reindex_core.php', ['options' => $options]);

        // $this->assertEquals('no core specified or invalid options set.', $content);
        $content = array_filter(explode("\n", $content));
        $expectedResults = [ 'no core specified or invalid options set.', 'Core not found or inactive.' ];
        $this->assertArraySubset($options, $expectedResults);

        $options = array(
            'c' => DEFAULT_TEST_CORENAME,
            'a' => true,
            'l' => true
        );

        $content = Helpers::getIncludeContents(\CB\BIN_DIR . 'solr_reindex_core.php', ['options' => $options]);
        $content = array_filter(explode("\n", $content));

        $this->assertEquals('optimizing', end($content));

    }

    public function testcliGetConfigFile()
    {

        $this->assertEquals('test', \CB\Install\cliGetConfigFile(['config' => 'test']));
        $this->assertEquals('test', \CB\Install\cliGetConfigFile(['f' => 'test']));
        $this->assertEquals('test', \CB\Install\cliGetConfigFile(['file' => 'test']));
    }

    public function testcliLoadConfig()
    {

        $configFile = \UnitTest\Helpers::getConfigFilenameTPL();
        $cfg = \CB\Install\cliLoadConfig(['config' => $configFile]);
        $realConf = \CB\Config::loadConfigFile($configFile);
        // test if loaded solr_port is equal with real solr_port
        $this->assertEquals($cfg['solr_port'], $realConf['solr_port']);
        $cfg = \CB\Install\cliLoadConfig(['config' => $configFile, 'solr_port' => 8180]);
        $this->assertEquals($cfg['solr_port'], 8180);

        $cfg = \CB\Install\cliLoadConfig(['config' => $configFile, 'su_db_pass' => '1234567', 'core_root_pass' => '1234567' ]);

        $this->assertEquals($cfg['su_db_pass'], '1234567');
        $this->assertEquals($cfg['core_root_pass'], '1234567');

        $cfg = \CB\Install\cliLoadConfig([ 'su_db_pass' => '1234567', 'core_root_pass' => '1234567' ]);

        $this->assertEquals($cfg['su_db_pass'], '1234567');
        $this->assertEquals($cfg['core_root_pass'], '1234567');

    }
}
