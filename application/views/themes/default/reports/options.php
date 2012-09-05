<div style="display:none" id="link_container"></div>
<div id="availability_toolbox">
<?php if ($type == 'avail') { ?>
	<?php if (!$options['report_id']) { ?>
	<a href="#save_report" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-save.png'), array('alt' => _('To schedule this report, save it first'), 'title' => _('To schedule this report, save it first'))); ?></a>
	<?php } ?>
	<a href="#options" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-edit.png'), array('alt' => _('edit settings'), 'title' => _('edit settings'))); ?></a>
<?php } else {?>
<a href="#sla_options" id="sla_save_report" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-save.png'), array('alt' => _('Save report'), 'title' => _('Save report'))); ?></a>
<?php } ?>
<span id="view_add_schedule"<?php if (!$options['report_id']) {?> style="display: none;"<?php } ?>>
	<a id="new_schedule_btn" href="#new_schedule_form_area" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-add-schedule.png'), array('alt' => _('Add').' '. strtolower(_('New schedule')), 'title' => _('Add').' '. strtolower(_('New schedule')))); ?></a>
	<a id="show_schedule" href="#schedule_report"<?php echo (empty($scheduled_info)) ? ' style="display:none;"' : ''; ?> class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-view-schedule.png'), array('alt' => _('View schedule'), 'title' => _('View schedule'))); ?></a>
</span>
<?php
if (Session::instance()->get('main_report_params', false)
	!= Session::instance()->get('current_report_params', false) && Session::instance()->get('main_report_params', false)) {
	# we have main_report_params and we are NOT showing the report (i.e we are showing a sub report)
	# => show backlink
	echo '&nbsp;'.html::anchor($type.'/generate?'.Session::instance()->get('main_report_params'), html::image($this->add_path('/icons/32x32/square-back.png'), array('title' => _('Back'), 'alt' => '')), array('title' => _('Back to original report'), 'style' => 'border: 0px')).'&nbsp;';
}
if (Session::instance()->get('current_report_params', false)) {
	# make it possible to get the link (GET) to the current report
	echo '&nbsp;'.html::anchor($type.'/generate?'.Session::instance()->get('current_report_params'), html::image($this->add_path('/icons/32x32/square-link.png'),array('alt' => '','title' => _('Direct link'))), array('id' => 'current_report_params', 'title' => _('Direct link to this report. Right click to copy or click to view.'),'style' => 'border: 0px'));
}
?>
</div>
<span id="save_to_schedule"><?php echo (!$options['report_id'] && $type != 'avail') ? '<em>'._('To schedule this report, save it first').'</em>' : ''; ?></span>
<div style="display: none;">
<?php
if ($type == 'avail') { ?>
<div id="save_report">
<?php echo form::open($type.'/generate', array('id' => 'save_report_form', 'onsubmit' => 'return validate_report_form(this);'));?>
	<h1><?php echo _('Save report') ?></h1>
	<table style="width: 350px">
		<tr class="none">
			<td style="vertical-align:middle"><label for="report_name" id="save_report_label"><?php echo _('Save as') ?></label></td>
			<td><div id="report_setup">
					<input type="text" name="report_name" id="report_name" class="input-save-name"
						value="<?php echo $options['report_name'] ?>" maxlength="255" style="margin: 0px" />
					<input type="hidden" name="saved_report_id" value="<?php echo $options['report_id'] ?>" />
					<input type="hidden" name="include_trends" value="<?php echo $options['include_trends'] ?>" />
					<input type="hidden" name="save_report_settings" value="1" />
					<input type="hidden" name="old_report_name" value="<?php echo $options['report_name'] ?>" />
					<input type="submit" name="s1" value="<?php echo (!empty($options['report_id'])) ? _('Update report') : _('Save') ?>" class="button update-report20" id="options_submit" />
				</div>
			</td>
		</tr>
	</table>
	<?php echo $options->as_form(false, true); ?>
	<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
</form>
</div>
<div id="options" style="overflow : auto">
<?php echo form::open($type.'/generate', array('id' => 'report_form', 'onsubmit' => 'return validate_report_form(this);'));?>
			<h1><?php echo _('Report settings') ?></h1>
			<table summary="Report settings" id="report" style="width: 350px">
				<tr class="none">
					<td>
						<input type="hidden" name="new_report_setup" value="1" />
						<?php echo _('Reporting period') ?><br />
						<?php echo form::dropdown(array('name' => 'report_period', 'onchange' => 'show_calendar(this.value);'), $options->get_alternatives('report_period'), $options['report_period']); ?>
					</td>
				</tr>
				<tr id="display" style="display: none; clear: both;" class="none fancydisplay">
					<td>
						<?php echo _('Start date') ?> (<span id="start_time_tmp"><?php echo _('Click calendar to select date') ?></span>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('start_time') ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
						<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $options->get_time('start_time') ?>">
						<br />
						<?php echo _('End date') ?> (<span id="end_time_tmp"><?php echo _('Click calendar to select date') ?></span>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('end_time') ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
						<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $options->get_time('end_time') ?>">
					</td>
				</tr>
				<tr class="none">
					<td><?php echo _('SLA calculation method') ?><br />
						<?php echo form::dropdown(array('name' => 'use_average'), $options->get_alternatives('use_average'), $options['use_average']) ?>
					</td>
				</tr>
				<tr class="none">
					<td>
						<label for="count" id="sched_downt" onclick="toggle_state('count');"><?php echo _('Count scheduled downtime as') ?></label><br />
						<?php echo form::dropdown(array('name' => 'scheduleddowntimeasuptime'), $options->get_alternatives('scheduleddowntimeasuptime'), $options['scheduleddowntimeasuptime']) ?>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="checkbox" value="1" class="checkbox" id="cluster_mode" name="cluster_mode" onchange="toggle_label_weight(this.checked, 'cluster_mode_label');" />
						<label for="cluster_mode" id="cluster_mode_label"><?php echo _('Cluster mode') ?></label>
					</td>
				</tr>
				<?php if (isset($extra_options)) {
					echo $extra_options;
				} ?>
				<tr class="none">
					<td>
						<div id="report_setup">
							<label for="report_name">Report name</label><br/><input type="text" name="report_name" id="report_name" class="input-save-name"
								value="<?php echo $options['report_name'] ?>" maxlength="255" />

							<?php
							$set_opts = $options->get_value('host_filter_status');
							foreach (Reports_Model::$host_states as $key => $name) {
								echo '<input type="hidden" name="host_filter_status['.$key.']" value="'.isset($set_opts[$key])."\"/>\n";
							}
							$set_opts = $options->get_value('service_filter_status');
							foreach (Reports_Model::$service_states as $key => $name) {
								echo '<input type="hidden" name="service_filter_status['.$key.']" value="'.isset($set_opts[$key])."\"/>\n";
							} ?>

							<input type="hidden" name="saved_report_id" value="<?php echo $options['report_id'] ?>" />
							<input type="hidden" name="old_report_name" value="<?php echo $options['report_name'] ?>" />
						</div>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="button" name="s1" value="<?php echo _('Update report') ?>" class="button update-report20 save_report_btn" />
					</td>
				</tr>
			</table>
		<?php echo $options->as_form(false, true); ?>
		<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
	</form>
	</div>
<?php
# SLA form - only save report. No "update"
} else { ?>
<div id="sla_options">
<?php echo form::open($type.'/save', array('id' => 'report_form_sla', 'onsubmit' => 'return trigger_ajax_save(this);'));?>
	<h1><?php echo _('Save report') ?></h1>
	<table style="width: 350px">
		<tr class="none">
			<td style="vertical-align:middle"><label for="report_name" id="save_report_label"><?php echo _('Save as') ?></label></td>
			<td><div id="report_setup">
						<input type="text" name="report_name" id="report_name" class="input-save-name"
						value="<?php echo $options['report_name'] ?>" maxlength="255" style="margin: 0px" />
					<input type="hidden" name="saved_report_id" value="<?php echo $options['report_id'] ?>" />
					<input type="hidden" name="sla_save" value="1" />
					<input type="hidden" name="save_report_settings" value="1" />
					<input type="hidden" name="old_report_name" value="<?php echo $options['report_name'] ?>" />
					<input type="submit" name="s1" value="<?php echo (!empty($options['report_id'])) ? _('Update report') : _('Save') ?>" class="button update-report20" id="options_submit" />
				</div>
			</td>
		</tr>

	</table>
	<?php echo $options->as_form(false, true); ?>
	<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
</form>
</div>
<?php } ?>
</div>
