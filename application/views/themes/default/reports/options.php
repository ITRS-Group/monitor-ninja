<a href="#options" class="fancybox"><?php echo $label_edit_settings ?></a>&nbsp;
<?php if ($report_id) { ?>
<span id="view_add_schedule">
	<a id="new_schedule_btn" href="#new_schedule_form_area" class="fancybox">Add <?php echo strtolower($label_new_schedule) ?></a>&nbsp;
	<?php if (!empty($scheduled_info)) { ?>
	<a id="show_schedule" href="#schedule_report" class="fancybox"><?php echo $label_view_schedule ?></a>
<?php } ?>
</span>
<?php } else {
echo '<em>'.$label_save_to_schedule.'</em>';
}
?>

<div id="options">
<?php	echo form::open('reports/generate', array('id' => 'report_form'));
		# @@@FIXME: onsubmit="return check_form_update_values(this);" ?>
			<h1><?php //echo help::render('report_settings_sml') ?> <?php echo $label_settings ?></h1>
			<table summary="Report settings" id="report" style="width: 350px">
				<tr class="none">
					<td>
						<input type="hidden" name="new_report_setup" value="1" />
						<?php //echo help::render('reporting_period');?> <?php echo $label_report_period ?><br />
						<?php echo form::dropdown(array('name' => 'report_period'), $report_periods, $selected); ?>
					</td>
				</tr>
				<tr id="display" style="display: none; clear: both;" class="none">
					<td>
						<?php echo $label_startdate ?> (<span id="start_time_tmp"><?php echo $label_click_calendar ?></span>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $start_time ?>" class="date-pick" title="<?php echo $label_startdate_selector ?>" />
						<input type="hidden" name="start_time" id="start_time" value="<?php echo $start_time ?>" />
						<br /><br /><br />
						<?php echo $label_enddate ?> (<span id="end_time_tmp"><?php echo $label_click_calendar ?></span>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $end_time ?>" class="date-pick" title="<?php echo $label_enddate_selector ?>" />
						<input type="hidden" name="end_time" id="end_time" value="<?php echo $end_time ?>" />
					</td>
				</tr>
				<tr class="none">
					<td>
						<?php //echo help::render('schedule_downtime'); ?>
						<input type="checkbox" value="1" class="checkbox" id="count" name="scheduleddowntimeasuptime" onchange="toggle_label_weight(this.checked, 'sched_downt')" />
						<label for="count" id="sched_downt"><?php echo $label_scheduleddowntimeasuptime ?></label></td>
				</tr>
				<tr class="none">
					<td>
						<?php //echo he	lp::render('assume_initial_states'); ?>
						<input type="checkbox" value="1" class="checkbox" id="assume" name="assumeinitialstates" onchange="edit_state_options(this.checked);toggle_label_weight(this.checked, 'assume_initial');" />
						<label for="assume" id="assume_initial"><?php echo $label_assumeinitialstates ?></label>
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
								<input type="text" name="report_name" id="report_name" class="input-save-name" value="<?php echo !empty($report_info) ? (!empty($report_info['report_name']) ?
									$report_info['report_name'] : $report_info['sla_name']) : '' ?>" maxlength="255" />
							</span>
							<input type="hidden" name="saved_report_id" value="<?php echo $report_id ?>" />
							<input type="hidden" name="old_report_name" value="<?php echo !empty($report_info) ? (!empty($report_info['report_name']) ?
									$report_info['report_name'] : $report_info['sla_name']) : '' ?>" />
						</div>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="submit" name="s1" value="<?php echo $label_update ?>" class="button update-report20" id="options_submit" />
					</td>
				</tr>
			</table>


		<?php	if (is_array($html_options))
				foreach ($html_options as $html_option)
					echo form::hidden($html_option[1], $html_option[2]); ?>
			<input type="hidden" name="report_id" value="<?php echo isset($report_id) ? $report_id : 0 ?>" />
		</div>
	</form>
	<span id="autoreport_periods"><?php echo $json_periods ?></span>
	<div id="new_schedule_form_area">
	<?php	echo form::open('reports/schedule', array('id' => 'schedule_report_form'));
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
				<caption><?php echo $lable_schedules ?> (<?php echo !empty($report_info) ? (!empty($report_info['report_name']) ?
									$report_info['report_name'] : $report_info['sla_name']) : '' ?>)</caption>
				<tr id="schedule_header">
					<th class="headerNone left"><?php echo $label_interval ?></th>
					<th class="headerNone left"><?php echo $label_recipients ?></th>
					<th class="headerNone left"><?php echo $label_filename ?></th>
					<th class="headerNone left" colspan="2"><?php echo $label_description ?></th>
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
				<td class="delete_report" id="<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/12x12/cross.gif')); ?></td>
			</tr>
		<?php }	} ?>
	</table>
</div>