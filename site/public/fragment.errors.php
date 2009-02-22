<?php if (!empty($errors)): ?>
	<div class="ui-state-error ui-corner-all">
	<ul>
	<?php foreach($errors as $field => $messages): foreach ($messages as $message): ?>
		<li><?= $message ?></li>
	<?php endforeach; endforeach; ?>
	</ul>
	</div>
<?php endif; ?>