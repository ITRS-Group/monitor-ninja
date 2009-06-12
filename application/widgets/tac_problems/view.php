<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm w66 left" id="widget-tac_problems">
	<div class="widget-header"><span class="tac_problems_editable" id="tac_problems_title"><?php echo $title ?></span></div>
	<div class="widget-editbox" style="background-color: #ffffff; padding: 15px; float: right; margin-top: -1px; border: 1px solid #e9e9e0; right: 0px; width: 200px">
		<?php echo form::open('ajax/save_widget_setting', array('id' => 'tac_problems_form', 'onsubmit' => 'return false;')); ?>
		<label for="tac_problems_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="tac_problems_refresh" id="tac_problems_refresh" value="<?php echo $tac_problems_refresh ?>" />
		<?php echo form::hidden('tac_problems_page', urlencode(Router::$controller.'/'.Router::$method)) ?>
		<div id="tac_problems_slider"></div>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
<?php } ?>
		<table style="border-spacing: 1px; background-color: #e9e9e0; margin-top: -1px">
			<?php for ($i = 0; $i < count($problem); $i++) { ?>
				<tr>
					<td class="dark"><?php echo html::image('/application/views/themes/default/icons/24x24/shield-'.strtolower($problem[$i]['status']).'.png', array('alt' => $problem[$i]['status'], 'id' => 'tac_problems_img')) ?></td>
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
					<td class="dark"><?php echo html::image('/application/views/themes/default/icons/24x24/shield-not-down.png', array('alt' => $this->translate->_('N/A'), 'id' => 'tac_problems_img')) ?></td>
					<td><?php echo $this->translate->_('N/A')?></td>
				</tr>
			<?php } ?>
		</table>
	</div>
<?php if (!$ajax_call) { ?>
</div>
<?php } ?>
