<?php
namespace CB\L;

require_once 'language_functions.php';

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

/*  If we do not have a tanslation file for users language, we use main core language.
    If there is no translation file for main language set then we use english by default */
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

\CB\Config::setEnvVar('rtl', !empty($languageSettings[$user_language]['rtl']));

/* end of define user_language constant /**/
