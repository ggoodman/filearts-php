<?php
	$title = "Login";
?>

<?php include('fragment.header.php'); ?>
<ul>
<li><?= anchor('index.index', "News") ?></li>
<li><?= anchor('login.login', "Login") ?></li>
</ul>

<?php if (isset($login_error)): ?>
	<div class="form_error">
	<?= $login_error ?>
	</div>
<?php endif; ?>
<?= $login_form ?>

<?php include('fragment.footer.php'); ?>