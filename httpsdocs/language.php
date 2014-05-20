<?php
namespace CB\L;

use CB\DB as DB;

$coreLanguage = \CB\Config::get('language');
$coreLanguages = \CB\Config::get('languages');
$languageSettings = \CB\Config::get('language_settings');

//define language fields
$fields = array();
for ($i=0; $i < sizeof($coreLanguages); $i++) {
    $fields[] = 'l'.getIndex(trim($coreLanguages[$i]));
}

\CB\Config::setEnvVar('language_fields', implode(',', $fields));

// index for default core language
\CB\Config::setEnvVar('language_index', getIndex(\CB\Config::get('language')));

/* define user_language constant /**/
$user_language = $coreLanguage;
if (!empty($_COOKIE['L']) && (strlen($_COOKIE['L']) == 2)) {
    $user_language = strtolower($_COOKIE['L']);
}
if (!empty($_GET['l']) && (strlen($_GET['l']) == 2)) {
    $user_language = strtolower($_GET['l']);
}

/*  If we do not have a tanslation file for users language, we use global core language.
    If there is no translation file for global set language then we use english by default */
if (isset($_SESSION['user']['language']) &&
    isset($languageSettings[$_SESSION['user']['language']])
    ) {
    $user_language = $_SESSION['user']['language'];
} elseif (!isset($languageSettings[@$_SESSION['user']['language']])) {
    $user_language = $coreLanguage;
}

\CB\Config::setEnvVar('user_language', $user_language);

// index for default user language
\CB\Config::setEnvVar('user_language_index', getIndex($user_language));

/* end of define user_language constant /**/

/* function to get the translation value, if defined, for custom specified language.
    If langiage not specified we return the translation for current user language  /**/
function get($name = false, $language = false)
{

    if (empty($name)) {
        return null;
    }
    if (empty($language)) {
        $language = \CB\Config::get('user_language');
        // return (defined('CB\\L\\'.$name) ? constant('CB\\L\\'.$name) : null);
    }

    if (($language[0] == 'l') && (is_numeric($language[1]))) {
        $language = substr($language, 1); // case when we receive laguage as "l{index}"
    }

    if (is_numeric($language)) {
        $language = \CB\Config::get('languages')[$language -1];
    }

    return (
        isset($GLOBALS['TRANSLATIONS'][$language][$name])
            ? $GLOBALS['TRANSLATIONS'][$language][$name]
            : null
    );
}

// get index for user language in core defined languages
function getIndex($language_abrev = false)
{
    $rez = null;
    if (empty($language_abrev)) {
        $language_abrev = \CB\Config::get('user_language');
    }
    $idx = array_search($language_abrev, \CB\Config::get('languages'));
    if ($idx !== false) {
        $rez = $idx + 1;
    }

    return $rez;
}

/**
 * convert a pseudovalue to its defined translation
 * @param  varchar $value  a pseudo value: "[translationName]"
 * @return varchar
 */
function getTranslationIfPseudoValue($value)
{
    if ((substr($value, 0, 1) == '[') &&
        (substr($value, -1, 1) == ']')
    ) {
        $varName = substr($value, 1, strlen($value) - 2);
        $userLanguage = \CB\Config::get('user_language');

        if (isset($GLOBALS['TRANSLATIONS'][$user_language][$varName])) {
            $value = @$GLOBALS['TRANSLATIONS'][$user_language][$varName];
        }
    }

    return $value;
}

/**
 * function for defining translations into $GLOBAL['TRANSLATIONS'] and recreating language files if updated
 */
function initTranslations()
{
    // if already defined translations then exit
    if (isset($GLOBALS['TRANSLATIONS'])) {
        return;
    }

    $languages = \CB\Config::get('languages'); // or : \CB\USER_LANGUAGE;

    /* reading global translations table from casebox database*/
    $res = DB\dbQuery(
        'SELECT name, ' . implode(',', $languages) . '
        FROM `casebox`.translations
        WHERE `type` < 2'
    ) or die( DB\dbQueryError() );

    while ($r = $res->fetch_assoc()) {
        reset($r);
        $name = current($r);
        while ($v = next($r)) {
            $GLOBALS['TRANSLATIONS'][key($r)][$name] = $v;
        }
    }
    $res->close();

    /* reading specific translations of core */
    $res = DB\dbQuery(
        'SELECT name, '.\CB\Config::get('language_fields').'
        FROM translations
        WHERE `type` < 2'
    ) or die( DB\dbQueryError() );

    while ($r = $res->fetch_assoc()) {
        reset($r);
        $name = current($r);
        while (($v = next($r)) !== false) {
            $l = substr(key($r), 1);
            $GLOBALS['TRANSLATIONS'][$languages[$l-1]][$name] = $v;
        }
    }
    $res->close();

    // foreach ($GLOBALS['TRANSLATIONS'][\CB\USER_LANGUAGE] as $k => $v) {
    //     define('CB\\L\\'.$k, $v);
    // }
}

function checkTranslationsUpToDate()
{
    /* verifying if localization JS file for current user language is up to date */
    $last_translations_update_date = null;
    $res = DB\dbQuery(
        'SELECT MAX(udate) `max_date`
        FROM
            (SELECT MAX(udate) `udate`
             FROM casebox.translations
             UNION SELECT MAX(udate)
             FROM translations) t'
    ) or die( DB\dbQueryError() );

    if ($r = $res->fetch_assoc()) {
        $last_translations_update_date = strtotime($r['max_date']);
    }
    $res->close();

    if (!empty($last_translations_update_date)) {
        $locale_filename = \CB\DOC_ROOT . DIRECTORY_SEPARATOR .
            'js' . DIRECTORY_SEPARATOR .
            'locale' . DIRECTORY_SEPARATOR .
            \CB\Config::get('user_language') .
            '.js';

        $create_locale_files = file_exists($locale_filename)
            ? (filemtime($locale_filename) < $last_translations_update_date)
            : true;

        if ($create_locale_files) {
            updateTranslationsFiles();
        }
    }
    /* end of verifying if localization JS file for current user language is up to date */
}

function updateTranslationsFiles()
{
    $rez = array();
    $res = DB\dbQuery(
        'SELECT name, '.\CB\Config::get('languages').'
        FROM `casebox`.translations
        WHERE `type` in (0,2)'
    ) or die( DB\dbQueryError() );

    while ($r = $res->fetch_assoc()) {
        reset($r);
        $name = current($r);
        while (($v = next($r)) !== false) {
            $rez[key($r)][] = "'".$name."':'".addcslashes($v, "'")."'";
        }
    }
    $res->close();
    foreach ($rez as $l => $v) {
        $filename = \CB\DOC_ROOT . DIRECTORY_SEPARATOR .
            'js' . DIRECTORY_SEPARATOR .
            'locale' . DIRECTORY_SEPARATOR .
            $l . '.js' ;

        if (file_exists($filename)) {
            unlink($filename);
        }

        file_put_contents($filename, 'L = {'.implode(',', $v).'}');
    }
}
