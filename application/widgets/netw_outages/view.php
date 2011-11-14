<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm" id="widget-<?php echo $widget_id ?>">
	<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<fieldset>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider"></div>
		</fieldset>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
<?php }
		if (!$user_has_access) { ?>
		<table class="w-table">
			<tr>
				<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-critical.png'), array('alt' => $label)) ?></td>
				<td><?php echo $no_access_msg; ?></td>
			</tr>
		</table>
	 	<?php	 } else { ?>
		<table class="w-table">
			<?php if ($total_blocking_outages > 0) { ?>
			<tr>
				<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => $label)) ?></td>
				<td class="status-outages"><?php echo html::anchor('outages/index/', html::specialchars($total_blocking_outages.' '.$label)); ?></td>
			</tr>
			<?php } else { ?>
			<tr>
				<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-critical.png'), array('alt' => $label)) ?></td>
				<td><?php echo html::anchor('outages/index/', html::specialchars($this->translate->_('N/A'))); ?></td>
			</tr>
			<?php } ?>
		</table>
		<?php
		} // end if user_has_access
?>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<?php } ?>
