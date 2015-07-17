<?php
namespace UnitTest;

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

    public function testCreateSolrConfigsetsSymlinks()
    {
        $cfg = \CB\Config::loadConfigFile(\CB_DOC_ROOT . 'config.ini');

        $result = \CB\Install\createSolrConfigsetsSymlinks($cfg);

        $this->assertTrue($result['success'], ' creates symplink return errors');

        $this->assertTrue(
            file_exists($result['links']['log']),
            'solr logs configset symlink not created : ' . $result['links']['log']
        );

        $this->assertTrue(
            file_exists($result['links']['default']),
            'solr default configset symlink not created : ' .$result['links']['default']
        );
    }

    public function testReindexSolr()
    {
        /*
        $argv[1] = '-c';
        $argv[2] = 'test';
        $argv[3] = '-a';
        $argv[4] = '-l'; */

        $content = Helpers::getIncludeContents(\CB\BIN_DIR . 'solr_reindex_core.php');

        $this->assertEquals('no core specified or invalid options set.', $content);

        $options =  array(
            'options' => array(
                'core' => DEFAULT_TEST_CORENAME
                ,'all' => true
                ,'nolimit' => true
            )
        );

        $content = Helpers::getIncludeContents(\CB\BIN_DIR . 'solr_reindex_core.php', $options);
        $content = array_filter(explode("\n", $content));

        $this->assertEquals('optimizing', end($content));
    }
}
