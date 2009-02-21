<?php
	$title = "Edit Article";
?>

<?php include('fragment.header.php'); ?>

<?php if (isset($article_form_error)): ?>
<div class="ui-widget ui-state-error ui-corner-all" style="padding: 0pt 0.7em;">
<p>
<span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span>
<strong>Alert:</strong>
<?= $article_form_error ?>
</p>
</div>
<?php endif; ?>

<?= $article_form ?>

<?php include('fragment.footer.php'); ?>