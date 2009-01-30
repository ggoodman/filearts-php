<html>
<head>
<title><?= $title ?> - FileArts</title>
<style type="text/css">
#menu ul {
	height: 16px;
	overflow: hidden;
	list-style: none;
	margin: 0;
	padding: 0;
}
#menu li {
	float: left;
	height: 16px;
	line-height: 16px;
	padding: 0 4px;
}
#breadcrumbs ul {
	list-style: none;
	margin: 0;
	padding: 0;
}
#breadcrumbs li {
	background: transparent url('img/breadcrumb.gif') no-repeat center right;
	display: inline;
	padding-right: 18px;
}
#breadcrumbs li.current {
	background: none;
	padding-right: 0px;
}
</style>
</head>
<body>
<div id="menu">
<ul>
<li><?= anchor('index.index', "News") ?></li>
<?php if ($visitor->isMember()): ?>
	<li><?= anchor('member.logout', "Logout") ?></li>
<?php else: ?>
	<li><?= anchor('login.login', "Login") ?></li>
	<li><?= anchor('member.register', "Register") ?></li>
<?php endif; ?>
</ul>
</div>
