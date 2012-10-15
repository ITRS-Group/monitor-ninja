<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table class="w-table">
	<?php for ($i = 0; $i < count($problem); $i++) { ?>
		<tr>
			<td class="icon dark">
			<?php 
				echo '<span class="icon-24 x24-shield-'.strtolower($problem[$i]['status']).'"></span>';
			?>
			</td>
			<td class="status-<?php echo strtolower($problem[$i]['status']); ?>" id="<?php echo $problem[$i]['html_id']?>">
				<strong><?php echo strtoupper($problem[$i]['type']).' '.strtoupper($problem[$i]['status']) ?></strong><br />
				<?php
					echo html::anchor($problem[$i]['url'],$problem[$i]['title']);
					if ($problem[$i]['no'] > 0)
						echo ' / '.html::anchor($problem[$i]['onhost'],$problem[$i]['title2']);
				?>
			</td>
		</tr>
	<?php } if (count($problem) == 0) { ?>
		<tr>
			<td class="dark"><span class="icon-24 x24-shield-not-down"></span></td>
			<td><?php echo _('N/A')?></td>
		</tr>
	<?php } ?>
</table>
