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

    /**
     * method for laoding core config
     * @param  array $cfg default configuration
     * @return array throw an exception if core is not defined in db
     */
    public static function load($cfg = array())
    {
        $instance = static::getInstance();

        $cfg = array_merge($cfg, static::getPlatformDBConfig());
        $cfg = array_merge($cfg, static::getPlatformConfigForCore());
        $cfg = array_merge($cfg, static::getCoreDBConfig());

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
    public static function getPlatformDBConfig()
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
     * get core config stored in casebox.cores table
     *
     * @return array
     */
    public static function getPlatformConfigForCore()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT cfg
            FROM casebox.cores
            WHERE name = $1',
            CORE_NAME
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = json_decode($r['cfg'], true);
        } else {
            throw new \Exception('Core not defined in cores table: '.CORE_NAME, 1);
        }
        $res->close();

        if ($rez === false) {
            throw new \Exception('Error decoding core config', 1);
        }

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
            : static::$config['api'];

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
            : static::$config['listeners'];

        $plugins = static::getPlugins();
        $pl = array();
        foreach ($plugins as $name => $data) {
            if (!empty($data['listeners'])) {
                $pl = array_merge_recursive($pl, $data['listeners']);
            }
        }

        $rez = array_merge_recursive($pl, $rez);

        return $rez;
    }

    public static function getCssList()
    {
        $rez = empty(static::$config['css'])
            ? array()
            : static::adjustPaths(static::$config['css'], CORE_DIR);

        array_unshift($rez, DOC_ROOT.'/css/'.getOption('theme', 'default').'/theme.css');

        $plugins = static::getPlugins();
        foreach ($plugins as $name => $data) {
            if (!empty($data['css'])) {
                $rez = array_merge($rez, static::adjustPaths($data['css'], PLUGINS_DIR.$name.DIRECTORY_SEPARATOR));
            }
        }

        return $rez;
    }

    public static function getJsList()
    {
        $rez = empty(static::$config['js'])
            ? array()
            : static::adjustPaths(static::$config['js'], CORE_DIR);

        $plugins = static::getPlugins();
        foreach ($plugins as $name => $data) {
            if (!empty($data['js'])) {
                $rez = array_merge($rez, static::adjustPaths($data['js'], PLUGINS_DIR.$name.DIRECTORY_SEPARATOR));
            }
        }

        return $rez;
    }

    public static function getMinifyGroups()
    {
        $rez = array();
        $css = static::getCssList();

        if (!empty($css)) {
            $rez[CORE_NAME.'_css'] = $css;
        }
        $js = static::getJsList();
        if (!empty($js)) {
            $rez[CORE_NAME.'_js'] = $js;
        }

        return $rez;
    }

    public static function getPlugins()
    {
        if (!empty(static::$plugins)) {
            return static::$plugins;
        }

        $plugins = array();
        $pc = Plugins\SingletonCollection::getInstance();

        if (!isset(static::$config['includeDefaultPlugins'])
            || static::$config['includeDefaultPlugins']
        ) {
            $plugins = $pc->getActivePlugins();
        }

        if (!empty(static::$config['plugins'])) {
            foreach (static::$config['plugins'] as $name => $config) {
                if (is_numeric($name)) {
                    $name = $config;
                    $config = array();
                }

                if (!isset($plugins[$name])) {
                    $plugins[$name] = array();
                }
                $plugins[$name] = array_merge($plugins[$name], $config);
            }
        }

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

    public static function getPluginsRemoteConfig()
    {
        $rez = array();
        $plugins = static::getPlugins();
        foreach ($plugins as $name => $cfg) {
            if (!empty($cfg['remote'])) {
                $rez[$name] = $cfg['remote'];
            }
        }

        return $rez;
    }

    /**
     * replace the begining double slash placeholder with document root path
     * or add main folder to begining
     * @param  varchar|array $value      single value or array of values
     * @param  varchar       $mainFolder optional default folder used. if not set then replacement is made with DOC_ROOT
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
