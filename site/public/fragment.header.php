<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?= $title ?> - FileArts</title>
<link type="text/css" rel="stylesheet" href="<?= $base_url ?>js/rte/jquery.rte.css" />
<script type="text/javascript" src="<?= $base_url ?>js/jquery.js"></script>
<script type="text/javascript" src="<?= $base_url ?>js/jquery.corners.js"></script>
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
});
</script>
<style type="text/css">
html, body {
	background-color: #406070;
	text-align: center;
	margin: 0;
	padding: 0;
	width: 100%;
	height: 100%;
	font-family: "Helvetica";
	position: relative;
}
a:hover {
	text-decoration: underline !important;
}
#wrap {
	margin: 0 10%;
	text-align: left;
	min-width: 800px;
	width: auto !important;
	width: 800px;
	height: 100%;
	margin-bottom: 60px;
}
#header {
	background-color:  #FFFFFF;
	padding-right: 201px;
}
#header h1 {
	background-color: #F0F0C0;
	color: #606039;
	height: 75px;
	line-height: 75px;
	margin: 0;
	padding: 20px 0 0 0;
	text-align: center;
}
#menu {
	background-color: #F0F0C0;
	border-top: 1px solid #FFFFFF;
	border-bottom: 1px solid #FFFFFF;
	padding-right: 200px;
}
#menu ul {
	background-color: #609090;
	border-right: 1px solid #FFFFFF;
	height: 25px;
	overflow: hidden;
	list-style: none;
	margin: 0;
	padding: 0;
}
#menu li {
	float: left;
	height: 25px;
	line-height: 25px;
	padding: 0 4px;
}
#menu a {
	color: #FFE3D0;
	text-decoration: none;
}
#body {
	background: #FFFFFF url('css/img/sidebar.gif') right repeat-y;
	overflow: auto;
	position: relative;
}
#sidebar {
	float: right;
	width: 200px;
}
#content {
	border-right: 1px solid #FFFFFF;
	margin-right: 200px;
	padding: 0.5em;
}
#breadcrumbs {
	padding: 4px 0 4px 1em;
}
#breadcrumbs ul {
	list-style: none;
	margin: 0;
	padding: 0;
}
#breadcrumbs li {
	background: transparent url('img/breadcrumb.gif') no-repeat right;
	display: inline;
	margin: 0;
	padding: 0;
	padding-right: 18px;
}
#breadcrumbs li.current {
	background: none;
	color: #609090;
	padding-right: 0px;
}
#breadcrumbs a {
	color: #406070;
	text-decoration: none;
}
#footer {
	background-color: #F0F0C0;
	border-top: 1px solid #FFFFFF;
	clear: both;
	height: 20px;
	font-size: 9px;
	line-height: 20px;
	text-align: center;
}
</style>
</head>
<body>
<div id="wrap">
<div id="header" class="rounded {10px top}">
<h1>FileArts</h1>
</div>
<div id="menu">
<ul>
<li><?= anchor('index.index', "News") ?></li>
<?php if ($visitor->isMember()): ?>
	<li><?= anchor('news.write', "Write Article") ?></li>
	<li><?= anchor('member.logout', "Logout") ?></li>
<?php else: ?>
	<li><?= anchor('login.login', "Login") ?></li>
	<li><?= anchor('member.register', "Register") ?></li>
<?php endif; ?>
</ul>
</div>
<div id="body">
<div id="sidebar">
<?php section_print('sidebar'); ?>
</div>
<div id="content">

<?php include('fragment.breadcrumbs.php'); ?>
