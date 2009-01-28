<html>
<head>
</head>
<body>
<div id="head">
<h1>FileArts</h1>
</div>
<div id="body">
<h2>Internal server error</h2>
<h3><?= $php_code ?> : <?= $php_message ?></h3>
<?php if ($debug): ?>
	<div id="backtrace">
	<table>
	<tr><th>&nbsp;</th><th>Call</th><th>File</th><th>Line</th></tr>
	<?php foreach ($php_backtrace as $i => $call): ?>
		<tr>
		<td>#<?= $i ?></td>
		<td>
		<?= (isset($call['class'])) ? $call['class'] . $call['type'] : '' ?>
		<?= $call['function'] ?>()
		</td>
		<td><?= (isset($call['file'])) ? $call['file'] : '&nbsp;' ?></td>
		<td><?= (isset($call['line'])) ? $call['line'] : '&nbsp;' ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
	</div>
<?php endif; ?>
</div>
</body>
</html>