<?php
namespace CB;

use CB\Config;

/**
 * Class used to purify values
 */
class Purify
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
     * purify given html value
     * @param  varchar $html
     * @param  array   $options associative array of purify library options
     * @return varchar
     */
    final public static function html($value, $options = array())
    {
        if (empty($value)) {
            return '';
        }

        static::getInstance();

        $value = Util\toUTF8String($value);

        $config = null;
        if (!empty($options)) {
            $config = \HTMLPurifier_Config::createDefault();

            foreach ($options as $k => $v) {
                $config->set($k, $v);
            }
        }

        $value = static::$purifier->purify($value, $config);

        return $value;
    }

    /**
     * purify filename by removing unsuported filesistem chars: \ / : * ? " < > |
     * @param  varchar $fielname
     * @return varchar
     */
    final public static function filename($filename)
    {
        // replace not allowed chars
        $filename = preg_replace('/[\\\\\/:\*\?"<>|\n\r\t]/', '', $filename);
        // replace more spaces with one space
        $filename = preg_replace('/\s+/', ' ', $filename);

        $filename = trim($filename);

        return $filename;
    }

    /**
     * purify human name
     * @param  varchar $fielname
     * @return varchar
     */
    final public static function humanName($name)
    {
        // replace not allowed chars
        $name = preg_replace('/[^\w\s\."\'`\-]/iu', '', $name);
        // replace more spaces with one space
        $name = preg_replace('/\s+/', ' ', $name);

        $name = trim($name);

        return $name;
    }
}
