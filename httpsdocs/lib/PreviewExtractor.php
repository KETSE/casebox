<?php
namespace CB;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

class PreviewExtractor
{
    public function PreviewExtractor()
    {
        $this->init();
    }

    public function init()
    {
        if (empty($_SERVER['SERVER_NAME'])) {
            $options = getopt('c:', array('core'));

            $core = empty($options['c'])
                ? @$options['core']
                : $options['c'];

            if (empty($core)) {
                die('no core passed');
            }

            $_SERVER['SERVER_NAME'] = $core . '.dummy.com';
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }

        require_once dirname(__FILE__).'/../config.php';
        require_once LIB_DIR.'DB.php';
        DB\connect();
    }

    public function removeFromQueue($id)
    {
        dbQuery('delete from file_previews where id = $1', $id);
    }

    public function purify($html, $options = array())
    {
        if (empty($html)) {
            return '';
        }
        require_once Config::get('HTML_PURIFIER');
        require_once 'HTMLPurifier.func.php';

        $html = Util\toUTF8String($html);

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('AutoFormat.AutoParagraph', false);
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
        //$config->set('AutoFormat.RemoveEmpty', true);//slows down htmls parsing
        //$config->set('AutoFormat.RemoveSpansWithoutAttributes', true); //medium slows down htmls parsing
        $config->set('HTML.ForbiddenElements', array('head'));
        $config->set('HTML.SafeIframe', true);
        $config->set('HTML.TargetBlank', true);
        $config->set('URI.DefaultScheme', 'https');
        $config->set('Attr.EnableID', true);
        if (!empty($options)) {
            foreach ($options as $k => $v) {
                $config->set($k, $v);
            }
        }

        $purifier = new \HTMLPurifier($config);

        // This storage is freed on error
        Cache::set('memory', str_repeat('*', 1024 * 1024));

        register_shutdown_function(array($this, 'onScriptShutdown'));

        $html = $purifier->purify($html);

        Cache::remove('memory');

        $html = str_replace('/preview/#', '#', $html);

        return $html;
    }

    public static function onScriptShutdown()
    {
        Cache::remove('memory');

        if ((!is_null($err = error_get_last())) && (!in_array($err['type'], array (E_NOTICE, E_WARNING)))) {

            DB\dbQuery(
                'UPDATE file_previews
                SET `status` = 3
                WHERE status = 2'
            );

            DB\commitTransaction();
        }
    }
}
