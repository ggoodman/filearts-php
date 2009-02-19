<?php
	$title = "Confirm Delete";
?>

<?php include('fragment.header.php'); ?>

Are you sure you want to delete this article?

<?= anchor('news.delete:id')->arg('confirm', 'yes')->text("Yes") ?>
<?= anchor('news.view:id')->text("No") ?>

<?php include('fragment.footer.php'); ?>