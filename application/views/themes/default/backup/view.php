<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div>
	<h2><?php echo _('View') . ' ' . $backup; ?></h2>
	<table class="white-table">
		<?php foreach ($files as $file): ?>
		<tr>
		  <td><?php echo $file; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
