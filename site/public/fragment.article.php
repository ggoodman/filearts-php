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