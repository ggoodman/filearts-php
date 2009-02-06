<h3 class="date"><?= date("F j, Y", strtotime($article->published)) ?></h3>
<h2 class="title"><?= anchor('news.view')->arg('id', $article->id)->text($article->title) ?></h2>
<div class="meta">
By <?= anchor('members.view')->arg('id', $article->user->id)->text($article->user->name) ?>
<?php if ($article->tags): ?>
 tagged as 
<?php foreach (array_map('trim', explode(',', $article->tags)) as $i => $tag): ?>
<?= ($i) ? ', ' : '' ?><?= anchor('tags.view', $tag)->arg('tag', $tag) ?>
<?php endforeach; ?>
<?php endif; ?>
</div>
<div class="body"><?= Markdown($article->body) ?></div>
<div class="feedback">
<?= anchor('news.view')->arg('id', $article->id)->anchor('comments')->text("Comments") ?> 
(<?= $article->num_comments ?>)
</div>