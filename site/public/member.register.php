<?php
	$title = "Register";
?>

<?php include('fragment.header.php'); ?>

<?php if (isset($register_error)): ?>
	<div class="form_error">
	<?= $register_error ?>
	</div>
<?php endif; ?>
<?= $register_form ?>

<?php include('fragment.footer.php'); ?>