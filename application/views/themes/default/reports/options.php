	<a id="optiontoggle" href="#">show/hide options</a>
	<div id="options" style="margin-left: 0px">
<?php	echo form::open('reports/generate', array('id' => 'report_form'));
		# @@@FIXME: onsubmit="return check_form_update_values(this);" ?>
		<div>
			<input type='hidden' name='new_report_setup' value='1' />
			<h1 id="report-h1" onclick="show_hide('report',this)"  style="width: 772px;"><?php echo $label_settings ?></h1>
			<span><?php echo help::render('report_settings_sml') ?></span>
			<table summary="Report settings" id="report" style="width: 795px">
				<tr>
					<td><?php echo help::render('reporting_period') ?></td>
					<td><?php echo $label_report_period ?></td>
					<td><?php echo form::dropdown(array('name' => 'report_period', 'style' => 'width: 140px'), $report_periods, $selected); ?></td>
				</tr>
				<tr id="display" style="display: none; clear: both;" class="even">
					<td>&nbsp;</td>
					<td colspan="2">
						<table summary="Reporting time" style="margin-left: -4px">
							<tr>
								<td style="width: 279px;"><?php echo $label_startdate ?></td>
								<td>
									<span style="width: 115px; margin-left: -4px" id="start_time_tmp"><?php echo $label_click_calendar ?></span>
									<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $start_time ?>" class="date-pick" title="<?php echo $label_startdate_selector ?>" />
									<input type="hidden" name="start_time" id="start_time" value="<?php echo $start_time ?>"/>
								</td>
							</tr>
							<tr>
								<td><?php echo $label_enddate ?></td>
								<td>
									<span style="width: 115px; margin-left: -4px" id="end_time_tmp"><?php echo $label_click_calendar ?></span>
									<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $end_time ?>" class="date-pick" title="<?php echo $label_enddate_selector ?>" />
									<input type="hidden" name="end_time" id="end_time" value="<?php echo $end_time ?>" />
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<td>
					<input type="checkbox" value="1" class="checkbox" id="assume" name="assumeinitialstates" onchange="edit_state_options(this.checked);toggle_label_weight(this.checked, 'assume_initial');" style="margin-top: 3px; float: left" />
					<label for="assume" id="assume_initial" style="margin: 2px 5px; float: left"><?php echo $label_assumeinitialstates ?></label>
				</td>
				<td colspan="2" style="padding: 0px">
					<table style="width: 100%" id="state_options" >
						<tr>
							<td style="width: 50%">
								<?php echo $label_initialassumedhoststate ?> &nbsp;
								<?php echo form::dropdown(array('name' => 'initialassumedhoststate', 'class' => 'select-initial', 'style' => 'width: auto'),
									$initial_assumed_host_states, $selected_initial_assumed_host_state); ?></td>
							</td>
							<td style="width: 50%"><?php echo $label_initialassumedservicestate ?> &nbsp;
								<?php echo form::dropdown(array('name' => 'initialassumedservicestate', 'class' => 'select-initial', 'style' => 'width: auto'),
									$initial_assumed_service_states, $selected_initial_assumed_service_state); ?></td>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td><input type="checkbox" value="1" class="checkbox" id="count" name="scheduleddowntimeasuptime" onchange="toggle_label_weight(this.checked, 'sched_downt')" /> <label for="count" id="sched_downt"><?php echo $label_scheduleddowntimeasuptime ?></label></td>
					<td style="width:256px; padding-left: 0px">
						<div class="save-report">
						<input type="checkbox" class="checkbox" name="save_report_settings" id="save_report_settings" value="1" onclick="toggle_field_visibility(this.checked, 'report_save_information');toggle_label_weight(this.checked, 'save_report_label')" />
						<label for="save_report_settings" id="save_report_label"><?php echo $label_save_report ?></label>
						</div>
						<div id="report_setup" style="float: left; margin-top: -2px">
							<input type="hidden" name="saved_report_id" value="<?php echo $report_id ?>" />
							<input type="hidden" name="old_report_name" value="<?php echo !empty($report_info) ? $report_info['report_name'] : '' ?>" />
							<span id="report_save_information">
								&nbsp;<strong><?php echo $label_as ?></strong>: <input type="text" name="report_name" id="report_name" class="input-save-name" value="<?php echo !empty($report_info) ? $report_info['report_name'] : '' ?>" maxlength="255" style="width: 123px" />
							</span>
						</div>
					</td>
				<td style="width: 270px; text-align: right">
						<?php if ($report_id) { ?>
							<span id="view_add_schedule">
								<input type="button" id="new_schedule_btn" alt="#TB_inline?height=350&width=350&inlineId=new_schedule_form_area" class="button new-schedule20 thickbox" value="<?php echo $label_new_schedule ?>" /><?php
									if (!empty($scheduled_info)) { ?>
								<input type="button" id="show_schedule" alt="#TB_inline?height=500&width=550&inlineId=schedule_report" class="button view-schedules20 thickbox" value="<?php echo $label_view_schedule ?>" />
							<?php } ?>
							</span>
						<?php } else { ?>
						<em><?php echo $label_save_to_schedule ?></em> &nbsp;
						<?php } ?>
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
		<h2><?php echo $label_new_schedule ?></h2>
		<table id="new_schedule_report_table" class="white-table">
			<?php if (!empty($available_schedule_periods)) { ?>
			<tr>
				<td><?php echo $label_interval ?>:</td>
			</tr>
			<tr>
				<td>
					<select name="period" id="period">
					<?php	foreach ($available_schedule_periods as $period) { ?>
					<option value="<?php echo $period['id'] ?>"><?php echo $period['periodname'] ?></option>
					<?php	} ?>
					</select>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td><?php echo $label_recipients ?><br /><input type="text" class="schedule" name="recipients" id="recipients" value="" /></td>
			</tr>
			<tr>
				<td><?php echo $label_filename ?><br /><input type="text" class="schedule" name="filename" id="filename" value="" /></td>
			</tr>
			<tr>
				<td><?php echo $label_description ?><br /><textarea cols="31" rows="3" id="description" name="description"></textarea></td>
			</tr>
			<tr>
				<td id="scheduled_btn_ctrl">
					<input type="submit" class="button save" name="sched_subm" id="sched_subm" value="<?php echo $label_save ?>" />
					<input type="reset" class="button clear" name="reset_frm" id="reset_frm" value="<?php echo $label_clear ?>" />
				</td>
			</tr>
		</table>
		<div><input type="hidden" name="saved_report_id" id="saved_report_id" value="<?php echo $report_id ?>" />
		<input type="hidden" name="rep_type" value="<?php echo $rep_type ?>" />
		<input type="hidden" name="module_save" id="module_save" value="1" /></div>
		</form>
	</div>
	<div id="schedule_report">
		<strong><?php echo $lable_schedules ?> (<?php echo $report_info['report_name'] ?>): </strong>
		<table id="schedule_report_table" class="white-table">
				<tr id="schedule_header">
					<th><?php echo $label_interval ?></th>
					<th><?php echo $label_recipients ?></th>
					<th><?php echo $label_filename ?></th>
					<th colspan="2"><?php echo $label_description ?></th>
				</tr>
			<?php if (!empty($scheduled_info)) { ?>
			<?php
				foreach ($scheduled_info as $schedule) {
					$recipients = str_replace(' ', '', $schedule->recipients);
					$recipients = str_replace(',', ', ', $recipients); ?>
			<tr id="report-<?php echo $schedule->id ?>">
				<td class="period_select" title="<?php echo $label_dblclick ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
				<td class="iseditable" title="<?php echo $label_dblclick ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
				<td class="iseditable" title="<?php echo $label_dblclick ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
				<td class="iseditable_txtarea" title="<?php echo $label_dblclick ?>" id="description-<?php echo $schedule->id ?>"><?php echo utf8_decode($schedule->description) ?></td>
				<td class="delete_report" id="<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/12x12/cross.gif')); ?></td>
			</tr>
	<?php 		}
			} ?>
		</table>
	</div>
</div>