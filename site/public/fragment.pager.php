<?php if ($pager->getNumPages() > 1): ?>
	<div class="pager ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
	<ul class="ui-helper-reset ui-helper-clearfix">
	<?php if (!$pager->isFirstPage()): ?>
		<li class="ui-state-default"><?= a($pager->getFirstPage(), "First")->class('ui-icon ui-icon-seek-first') ?></li>
		<li class="ui-state-default"><?= a($pager->getPrevPage(), "Prev")->class('ui-icon ui-icon-seek-prev') ?></li>
	<?php else: ?>
		<li class="ui-state-disabled"><span class="ui-icon ui-icon-seek-first">First</span></li>
		<li class="ui-state-disabled"><span class="ui-icon ui-icon-seek-prev">Prev<li>
	<?php endif; ?>
	<?php foreach ($pager->getPages() as $i => $page): ?>
		<li class="ui-state-default"><?= a($page, $i) ?></li>
	<?php endforeach; ?>
	<?php if (!$pager->isLastPage()): ?>
		<li class="ui-state-default"><?= a($pager->getNextPage(), "Next")->class('ui-icon ui-icon-seek-next') ?></li>
		<li class="ui-state-default"><?= a($pager->getLastPage(), "Last")->class('ui-icon ui-icon-seek-end') ?></li>
	<?php else: ?>
		<li class="ui-state-disabled"><span class="ui-icon ui-icon-seek-next">Next</li>
		<li class="ui-state-disabled"><span class="ui-icon ui-icon-seek-end">Last</li>
	<?php endif; ?>
	</ul>
	</div>
<?php endif; ?>
