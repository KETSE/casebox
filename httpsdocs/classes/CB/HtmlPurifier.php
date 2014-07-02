<?php
namespace CB;

use CB\Config;

/**
 * abstract class for singleton classes definition
 */
class HtmlPurifier
{
    protected static $_instance = null;

    protected static $purifier = null;

    /**
    * Prevent direct object creation
    */
    final private function __construct()
    {
        require_once Config::get('HTML_PURIFIER');
        require_once 'HTMLPurifier.func.php';

        //create default config
        $config = \HTMLPurifier_Config::createDefault();

        $config->set('AutoFormat.AutoParagraph', false);
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
        $config->set('HTML.ForbiddenElements', array('head'));
        $config->set('HTML.SafeIframe', true);
        $config->set('HTML.TargetBlank', true);
        $config->set('URI.DefaultScheme', 'https');
        $config->set('Attr.EnableID', true);

        static::$purifier = new \HTMLPurifier($config);

    }

    /**
    * Prevent object cloning
    */
    final private function __clone()
    {
    }

    /**
    * Returns new or existing Singleton instance
    * @return Singleton
    */
    final public static function getInstance()
    {
        if (null !== static::$_instance) {
            return static::$_instance;
        }
        static::$_instance = new static();

        return static::$_instance;
    }

    /**
     * purify given value
     * @param  varchar $html
     * @param  array   $options associative array of purify library options
     * @return varchar
     */
    final public static function purify($html, $options = array())
    {
        if (empty($html)) {
            return '';
        }

        static::getInstance();

        // detect html encoding
        $cs = mb_detect_encoding($html);

        if (empty($cs)) {
            $cs = 'UTF-8';
        }

        $cs = @iconv($cs, 'UTF-8', $html);
        if (empty($cs)) {
            $cs = $html;
        }

        $config = null;
        if (!empty($options)) {
            $config = \HTMLPurifier_Config::createDefault();

            foreach ($options as $k => $v) {
                $config->set($k, $v);
            }
        }

        $html = static::$purifier->purify($cs, $config);

        return $html;
    }
}
