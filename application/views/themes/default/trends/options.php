<a href="#options" class="fancybox">
	<?php
		echo html::image($this->add_path('icons/32x32/square-edit.png'),
		array('alt' => _('edit settings'), 'title' => _('edit settings'), 'style' => 'position: absolute; right: 1%; top: 10px'))
	?>
</a>


<div style="display:none">
<div id="options">
<?php	echo form::open('trends/generate', array('id' => 'report_form', 'onsubmit' => 'return check_form_values(this);')); ?>
			<h1> <?php echo _('Report settings') ?></h1>
			<table summary="Report settings" id="report" style="width: 350px">
				<tr class="none">
					<td>
						<input type="hidden" name="new_report_setup" value="1" />
						<?php //echo help::render('reporting_period');?> <?php echo _('Reporting period') ?><br />
						<?php echo form::dropdown(array('name' => 'report_period', 'onchange' => 'show_calendar(this.value);'), $report_periods, $selected); ?>
					</td>
				</tr>
				<tr id="display" style="display: none; clear: both;" class="none fancydisplay">
					<td>
						<?php echo _('Start date') ?> (<span id="start_time_tmp"><?php echo _('Click calendar to select date') ?></span>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $start_date ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
						<input type="hidden" name="start_time" id="start_time" value="<?php echo $start_date ?>" />
						<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $start_time ?>">
						<br />
						<?php echo _('End date') ?> (<span id="end_time_tmp"><?php echo _('Click calendar to select date') ?></span>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $end_date ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
						<input type="hidden" name="end_time" id="end_time" value="<?php echo $end_date ?>" />
						<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $end_time ?>">
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="checkbox" value="1" class="checkbox" id="count" name="assumestatesduringnotrunning" onchange="toggle_label_weight(this.checked, 'assume_statesnotrunning')" />
						<label for="count" id="assume_statesnotrunning"><?php echo _('Assume states during program downtime') ?></label></td>
				</tr>
				<tr class="none">
					<td>
						<?php //echo he	lp::render('assume_initial_states'); ?>
						<input type="checkbox" value="1" class="checkbox" id="assume" name="assumeinitialstates" onchange="edit_state_options(this.checked);toggle_label_weight(this.checked, 'assume_initial');" />
						<label for="assume" id="assume_initial"><?php echo _('Assume initial states') ?></label>
					</td>
				</tr>
				<tr id="state_options" class="none">
					<td>
						<?php echo _('First assumed host state') ?><br />
						<?php echo form::dropdown(array('name' => 'initialassumedhoststate', 'class' => 'select-initial'),
							$initial_assumed_host_states, $selected_initial_assumed_host_state); ?>
							<br />
						<?php echo _('First assumed service state') ?> <br />
						<?php echo form::dropdown(array('name' => 'initialassumedservicestate', 'class' => 'select-initial'),
						$initial_assumed_service_states, $selected_initial_assumed_service_state); ?>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="submit" name="s1" value="<?php echo _('Update report') ?>" class="button update-report20" id="options_submit" />
					</td>
				</tr>
			</table>


		<?php	if (is_array($html_options))
				foreach ($html_options as $html_option)
					echo form::hidden($html_option[1], $html_option[2]); ?>
			<input type="hidden" name="report_id" value="<?php echo isset($report_id) ? $report_id : 0 ?>" />
		</div>
	</form>
</div>
