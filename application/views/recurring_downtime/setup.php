<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<div id="recurring_downtime_error">
	<?php
		if (isset($error)) {
			echo _('<strong>ERROR: </strong>' . $error);
		}
	?>
</div>
<?php
echo form::open('', array('class' => 'report-page-setup', 'id' => "setup_form"));
?>
	<div class="report-block">
		<h2><?php echo _('Report Mode'); ?></h2>
		<hr/>
<?php	if (isset($schedule_id) && !empty($schedule_id)) {
			# show link to create new recurring schedule
			echo '&nbsp'.html::anchor('recurring_downtime/', _('Add New Downtime Schedule')).'<br /><br />';
			form::hidden('schedule_id', $schedule_id);
		}
?>
		<table class="setup-tbl obj_selector">
			<tr>
				<td colspan="3">
<?php
echo form::dropdown(array('name' => 'downtime_type', 'id' => 'report_type'),
	array(
		'hostgroups' => _('Hostgroups'),
		'hosts' => _('Hosts'),
		'servicegroups' => _('Servicegroups'),
		'services' => _('Services')
	),
	$schedule_info->get_downtime_type()
);
?>
					&nbsp;<br /><br />
				</td>
			</tr>
			<tr>
				<td colspan="3">
<?php

$obj_options = array();
// In PHP 5.3, array_combine requires both arrays to have at least one element... Sigh.
if (count($schedule_info->get_objects()) > 0) {
	$obj_options = array_combine($schedule_info->get_objects(), $schedule_info->get_objects());
}
$trimmed_downtime_type = $schedule_info->get_downtime_type() ? rtrim($schedule_info->get_downtime_type(), 's') : 'hostgroup'; // first from select above
echo form::dropdown(array('data-filterable' => '', 'data-type' => $trimmed_downtime_type, 'name' => 'objects[]', 'id' => 'objects', 'multiple' => 'multiple'),
	$obj_options
);
?>
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
					<?php echo _('Comment') . " *" ?><br />
					<?php
echo form::textarea(
	array('cols' => '40', 'rows' => '4', 'name' => 'comment', 'width' => '100%'),
	$schedule_info->get_comment()
);
?>
				</td>
			</tr>
			<tr>
				<td style="vertical-align: top;">
<?php
echo form::checkbox(array('name' => 'fixed', 'id' => 'fixed'), "1", $schedule_info->get_fixed());
?> <?php echo _('Fixed'); ?>
				</td>
				<td id="triggered_row" style="display:none">
					<?php echo _('Duration') . " *" ?> (hh:mm or hh:mm:ss)<br />
<?php
echo form::input(array('class' => 'time-entry', 'type' => 'text', 'id' => 'duration', 'name' => 'duration'), $schedule_info->get_duration_string());
?>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					<?php echo _('Start Time') . " *" ?> (hh:mm or hh:mm:ss)<br />
<?php
echo form::input(array('class' => 'time-entry', 'type' => 'text', 'id' => 'start_time_input', 'name' => 'start_time'), $schedule_info->get_start_time_string());
?>
				</td>
				<td>
					<?php echo _('End Time') . " *" ?> (hh:mm or hh:mm:ss)<br />
<?php
echo form::input(array('class' => 'time-entry', 'type' => 'text', 'id' => 'end_time_input', 'name' => 'end_time'), $schedule_info->get_end_time_string());
?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">
<?php echo _('Days of week') . " * ";
echo form::button(array('type' => 'button', 'id' => 'select-all-days', 'value' => ''), 'Select all');?> <?php
echo form::button(array('type' => 'button', 'id' => 'deselect-all-days'), 'Deselect all');
?>
<br />
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
<?php echo _('Months') . " * ";
echo form::button(array('type' => 'button', 'id' => 'select-all-months'), 'Select all'); ?> <?php echo form::button(array('type' => 'button', 'id' => 'deselect-all-months'), 'Deselect all'); ?>
					<br />
					<table style="margin-top: 5px; width: 480px; border-collapse: collapse; border-spacing: 0px">
						<tr>
						<?php	$i = 0;
					foreach($month_names as $month) {
						$i++;
?>
						<td style="width: 80px">
						<?php echo form::checkbox(array('name' => 'months[]', 'class' => 'recurring_month', 'id' => $month), $i, in_array($i, $schedule_info->get_months())); ?> <?php echo form::label($month, $month); ?>
</td>
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
<?php
echo form::submit(array('id' => 'reports_submit_button', 'name' => '', 'class' => 'button create-report'), $schedule_id ? _('Update schedule') : _('Add Schedule'));
?>
	</div>
</form>
</div>
