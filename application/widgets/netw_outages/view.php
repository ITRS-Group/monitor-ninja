<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm" id="widget-<?php echo $widget_id ?>">
	<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider" style="z-index:1000"></div>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
<?php }
		if (!$user_has_access) {
			echo $no_access_msg;
	 	} else { ?>
		<table class="w-table">
			<tr>
				<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-critical.png', array('alt' => $label)) ?></td>
				<td><?php echo html::anchor('outages/index/', html::specialchars($total_blocking_outages.' '.$label)); ?></td>
			</tr>
		</table>
		<?php
		} // end if user_has_access
?>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<?php } ?>
