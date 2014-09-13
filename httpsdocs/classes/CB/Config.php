<?php
namespace CB;

/**
 * Class used for configuration management
 */
use CB\DB as DB;

class Config extends Singleton
{
    protected static $config = array();
    protected static $environmentVars = array();
    protected static $plugins = array();

    /**
     * method for laoding core config
     * @param  array $cfg default configuration
     * @return array throw an exception if core is not defined in db
     */
    public static function load($cfg = array())
    {
        $instance = static::getInstance();

        // merging configs from platform, from casebox database and from core itself
        $cfg = array_merge($cfg, static::getPlatformDBConfig());
        $cfg = array_merge($cfg, static::getPlatformConfigForCore($cfg['core_name']));
        $cfg = static::mergeConfigs(
            $cfg,
            static::getCoreDBConfig(),
            array('files')
        );

        static::$config = static::adjustConfig($cfg);
        static::$environmentVars = static::getEnvironmentVars(static::$config);

        // add core path to include path
        set_include_path(
            INCLUDE_PATH . PATH_SEPARATOR .
            static::$environmentVars['core_dir'] . PATH_SEPARATOR .
            static::get('ZEND_PATH')
        );

        // set max file version count
        if (isset(static::$config['files']['max_versions'])) {
            __autoload('CB\\Files');
            Files::setMFVC(static::$config['files']['max_versions']);
        } elseif (isset(static::$config['max_files_version_count'])) { //backward compatibility check
            __autoload('CB\\Files');
            Files::setMFVC(static::$config['max_files_version_count']);
        }

        // set temp upload directory
        ini_set('upload_tmp_dir', static::$environmentVars['upload_temp_dir']);

        ini_set('error_log', static::$environmentVars['error_log']);

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
        } else {
            throw new \Exception('Can\t load config file: ' . $filename, 1);
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
    public static function getPlatformConfigForCore($coreName)
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT id, cfg, active
            FROM casebox.cores
            WHERE name = $1',
            $coreName
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = json_decode($r['cfg'], true);
        } else {
            throw new \Exception('Core not defined in cores table: '. $coreName, 1);
        }
        $res->close();

        if ($rez === false) {
            throw new \Exception('Error decoding core config', 1);
        }

        $rez['core_id'] = $r['id'];
        $rez['core_active'] = $r['active'];

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

    /**
     * get environment variables from given config
     * @return void
     */
    private static function getEnvironmentVars($config)
    {
        $coreName = $config['core_name'];

        $rez = array(
            'db_name' => empty($config['db_name'])
                ? 'cb_'.$coreName
                : $config['db_name']

            ,'solr_core' => empty($config['solr_core'])
                ? 'cb_'.$coreName
                : $config['solr_core']

            ,'core_dir' => empty($config['core_dir'])
                ? DOC_ROOT.'cores'.DIRECTORY_SEPARATOR.$coreName.DIRECTORY_SEPARATOR
                : $config['core_dir']

            // path to photos folder
            ,'photos_path' => DOC_ROOT.'photos'.DIRECTORY_SEPARATOR.$coreName.DIRECTORY_SEPARATOR

            // path to files folder
            ,'files_dir' => DATA_DIR.'files'.DIRECTORY_SEPARATOR.$coreName.DIRECTORY_SEPARATOR

            /* path to preview folder. Generated previews are stored for some filetypes */
            ,'files_preview_dir' => DATA_DIR.'files'.DIRECTORY_SEPARATOR.$coreName.DIRECTORY_SEPARATOR.'preview'.DIRECTORY_SEPARATOR

            ,'core_url' => 'https://'.$_SERVER['SERVER_NAME'].'/'.$coreName.'/'

            ,'upload_temp_dir' => TEMP_DIR.$coreName.DIRECTORY_SEPARATOR

            /* path to incomming folder. In this folder files are stored when just uploaded
            and before checking existance in target.
            If no user intervention is required then files are stored in db. */
            ,'incomming_files_dir' => TEMP_DIR.$coreName.DIRECTORY_SEPARATOR.'incomming'.DIRECTORY_SEPARATOR

            ,'error_log' => LOGS_DIR.'cb_'.$coreName.'_error_log'

            // custom Error log per Core, use it for debug/reporting purposes
            ,'debug_log' => LOGS_DIR.'cb_'.$coreName.'_debug_log'
        );

        /* Define folder templates */

        $rez['folder_templates'] = empty($config['folder_templates'])
            ? array()
            : explode(',', $config['folder_templates']);

        $rez['default_folder_template'] = empty($rez['folder_templates'])
            ? 0
            : $rez['folder_templates'][0];

        if (!empty($config['default_file_template'])) {
            $rez['default_file_template'] = $config['default_file_template'];
        } else {
            $res = DB\dbQuery(
                'SELECT id
                FROM templates
                WHERE `type` = $1',
                'file'
            ) or die( DB\dbQueryError() );

            if ($r = $res->fetch_assoc()) {
                $rez['default_file_template'] = $r['id'];
            } else {
                $rez['default_file_template'] = 0;
            }

            $res->close();
        }

        foreach ($config as $k => $v) {
            if (( strlen($k) == 11 ) && ( substr($k, 0, 9) == 'language_')) {
                $rez['language_settings'][substr($k, 9)] = Util\toJSONArray($v);
            }
        }

        /* Define Core available languages */
        $rez['languages'] = implode(',', array_keys($rez['language_settings']));

        if (!empty($config['languages'])) {
            $rez['languages'] = explode(',', $config['languages']);
            for ($i=0; $i < sizeof($rez['languages']); $i++) {
                $rez['languages'][$i] = trim($rez['languages'][$i]);
            }

            // define default core language
            $rez['language'] = empty($config['default_language'])
                ? $rez['languages'][0]
                : $config['default_language'];
        }

        // Default row count limit used for solr results

        $rez['max_rows'] = empty($config['max_rows'])
            ? 50
            : $config['max_rows'];

        return $rez;
    }

    /**
     * set an environment core varibale
     * @param varchar $varName
     * @param variant $value
     */
    public static function setEnvVar($varName, $value)
    {
        static::$environmentVars[$varName] = $value;
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
            : static::adjustPaths(static::$config['css'], static::$environmentVars['core_dir']);

        array_unshift($rez, DOC_ROOT.'/css/'.static::get('theme', 'default').'/theme.css');

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
            : static::adjustPaths(static::$config['js'], static::$environmentVars['core_dir']);

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
        $coreName = static::$config['core_name'];

        if (!empty($css)) {
            $rez[$coreName.'_css'] = $css;
        }
        $js = static::getJsList();
        if (!empty($js)) {
            $rez[$coreName.'_js'] = $js;
        }

        // add available languages of the core to the minify groups
        $languages = Config::get('languages', array('en'));

        foreach ($languages as $l) {
            $k = mb_strtolower(trim($l));
            $rez['lang-'.$l] = array('//js/locale/'.$l.'.js');
        }

        return $rez;
    }

    /**
     * get core plugins config
     * @return array
     */
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

    /**
     * get remote configuration for core plugins to be included in Ext.Direct api
     * @return array
     */
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
     * get defined plugins for right panel for given object type
     * @param  varchar $objectType
     * @param  string  $from       defines subgroup plugin definition (window - object edit window)
     * @return array
     */
    public static function getObjectTypePluginsConfig($objectType, $from = '')
    {
        $rez = array();
        $tmp = Config::get('object_type_plugins');

        if (!empty($from)) {
            $tmp = @$tmp[$from];
        }

        if (!empty($tmp[$objectType])) {
            $rez = $tmp[$objectType];
        } else {
            $tmp = Config::get('default_object_plugins');

            if (!empty($from)) {
                $tmp = @$tmp[$from];
            }

            if (!empty($tmp)) {
                $rez = $tmp;
            }
        }

        return $rez;
    }

    private static function adjustConfig($cfg)
    {
        /* post processing the obtained config */

        //facet definitions defined globally in casebox config
        $dfd = array();

        if (!empty($cfg['default_facet_configs'])) {
            $dfd = Util\toJSONArray($cfg['default_facet_configs']);
            unset($cfg['default_facet_configs']);
        }

        //check if have defined facets in core config
        if (!empty($cfg['facet_configs'])) {
            $dfd = array_merge($dfd, Util\toJSONArray($cfg['facet_configs']));
        }
        $cfg['facet_configs'] = $dfd;

        //detect Display Columns definitions in casebox config
        // $dcd = array();
        // if (!empty($cfg['default_DC'])) {
        //     $dcd = Util\toJSONArray($cfg['default_DC']);
        //     // unset($cfg['default_DC']);
        // }

        // //check if have defined facets in core config
        // if (!empty($cfg['DC'])) {
        //     $dcd = array_merge($dcd, Util\toJSONArray($cfg['DC']));
        // }
        // $cfg['DC'] = $dcd;

        // detect core plugins (use defined or default if set)
        $plugins = array();
        if (!empty($cfg['default_plugins'])) {
            $plugins = $cfg['default_plugins'];
        }
        if (!empty($cfg['plugins'])) {
            $plugins = Util\toJSONArray($cfg['plugins']);
        }
        $cfg['plugins'] = $plugins;
        // end of detect plugins

        //decode properties of the core config that should be json
        $jsonProperties = array(
            'api'
            ,'css'
            ,'comments_config'
            ,'files'
            ,'js'
            ,'plugins'
            ,'listeners'
            ,'node_facets'
            ,'node_DC'
            ,'default_DC'
            ,'default_object_plugins'
            ,'object_type_plugins'
            ,'treeNodes'
            ,'action_log'
        );

        foreach ($jsonProperties as $property) {
            if (!empty($cfg[$property])) {
                $cfg[$property] = Util\toJSONArray($cfg[$property]);
            }
        }

        return static::adjustPaths($cfg);
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

    /**
    *
    * @param  varchar $optionName name of the option to get
    * @return variant | null
    */
    public static function get($optionName, $defaultValue = null)
    {
        if (isset(static::$environmentVars[$optionName])) {
            return static::$environmentVars[$optionName];
        }

        if (isset(static::$config[$optionName])) {
            return static::$config[$optionName];
        }

        return $defaultValue;
    }

    /**
    * Check if a given value is presend in a config property
    * Property is considered to be an array or a comma separated list of values
    *
    * @param  varchar $optionName name of the option to get
    * @param  varchar $value checked value
    * @return boolean
    */
    public static function isInListValue($optionName, $value)
    {
        $v = static::get($optionName);
        if (is_scalar($v) || is_null($v)) {
            $v = explode(',', $v);
        }

        return in_array(
            $value,
            $v
        );
    }

    /**
    * Custom function for merging two config arrays
    * This function takes as third param an array of properties that should be merged separately
    * It's evident that these properties should have array values in configs
    * @param  array $cfg1
    * @param  array $cfg2
    * @param  array $properties
    * @return array
    */
    public static function mergeConfigs($cfg1, $cfg2, $properties)
    {
        foreach ($cfg2 as $k => $v) {
            if (in_array($k, $properties) && is_array($v)) {
                if (empty($cfg1[$k])) {
                    $cfg1[$k] = array();
                }
                $cfg1[$k] = array_merge($cfg1[$k], $cfg2[$k]);
            } else {
                $cfg1[$k] = $v;
            }
        }

        return $cfg1;
    }
}
