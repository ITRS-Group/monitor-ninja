<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div id="progress"></div>
<div class="report-page-setup recurring_dt_schedule">
	<div id="response"></div>

	<div id="schedule-tabs-container">
		<ul>
			<li><a href="#create-tab" style="border: 0px"><?php echo $this->translate->_('Create new') ?></a></li>
			<li><a href="#schedules-tab" style="border: 0px"><?php echo $this->translate->_('Schedules') ?></a></li>
		</ul>
		<div id="create-tab">
			<div class="setup-table"><br />
		<?php	if (isset($schedule_id) && !empty($schedule_id)) {
					# show link to create new recurring schedule
					echo '&nbsp'.html::anchor('recurring_downtime/', $this->translate->_('Add New Downtime Schedule')).'<br /><br />';
				}

				echo form::open('recurring_downtime/generate', array('id' => 'setup_form')); ?>
					<input type="hidden" name="new_recurring_setup" value="1" />
					<table summary="Select object type" class="setup-tbl">
						<tr>
							<td colspan="3">
								<select name="report_type" id="report_type" onchange="set_selection(this.value);">
									<option value="hostgroups"><?php echo $label_hostgroups ?></option>
									<option value="hosts"><?php echo $label_hosts ?></option>
									<option value="servicegroups"><?php echo $label_servicegroups ?></option>
									<option value="services"><?php echo $label_services ?></option>
								</select>
								<input type="button" id="sel_report_type" class="button select20" onclick="set_selection(document.forms['report_form'].report_type.value);" value="<?php echo $label_select ?>" />
							</td>
						</tr>
						<tr id="filter_row">
							<td colspan="3">
								<?php echo $this->translate->_('Filter:') ?><br />
								<input type="text" name="filter_field" id="filter_field" autocomplete=off size="10" value="">
								<input type="button" name="clear_filter" id="clear_filter" value="<?php echo $this->translate->_('Clear') ?>">
							</td>
						</tr>
						<tr id="hostgroup_row">
							<td>
								<?php echo $label_available.' '.$label_hostgroups ?><br />
								<select name="hostgroup_tmp[]" id="hostgroup_tmp" multiple="multiple" size='8' class="multiple">
								</select>
							</td>
							<td class="move-buttons">
								<input type="button" value="&gt;" id="mv_hg_r" class="button arrow-right" /><br />
								<input type="button" value="&lt;" id="mv_hg_l" class="button arrow-left" />
							</td>
							<td>
								<?php echo $label_selected.' '.$label_hostgroups ?><br />
								<select name="hostgroup[]" id="hostgroup" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
						</tr>
						<tr id="servicegroup_row">
							<td>
								<?php echo $label_available.' '.$label_servicegroups ?><br />
								<select name="servicegroup_tmp[]" id="servicegroup_tmp" multiple="multiple" size='8' class="multiple">
								</select>
							</td>
							<td class="move-buttons">
								<input type="button" value="&gt;" id="mv_sg_r" class="button arrow-right" /><br />
								<input type="button" value="&lt;" id="mv_sg_l" class="button arrow-left" />
							</td>
							<td>
								<?php echo $label_selected.' '.$label_servicegroups ?><br />
								<select name="servicegroup[]" id="servicegroup" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
						</tr>
						<tr id="host_row_2">
							<td>
								<?php echo $label_available.' '.$label_hosts ?><br />
								<select name="host_tmp[]" id="host_tmp" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
							<td class="move-buttons">
								<input type="button" value="&gt;" id="mv_h_r" class="button arrow-right" /><br />
								<input type="button" value="&lt;" id="mv_h_l" class="button arrow-left" />
							</td>
							<td>
								<?php echo $label_selected.' '.$label_hosts ?><br />
								<select name="host_name[]" id="host_name" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
						</tr>
						<tr id="service_row_2">
							<td>
								<?php echo $label_available.' '.$label_services ?><br />
								<select name="service_tmp[]" id="service_tmp" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
							<td class="move-buttons">
								<input type="button" value="&gt;" id="mv_s_r" class="button arrow-right" /><br />
								<input type="button" value="&lt;" id="mv_s_l" class="button arrow-left"  />
							</td>
							<td>
								<?php echo $label_selected.' '.$label_services ?><br />
								<select name="service_description[]" id="service_description" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
						</tr>
					</table>
				</div>

				<div class="setup-table">
					<table class="setup-tbl" style="width: 785px">
						<tr>
							<td colspan="3">
								<?php echo $label_comment ?> *<br />
								<textarea cols="40" rows="4" name="comment" style="width: 100%"><?php echo $comment ?></textarea>
							</td>
						</tr>
						<tr>
							<td style="width: 100px">
								<?php echo $label_time ?> (hh:mm) *<br />
								<input class="recurrence_input time-picker" type='text' name='time' autocomplete="off" id="time_input" value='<?php echo $time ?>'>
							</td>
							<td>
								<?php echo $label_duration ?> (hh:mm) *<br />
								<input class="recurrence_input time-picker" type='text' id="duration" name='duration' value='<?php echo $duration ?>'>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2">
								<?php echo $label_days_of_week ?><br />
								<table style="margin-top: 5px;width: 560px; border-collapse: collapse; border-spacing: 0px">
									<tr>
										<?php for ($i=0;$i<=6;$i++) {
										$checked = '';
										if (isset($schedule_info['recurring_day']) && in_array($i, $schedule_info['recurring_day'])) {
											$checked = 'checked=checked';
										} ?>

										<td style="width: 80px"><input type="checkbox" <?php echo $checked ?> name="recurring_day[]" value="<?php echo $i ?>" id="<?php echo $day_names[$i];?>"> <label for="<?php echo $day_names[$i];?>"><?php echo $day_names[$i] ?></label></td>
										<?php	} ?>
									</tr>
								</table>
								<br>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<?php echo $label_months ?><br />
								<table style="margin-top: 5px; width: 480px; border-collapse: collapse; border-spacing: 0px">
									<tr>
									<?php 	$i = 0;
								foreach($month_names as $month) {
									$i++;
									$checked = '';
									if (isset($schedule_info['recurring_month']) && in_array($i, $schedule_info['recurring_month'])) {
										$checked = 'checked=checked';
									} ?>
								<td style="width: 80px"><input type="checkbox" <?php echo $checked ?> name="recurring_month[]" value="<?php echo $i ?>" id="<?php echo $month; ?>"> <label for="<?php echo $month; ?>"><?php echo $month ?></label></td>
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
					<input id="reports_submit_button" type="submit" name="" value="<?php echo $label_add_schedule ?>" class="button create-report" />
				</div>
			</form>
		</div>
	<div id="schedules-tab">
<?php
if (isset($saved_info) && !empty($saved_info)) {
	foreach ($downtime_types as $dt_type => $translated) {
		if (!isset($saved_info[$dt_type])) {
			continue;
		} ?>
	<table class="recurrence_table" id="table_<?php echo $dt_type ?>" cellspacing="2">
		<tr>
			<td colspan="8"><h1><?php echo sprintf($this->translate->_('Recurring %s Downtime'), $translated); ?></h1></td>
		</tr>
		<tr>
			<th><?php echo sprintf($this->translate->_('%s Name'), $translated); ?></th>
			<th><?php echo $this->translate->_('Author'); ?></th>
			<th><?php echo $this->translate->_('Comment'); ?></th>
			<th><?php echo $this->translate->_('Time'); ?></th>
			<th><?php echo $this->translate->_('Duration'); ?></th>
			<th><?php echo $this->translate->_('Weekdays'); ?></th>
			<th><?php echo $this->translate->_('Months'); ?></th>
			<th>&nbsp;</th>
		</tr>
	<?php	foreach ($saved_info[$dt_type] as $data) {
				$days = isset($data['data']['recurring_day']) ? $data['data']['recurring_day'] : '';
				$weekdays = '';
				if (!empty($days)) {
					foreach ($days as $day) {
						$weekdays[] = $abbr_day_names[$day];
					}
					$weekdays = implode(', ', $weekdays);
				}

				$months = isset($data['data']['recurring_month']) ? $data['data']['recurring_month'] : '';
				$month_list = '';
				if (!empty($months)) {
					foreach ($months as $m) {
						$month_list[] = $abbr_month_names[$m+1];
					}
					$month_list = implode(', ', $month_list);
				} ?>
		<tr class="scheduled_<?php echo $dt_type ?>" id="schedule_<?php echo $data['id'] ?>">
			<td><?php
				# limit output but enable possibility to view all
				$max_objlist_len = 60;
				$object_list = implode(', ', $data['data'][$objfields[$dt_type]]);
				$object_list_wrap = substr($object_list, 0, $max_objlist_len);
				if (strlen($object_list_wrap) < strlen($object_list)) {
					echo '<span class="show_all_subobjects" title="'.
						$this->translate->_('Click to show/hide all objects defined for this schedule').
						'" id="show_all_objects_'.$data['id'].'"><strong>['.
						$this->translate->_('Show/Hide').']</strong></span><br />';

					echo '<span id="objects_small_'.$data['id'].'">'.$object_list_wrap."... </span>";

					echo '<div id="all_objects_'.$data['id'].'" style="display:none">'.
						implode(', <br />', $data['data'][$objfields[$dt_type]]);
				} else {
					echo $object_list;
				}
				 ?></td>
			<td><?php echo $data['author'] ?></td>
			<td><?php echo nl2br($data['data']['comment']) ?></td>
			<td><?php echo $data['data']['time'] ?></td>
			<td><?php echo $data['data']['duration'] ?></td>
			<td><?php echo $weekdays ?></td>
			<td><?php echo $month_list ?>
			</td>
			<td style="float:right">
					<?php echo html::anchor('recurring_downtime/index/'.$data['id'], '['.$this->translate->_('edit').']') ?>
					<?php echo html::anchor('recurring_downtime/delete/'.$data['id'], '['.$this->translate->_('delete').']',
						array('id' => 'recurring_delete_'.$data['id'], 'class' => 'recurring_delete')) ?>
			</td>
		</tr>
	<?php 	} ?>
	</table><br /><br />
<?php
	}
} else {
	echo $this->translate->_('There are no saved recurring downtime schedules yet.');
}
?>
</div>
</div>
