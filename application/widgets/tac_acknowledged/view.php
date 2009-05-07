<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget movable collapsable removable closeconfirm w32 left" id="widget-tac_acknowledged">
	<div class="widget-header"><?php echo $this->translate->_('Acknowledged problems') ?></div>
	<div class="widget-editbox"></div>
	<div class="widget-content">
		<table>
			<?php for ($i = 0; $i < count($problem); $i++) { ?>
				<tr>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/acknowledged.png', array('alt' => $problem[$i]['status'])) ?></td>
					<td>
						<?php echo strtoupper($problem[$i]['status']) ?><br />
						<?php
							echo html::anchor($problem[$i]['url'],$problem[$i]['title']);
						?>
					</td>
				</tr>
			<?php } if (count($problem) == 0) { ?>
				<tr>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/acknowledged-not.png', array('alt' => $this->translate->_('N/A'))) ?></td>
					<td><?php echo $this->translate->_('N/A')?></td>
				</tr>
			<?php } ?>
		</table>
	</div>
</div>