<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div>
	<table class="padd-table" class="white-table">
		<?php foreach ($files as $file): ?>
		<tr>
		  <td><?php echo $file; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
