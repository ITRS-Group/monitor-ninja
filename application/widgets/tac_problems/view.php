<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget movable collapsable removable closeconfirm w66 left" id="widget-tac_problems">
	<div class="widget-header"><?php echo $this->translate->_('Unhandled problems') ?></div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table>
			<?php for ($i = 0; $i < count($problem); $i++) { ?>
				<tr>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/24x24/shield-'.strtolower($problem[$i]['status']).'.png', array('alt' => $problem[$i]['status'])) ?></td>
					<td>
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
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/24x24/shield-not-down.png', array('alt' => $this->translate->_('N/A'))) ?></td>
					<td>N/A</td>
				</tr>
			<?php } ?>
		</table>
	</div>
</div>
