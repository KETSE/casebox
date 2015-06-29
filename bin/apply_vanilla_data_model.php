<?php

namespace CB;

/**
 * Script for applying vanilla data model to an existing core.
 * Data model described in https://dev.casebox.org/dev/view/5916/
 *
 * Script params:
 *     -c, --core  - required, core name
 *     -s, --sql <sql_dump_file>  - optional, sql dump file,
 *                                 if no value specified then barebone core is used
 *
 * Example: php -f apply_vanilla_data_model.php -- -c test_core_name
 *          php apply_vanilla_data_model.php -c test_core_name -s
 *          php apply_vanilla_data_model.php -c test_core_name -s /tmp/custom_core_sql_dump.sql
 */

$binDirectorty = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$cbHome = dirname($binDirectorty) . DIRECTORY_SEPARATOR;
$bareBoneCoreSql = $cbHome . 'install/mysql/bare_bone_core.sql' ;

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

//apply sql dump if "s" param is present
if ($importSql) {
    require_once $cbHome . 'httpsdocs/config_platform.php';
    require_once LIB_DIR . 'install_functions.php';

    Cache::set('RUN_SETUP_INTERACTIVE_MODE', true);

    $cfg['su_db_user'] = INSTALL\readParam('su_db_user', $cfg['su_db_user']);
    $cfg['su_db_pass'] = INSTALL\readParam('su_db_pass');

    if (!INSTALL\verifyDBConfig($cfg)) {
        die('Wrong database credentials');
    }

    Cache::set('RUN_SETUP_INTERACTIVE_MODE', false);

    //start Importing sql ...
    $config = array(
        'overwrite_existing_core_db' => 'y'
        ,'core_solr_overwrite' => 'n'
        ,'core_solr_reindex' => 'n'
    );

    Cache::set('RUN_SETUP_CFG', $config);

    $options = array(
        'core' => $coreName
        ,'sql' => $sqlFile
    );

    include $binDirectorty . 'core_create.php';
}

//initializing and loading core config
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$_GET['core'] = $coreName;
$_SESSION['user'] = array('id' => 1);

ini_set('max_execution_time', 0);
error_reporting(E_ALL);

include $cbHome . 'httpsdocs/config.php';
require_once $cbHome . 'httpsdocs/lib/language.php';

$vanilla = new Install\VanillaModel();
$vanilla->apply();

echo "Reindexing solr .. ";

$solrClient = new Solr\Client();
$solrClient->updateTree(array('all' => true));

echo "Ok\n";

echo "Done\n";
