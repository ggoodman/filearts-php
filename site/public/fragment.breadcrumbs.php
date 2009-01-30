<div id="breadcrumbs">
<ul class="breadcrumbs">
<?php foreach (path()->getAncestors() as $ancestor): ?>
<li><a href="<?= $ancestor ?>"><?= ucwords($ancestor->getTitle()) ?></a></li>
<?php endforeach; ?>
<li class="current"><?= $title ?></li>
</ul>
</div>