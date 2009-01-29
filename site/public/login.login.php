<?php
	$title = "Login";
?>

<?php include('fragment.header.php'); ?>
<ul>
<li><?= anchor('index.index', "News") ?></li>
<li><?= anchor('login.login', "Login") ?></li>
</ul>

<div class="form_error">
<?= $login_error ?>
</div>

<?= $login_form ?>

<?php include('fragment.footer.php'); ?>