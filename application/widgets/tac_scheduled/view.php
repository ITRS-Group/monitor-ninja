<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm w32 left" id="widget-<?php echo $widget_id ?>">
<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider" style="z-index:1000"></div>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
<?php } ?>
		<table class="w-table">
			<?php for ($i = 0; $i < count($problem); $i++) { ?>
				<tr>
					<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/scheduled.png', array('alt' => $problem[$i]['status'])) ?></td>
					<td>
						<?php echo strtoupper($problem[$i]['status']) ?><br />
						<?php
							echo html::anchor($problem[$i]['url'],$problem[$i]['title']);
						?>
					</td>
				</tr>
			<?php } if (count($problem) == 0) { ?>
				<tr>
					<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/scheduled-not.png', array('alt' => $this->translate->_('N/A'))) ?></td>
					<td><?php echo $this->translate->_('N/A')?></td>
				</tr>
			<?php } ?>
		</table>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<?php } ?>