#!/usr/bin/php
<?php
/**
 * Script to manually start files content extraction
 * It reuses the cron script functionality
 * Params are:
 *         -c <name>, --core <name> - core name
 *         -a, --all  - try extract for all files
 */

ini_set('max_execution_time', 0);

$path = realpath(
    dirname(__FILE__) . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    'sys' . DIRECTORY_SEPARATOR .
    'crons' . DIRECTORY_SEPARATOR
);

//check script options
$options = getopt('c:a', array('core', 'all'));

$core = empty($options['c'])
    ? @$options['core']
    : $options['c'];

if (empty($core)) {
    die('no core specified or invalid options set.');
}

$all = isset($options['a']) || isset($options['all']);

$cmd = 'php -f "'.$path.DIRECTORY_SEPARATOR.'run_cron.php" extract_files_content -c ' . $core;

if ($all) {
    $cmd .= ' -a';
}

echo shell_exec($cmd);
