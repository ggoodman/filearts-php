<html>
<head>
</head>
<body>
<div id="head">
<h1>FileArts</h1>
</div>
<div id="body">
<h2>Access Denied</h2>
<?= $php_message ?>
<?php if ($debug): ?>
	<h3><?= path()->getAction() ?></h3>
<?php endif; ?>
</div>
</body>
</html>