<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?= $title ?> - FileArts</title>
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>css/reset.css" />
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>css/style.css" />
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>js/rte/jquery.rte.css" />
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>js/sh/styles/shCore.css"/>
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>js/sh/styles/shThemeDefault.css"/>
<script type="text/javascript" src="<?= $base_url ?>js/jquery.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/jquery.corners.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/sh/scripts/shCore.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/sh/scripts/shBrushPhp.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/sh/scripts/shBrushXml.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/jquery.code.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/rte/jquery.rte.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/rte/jquery.rte.tb.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/rte/jquery.ocupload-1.1.4.js"></script>
<script type="text/javascript">
jQuery(function(){
	$('.richedit').rte({
		css: 'default.css',
		width: '99%',
		controls_rte: rte_toolbar,
		controls_html: html_toolbar
	});
	$("pre code").addClass("brush: php");
	SyntaxHighlighter.config['tagName'] = "code";
	SyntaxHighlighter.defaults['html-script'] = true;
	SyntaxHighlighter.all();
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
<h1>File&Aring;rts</h1>
<div id="topnav">
<?php foreach ($topnav as $link): ?>
	<?= $link ?>
<?php endforeach; ?>
<a id="download" target="_blank" href="http://github.com/ggoodman/filearts/tree/master">Download</a>
</div>
</div>
</div>
<div id="subhead">
<div class="wrap">
<?= (section_get('subhead')) ? section_get('subhead') : "<h2>$title</h2>"; ?>
</div>
</div>
<div class="wrap">