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

    /* define possible statuses for a core */
    public static $CORESTATUS_DISABLED = 0;
    public static $CORESTATUS_ACTIVE = 1;
    public static $CORESTATUS_MAINTAINANCE = 2;

    /* flags */
    protected static $flags = array(
        'disableTriggers' => false
        ,'disableSolrIndexing' => false
        ,'disableActivityLog' => false
    );

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

        $coreDBConfig = static::getCoreDBConfig();

        $propertiesToMerge = array('files');

        //detect available languages
        $languages = empty($coreDBConfig['languages'])
            ? $cfg['languages']
            : $coreDBConfig['languages'];

        //prepare language properties to be decoded and merged
        $languages = explode(',', $languages);
        foreach ($languages as $l) {
            $l = 'language_' . $l;
            if (isset($cfg[$l])) {
                $cfg[$l] = Util\toJSONArray($cfg[$l]);
            }
            if (isset($coreDBConfig[$l])) {
                $coreDBConfig[$l] = Util\toJSONArray($coreDBConfig[$l]);
            }
            $propertiesToMerge[] = $l;
        }

        $cfg = static::mergeConfigs(
            $cfg,
            $coreDBConfig,
            $propertiesToMerge
        );

        static::$config = static::adjustConfig($cfg);
        static::$environmentVars = static::getEnvironmentVars(static::$config);

        // add core path to include path
        set_include_path(
            INCLUDE_PATH . PATH_SEPARATOR .
            static::$environmentVars['core_dir']
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
     * Reading configuration file
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
            FROM ' . PREFIX . '_casebox.config
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
            FROM ' . PREFIX . '_casebox.cores
            WHERE name = $1',
            $coreName
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = json_decode($r['cfg'], true);
        } else {
            throw new \Exception('Core not defined in cores table: '.$coreName, 1);
        }
        $res->close();

        if ($rez === false) {
            throw new \Exception('Error decoding core config', 1);
        }

        $rez['core_id'] = $r['id'];
        $rez['core_status'] = $r['active'];

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
     * get core status
     * @return integer
     */
    public static function getCoreStatus()
    {
        $status = static::get('core_status', static::$CORESTATUS_DISABLED);

        if ($status != static::$CORESTATUS_MAINTAINANCE) {
            return $status;
        }

        //analize maintainance config and if in maintainance period
        //then allow only console scripts, local ip and defined allowed ips

        // allow all console scripts
        if (isset($_SERVER['argc'])) {
            return static::$CORESTATUS_ACTIVE;
        }

        //get maintainance config. Possible options are startTime, endTime, allowIps
        $mcfg = static::get('maintainance');

        //check if time limits are set and we are outside of that period
        //then core is considered to be active
        $startTime = empty($mcfg['startTime'])
            ? null
            : strtotime($mcfg['startTime']);
        $endTime = empty($mcfg['endTime'])
            ? null
            : strtotime($mcfg['endTime']);
        $now = strtotime('now');

        if (//(is_null($startTime) && is_null($endTime)) ||
            (!is_null($startTime) && ($startTime > $now)) //||
            //(!is_null($endTime) && ($endTime < $now)) // dont autoenable after end time
        ) {
            return static::$CORESTATUS_ACTIVE;
        }

        //check if request is in allowed ips
        $ips = array('localhost', '127.0.0.1');
        if (!empty($mcfg['allowIps'])) {
            $ips = array_merge($ips, Util\toTrimmedArray($mcfg['allowIps']));
        }

        if (in_array($_SERVER['REMOTE_ADDR'], $ips)) {
            return static::$CORESTATUS_ACTIVE;
        }

        return $status;
    }

    /**
     * get message for core status
     * @param  int     $status
     * @return varchar
     */
    public static function getCoreStatusMessage($status = false)
    {
        $rez = '';

        if ($status === false) {
            $status = static::getCoreStatus();
        }

        switch ($status) {
            case static::$CORESTATUS_DISABLED:
                $rez = 'Core is not active at the moment, please try again later.';
                break;

            case static::$CORESTATUS_MAINTAINANCE:
                $coreName = static::get('core_name');

                $rez = file_get_contents(TEMPLATES_DIR . 'maintenance.html');
                if (empty($rez)) {
                    $rez = 'Core is under maintainance, please try again {time}.';
                }

                $mcfg = static::get('maintainance');

                $endTime = empty($mcfg['endTime'])
                    ? null
                    : $mcfg['endTime'];

                $time = 'later.';
                if (!is_null($endTime)) {
                    $dt = new \DateTime($endTime);
                    $ct = new \DateTime('now');
                    $diff = $ct->diff($dt);

                    if ($diff->invert == 0) {
                        $time = $diff->h;
                        if ($time > 0) {
                            $time = 'in ~' . $time . ' hour(s).';
                        } else {
                            $time = 'in about an hour.';
                        }
                    } {
                        $time = 'soon.';
                    }
                }

                $rez = str_replace(
                    array(
                        '{title}',
                        '{mail}',
                        '{time_left}'
                    ),
                    array(
                        static::getProjectName(),
                        static::get('admin_email'),
                        $time
                    ),
                    $rez
                );

                break;
        }

        return $rez;
    }

    /**
     * get project name from config
     * if cannot be found - core_name is returned
     * @return varchar
     */
    public static function getProjectName()
    {
        $userLanguage = Config::get('user_language', 'en');

        $rez = static::get('project_name_' . $userLanguage);

        if (empty($rez)) {
            $rez = static::get('project_name');
        }

        if (empty($rez)) {
            $rez = static::get('project_name_en');
        }

        if (empty($rez)) {
            $rez = static::get('core_name');
        }

        return $rez;
    }

    /**
     * get environment variables from given config
     * @return void
     */
    private static function getEnvironmentVars($config)
    {
        $coreName = $config['core_name'];
        $filesDir = DATA_DIR.'files'.DIRECTORY_SEPARATOR.$coreName.DIRECTORY_SEPARATOR;

        $rez = array(
            'db_name' => empty($config['db_name'])
                ? PREFIX . $coreName
                : $config['db_name']

            ,'solr_core' => empty($config['solr_core'])
                ? PREFIX . $coreName
                : $config['solr_core']

            ,'core_dir' => empty($config['core_dir'])
                ? DOC_ROOT.'cores'.DIRECTORY_SEPARATOR.$coreName.DIRECTORY_SEPARATOR
                : $config['core_dir']

            // path to files folder
            ,'files_dir' => $filesDir

            /* path to preview folder. Generated previews are stored for some filetypes */
            ,'files_preview_dir' => $filesDir.'preview'.DIRECTORY_SEPARATOR

            // path to photos folder
            ,'photos_path' => $filesDir.'_photo'.DIRECTORY_SEPARATOR

            ,'core_url' => 'https://'.$_SERVER['SERVER_NAME'].'/'.$coreName.'/'

            ,'upload_temp_dir' => TEMP_DIR.$coreName.DIRECTORY_SEPARATOR

            /* path to incomming folder. In this folder files are stored when just uploaded
            and before checking existance in target.
            If no user intervention is required then files are stored in db. */
            ,'incomming_files_dir' => TEMP_DIR.$coreName.DIRECTORY_SEPARATOR.'incomming'.DIRECTORY_SEPARATOR

            ,'error_log' => LOGS_DIR . PREFIX . $coreName.'_error_log'

            // custom Error log per Core, use it for debug/reporting purposes
            ,'debug_log' => LOGS_DIR . PREFIX . $coreName.'_debug_log'
        );

        /* Define folder templates */

        $rez['folder_templates'] = empty($config['folder_templates'])
            ? array()
            : explode(',', $config['folder_templates']);

        $rez['default_folder_template'] = empty($rez['folder_templates'])
            ? 0
            : $rez['folder_templates'][0];

        if (empty($config['default_file_template'])) {
            $a = Templates::getIdsByType('file');
            $rez['default_file_template'] = array_shift($a);
        } else {
            $rez['default_file_template'] = $config['default_file_template'];

        }

        if (empty($config['default_shortcut_template'])) {
            $a = Templates::getIdsByType('shortcut');
            $rez['default_shortcut_template'] = array_shift($a);
        } else {
            $rez['default_shortcut_template'] = $config['default_shortcut_template'];
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

        $path = DOC_ROOT.'/css/'.static::get('theme', 'default') . '/';

        array_unshift($rez, $path . 'ribbon.css');
        array_unshift($rez, $path . 'theme.css');

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
                $rez = array_merge(
                    $rez,
                    static::adjustPaths(
                        $data['js'],
                        PLUGINS_DIR.$name.DIRECTORY_SEPARATOR
                    )
                );
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

            $cf = DOC_ROOT . 'js' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $coreName . '_' . $l . '.js';
            if (file_exists($cf)) { //include core's custom translation file if present
                $rez['lang-'.$l] = array('//js/locale/' . $coreName . '_' . $l . '.js');
            } else {
                $rez['lang-'.$l] = array('//js/locale/' . $l . '.js');
            }

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

    /**
     * return default columns available for griv view
     * @return array
     */
    public static function getDefaultGridViewColumns()
    {
        $instance = static::getInstance();

        if (empty($instance->defaultGridViewColumns)) {
            $instance->defaultGridViewColumns = array(
                'nid' => 'ID'
                ,'name' => L\get('Name')
                ,'path' => L\get('Path')
                ,'case' => L\get('Project')
                ,'date' => L\get('Date')
                ,'size' => L\get('Size')
                ,'cid' => L\get('Creator')
                ,'oid' => L\get('Owner')
                ,'uid' => L\get('UpdatedBy')
                ,'comment_user_id' => L\get('CommentedBy')
                ,'cdate' => L\get('CreatedDate')
                ,'udate' => L\get('UpdatedDate')
                ,'comment_date' => L\get('CommentedDate')
                ,'date_end' => L\get('EndDate')
            );
        }

        return $instance->defaultGridViewColumns;
    }

    /**
     * return default configs for known grid columns
     * @return array
     */
    public static function getDefaultGridColumnConfigs()
    {
        $instance = static::getInstance();

        if (empty($instance->defaultGridColumnConfigs)) {
            $userConfig = &$_SESSION['user']['cfg'];
            $dateFormat = $userConfig['short_date_format'];
            $dateTimeFormat = $dateFormat . ' ' . $userConfig['time_format'];

            $instance->defaultGridColumnConfigs = array(
                'nid' => array(
                    'title' => 'ID'
                    ,'width' => 80
                )
                ,'name' => array(
                    'title' => L\get('Name')
                    ,'width' => 300
                )
                ,'path' => array(
                    'title' => L\get('Path')
                    ,'width' => 150
                )
                ,'case' => array(
                    'title' => L\get('Project')
                    ,'width' => 150
                )
                ,'date' => array(
                    'title' => L\get('Date')
                    ,'width' => 130
                    ,'xtype' => 'datecolumn'
                    ,'format' => $dateTimeFormat
                )
                ,'size' => array(
                    'title' => L\get('Size')
                    ,'width' => 80
                )

                ,'cid' => array(
                    'title' => L\get('Creator')
                    ,'width' => 200
                )
                ,'oid' => array(
                    'title' => L\get('Owner')
                    ,'width' => 200
                )
                ,'uid' => array(
                    'title' => L\get('UpdatedBy')
                    ,'width' => 200
                )
                ,'did' => array(
                    'title' => L\get('UpdatedBy')
                    ,'width' => 200
                )
                ,'comment_user_id' => array(
                    'title' => L\get('CommentedBy')
                    ,'width' => 200
                )

                ,'cdate' => array(
                    'title' => L\get('CreatedDate')
                    ,'width' => 130
                    ,'xtype' => 'datecolumn'
                    ,'format' => $dateTimeFormat
                )
                ,'udate' => array(
                    'title' => L\get('UpdatedDate')
                    ,'width' => 130
                    ,'xtype' => 'datecolumn'
                    ,'format' => $dateTimeFormat
                )
                ,'ddate' => array(
                    'title' => L\get('DeletedDate')
                    ,'width' => 130
                    ,'xtype' => 'datecolumn'
                    ,'format' => $dateTimeFormat
                )
                ,'comment_date' => array(
                    'title' => L\get('CommentedDate')
                    ,'width' => 130
                    ,'xtype' => 'datecolumn'
                    ,'format' => $dateTimeFormat
                )
                ,'date_end' => array(
                    'title' => L\get('EndDate')
                    ,'width' => 130
                    ,'xtype' => 'datecolumn'
                    ,'format' => $dateTimeFormat
                )
                ,'order' => array(
                    'title' => L\get('Order')
                    //we shouldnt set solr_column_name by default
                    //because there are templates that could extract values from objects
                    // ,'solr_column_name' => 'order'
                    ,"align" => "center"
                    ,"width" => 10
                    ,"columnWidth" => 10

                )

                ,'task_u_assignee' => array(
                    'title' => L\get('Assignee')
                    ,'width' => 200
                )
                ,'task_u_started' => array(
                    'title' => L\get('StartedBy')
                    ,'width' => 200
                )
                ,'task_u_ongoing' => array(
                    'title' => L\get('Ongoing')
                    ,'width' => 200
                )
                ,'task_u_done' => array(
                    'title' => L\get('DoneBy')
                    ,'width' => 200
                )
                ,'task_u_blocker' => array(
                    'title' => L\get('Blocker')
                    ,'width' => 200
                )
                ,'task_u_all' => array(
                    'title' => L\get('All')
                    ,'width' => 200
                )

                ,'task_d_closed' => array(
                    'title' => L\get('ClosedDate')
                    ,"solr_column_name" => "task_d_closed"
                    ,'width' => 130
                    ,'xtype' => 'datecolumn'
                    ,'format' => $dateTimeFormat
                )
                ,'task_status' => array(
                    'title' => L\get('Status')
                    ,'width' => 70
                )
            );
        }

        return $instance->defaultGridColumnConfigs;
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

        //transform boolean properties to boolean
        $boolProperties = array(
            'allow_duplicates'
        );

        foreach ($boolProperties as $property) {
            if (isset($cfg[$property])) {
                $cfg[$property] = in_array($cfg[$property], array('true', true, 'y', 1, '1'), true);
            }
        }
        //end of transform boolean properties to boolean

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
            ,'maintenance'
        );

        foreach ($jsonProperties as $property) {
            if (!empty($cfg[$property])) {
                $cfg[$property] = Util\toJSONArray($cfg[$property]);
            }
        }

        //change date formats from mysql to php
        if (!empty($cfg['language_settings'])) {
            foreach ($cfg['language_settings'] as $k => &$v) {
                $v['long_date_format'] = str_replace('%', '', $v['long_date_format']);
                $v['short_date_format'] = str_replace('%', '', $v['short_date_format']);
                $v['time_format'] = str_replace('%', '', $v['time_format']);
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
    * get flag value
    * @param  varchar $name  flag name
    * @return variant return false if not set
    */
    public static function getFlag($name)
    {
        if (isset(static::$flags[$name])) {
            return static::$flags[$name];
        }

        return false;
    }

    /**
    * set flag value
    * @param  varchar $name
    * @param  variant $value
    * @return variant return false if not set
    */
    public static function setFlag($name, $value)
    {
        static::$flags[$name] = $value;
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
