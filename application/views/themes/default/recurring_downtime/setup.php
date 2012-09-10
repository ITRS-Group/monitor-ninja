<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>


<div class="report-page-setup recurring_dt_schedule">
	<div id="response"></div>

	<div id="schedule-tabs-container">
		<ul>
			<li><a href="#create-tab" style="border: 0px"><?php echo _('Create new') ?></a></li>
			<li><a href="#schedules-tab" style="border: 0px"><?php echo _('Schedules') ?></a></li>
		</ul>
		<div id="create-tab">
			<?php
				echo form::open('recurring_downtime/generate', array('id' => 'setup_form'));
			?>
			<div class="setup-table"><br />
		<?php	if (isset($schedule_id) && !empty($schedule_id)) {
					# show link to create new recurring schedule
					echo '&nbsp'.html::anchor('recurring_downtime/', _('Add New Downtime Schedule')).'<br /><br />';
				}

				if (isset($schedule_id) && !empty($schedule_id)) {?>
				<input type="hidden" name="schedule_id" value="<?php echo $schedule_id ?>" />
				<?php }?>
					<input type="hidden" name="new_recurring_setup" value="1" />
					<table summary="Select object type" class="setup-tbl">

					<?php if (isset($_GET['host'])) { ?>
						<tr>
							<td colspan="3">
								<input type="hidden" name="host_name[]" value="<?php echo $_GET['host'];?>" />
								<?php
									if (isset($_GET['service'])) {
										echo '<h2>'._('Schedule downtime for service').' <strong>'.$_GET['service'].'</strong> on host <strong>'.$_GET['host'].'</strong></h2>';
										echo '<input type="hidden" name="report_type" id="report_type" value="services">';
										echo '<input type="hidden" name="service_description[]" value="'.$_GET['host'].';'.$_GET['service'].'" />';
									}
									else {
										echo '<h2>'._('Schedule downtime for host').' <strong>'.$_GET['host'].'</strong></h2>';
										echo '<input type="hidden" name="report_type" id="report_type" value="hosts">';
									}
								?>
							</td>
						</tr>
						<?php } else { ?>
						<tr>
							<td colspan="3">
								<select name="report_type" id="report_type" onchange="set_selection(this.value);">
									<option value="hostgroups"><?php echo _('Hostgroups') ?></option>
									<option value="hosts"><?php echo _('Hosts') ?></option>
									<option value="servicegroups"><?php echo _('Servicegroups') ?></option>
									<option value="services"><?php echo _('Services') ?></option>
								</select>
								<input type="button" id="sel_report_type" class="button select20" onclick="set_selection(document.forms['report_form'].report_type.value);" value="<?php echo _('Select') ?>" />
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
						<tr id="hostgroup_row">
							<td>
								<?php echo _('Available').' '._('Hostgroups') ?><br />
								<select name="hostgroup_tmp[]" id="hostgroup_tmp" multiple="multiple" size='8' class="multiple">
								</select>
							</td>
							<td class="move-buttons">
								<input type="button" value="&gt;" id="mv_hg_r" class="button arrow-right" /><br />
								<input type="button" value="&lt;" id="mv_hg_l" class="button arrow-left" />
							</td>
							<td>
								<?php echo _('Selected').' '._('Hostgroups') ?><br />
								<select name="hostgroup[]" id="hostgroup" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
						</tr>
						<tr id="servicegroup_row">
							<td>
								<?php echo _('Available').' '._('Servicegroups') ?><br />
								<select name="servicegroup_tmp[]" id="servicegroup_tmp" multiple="multiple" size='8' class="multiple">
								</select>
							</td>
							<td class="move-buttons">
								<input type="button" value="&gt;" id="mv_sg_r" class="button arrow-right" /><br />
								<input type="button" value="&lt;" id="mv_sg_l" class="button arrow-left" />
							</td>
							<td>
								<?php echo _('Selected').' '._('Servicegroups') ?><br />
								<select name="servicegroup[]" id="servicegroup" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
						</tr>
						<tr id="host_row_2">
							<td>
								<?php echo _('Available').' '._('Hosts') ?><br />
								<select name="host_tmp[]" id="host_tmp" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
							<td class="move-buttons">
								<input type="button" value="&gt;" id="mv_h_r" class="button arrow-right" /><br />
								<input type="button" value="&lt;" id="mv_h_l" class="button arrow-left" />
							</td>
							<td>
								<?php echo _('Selected').' '._('Hosts') ?><br />
								<select name="host_name[]" id="host_name" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
						</tr>
						<tr id="service_row_2">
							<td>
								<?php echo _('Available').' '._('Services') ?><br />
								<select name="service_tmp[]" id="service_tmp" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
							<td class="move-buttons">
								<input type="button" value="&gt;" id="mv_s_r" class="button arrow-right" /><br />
								<input type="button" value="&lt;" id="mv_s_l" class="button arrow-left"  />
							</td>
							<td>
								<?php echo _('Selected').' '._('Services') ?><br />
								<select name="service_description[]" id="service_description" multiple="multiple" size="8" class="multiple">
								</select>
							</td>
						</tr>
					</table>
					<?php } ?>
				</div>

				<div class="setup-table">
					<table class="setup-tbl" style="width: 785px">
						<tr>
							<td colspan="3">
								<?php echo _('Comment') ?> <em>*</em><br />
								<textarea cols="40" rows="4" name="comment" style="width: 770px; padding: 5px;"><?php echo $comment ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<input type="checkbox" name="fixed" id="checkbox_fixed" value="1"<?php if ($fixed) { ?> checked=checked<?php } ?>> <?php echo _('Fixed') ?>
							</td>
						</tr>
						<tr id="triggered_row" style="display:none">
							<td colspan="3">
								<?php echo _('Triggered By') ?><br />
								<?php echo form::dropdown('triggered_by', ($current_dt_type == 'host' ? $host_downtime_ids : $svc_downtime_ids), $triggered_by) ?>
							</td>
						</tr>
						<tr>
							<td style="width: 100px">
								<?php echo _('Time') ?> (hh:mm) <em>*</em><br />
								<input class="recurrence_input time-picker" type='text' maxlength="5" name='time' autocomplete="off" id="time_input" value='<?php echo $time ?>'>
							</td>
							<td>
								<?php echo _('Duration') ?> (hh:mm) <em>*</em><br />
								<input class="recurrence_input time-picker" type='text' maxlength="5" id="duration" name='duration' value='<?php echo $duration ?>'>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2">
								<?php echo _('Days of week') ?><br />
								<table style="margin-top: 5px;width: 560px; border-collapse: collapse; border-spacing: 0px">
									<tr>
										<?php foreach ($day_index as $i) {
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
								<?php echo _('Months') ?><br />
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
					<input id="reports_submit_button" type="submit" name="" value="<?php echo $schedule_id ? _('Update schedule') : _('Add Schedule') ?>" class="button create-report" />
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
	<h1><?php echo sprintf(_('Recurring %s Downtime'), $translated); ?></h1>
	<table class="recurrence_table" id="table_<?php echo $dt_type ?>" style="margin-top: -5px">
		<tr>
			<th class="headerNone left"><?php echo sprintf(_('%s Name'), $translated); ?></th>
			<th class="headerNone left"><?php echo _('Author'); ?></th>
			<th class="headerNone left"><?php echo _('Comment'); ?></th>
			<th class="headerNone left"><?php echo _('Time'); ?></th>
			<th class="headerNone left"><?php echo _('Duration'); ?></th>
			<th class="headerNone left"><?php echo _('Type'); ?></th>
			<th class="headerNone left"><?php echo _('Weekdays'); ?></th>
			<th class="headerNone left"><?php echo _('Months'); ?></th>
			<th class="headerNone left" style="width: 40px"><?php echo _('Actions'); ?></th>
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
						$month_list[] = $abbr_month_names[$m-1];
					}
					$month_list = implode(', ', $month_list);
				} ?>
		<tr class="scheduled_<?php echo $dt_type ?> <?php echo $i%2 == 0 ? 'even' : 'odd'?>" id="schedule_<?php echo $data['id'] ?>">
			<td><?php
				# limit output but enable possibility to view all
				$max_objlist_len = 60;
				$object_list = implode(', ', $data['data'][$objfields[$dt_type]]);
				$object_list_wrap = substr($object_list, 0, $max_objlist_len);
				if (strlen($object_list_wrap) < strlen($object_list)) {
					echo '<span class="show_all_subobjects" title="'.
						_('Click to show/hide all objects defined for this schedule').
						'" id="show_all_objects_'.$data['id'].'"><strong>['.
						_('Show/Hide').']</strong></span><br />';

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
			<td><?php 	if (isset($data['data']['fixed'])) {
							echo $data['data']['fixed'] ? _('Fixed') : _('Flexible');
						} else {
							echo _('Fixed');
						}
				?></td>
			<td><?php echo $weekdays ?></td>
			<td><?php echo $month_list ?></td>
			<td style="text-align: center">
				<?php echo html::anchor('recurring_downtime/index/'.$data['id'], html::image($this->add_path('/icons/16x16/edit.png'), array('title' => _('Edit'), 'alt' => '', 'style' => 'margin-bottom: -2px')),array('style' => 'border: 0px')); ?>
				<?php echo html::anchor('recurring_downtime/delete/'.$data['id'], html::image($this->add_path('/icons/16x16/delete-doc.png'), array('title' => _('Delete'), 'alt' => '', 'style' => 'margin-bottom: -2px')), array('id' => 'recurring_delete_'.$data['id'], 'class' => 'recurring_delete', 'style' => 'border: 0px')); ?>
			</td>
		</tr>
	<?php 	} ?>
	</table><br /><br />
<?php
	}
} else {
	echo _('There are no saved recurring downtime schedules yet.');
}
?>
</div>
</div>
