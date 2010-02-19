<?php $t = $this->translate; ?>
<a href="#options" class="fancybox">
	<?php
		echo html::image($this->add_path('icons/32x32/square-edit.png'),
		array('alt' => $label_edit_settings, 'title' => $label_edit_settings, 'style' => 'position: absolute; right: 0px; top: 10px'))
	?>
</a>


<div id="options">
<?php	echo form::open('histogram/generate', array('id' => 'histogram_form')); ?>
			<table summary="Report settings" id="report">
				<tr class="none">
					<td><?php echo form::dropdown(array('name' => 'report_period', 'onchange' => 'show_calendar(this.value);'), $report_periods, $selected); ?></td>
				</tr>
				<tr>
					<td>
						<?php echo $label_statetypes_to_graph ?><br />
						<?php echo form::dropdown('state_types', $statetypes, $selected_state_types) ?>
					</td>
				</tr>
				<tr id="display" style="display: none; clear: both;" class="none">
					<td>
						<?php echo $label_startdate ?> (<span id="start_time_tmp"><?php echo $label_click_calendar ?></span>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $start_time ?>" class="date-pick" title="<?php echo $label_startdate_selector ?>" />
						<input type="hidden" name="start_time" id="start_time" value="<?php echo $start_time ?>" />
						<input type="text" maxlength="5" name="time_start" id="time_start" value="08:00">
						<br /><br /><br />
						<?php echo $label_enddate ?> (<span id="end_time_tmp"><?php echo $label_click_calendar ?></span>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $end_time ?>" class="date-pick" title="<?php echo $label_enddate_selector ?>" />
						<input type="hidden" name="end_time" id="end_time" value="<?php echo $end_time ?>" />
						<input type="text" maxlength="5" name="time_end" id="time_end" value="09:00">
					</td>
				</tr>
				<tr>
					<td>
						<?php echo $label_breakdown ?><br />
						<?php echo form::dropdown('breakdown', $breakdown, $selected_breakdown) ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo $label_events_to_graph ?><br />
						<?php if ($sub_type == 'host') { ?>
							<?php echo form::dropdown('host_states', $hoststates, $selected_host_state);
							} else { ?>
							<?php echo form::dropdown('service_states', $servicestates, $selected_service_state);
							} ?>
						</div>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo form::checkbox('newstatesonly', 1, $selected_newstatesonly); ?>
					<?php echo $label_newstatesonly ?>
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
		</div>
	</form>