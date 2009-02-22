<?php
	$title = "Edit Article";
?>

<?php include('fragment.header.php'); ?>

<?php render('fragment.errors.php', array('errors' => form('article')->getErrors())); ?>

<?= form('article') ?>

<?php include('fragment.footer.php'); ?>