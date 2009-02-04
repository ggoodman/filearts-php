<?php
	$title = "Home";
?>

<?php section_start('subhead'); ?>
<a target="_blank" style="float: left; font-size: 100px; height:100px;line-height: 100px;padding: 0 20px;" href="http://github.com/ggoodman/filearts/tree/master">&Aring;</a>
<p class="intro">FileArts is a light-weight PHP framework designed with simplicity and convention as
fundamental principles.  The framework emphasizes clarity without sacrificing modern object-oriented
principles.</p>
<p>To get started with FileArts, check out the 
<?= anchor('wiki.page', "quick-start tutorial")->arg('title', "quick-start") ?> or browse the
<?= anchor('wiki.index', "documentation") ?>.</p>
<?php section_end(); ?>

<?php include('fragment.header.php'); ?>

<?php foreach($news->orderBy('published DESC') as $article): ?>
	<?php include('fragment.article.php'); ?>
<?php endforeach; ?>

<?php include('fragment.footer.php'); ?>