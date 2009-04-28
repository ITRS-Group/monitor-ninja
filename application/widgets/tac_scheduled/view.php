<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php if (count($problem) > 0) { ?>
<div class="widget movable collapsable removable closeconfirm w32 left" id="widget-tac_hosts">
	<div class="widget-header"><?php echo $this->translate->_('Scheduled downtime') ?></div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table>
			<?php for ($i = 0; $i < count($problem); $i++) { ?>
				<tr>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/scheduled.png', array('alt' => $problem[$i]['status'])) ?></td>
					<td>
						<?php echo strtoupper($problem[$i]['status']) ?><br />
						<?php
							echo html::anchor($problem[$i]['url'],$problem[$i]['title']);
						?>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
</div>
<?php } ?>