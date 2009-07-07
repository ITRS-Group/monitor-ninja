<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm" id="widget-<?php echo $widget_id ?>">
	<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<fieldset>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider"></div>
		</fieldset>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
<?php } ?>
		<table class="w-table">
			<tr>
				<td class='perfBox'>
					<table border="0" cellspacing="4" cellspadding="0">
						<tr>
							<td align="left" valign="center" class='perfItem'>
								<?php echo html::anchor('extinfo/performance', $label_service_check_execution_time.':'  ) ?>
							</td>
							<td valign="top" class='perfValue' nowrap="nowrap">
								<?php echo html::anchor('extinfo/performance', $min_service_execution_time.' / '.$max_service_execution_time.' / '.$average_service_execution_time.' '.$label_sec) ?>
							</td>
						</tr>
						<tr>
							<td align="left" valign="center" class='perfItem'>
								<?php echo html::anchor('extinfo/performance', $label_service_check_latency.':'  ) ?>
							</td>
							<td valign="top" class='perfValue' nowrap="nowrap">
								<?php echo html::anchor('extinfo/performance', $min_service_latency.' / '.$max_service_latency.' / '.$average_service_latency.' '.$label_sec) ?>
							</td>
						</tr>
						<tr>
							<td align="left" valign="center" class='perfItem'>
								<?php echo html::anchor('extinfo/performance', $label_host_check_execution_time.':') ?>
							</td>
							<td valign="top" class='perfValue' nowrap="nowrap">
								<?php echo html::anchor('extinfo/performance', $min_host_execution_time.' / '.$max_host_execution_time.' / '.$average_host_execution_time.' '.$label_sec) ?>
							</td>
						</tr>
						<tr>
							<td align="left" valign="center" class='perfItem'>
								<?php echo html::anchor('extinfo/performance', $label_host_check_latency.':') ?>
							</td>
							<td valign="top" class='perfValue' nowrap="nowrap">
								<?php echo html::anchor('extinfo/performance', $min_host_latency.' / '.$max_host_latency.' / '.$average_host_latency.' '.$label_sec) ?>
							</td>
						</tr>
						<tr>
							<td align="left" valign="center" class='perfItem'>
								<?php echo html::anchor('status/host/?serviceprops='.nagstat::SERVICE_ACTIVE_CHECK, $label_active_host_svc_check.':') ?>
							</td>
							<td valign="top" class='perfValue' nowrap="nowrap">
								<?php echo html::anchor('status/host/?hostprops='.nagstat::SERVICE_ACTIVE_CHECK, $total_active_host_checks) ?>
								/
								<?php echo html::anchor('status/host/?serviceprops='.nagstat::SERVICE_ACTIVE_CHECK, $total_active_service_checks) ?>
							</td>
						</tr>
						<tr>
							<td align="left" valign="center" class='perfItem'>
								<?php echo html::anchor('status/host/?serviceprops='.nagstat::SERVICE_PASSIVE_CHECK, $label_passive_host_svc_check.':') ?>
							</td>
							<td valign="top" class='perfValue' nowrap="nowrap">
								<?php echo html::anchor('status/host/?hostprops='.nagstat::SERVICE_PASSIVE_CHECK, $total_passive_host_checks) ?>
								/
								<?php echo html::anchor('status/host/?hostprops='.nagstat::SERVICE_PASSIVE_CHECK, $total_passive_service_checks) ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<?php } ?>