<?php
	$title = "News";
?>

<?php section_start('subhead'); ?>
<h2>News</h2>
<?php section_end(); ?>

<?php include('fragment.header.php'); ?>

<?php foreach($news->orderBy('published DESC') as $article): ?>
	<?php include('fragment.article.php'); ?>
<?php endforeach; ?>

<?php render('fragment.pager.php', array('pager' => $news->pager)); ?>

<?php include('fragment.footer.php'); ?>