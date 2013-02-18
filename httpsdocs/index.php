<?php
	require_once 'init.php';
	if(empty($_SESSION['user'])) exit(header('Location: /login.php'));  
?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" >
	<meta name="author" content="HURIDOCS" >
	<meta name="description" content="CaseBox - Litigation Software, Судопроизводство" >
<?php
  echo '<link rel="stylesheet" type="text/css" href="/libx/ext/resources/css/ext-all.css" />';
  echo '<link rel="stylesheet" type="text/css" href="/libx'.Minify_getUri('css').'" />';
  echo '<title>'.CB_get_param('project_name_'.UL()).'</title></title>';
?></head>
<body>
<div id="loading-mask"></div>
<div id="loading" style="width: 250px">
    <div>
    <img src="/css/i/loading.gif" width="41" height="39" style="margin-right:8px;float:left;vertical-align:top; margin-top: -5px" alt="Loading ..." />
    <span style="color: #003399; padding-right: 2px">Case</span><span style="color: #3AAF00;">Box</span><br /><span id="loading-msg"><?php echo L('Loading_CSS')?> ...</span>
    </div>
</div>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = '<?php echo L('Loading_ExtJS_Core')?> ...';</script>    
<script type="text/javascript" src="<?php echo CB_EXT_FOLDER ?>/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="<?php echo CB_EXT_FOLDER ?>/ext-all<?php echo is_debug_host() ? '-debug' : ''; ?>.js"></script>
<?php if(!empty($_SESSION['user']['language']) && ($_SESSION['user']['language'] != 'en')){
		if(file_exists(CB_SITE_PATH.CB_EXT_FOLDER.'/src/locale/ext-lang-'.$_SESSION['user']['language'].'.js'))
			echo '<script type="text/javascript" src="'.CB_EXT_FOLDER.'/src/locale/ext-lang-'.$_SESSION['user']['language'].'.js"></script>';
		echo '<script type="text/javascript" src="/libx'.Minify_getUri('lang-'.$_SESSION['user']['language']).'"></script>';
	}else echo '<script type="text/javascript" src="/libx'.Minify_getUri('lang-en').'"></script>';
?>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = '<?php echo L('Loading_ExtJS_UI')?> ...';</script>
<script type="text/javascript" src="/remote/api.php"></script>
<script type="text/javascript" src="/js/CB.DB.php"></script>
<?php echo '<script type="text/javascript" src="/libx'.Minify_getUri('js').(is_debug_host() ? '&debug=1': '').'"></script>'; ?>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = '<?php echo L('Initialization')?> ...';</script>
</body>
</html>