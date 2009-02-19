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

<hr />
<h2 id="respond">Respond</h2>
<?php if ($visitor->isMember()): ?>
	<?= $comment_form ?>
<?php else: ?>
	<p>
	<?= anchor('login.login', "Login")->backRef('respond') ?>
	or
	<?= anchor('member.register', "Register")->backRef('respond') ?>
	to share your comments.
	</p>
<?php endif; ?>

<?php include('fragment.footer.php'); ?>