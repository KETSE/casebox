<?php
namespace CB;

require_once 'init.php';
if (empty($_SESSION['user'])) {
    exit(header('Location: /login.php'));
}

L\checkTranslationsUpToDate();

$customConfig = getCustomConfig();

require_once(MINIFY_PATH.'utils.php');

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" >
    <meta name="author" content="HURIDOCS" >
    <meta name="description" content="CaseBox - Litigation Software, Судопроизводство" >
<?php

echo '<link rel="stylesheet" type="text/css" href="/libx/ext/resources/css/ext-all.css" />';
echo '<link rel="stylesheet" type="text/css" href="'.Minify_getUri('css').'" />';
if (!empty($customConfig[CORENAME.'_css'])) {
    echo '<link rel="stylesheet" type="text/css" href="'.Minify_getUri(CORENAME.'_css').'" />';
}

echo '<title>'.constant('CB\\CONFIG\\PROJECT_NAME_'.strtoupper(USER_LANGUAGE)).'</title>';

?></head>
<body>
<div id="loading-mask"></div>
<div id="loading" style="width: 250px">
        <div>
        <img src="/css/i/loading.gif" width="41" height="39" style="margin-right:8px;float:left;vertical-align:top; margin-top: -5px" alt="Loading ..." />
        <span style="color: #003399; padding-right: 2px">Case</span><span style="color: #3AAF00;">Box</span><br /><span id="loading-msg"><?php echo L\get('Loading_CSS')?> ...</span>
        </div>
</div>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = '<?php echo L\get('Loading_ExtJS_Core')?> ...';</script>
<script type="text/javascript" src="<?php echo EXT_PATH ?>/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="<?php echo EXT_PATH ?>/ext-all<?php echo isDebugHost() ? '-debug' : ''; ?>.js"></script>
<?php

if (!empty($_SESSION['user']['language']) && ($_SESSION['user']['language'] != 'en')) {
    if (file_exists(DOC_ROOT.EXT_PATH.'/src/locale/ext-lang-'.$_SESSION['user']['language'].'.js')) {
        echo '<script type="text/javascript" src="'.EXT_PATH.'/src/locale/ext-lang-'.$_SESSION['user']['language'].'.js"></script>';
    }
    echo '<script type="text/javascript" src="'.Minify_getUri('lang-'.$_SESSION['user']['language']).'"></script>';
} else {
    echo '<script type="text/javascript" src="'.Minify_getUri('lang-en').'"></script>';
}
?>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = '<?php echo L\get('Loading_ExtJS_UI')?> ...';</script>
<script type="text/javascript" src="/remote/api.php"></script>
<?php

echo '<script type="text/javascript" src="'.Minify_getUri('js').(isDebugHost() ? '&debug=1': '').'"></script>';
echo '<script type="text/javascript" src="'.Minify_getUri('jsdev').(isDebugHost() ? '&debug=1': '').'"></script>';
if (!empty($customConfig[CORENAME.'_js'])) {
    echo '<script type="text/javascript" src="'.Minify_getUri(CORENAME.'_js').(isDebugHost() ? '&debug=1': '').'"></script>';
}

?>
<script type="text/javascript" src="/js/CB.DB.php"></script>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = '<?php echo L\get('Initialization')?> ...';</script>
</body>
</html>
