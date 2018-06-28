<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>

<?php if ($error) { ?>
<div class='alert error'>
	<?php echo html::specialchars($error); ?>
</div>
<?php } ?>

<?php echo form::open(null, array(
	'method' => 'post',
	'class' => 'report-page-setup recurring-downtime-form',
	'id' => 'setup_form'
)); ?>

	<div class="report-block">
		<?php 
		if (isset($schedule_id) && !empty($schedule_id)) {
			# show link to create new recurring schedule
			echo '&nbsp'.html::anchor('recurring_downtime/', _('Add New Downtime Schedule')).'<br /><br />';
			echo form::hidden('schedule_id', $schedule_id);
		}
		?>
		<table class="setup-tbl obj_selector rec-table">
			<tr>
				<td class="label"><?php echo _('Object type'); ?></td>
				<td>
					<?php
					echo form::dropdown(array('name' => 'downtime_type', 'id' => 'report_type', 'class' => 'obj-type'),
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
				<td class="label"><?php echo _('Objects *'); ?></td>
				<td>
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
			<tr>
				<td colspan="2"><hr /></td>
			</tr>
			<tr>
				<td class="label sub-heading"><?php echo _('First scheduled downtime'); ?></td>
			</tr>
			<tr>
				<td class="label" colspan="2" >
					<input name="fixed" id="fixed" type="checkbox" value=0>
					<?php echo _('Flexible starttime'); ?>
				</td>
				<tr>
					<td colspan="2" id="flexible-help-text" class="hide">
						<div class="help-text">With flexible starttime, the downtime will start as soon as the service gets a problem state within the defined start time span. The downtime will last as long as the entered time of duration. E.g  if the downtime duration is set to 4 hours with start between 8:00-10:00, and the downtime starts at 9:00, it will continue untill 13:00
						</div>
					</td>
				</tr>
				<tr id="rec-flexible-part" class="hide">
					<td class="label"><?php echo _('Downtime duration *'); ?></td>
					<td> 
						<input id="duration-days" type="number" class="num " min=0 value="0" name="duration[]"> days 
						<input id="duration-hours" type="number" class="num " min=0 max=23 value="0" name="duration[]"> hours 
						<input id="duration-minutes" type="number" class="num " min=0 max=59 value="0" name="duration[]"> minutes
					</td>
				</tr>
				<tr id="rec-fixed-part">
					<td class="label"><?php echo _('Downtime duration'); ?></td>
					<td>
						<div style="position:relative">
							<input id="fixed-duration-start-time" class="time fixed-duration-part" name="start_time">
							<input id="fixed-duration-start-date" class="date-picker date fixed-duration-part" name="start_date">
							&#8212; 
							<input id="fixed-duration-end-time" class="time fixed-duration-part" name="end_time"> 
							<input id="fixed-duration-end-date" class="date-picker date fixed-duration-part" name="end_date">

							<input class="hide" name="weekdays[]"><input class="hide" name="months[]">

							<span class="duration-text" id="fixed-duration-text"> </span>

							<div id="starttime-options" class="starttime-quickselect quickselect hide"></div>

							<div id="endtime-options" class="endtime-quickselect quickselect hide"></div>

						</div>
					</td>
				</tr>
			 	<tr>
			 		<td class="label sub-heading"><?php echo _('Recurrence pattern'); ?></td>
			 	</tr>

				<tr>
					<td class="label"><?php echo _('Recurrence'); ?></td>
					<td><?php
					echo form::dropdown(array('name' => 'recurrence_select', 'id' => 'recurrence', 'class' => 'occur'),
						array(
							'no' => _('Choose recurrence')
						),
						0
					);
					?></td>
				</tr>
				<tr class="recurrence hide">
					<td class="label"><?php echo _('Repeat every'); ?></td>
					<td><input type="number" class="num" min=1 value="1" name="recurrence_no">
						<select class="repeat-text" name="recurrence_text">
							<option value="day">Day</option>
							<option value="week">Week</option>
							<option value="month">Month</option>
							<option value="year">Year</option>
						</select>
					</td>
				</tr>
				<tr class="recurrence-on hide">
					<td class="label"><?php echo _('On'); ?> </td>
					<td>
						<div id="recurrence-on-week" class="hide">
						</div>
						<div id="recurrence-on-month" class="hide">
						</div>
						<div id="recurrence-on-year" class="hide">
						</div>
					</td>
				</tr>
				<tr class="recurrence hide">
					<td class="label"><?php echo _('Ends *'); ?> </td>
					<td>
						<div><input type="radio" name="ends" value="never"> Never</div>
						<div><input type="radio" name="ends" checked="checked" value="finite_ends"> On  <input name="finite_ends_value" class="date-picker date" id="endson-date"></div>
					</td>
				</tr>
				<tr>
					<td colspan="2" id="schedule-notification" class="hide">
						<div class="note">
							<div class="note-warning"></div>
							<div id="duration-note"></div>
							<div id="recur-note"></div>
						</div>
					</td>
				</tr>
				<tr><td colspan="2"><hr /></td></tr>
				<tr>
					<td class="label"><?php echo _('Comments *'); ?></td>
					<td>
						<?php
						echo form::textarea(
							array('cols' => '40', 'rows' => '4', 'name' => 'comment', 'width' => '100%'),
							$schedule_info->get_comment()
						);
						?>
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