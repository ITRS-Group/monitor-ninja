<?php $t = $this->translate; ?>
<a href="#options" class="fancybox">
	<?php
		echo html::image($this->add_path('icons/32x32/square-edit.png'),
		array('alt' => $label_edit_settings, 'title' => $label_edit_settings, 'style' => 'position: absolute; right: 0px; top: 10px'))
	?>
</a>


<div id="options">
<?php	echo form::open('trends/generate', array('id' => 'report_form', 'onsubmit' => 'return check_form_values(this);'));
		# @@@FIXME: onsubmit="return check_form_update_values(this);" ?>
			<h1><?php //echo help::render('report_settings_sml') ?> <?php echo $label_settings ?></h1>
			<table summary="Report settings" id="report" style="width: 350px">
				<tr class="none">
					<td>
						<input type="hidden" name="new_report_setup" value="1" />
						<?php //echo help::render('reporting_period');?> <?php echo $label_report_period ?><br />
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
				<tr>
					<td class="none">
						<?php echo form::checkbox(array('name' => 'show_event_duration'), 1, false); ?>
						<label for="show_event_duration"><?php echo $label_show_event_duration ?></label>
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