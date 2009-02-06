<?php
	$title = "Deleting: " . $article->title;
?>

<?php include('fragment.header.php'); ?>

<h2>Confirm Delete</h2>
<p>Are you sure that you want to delete this article?</p>
<button class="link" value="<?= path('articles.delete:id')->arg('confirm', 'yes') ?>">Yes</button>
<button class="link" value="<?= path('articles.index') ?>">No</button>

<h3 class="date"><?= date("F j, Y", strtotime($article->published)) ?></h3>
<h2><?= anchor('news.view')->arg('id', $article->id)->text($article->title) ?></h2>
<div class="meta">
By <?= anchor('members.view')->arg('id', $article->user->id)->text($article->user->name) ?>
</div>
<div class="body"><?= Markdown($article->body) ?></div>
<div class="feedback">
<?= anchor('news.view')->arg('id', $article->id)->anchor('comments')->text("Comments") ?> 
(<?= $article->num_comments ?>)
</div>

<?php include('fragment.footer.php'); ?>