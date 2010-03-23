<?php $t = $this->translate; ?>
<div style="position: relative; top: -33px; right: 118px; float: right">
<?php if ($type == 'avail') { ?>
	<?php if (!$report_id) { ?>
	<a href="#options" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-save.png'), array('alt' => $label_save_to_schedule, 'title' => $label_save_to_schedule)); ?></a>
	<?php } ?>
	<a href="#options" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-edit.png'), array('alt' => $label_edit_settings, 'title' => $label_edit_settings)); ?></a>
<?php } else {?>
<a href="#sla_options" id="sla_save_report" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-save.png'), array('alt' => $t->_('Save report'), 'title' => $t->_('Save report'))); ?></a>
<?php } ?>
<span id="view_add_schedule"<? if (!$report_id) {?> style="display: none;"<?php } ?>>
	<a id="new_schedule_btn" href="#new_schedule_form_area" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-add-schedule.png'), array('alt' => $t->_('Add').' '. strtolower($label_new_schedule), 'title' => $t->_('Add').' '. strtolower($label_new_schedule))); ?></a>
	<a id="show_schedule" href="#schedule_report"<?php echo (empty($scheduled_info)) ? ' style="display:none;"' : ''; ?> class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-view-schedule.png'), array('alt' => $label_view_schedule, 'title' => $label_view_schedule)); ?></a>
</span>
	<a id="old_avail_link" style="border: 0px; margin-left: 4px;<?php if ($type=='sla') {?>display:none<?php } ?>" href="<?php echo $old_avail_link ?>" target="_blank"><?php echo html::image($this->add_path('/icons/32x32/old-availability.png'),array('alt' => $this->translate->_('Old availability'), 'title' => $this->translate->_('Old availability'))); ?></a>
</div>
<span id="save_to_schedule"><?php echo (!$report_id && $type != 'avail') ? '<em>'.$label_save_to_schedule.'</em>' : ''; ?></span>

<?php
if ($type == 'avail') { ?>
<div id="options">
<?php	echo form::open('reports/generate', array('id' => 'report_form', 'onsubmit' => 'return validate_report_form(this);'));?>
			<h1><?php echo $label_settings ?></h1>
			<table summary="Report settings" id="report" style="width: 350px">
				<tr class="none">
					<td>
						<input type="hidden" name="new_report_setup" value="1" />
						<?php echo $label_report_period ?><br />
						<?php echo form::dropdown(array('name' => 'report_period', 'onchange' => 'show_calendar(this.value);'), $report_periods, $selected); ?>
					</td>
				</tr>
				<tr id="display" style="display: none; clear: both;" class="none fancydisplay">
					<td>
						<?php echo $label_startdate ?> (<span id="start_time_tmp"><?php echo $label_click_calendar ?></span>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $start_date ?>" class="date-pick datepick-start" title="<?php echo $label_startdate_selector ?>" />
						<input type="hidden" name="start_time" id="start_time" value="<?php echo $start_date ?>" />
						<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $start_time ?>">
						<br />
						<?php echo $label_enddate ?> (<span id="end_time_tmp"><?php echo $label_click_calendar ?></span>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $end_date ?>" class="date-pick datepick-end" title="<?php echo $label_enddate_selector ?>" />
						<input type="hidden" name="end_time" id="end_time" value="<?php echo $end_date ?>" />
						<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $end_time ?>">
					</td>
				</tr>
				<tr class="none">
					<td><?php echo $label_sla_calc_method ?><br />
						<?php echo form::dropdown(array('name' => 'use_average'), $use_average_options, $use_average_selected) ?>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="checkbox" value="1" class="checkbox" id="count" name="scheduleddowntimeasuptime" onchange="toggle_label_weight(this.checked, 'sched_downt')" />
						<label for="count" id="sched_downt"><?php echo $label_scheduleddowntimeasuptime ?></label>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="checkbox" value="1" class="checkbox" id="assume" name="assumeinitialstates" onchange="edit_state_options(this.checked);toggle_label_weight(this.checked, 'assume_initial');" />
						<label for="assume" id="assume_initial"><?php echo $label_assumeinitialstates ?></label>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="checkbox" value="0" class="checkbox" id="cluster_mode" name="cluster_mode" onchange="edit_state_options(this.checked);toggle_label_weight(this.checked, 'cluster_mode');" />
						<label for="cluster_mode" id="cluster_mode"><?php echo $label_cluster_mode ?></label>
					</td>
				</tr>
				<tr id="state_options" class="none">
					<td>
						<?php echo $label_initialassumedhoststate ?><br />
						<?php echo form::dropdown(array('name' => 'initialassumedhoststate', 'class' => 'select-initial'),
							$initial_assumed_host_states, $selected_initial_assumed_host_state); ?>
							<br />
						<?php echo $label_initialassumedservicestate ?> <br />
						<?php echo form::dropdown(array('name' => 'initialassumedservicestate', 'class' => 'select-initial'),
						$initial_assumed_service_states, $selected_initial_assumed_service_state); ?>
					</td>
				</tr>
				<tr class="none">
					<td>
						<div class="save-report">
							<input type="checkbox" class="checkbox" name="save_report_settings" id="save_report_settings" value="1" onclick="toggle_field_visibility(this.checked, 'report_save_information');toggle_label_weight(this.checked, 'save_report_label')" />
							<label for="save_report_settings" id="save_report_label"><?php echo $label_save_report ?></label>
						</div>
						<div id="report_setup">
							<span id="report_save_information">
								<input type="text" name="report_name" id="report_name" class="input-save-name"
									value="<?php echo isset($report_info['report_name']) && !empty($report_info['report_name']) ? $report_info['report_name'] : '' ?>" maxlength="255" />
							</span>
							<input type="hidden" name="saved_report_id" value="<?php echo $report_id ?>" />
							<input type="hidden" name="old_report_name"
									value="<?php echo isset($report_info['report_name']) && !empty($report_info['report_name']) ? $report_info['report_name'] : '' ?>" />
						</div>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="submit" name="s1" value="<?php echo $label_update ?>" class="button update-report20" id="options_submit" />
					</td>
				</tr>
			</table>
		<?php
	if (is_array($html_options))
			foreach ($html_options as $html_option)
				echo form::hidden($html_option[1], $html_option[2]); ?>
		<input type="hidden" name="report_id" value="<?php echo isset($report_id) ? $report_id : 0 ?>" />
	</form>
	</div>
<?php
# SLA form - only save report. No "update"
} else { ?>
<div id="sla_options">
<?php	echo form::open('reports/save', array('id' => 'report_form_sla', 'onsubmit' => 'return trigger_ajax_save(this);'));?>
	<h1><?php echo $t->_('Save report') ?></h1>
	<table style="width: 350px">
		<tr class="none">
			<td>
				<label for="save_report_settings" id="save_report_label"><?php echo $label_save_report ?></label>
				<div id="report_setup">
						<input type="text" name="report_name" id="report_name" class="input-save-name"
						value="<?php echo isset($report_info['sla_name']) && !empty($report_info['sla_name']) ? $report_info['sla_name'] : '' ?>" maxlength="255" />
					<input type="hidden" name="saved_report_id" value="<?php echo $report_id ?>" />
					<input type="hidden" name="sla_save" value="1" />
					<input type="hidden" name="save_report_settings" value="1" />
					<input type="hidden" name="old_report_name"
						value="<?php echo isset($report_info['sla_name']) && !empty($report_info['sla_name']) ? $report_info['sla_name'] : '' ?>" />
				</div>
			</td>
		</tr>
		<tr class="none">
			<td>
				<input type="submit" name="s1" value="<?php echo (!empty($report_id)) ? $label_update : $t->_('Save') ?>" class="button update-report20" id="options_submit" />
			</td>
		</tr>

	</table>
<?php
	if (is_array($html_options))
			foreach ($html_options as $html_option)
				echo form::hidden($html_option[1], $html_option[2]); ?>
		<input type="hidden" name="report_id" value="<?php echo isset($report_id) ? $report_id : 0 ?>" />
	</form>
</div>
<?php } ?>
	<span id="autoreport_periods"><?php echo $json_periods ?></span>
	<div id="new_schedule_form_area">
	<?php	echo form::open('reports/schedule', array('id' => 'schedule_report_form', 'onsubmit' => 'return trigger_schedule_save(this);'));
		?>
		<h1><?php echo $label_new_schedule ?></h1>
		<table id="new_schedule_report_table" cellpadding="0" cellspacing="0" style="margin-left: -3px">
			<?php if (!empty($available_schedule_periods)) { ?>
			<tr class="none">
				<td><?php echo $label_interval ?></td>
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
				<td><?php echo $label_recipients ?><br /><input type="text" class="schedule" name="recipients" id="recipients" value="" /></td>
			</tr>
			<tr class="none">
				<td><?php echo $label_filename ?><br /><input type="text" class="schedule" name="filename" id="filename" value="" /></td>
			</tr>
			<tr class="none">
				<td><?php echo $label_description ?><br /><textarea cols="31" rows="3" id="description" name="description"></textarea></td>
			</tr>
			<tr class="none">
				<td id="scheduled_btn_ctrl">
					<input type="submit" name="sched_subm" id="sched_subm" value="<?php echo $label_save ?>" />
					<input type="reset" name="reset_frm" id="reset_frm" value="<?php echo $label_clear ?>" />
				</td>
			</tr>
		</table>
		<div><input type="hidden" name="saved_report_id" id="saved_report_id" value="<?php echo $report_id ?>" />
		<input type="hidden" name="rep_type" value="<?php echo $rep_type ?>" />
		<input type="hidden" name="module_save" id="module_save" value="1" /></div>
		</form>
	</div>

	<div id="schedule_report">
		<table id="schedule_report_table">
				<caption><?php echo $lable_schedules ?> (<span id="scheduled_report_name"><?php echo !empty($report_info) ? (!empty($report_info['report_name']) ?
									$report_info['report_name'] : $report_info['sla_name']) : '' ?></span>)</caption>
				<tr id="schedule_header">
					<th class="headerNone left"><?php echo $label_interval ?></th>
					<th class="headerNone left"><?php echo $label_recipients ?></th>
					<th class="headerNone left"><?php echo $label_filename ?></th>
					<th class="headerNone left"><?php echo $label_description ?></th>
					<th class="headerNone left">&nbsp;</th>
				</tr>
			<?php if (!empty($scheduled_info)) {
				$i = 0;
				foreach ($scheduled_info as $schedule) {
					$i++;
					$schedule = (object)$schedule;
					$recipients = str_replace(' ', '', $schedule->recipients);
					$recipients = str_replace(',', ', ', $recipients); ?>
			<tr id="report-<?php echo $schedule->id ?>" class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
				<td class="period_select" title="<?php echo $label_dblclick ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
				<td class="iseditable" title="<?php echo $label_dblclick ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
				<td class="iseditable" title="<?php echo $label_dblclick ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
				<td class="iseditable_txtarea" title="<?php echo $label_dblclick ?>" id="description-<?php echo $schedule->id ?>"><?php echo utf8_decode($schedule->description) ?></td>
				<td class="delete_schedule" id="<?php echo $schedule->id ?>" style="width: 16px; padding-left: 0px"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'),array('alt' => $this->translate->_('Delete scheduled report'), 'title' => $this->translate->_('Delete scheduled report'),'class' => 'deleteimg')); ?></td>
			</tr>
		<?php }	} ?>
	</table>
</div>
