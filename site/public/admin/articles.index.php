<?php
	$title = "Articles";
?>

<?php include('fragment.header.php'); ?>

<table width="100%" class="ui-helper-reset ui-widget ui-widget-content ui-corner-top" cellspacing="0">
<thead class="ui-widget-header">
<tr><th colspan="99">
<?php render('fragment.pager.php', array('pager' => $articles->pager)); ?>
<?= a('articles.create', '<span class="ui-icon ui-icon-plus"></span>Create Article')
	->class('icon-button ui-state-default ui-corner-all ui-priority-primary')
	->title("Create Article"); 
?>
</th></tr>
</thead>
<tbody>
<?php foreach($articles->orderBy('published DESC') as $article): ?>
	<tr>
	<td width="90%"><?= anchor("articles.view", $article->title)->arg('id', $article->id) ?></td>
	<td>
	<ul class="ops ui-helper-reset ui-helper-clearfix">
	<li class="ui-state-default ui-corner-all">
	<?= a('articles.edit', 'Edit')->class("ui-icon ui-icon-pencil")->arg('id', $article->id); ?>
	</li>
	<li class="ui-state-default ui-corner-all">
	<?= a('articles.delete', 'Delete')->class("ui-icon ui-icon-trash")->arg('id', $article->id); ?>
	</li>
	</ul>
	</td>
	</tr>
<?php endforeach; ?>
</tbody>
<thead class="ui-widget-header">
<tr><th colspan="99">
<?php render('fragment.pager.php', array('pager' => $articles->pager)); ?>
<?= a('articles.create', '<span class="ui-icon ui-icon-plus"></span>Create Article')
	->class('icon-button ui-state-default ui-corner-all ui-priority-primary')
	->title("Create Article"); 
?>
</th></tr>
</thead>
</table>



<?php include('fragment.footer.php'); ?>