<?php

namespace CB;

/**
 * Generic initialization script to analize script params
 * and prepare options for other core updating and/or importing scripts
 *
 * This script is Not designed to be executed directly.
 *
 * Script initiates $importConfig variable that can be passed to
 * descendend classes of CB\Import\Base
 *
 * Script params:
 *     -c, --core  - required, core name
 *     -s, --sql <sql_dump_file>  - optional, sql dump file,
 *                                 if no value specified then barebone core is used
 *
 * If you dont use -s option it's considered that you want to apply the model to an existing core.
 * If you specify -s without value, then barebone sql dump will be used to create the specified core.
 *
 */

$binDirectorty = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR;
$cbHome = dirname($binDirectorty) . DIRECTORY_SEPARATOR;
$bareBoneCoreSql = $cbHome . 'install/mysql/bare_bone_core.sql';

//check script options
if (empty($options)) {
    $options = getopt('c:s::', array('core:', 'sql::'));
}

$coreName = empty($options['c'])
    ? @$options['core']
    : $options['c'];

if (empty($coreName)) {
    die('no core specified or invalid options set.');
}

$importConfig = array(
    'core_name' => $coreName
);

$importSql = (isset($options['s']) || isset($options['sql']));
$sqlFile = '';

if ($importSql) {
    $sqlFile = empty($options['s'])
        ? @$options['sql']
        : $options['s'];

    //set file to bare bone core if empty
    if (empty($sqlFile)) {
        $sqlFile =  $bareBoneCoreSql;
    }
}

require_once $cbHome . 'httpsdocs/config_platform.php';

//apply sql dump if "s" param is present
if ($importSql) {
    require_once LIB_DIR . 'install_functions.php';

    //disable only if not set in config
    if (!isset($cfg['su_db_pass'])) {
        Cache::set('RUN_SETUP_INTERACTIVE_MODE', true);
    }

    if (empty($cfg['su_db_user'])) {
        $cfg['su_db_user'] = 'root';
    }

    $cfg['su_db_user'] = Install\readParam('su_db_user', $cfg['su_db_user']);
    $cfg['su_db_pass'] = Install\readParam('su_db_pass');

    if (!Install\verifyDBConfig($cfg)) {
        die('Wrong database credentials');
    }

    Cache::set('RUN_SETUP_INTERACTIVE_MODE', false);

    //start Importing sql ...
    $importConfig = array_merge(
        $importConfig,
        array(
            'overwrite_existing_core_db' => 'y'
            ,'core_solr_overwrite' => 'n'
            ,'core_solr_reindex' => 'n'
        )
    );

    //set default root password to 'test' is applying barebone sql dump
    if ($sqlFile == $bareBoneCoreSql) {
        $importConfig['core_root_pass'] = 'test';
    }

    $importConfig['importSql'] = $sqlFile;
}
