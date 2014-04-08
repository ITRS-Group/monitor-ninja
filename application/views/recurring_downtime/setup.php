<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<div id="recurring_downtime_error">
	<?php
		if (isset($error)) {
			echo _('<strong>ERROR: </strong>' . $error);
		}
	?>
</div>
<form class="report-page-setup" method="POST" action="">
	<div class="report-block">
		<h2><?php echo _('Report Mode'); ?></h2>
		<hr/>
<?php	if (isset($schedule_id) && !empty($schedule_id)) {
			# show link to create new recurring schedule
			echo '&nbsp'.html::anchor('recurring_downtime/', _('Add New Downtime Schedule')).'<br /><br />';
		}

		if (isset($schedule_id) && !empty($schedule_id)) {?>
		<input type="hidden" name="schedule_id" value="<?php echo $schedule_id ?>" />
		<?php }?>
		<table summary="Select object type" class="setup-tbl">

			<tr>
				<td colspan="3">
					<select name="downtime_type" id="downtime_type">
						<option value="hostgroups" <?php echo $schedule_info->get_downtime_type() === 'hostgroups' ? 'selected="selected"' : ''; ?>><?php echo _('Hostgroups') ?></option>
						<option value="hosts" <?php echo $schedule_info->get_downtime_type() === 'hosts' ? 'selected="selected"' : ''; ?>><?php echo _('Hosts') ?></option>
						<option value="servicegroups" <?php echo $schedule_info->get_downtime_type() === 'servicegroups' ? 'selected="selected"' : ''; ?>><?php echo _('Servicegroups') ?></option>
						<option value="services" <?php echo $schedule_info->get_downtime_type() === 'services' ? 'selected="selected"' : ''; ?>><?php echo _('Services') ?></option>
					</select>
					<input type="button" id="sel_downtime_type" class="button select20" value="<?php echo _('Select') ?>" />
					<div id="progress"></div>
					&nbsp;
				</td>
			</tr>
			<tr id="filter_row">
				<td colspan="3">
					<?php echo _('Filter:') ?><br />
					<input type="text" name="filter_field" id="filter_field" autocomplete=off size="10" value="">
					<input type="button" name="clear_filter" id="clear_filter" value="<?php echo _('Clear') ?>">
				</td>
			</tr>
			<tr>
				<td colspan="3">

				<div class="left" style="width: 40%">
					<?php echo _('Available <span class="object-list-type">hostgroups</span>') ?><br />
					<select id="objects_tmp" multiple="multiple" size='8' style="width: 100%;" class="multiple">
					</select>
				</div>
				<div class="left" style="padding-top: 16px;">
					<input type="button" value="&gt;" id="mv_hg_r" class="button arrow-right" /><br />
					<input type="button" value="&lt;" id="mv_hg_l" class="button arrow-left" />
				</div>
				<div class="left" style="width: 40%">
					<?php echo _('Selected <span class="object-list-type">hostgroups</span>') ?><br />
					<select name="objects[]" id="objects" multiple="multiple" size="8" style="width: 100%;" class="multiple">
					</select>
				</div>
				<div class="clear"></div>
				</td>
			</tr>
		</table>
	</div>

	<div class="report-block">
		<h2><?php echo _('Report Settings'); ?></h2>
		<hr />
		<table class="setup-tbl">
			<tr>
				<td colspan="3">
					<?php echo _('Comment') ?> <em>*</em><br />
					<textarea cols="40" rows="4" name="comment" width="100%"><?php echo $schedule_info->get_comment() ?></textarea>
				</td>
			</tr>
			<tr>
				<td style="vertical-align: top;">
					<input type="checkbox" name="fixed" id="fixed" value="1"<?php if ($schedule_info->get_fixed()) { ?> checked=checked<?php } ?>> <?php echo _('Fixed') ?>
				</td>
				<td id="triggered_row" style="display:none">
					<?php echo _('Duration') ?> (hh:mm or hh:mm:ss) <em>*</em><br />
					<input class="time-entry" type='text' id="duration" name='duration' value='<?php echo $schedule_info->get_duration_string() ?>'>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					<?php echo _('Start Time') ?> (hh:mm or hh:mm:ss) <em>*</em><br />
					<input class="time-entry" type='text' name='start_time' id="start_time_input" value='<?php echo $schedule_info->get_start_time_string() ?>'>
				</td>
				<td>
					<?php echo _('End Time') ?> (hh:mm or hh:mm:ss) <em>*</em><br />
					<input class="time-entry" type='text' name='end_time' id="end_time_input" value='<?php echo $schedule_info->get_end_time_string() ?>'>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">
					<?php echo _('Days of week') ?> <em>*</em> <button type="button" id="select-all-days" value="">Select all</button> <button type="button" id="deselect-all-days">Deselect all</button><br />
					<table style="margin-top: 5px;width: 560px; border-collapse: collapse; border-spacing: 0px">
						<tr>
							<?php foreach ($day_index as $i) {
							$checked = '';
							if (in_array($i, $schedule_info->get_weekdays())) {
								$checked = 'checked=checked';
							} ?>

							<td style="width: 80px"><input type="checkbox" <?php echo $checked ?> name="weekdays[]" class="recurring_day" value="<?php echo $i ?>" id="<?php echo $day_names[$i];?>"> <label for="<?php echo $day_names[$i];?>"><?php echo $day_names[$i] ?></label></td>
							<?php	} ?>
						</tr>
					</table>
					<br>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<?php echo _('Months') ?> <em>*</em> <button type="button" id="select-all-months">Select all</button> <button type="button" id="deselect-all-months">Deselect all</button><br />
					<table style="margin-top: 5px; width: 480px; border-collapse: collapse; border-spacing: 0px">
						<tr>
						<?php 	$i = 0;
					foreach($month_names as $month) {
						$i++;
						$checked = '';
						if (in_array($i, $schedule_info->get_months())) {
							$checked = 'checked=checked';
						} ?>
					<td style="width: 80px"><input type="checkbox" <?php echo $checked ?> name="months[]" class="recurring_month" value="<?php echo $i ?>" id="<?php echo $month; ?>"> <label for="<?php echo $month; ?>"><?php echo $month ?></label></td>
					<?php	if ($i == 6) {
								echo "</tr><tr>";
							}
						} ?>
						</tr>
					</table>
					<br>
				</td>
			</tr>
		</table>
	</div>

	<div class="setup-table">
		<input id="reports_submit_button" type="submit" name="" value="<?php echo $schedule_id ? _('Update schedule') : _('Add Schedule') ?>" class="button create-report" />
	</div>
</form>
</div>
