<?php
	$title = "Write an article";
?>

<?php include('fragment.header.php'); ?>

<h3><?= date("F j, Y", strtotime($article->published)) ?></h3>
<h2><?= anchor('news.view')->arg('id', $article->id)->text($article->title) ?></h2>
<div class="meta">
By <?= anchor('members.view')->arg('id', $article->user->id)->text($article->user->name) ?>
</div>
<div class="body"><?= $article->body ?></div>

<?php if ($article->user->id == $visitor->id): ?>
	<?= anchor('news.edit')->arg('id', $article->id)->text("Edit") ?>
	<?= anchor('news.delete')->arg('id', $article->id)->text("Delete") ?>
<?php endif; ?>

<h2>Comments</h2>

<?= $comment_form ?>

<?php include('fragment.footer.php'); ?>