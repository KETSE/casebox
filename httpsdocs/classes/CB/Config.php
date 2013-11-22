<?php
namespace CB;

/**
 * Class used for configuration management
 */
use CB\DB as DB;

class Config extends Singleton
{
    protected static $config = array();
    protected static $plugins = array();

    public static function load()
    {
        $instance = static::getInstance();

        /* load main casebox configs
            later is possible that these configs will be moved to database
            and only database conection params will be defined in a separate configuration file.
        */
        $cfg = static::loadConfigFile(DOC_ROOT.'system.ini');
        $cfg = array_merge($cfg, static::loadConfigFile(DOC_ROOT.'config.ini'));

        //TODO: remove this block after cofig migration to DB.
        // core is not defined in cores table, so we try to load it's cofig from disc
        // for backward compatibility
        $cfg = array_merge($cfg, static::loadConfigFile(CORE_ROOT.'config.ini'));
        $cfg = array_merge($cfg, static::getPlatformDBConfig());
        $cfg = array_merge($cfg, static::getCoreDBConfig());

        $customCoreConfig = array();
        if (is_file(CORE_ROOT.'config.php')) {
            $customCoreConfig = (require CORE_ROOT.'config.php');
            foreach ($customCoreConfig as $k => $v) {
                $a = explode('_', $k);
                $cfg[array_pop($a)] = $v;
            }
        }

        /* try to select configuration of the core from database */
        $res = DB\dbQuery(
            'SELECT cfg FROM casebox.cores WHERE name = $1',
            CORENAME
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $cfg = array_merge($cfg, Util\toJSONArray($r['cfg']));
        }
        $res->close();

        static::$config = static::adjustPaths($cfg);

        return static::$config;
    }

    /**
     * Reading platform system.ini file
     * @return array
     */
    public static function loadConfigFile($filename)
    {
        $rez = array();
        if (file_exists($filename)) {
            $rez = parse_ini_file($filename);
        }

        return $rez;
    }

    /**
     * get casebox config stored in database
     *
     * TODO: remove this method after config migration
     * @return array
     */
    private static function getPlatformDBConfig()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT param
                ,`value`
            FROM casebox.config
            WHERE pid IS NOT NULL'
        ) or die( DB\dbQueryError() );

        while ($r = $res->fetch_assoc()) {
            $rez[$r['param']] = $r['value'];
        }
        $res->close();

        return $rez;
    }

    /**
     * get core config stored in database
     *
     * TODO: remove this method after config migration
     * @return array
     */
    private static function getCoreDBConfig()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT param
                ,`value`
            FROM config'
        ) or die( DB\dbQueryError() );

        while ($r = $res->fetch_assoc()) {
            $rez[$r['param']] = $r['value'];
        }
        $res->close();

        return $rez;
    }

    public static function getApiList()
    {
        $rez = empty(static::$config['api'])
            ? array()
            : $config['api'];

        $plugins = static::getPlugins();
        foreach ($plugins as $name => $data) {
            if (!empty($data['api'])) {
                $rez = array_merge($rez, $data['api']);
            }
        }

        return $rez;
    }

    public static function getListeners()
    {
        $rez = empty(static::$config['listeners'])
            ? array()
            : $config['listeners'];

        $plugins = static::getPlugins();
        foreach ($plugins as $name => $data) {
            if (!empty($data['listeners'])) {
                $rez = array_merge($rez, $data['listeners']);
            }
        }

        return $rez;
    }

    public static function getCssList()
    {
        $rez = empty(static::$config['css'])
            ? array()
            : static::$config['css'];

        $plugins = static::getPlugins();
        foreach ($plugins as $name => $data) {
            if (!empty($data['css'])) {
                $css = array_merge($rez, static::adjustPaths($data['css'], PLUGINS_PATH.$name.DIRECTORY_SEPARATOR));
            }
        }

        return $rez;
    }

    public static function getJsList()
    {
        $rez = empty(static::$config['js'])
            ? array()
            : static::$config['js'];

        $plugins = static::getPlugins();
        foreach ($plugins as $name => $data) {
            if (!empty($data['js'])) {
                $rez = array_merge($rez, static::adjustPaths($data['js'], PLUGINS_PATH.$name.DIRECTORY_SEPARATOR));
            }
        }

        return $rez;
    }

    public static function getMinifyGroups()
    {
        $rez = array();
        $css = static::getCssList();

        if (!empty($css)) {
            $rez[CORENAME.'_css'] = $css;
        }
        $js = static::getJsList();
        if (!empty($js)) {
            $rez[CORENAME.'_js'] = $js;
        }
// var_dump($rez);
        return $rez;
    }

    public static function getPlugins()
    {
        if (!empty(static::$plugins)) {
            return static::$plugins;
        }
        $pc = Plugins\SingletonCollection::getInstance();
        $plugins = isset(static::$config['plugins'])
            ? static::$config['plugins']
            : $pc->getActivePlugins();
        static::$plugins = array();

        foreach ($plugins as $k => $v) {
            if (is_numeric($k)) {
                static::$plugins[$v] = $pc->getData($v)['cfg'];
            } else {
                if (!is_array($v)) {
                    $v = $pc->getData($k)['cfg'];
                }
                static::$plugins[$k] = $v;
            }
        }

        return static::$plugins;
    }

    /**
     * replace the begining double slash placeholder with document root path
     * or add main folder to begining
     * @param  varchar|array $value      single value or array of values
     * @param  varchar       $mainFolder optional default folder used if no replacement is made with DOC_ROOT
     * @return varchar|array
     */
    public static function adjustPaths($value, $mainFolder = false)
    {
        $rez = null;
        if (is_string($value)) {
            if ((substr($value, 0, 2) == '\\\\') || (substr($value, 0, 2) == '//')) {
                $rez = DOC_ROOT.substr($value, 2);
            } elseif ($mainFolder !== false) {
                while ((strlen($value) > 0) && (in_array($value[0], array('\\', '/')))) {
                    $value = substr($value, 1);
                }
                $value = $mainFolder.$value;
            }
        } elseif (is_array($value)) {
            foreach ($value as $key => $v) {
                if (is_string($v) && ((substr($v, 0, 2) == '\\\\') || (substr($v, 0, 2) == '//'))) {
                    $v = DOC_ROOT.substr($v, 2);
                } elseif ($mainFolder !== false) {
                    while ((strlen($v) > 0) && (in_array($v[0], array('\\', '/')))) {
                        $v = substr($v, 1);
                    }
                    $v = $mainFolder.$v;
                }
                $rez[$key] = $v;
            }
        }

        return $rez;
    }
}
