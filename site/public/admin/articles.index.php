<?php
	$title = "Articles";
?>

<?php include('fragment.header.php'); ?>

<button class="link" value="<?= path('articles.create') ?>">Write Article</button>

<table width="100%">
<tr><th>Ops</th><th>Article title</th></tr>
<?php foreach($articles->orderBy('published DESC') as $article): ?>
	<tr><td>
	<?= anchor("articles.edit", "E")->arg('id', $article->id) ?>
	<?= anchor("articles.delete", "D")->arg('id', $article->id) ?>
	</td><td><?= $article->title ?></td></tr>
	<?php include('fragment.article.php'); ?>
<?php endforeach; ?>
</table>

<?php include('fragment.footer.php'); ?>