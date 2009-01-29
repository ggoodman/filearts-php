<?php
	$title = "News";
?>

<?php include('fragment.header.php'); ?>
<ul>
<li><?= anchor('index.index', "News") ?></li>
<li><?= anchor('login.login', "Login") ?></li>
</ul>

<ul class="breadcrumbs">
<?php foreach (path()->getAncestors() as $ancestor): ?>
<li><a href="<?= $ancestor ?>"><?= ucwords($ancestor->getTitle()) ?></a></li>
<?php endforeach; ?>
<li class="current"><?= $title ?></li>
</ul>

<?php include('fragment.footer.php'); ?>