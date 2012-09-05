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
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $options['start_date'] ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
						<input type="hidden" name="start_time" id="start_time" value="<?php echo $options['start_date'] ?>" />
						<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $options['start_time'] ?>">
						<br />
						<?php echo _('End date') ?> (<span id="end_time_tmp"><?php echo _('Click calendar to select date') ?></span>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $end_date ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
						<input type="hidden" name="end_time" id="end_time" value="<?php echo $end_date ?>" />
						<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $end_time ?>">
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
						<input type="checkbox" value="1" class="checkbox" id="assume" name="assumeinitialstates" onchange="edit_state_options(this.checked);toggle_label_weight(this.checked, 'assume_initial');" />
						<label for="assume" id="assume_initial"><?php echo _('Assume initial states') ?></label>
					</td>
				</tr>
				<tr id="state_options" class="none">
					<td>
						<?php echo _('First assumed host state') ?><br />
						<?php echo form::dropdown(array('name' => 'initialassumedhoststate', 'class' => 'select-initial'),
							$options->get_alternatives('initialassumedhoststate'), $options['initialassumedhoststate']); ?>
							<br />
						<?php echo _('First assumed service state') ?> <br />
						<?php echo form::dropdown(array('name' => 'initialassumedservicestate', 'class' => 'select-initial'),
							$options->get_alternatives('initialassumedservicestate'), $options['initialassumedservicestate']); ?>
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
	<span id="autoreport_periods"><?php echo $json_periods ?></span>
	<div id="new_schedule_form_area" style="padding-left:5px">
	<?php	echo form::open($type.'/schedule', array('id' => 'schedule_report_form', 'onsubmit' => 'return trigger_schedule_save(this);'));
		?>
		<h1><?php echo _('New schedule') ?></h1>
		<table id="new_schedule_report_table" cellpadding="0" cellspacing="0" style="margin-left: -3px">
			<?php if (!empty($available_schedule_periods)) { ?>
			<tr class="none">
				<td><?php echo _('Report Interval') ?></td>
			</tr>
			<tr class="none">
				<td>
					<select name="period" id="period">
					<?php	foreach ($available_schedule_periods as $id => $period) { ?>
					<option value="<?php echo $id ?>"><?php echo $period ?></option>
					<?php	} ?>
					</select>
				</td>
			</tr>
			<?php } ?>
			<tr class="none">
				<td><label><?php echo _('Recipients') ?><br /><input type="text" class="schedule" name="recipients" id="recipients" value="" /></label></td>
			</tr>
			<tr class="none">
				<td><label><?php echo _('Filename') ?><br /><input type="text" class="schedule" name="filename" id="filename" value="" /></label></td>
			</tr>
			<tr class="none">
				<td><label><?php echo _("Local persistent filepath (absolute path to folder, e.g. /tmp)") ?><br /><input type="text" class="schedule" name="local_persistent_filepath" id="local_persistent_filepath" value="" /></label></td>
			</tr>
			<tr class="none">
				<td><label><?php echo _('Description') ?><br /><textarea cols="31" rows="3" id="description" name="description"></textarea></label></td>
			</tr>
			<tr class="none">
				<td id="scheduled_btn_ctrl">
					<input type="submit" name="sched_subm" id="sched_subm" value="<?php echo _('Save') ?>" />
					<input type="reset" name="reset_frm" id="reset_frm" value="<?php echo _('Clear') ?>" />
				</td>
			</tr>
		</table>
		<div><input type="hidden" name="saved_report_id" id="saved_report_id" value="<?php echo $options['report_id'] ?>" />
		<input type="hidden" name="rep_type" value="<?php echo $rep_type ?>" />
		<input type="hidden" name="module_save" id="module_save" value="1" /></div>
		</form>
	</div>

	<div id="schedule_report">
		<table id="schedule_report_table">
				<caption><?php echo _('Schedules for this report') ?> (<span id="scheduled_report_name"><?php echo $options['report_name'] ?></span>)</caption>
				<tr id="schedule_header">
					<th class="headerNone left"><?php echo _('Report Interval') ?></th>
					<th class="headerNone left"><?php echo _('Recipients') ?></th>
					<th class="headerNone left"><?php echo _('Filename') ?></th>
					<th class="headerNone left"><?php echo _("Local persistent filepath (absolute path to folder, e.g. /tmp)") ?></th>
					<th class="headerNone left"><?php echo _('Description') ?></th>
					<th class="headerNone left" style="width: 45px"><?php echo _('Actions') ?></th>
				</tr>
			<?php if (!empty($scheduled_info)) {
				$i = 0;
				foreach ($scheduled_info as $schedule) {
					$i++;
					$schedule = (object)$schedule;
					$recipients = str_replace(' ', '', $schedule->recipients);
					$recipients = str_replace(',', ', ', $recipients); ?>
			<tr id="report-<?php echo $schedule->id ?>" class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
				<td class="period_select" title="<?php echo _('Double click to edit') ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
				<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
				<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
				<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="local_persistent_filepath-<?php echo $schedule->id ?>"><?php echo $schedule->local_persistent_filepath ?></td>
				<td class="iseditable_txtarea" title="<?php echo _('Double click to edit') ?>" id="description-<?php echo $schedule->id ?>"><?php echo $schedule->description ?></td>
				<td>
					<form><input type="button" class="send_report_now" id="send_now_avail_<?php echo $schedule->id ?>" title="<?php echo _('Send this report now') ?>" value="&nbsp;"></form>
					<div class="delete_schedule <?php echo $type ?>_del" id="alldel_<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'), array('alt' => _('Delete scheduled report'), 'title' => _('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
				</td>
			</tr>
		<?php }	} ?>
	</table>
</div>
</div>
