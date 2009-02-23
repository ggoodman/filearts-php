<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?= $title ?> - FileArts</title>
<!--<link type="text/css" rel="stylesheet" href="<?= $base_url ?>css/reset.css" />-->
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>css/style.css" />
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>js/sh/styles/shCore.css"/>
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>js/sh/styles/shThemeDefault.css"/>
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>js/jquery.autocomplete.css"/>
<script type="text/javascript" src="<?= $base_url ?>js/jquery.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/jquery.ui.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/jquery.corners.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/sh/scripts/shCore.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/sh/scripts/shBrushPhp.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/sh/scripts/shBrushXml.js"></script>
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>js/theme/ui.base.css" />
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>js/theme/ui.theme.css" />
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>css/filearts.css" />
<script type="text/javascript" src="<?= $base_url ?>js/filearts.js"></script>
<script type="text/javascript">
jQuery(function(){
	SyntaxHighlighter.config['tagName'] = "code";
	SyntaxHighlighter.defaults['html-script'] = true;
	SyntaxHighlighter.all();
	
	$("button.link").click(function(){
		document.location = $(this).val();
	});
	$("input[name=_tags]").autocomplete('tags.php', {
		multiple: true
	});
});
</script>
</head>
<body>
<div id="header">
<div class="wrap">
<div id="user_panel">
<?php if (!$visitor->isMember()): ?>
	<?= anchor('.login.login', "Login")->backRef() ?> or <?= anchor('.member.register', "Register")->backRef() ?>
<?php else: ?>
	Welcome, <?= $visitor->name ?>
	|
	<?= anchor('.member.logout', "Logout")->backRef() ?>
<?php endif; ?>
</div>
<h1>File<span style="color: #CCCCCC;">&Aring;</span>rts</h1>
<div id="topnav">
<?php foreach ($topnav as $link): ?>
	<?= $link ?>
<?php endforeach; ?>
<?= anchor("..", "Back to site")->id('download') ?>
</div>
</div>
</div>
<div id="subhead">
<div class="wrap">
<?= (section_get('subhead')) ? section_get('subhead') : "<h2>$title</h2>"; ?>
</div>
</div>
<div class="wrap">