<?php
	$title = "Viewing: " . $article->title;
?>

<?php include('fragment.header.php'); ?>

<?php render('fragment.article.php', array('article' => $article)); ?>

<?php include('fragment.footer.php'); ?>