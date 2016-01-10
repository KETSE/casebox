#!/usr/bin/php
<?php
/**
 * languagess
 */

namespace CB;

use CB\L;

$path = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'httpsdocs' . DIRECTORY_SEPARATOR;

require_once $path . 'config_platform.php';

require_once LIB_DIR . 'language_functions.php';

// select main translations
$T = array();

$cfg = Config::getPlatformDBConfig();

$res = DB\dbQuery(
    'SELECT *
    FROM ' . \CB\PREFIX . '_casebox.translations
    WHERE `type` in (0, 2)'
);

while ($r = $res->fetch_assoc()) {
    foreach ($r as $k => $v) {
        if ((strlen($k) == 2) && ($k != 'id') && !empty($v)) {
            $T[$k][] = "'".$r['name'] . "':'".addcslashes($v, "'")."'";

        }
    }
}
$res->close();

//save each translations as main language file
saveFiles($T);

echo "main language files saved\n";

//iterate cores and collect those that have custom translations
$cores = array();

$res = DB\dbQuery(
    'SELECT name, cfg
    FROM ' . \CB\PREFIX . '_casebox.cores'
);

while ($r = $res->fetch_assoc()) {
    $cfg = Util\jsonDecode($r['cfg']);

    $db = empty($cfg['db_name'])
        ? \CB\PREFIX . $r['name']
        : $cfg['db_name'];

    $res2 = DB\dbQuery(
        'SELECT count(*) `count`
        FROM ' . $db . '.translations',
        array(
            'hideErrors' => true
        )
    );

    if ($res2) {
        if ($r2 = $res2->fetch_assoc()) {
            if ($r2['count'] > 0) {
                $cores[$r['name']] = $db;
            }
        }
        $res2->close();
    }
}
$res->close();

if (empty($cores)) {
    echo "No cores with custom translations.\n";
} else {
    echo "Processing " . sizeof($cores) . " cores with custom translations:\n";

    foreach ($cores as $core => $db) {
        $CT = $T;

        $res = DB\dbQuery(
            'SELECT *
            FROM ' . $db . '.translations
            WHERE `type` in (0, 2)',
            array(
                'hideErrors' => true
            )
        );

        while ($r = $res->fetch_assoc()) {
            foreach ($r as $k => $v) {
                if ((strlen($k) == 2) && ($k != 'id') && !empty($v)) {
                    $CT[$k][] = "'".$r['name']."':'".addcslashes($v, "'")."'";
                }
            }
        }

        saveFiles($CT, $core . '_');
        echo '.';
    }
}

echo "\nDone";

/**
 * save translation array as files
 * @param  array &$T
 * @param  string $prefix prefix used for file names
 * @return void
 */
function saveFiles(&$T, $prefix = '')
{
    $localePath = DOC_ROOT . 'js' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR;

    foreach ($T as $l => &$v) {
        $filename = $localePath . $prefix . $l . '.js' ;

        if (file_exists($filename)) {
            unlink($filename);
        }

        file_put_contents($filename, 'L = {'.implode(',', $v).'}');
    }
}
