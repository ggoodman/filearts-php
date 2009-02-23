<?php
	$title = "Register";
?>

<?php include('fragment.header.php'); ?>

<?php render('fragment.errors.php', array('errors' => form('register')->getErrors())); ?>

<?= form('register') ?>

<?php include('fragment.footer.php'); ?>