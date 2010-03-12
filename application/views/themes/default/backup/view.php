<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w98">
	<h2><?php echo $this->translate->_('View') . ' ' . $backup; ?></h2>
	<table class="white-table">
		<?php foreach ($files as $file): ?>
		<tr>
		  <td><?php echo $file; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
