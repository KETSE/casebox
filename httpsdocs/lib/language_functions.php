<?php

namespace CB\L;

use CB\DB;

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

    $translations = \CB\Cache::get('translations', []);

    return (
        isset($translations[$language][$name])
            ? $translations[$language][$name]
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

        $translations = \CB\Cache::get('translations', []);

        if (isset($translations[$userLanguage][$varName])) {
            $value = $translations[$userLanguage][$varName];
        }
    }

    return $value;
}

/**
 * function to set translations in Cache
 */
function initTranslations()
{
    $translations = \CB\Cache::get('translations', []);
    // if already defined translations then exit
    if (!empty($translations)) {
        return;
    }

    $languages = \CB\Config::get('languages'); // or : \CB\USER_LANGUAGE;

    /* reading main translations table from casebox database*/
    $res = DB\dbQuery(
        'SELECT name, ' . implode(',', $languages) . '
        FROM ' . \CB\PREFIX . '_casebox.translations
        WHERE `type` < 2'
    ) or die( DB\dbQueryError() );

    while ($r = $res->fetch_assoc()) {
        reset($r);
        $name = current($r);
        while ($v = next($r)) {
            $translations[key($r)][$name] = $v;
        }
    }
    $res->close();

    /* reading specific translations of core */
    $res = DB\dbQuery(
        'SELECT *
        FROM translations
        WHERE `type` < 2'
    ) or die( DB\dbQueryError() );

    while ($r = $res->fetch_assoc()) {
        foreach ($languages as $l) {
            if (!empty($r[$l])) {
                $translations[$l][$r['name']] = $r[$l];
            }
        }
    }
    $res->close();

    \CB\Cache::set('translations', $translations);
}
