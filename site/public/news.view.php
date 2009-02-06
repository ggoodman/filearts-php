<?php
	$title = $article->title;
?>

<?php include('fragment.header.php'); ?>

<?php include('fragment.article.php'); ?>

<?php if ($article->comments->orderBy('Comment.posted DESC')->valid()): ?>
	<h2 id="comments"><? $article->num_comments ?> Comments</h2>
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
	<h2 id="comments">No Comments Yet</h2>
<?php endif; ?>

<?php if ($visitor->isMember()): ?>
	<hr />
	<h2>Respond</h2>
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