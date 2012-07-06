<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table class="w-table">
	<?php for ($i = 0; $i < count($problem); $i++) { ?>
		<tr>
			<td class="dark"><?php echo html::image($this->add_path('icons/24x24/shield-'.strtolower($problem[$i]['status']).'.png'), array('alt' => $problem[$i]['status'])) ?></td>
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
			<td class="dark"><?php echo html::image($this->add_path('icons/24x24/shield-not-down.png'), array('alt' => $this->translate->_('N/A'))) ?></td>
			<td><?php echo $this->translate->_('N/A')?></td>
		</tr>
	<?php } ?>
</table>
