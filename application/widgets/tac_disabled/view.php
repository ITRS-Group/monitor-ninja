<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table class="w-table">
	<?php for ($i = 0; $i < count($problem); $i++) { ?>
		<tr>

			<td class="icon dark"><span class="icon-16 x16-shield-disabled"></span></td>
			<td class="status-<?php echo strtolower($problem[$i]['status']);?>">
				<?php echo strtoupper($problem[$i]['status']) ?><br />
				<?php
					echo html::anchor($problem[$i]['url'],$problem[$i]['title']);
				?>
			</td>
		</tr>
	<?php } if (count($problem) == 0) { ?>
		<tr>
			<td class="icon dark"><span class="icon-16 x16-shield-not-disabled"></span></td>
			<td><?php echo _('N/A')?></td>
		</tr>
	<?php } ?>
</table>
