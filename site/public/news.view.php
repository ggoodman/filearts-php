<?php
	$title = $article->title;
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

<?php if ($article->comments->valid()): ?>
	<hr />
	<h2><? $article->num_comments ?> Comments</h2>
	<ul>
	<?php foreach ($article->comments as $comment): ?>
		<li id="comment_<?= $comment->id ?>">
		<cite><?= anchor('members.view')->arg('id', $comment->user->id)->text($comment->user->name) ?></cite>
		<small><?= date("F j, Y \\a\\t g:i a", strtotime($comment->posted)) ?></small>
		<div><?= $comment->body ?></div>
		</li>
	<?php endforeach; ?>
	</ul>
<?php else: ?>
	<hr />
	<h2>No Comments Yet</h2>
<?php endif; ?>

<?php if ($visitor->isMember()): ?>
	<hr />
	<h2>Comment</h2>
	<?= $comment_form ?>
<?php else: ?>
	<p>
	<?= anchor('login.login')->backRef()->text("Login") ?>
	or
	<?= anchor('member.register')->backRef()->text("Register") ?>
	to share your comments.
	</p>
<?php endif; ?>

<?php include('fragment.footer.php'); ?>