<?php
	$title = "Login";
?>

<?php include('fragment.header.php'); ?>

<?php render('fragment.errors.php', array('errors' => form('login')->getErrors())); ?>

<?= form('login') ?>

<?php include('fragment.footer.php'); ?>