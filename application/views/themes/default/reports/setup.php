<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
$saved_reports_exists = false;
if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
	$saved_reports_exists = true;
}
?>

<div id="report-tabs">
	<ul>
		<li><a href="#report-tab" style="border: 0px"><?php echo _('Reports') ?></a></li>
		<li><a href="#schedule-tab" style="border: 0px"><?php echo _('Schedules') ?></a></li>
	</ul>
	<div id="report-tab">

		<div class="report-page-setup availsla">
			<div id="response"></div>
			<div class="setup-table">
				<h1 id="report_type_label"><?php echo $label_create_new ?></h1>

				<div id="switcher" style="margin-top: -7gpx; padding-bottom: 15px;">
					<a id="switch_report_type" href="" style="border: 0px; float: left; margin-right: 5px">
					<?php
						echo $type == 'avail' ?
						html::image($this->add_path('icons/16x16/sla.png'), array('alt' => _('SLA'), 'title' => _('SLA'), 'ID' => 'switcher_image')) :
						html::image($this->add_path('icons/16x16/availability.png'), array('alt' => _('Availability'), 'title' => _('Availability'), 'ID' => 'switcher_image'));
					?>
					<span id="switch_report_type_txt" style="border-bottom: 1px dotted #777777">
					<?php echo $type == 'avail' ? _('Switch to SLA') :_('Switch to Availability'); ?>
					<?php echo ' '.$label_report; ?>
					</span>
					</a>
				</div><br />

				<?php echo form::open($type.'/index', array('id' => 'saved_report_form', 'style' => 'margin-top: 7px;')); ?>
					<div id="saved_reports_display" style="width: 100%; padding-left: 0px;<?php if (!$saved_reports_exists) { ?>display:none;<?php } ?>">
						<?php echo help::render('saved_reports') ?> <?php echo _('Saved reports') ?><br />
						<select name="report_id" id="report_id">
							<option value=""> - <?php echo _('Select saved report') ?> - </option>
							<?php	$sched_str = "";
							if ($saved_reports_exists) {
								foreach ($saved_reports as $info) {
									$sched_str = in_array($info->id, $scheduled_ids) ? " ( *"._('Scheduled')."* )" : "";
									if (in_array($info->id, $scheduled_ids)) {
										$sched_str = " ( *"._('Scheduled')."* )";
										$title_str = $scheduled_periods[$info->id]." "._('schedule');
									} else {
										$sched_str = "";
										$title_str = "";
									}
									echo '<option title="'.$title_str.'" '.(($options['report_id'] == $info->id) ? 'selected="selected"' : '').
										' value="'.$info->id.'">'.($type == 'avail' ? $info->report_name : $info->sla_name).$sched_str.'</option>'."\n";
								}
							} ?>
						</select>
						<input type="hidden" name="type" value="<?php echo $type ?>" />
						<input type="submit" class="button select" value="<?php echo _('Select') ?>" name="fetch_report" />
						<input type="button" class="button new" value="<?php echo _('New') ?>" name="new_report" title="<?php echo $new_saved_title ?>" id="new_report" />
						<input type="button" class="button delete" value="Delete" name="delete_report" title="<?php echo _('Delete report') ?>" id="delete_report" />
						<span id="autoreport_periods"><?php echo $json_periods ?></span>
						<?php if (isset($is_scheduled) && $is_scheduled) { ?>
						<div id="single_schedules" style="display:inline">
							<span id="is_scheduled" title="<?php echo _('This report has been scheduled. Click the icons below to change settings') ?>">
								<?php echo _('This is a scheduled report') ?>
								<a href="#" id="show_scheduled" class="help">[<?php echo _('edit') ?>]</a>
							</span>
						</div>
						<?php	} ?>
					</div>
				<?php echo form::close();?>
			</div>

			<?php echo form::open($type.'/generate', array('id' => 'report_form')); ?>
				<input type="hidden" name="new_report_setup" value="1" />
				<input type="hidden" name="type" value="<?php echo $type ?>" />
				<table summary="Select report type" class="setup-tbl"><!--id="main_table"-->
					<tr>
						<td colspan="3">
							<?php echo help::render('report-type').' '._('Report type'); ?><br />
							<select name="report_type" id="report_type" onchange="set_selection(this.value);">
								<option value="hostgroups"><?php echo _('Hostgroups') ?></option>
								<option value="hosts"><?php echo _('Hosts') ?></option>
								<option value="servicegroups"><?php echo _('Servicegroups') ?></option>
								<option value="services"><?php echo _('Services') ?></option>
							</select>
							<input type="button" id="sel_report_type" class="button select20" onclick="set_selection(document.forms['report_form'].report_type.value);" value="<?php echo _('Select') ?>" />
							<div id="progress"></div>
						</td>
					</tr>
					<tr id="filter_row">
						<td colspan="3">
							<?php echo help::render('filter').' '._('Filter') ?><br />
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

				<div class="setup-table" id="settings_table">
					<table class="setup-tbl">
						<tr>
							<td><?php echo help::render('reporting_period').' '._('Reporting period') ?></td>
							<td style="width: 18px">&nbsp;</td>
							<td><?php echo help::render('report_time_period').' '._('Report Period') ?></td>
						</tr>
						<tr>
							<td><?php echo form::dropdown(array('name' => 'report_period'), $options->get_alternatives('report_period'), $options['report_period']); ?></td>
							<td>&nbsp;</td>
							<td>
								<select name="rpttimeperiod">
									<option value=""></option>
									<?php echo $reporting_periods ?>
								</select>
							</td>
						</tr>
						<tr id="display" style="display: none; clear: both;">
							<td class="avail_display"<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>><?php echo help::render('start-date').' '._('Start date') ?> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
								<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo isset($start_date) ? $start_date : '' ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
								<input type="hidden" name="start_time" id="start_time"  value="<?php echo isset($start_date) ? $start_date : '' ?>"/>
								<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo isset($start_time) ? $start_time : '08:00' ?>">
							</td>
							<td class="avail_display"<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>>&nbsp;</td>
							<td class="avail_display"<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>><?php echo help::render('end-date').' '._('End date') ?> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
								<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo isset($end_date) ? $end_date : '' ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
								<input type="hidden" name="end_time" id="end_time" value="<?php echo isset($end_date) ? $end_date : '' ?>" />
								<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo isset($end_time) ? $end_time : '09:00' ?>">
							</td>
							<td class="sla_display"<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>>
								<?php echo help::render('start-date').' '._('Start date') ?>
								<table summary="Reporting time" style="margin-left: -4px">
									<tr>
										<td><?php echo _('Start year') ?></td>
										<td><select name="start_year" id="start_year"  style="width: 50px" onchange="js_print_date_ranges(this.value, 'start', 'month');"><option value=""></option></select></td>
										<td><?php echo _('Start month') ?></td>
										<td><select name="start_month" id="start_month" style="width: 50px" onchange="check_custom_months();"><option value=""></option></select></td>
									</tr>
								</table>
							</td>
							<td class="sla_display"<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>>&nbsp;</td>
							<td class="sla_display"<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>><?php echo help::render('end-date').' '._('End date') ?>
								<input type="hidden" name="start_time" id="start_time" value="" />
								<input type="hidden" name="end_time" id="end_time" value="" />
								<table summary="Reporting time" style="margin-left: -4px">
									<tr>
										<td><?php echo _('End year') ?></td>
										<td><select name="end_year" id="end_year" style="width: 50px" onchange="js_print_date_ranges(this.value, 'end', 'month');"><option value=""></option></select></td>
										<td><?php echo _('End month') ?></td>
										<td><select name="end_month" id="end_month" style="width: 50px" onchange="check_custom_months();"><option value=""></option></select></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo help::render('use_average').' '._('SLA calculation method') ?><br />
								<select name='use_average'>
									<option value='0' <?php print $options['use_average']?'':'checked="checked"' ?>><?php echo _('Group availability (SLA)') ?></option>
									<option value='1' <?php print $options['use_average']?'checked="checked"':'' ?>><?php echo _('Average') ?></option>
								</select>
							</td>
							<td>&nbsp;</td>
							<td<?php echo ($type == 'sla') ? ' style="display:none"' : ''?>>
								<?php echo help::render('status_to_display') ?>
								<?php echo _('Status to display'); ?><br>
								<div id="display_host_status">
								<?php
								foreach (Reports_Model::$host_states as $id => $name) {
									if ($name === 'excluded')
										continue;
									echo "<input type=\"checkbox\" name=\"host_filter_status[$id]\" id=\"host_filter_status[$id]\" value=\"1\" ".($options['host_filter_status'][$id]?'checked="checked"':'')." style=\"margin-top: 4px; margin-left: 14px\"> <label for=\"host_filter_status[$id]\">".ucfirst($name)."</label>\n";
								} ?>
								</div>
								<div id="display_service_status">
								<?php
								foreach (Reports_Model::$service_states as $id => $name) {
									if ($name === 'excluded')
										continue;
									echo "<input type=\"checkbox\" name=\"service_filter_status[$id]\" id=\"service_filter_status[$id]\" value=\"1\" ".($options['service_filter_status'][$id]?'checked="checked" ':'')." style=\"margin-top: 4px; margin-left: 14px\"> <label for=\"service_filter_status[$id]\">".ucfirst($name)."</label>\n";
								} ?>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo help::render('scheduled_downtime').' '._('Count scheduled downtime as')?><br />
								<?php echo form::dropdown(array('name' => 'scheduleddowntimeasuptime'), $options->get_alternatives('scheduleddowntimeasuptime'), $options['scheduleddowntimeasuptime']) ?>
							</td>
							<td>&nbsp;</td>
							<td>
								<?php echo help::render('use_alias') ?>
								<input type="checkbox" class="checkbox" value="1" id="use_alias" name="use_alias"
										onchange="toggle_label_weight(this.checked, 'usealias');" <?php print $options['use_alias']?'checked="checked"':'' ?> />
								<label for="use_alias" id="usealias"><?php echo _('Use alias') ?></label>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo help::render('stated_during_downtime') ?>
								<input type="checkbox" class="checkbox" value="1" id="assumestatesduringnotrunning" name="assumestatesduringnotrunning"
										onchange="toggle_label_weight(this.checked, 'assume_progdown');" <?php echo $options['assumestatesduringnotrunning']?'checked="checked"':''; ?> />
								<label for="assumestatesduringnotrunning" id="assume_progdown"><?php echo _('Assume states during program downtime') ?></label>
							</td>
							<td>&nbsp;</td>
							<td style="vertical-align:top">
								<?php echo help::render('includesoftstates') ?>
								<input type="checkbox" class="checkbox" value="1" id="includesoftstates" name="includesoftstates"
										onchange="toggle_label_weight(this.checked, 'include_softstates');" <?php echo $options['includesoftstates']?'checked="checked"':''; ?> />
								<label for="includesoftstates" id="include_softstates"><?php echo _('Include soft states') ?></label>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo help::render('initial_states') ?>
								<input type="checkbox" class="checkbox" value="1" id="assumeinitialstates" name="assumeinitialstates"
										onchange="show_state_options(this.checked);toggle_label_weight(this.checked, 'assume_initial');" <?php print $options['assumeinitialstates']?'checked="checked"':'' ?> />
								<label for="assumeinitialstates" id="assume_initial"><?php echo _('Assume initial states') ?></label>
							</td>
							<td>&nbsp;</td>
							<td style="vertical-align:top">
								<?php echo help::render('cluster_mode') ?>
								<input type="checkbox" class="checkbox" value="1" id="cluster_mode" name="cluster_mode"
										onchange="toggle_label_weight(this.checked, 'cluster_mode');" <?php print $options['cluster_mode']?'checked="checked"':''?> />
								<label for="cluster_mode" id="cluster_mode"><?php echo _('Cluster mode') ?></label>
							</td>
						</tr>
						<tr id="assumed_host_state">
							<td style="padding-top: 10px"><?php echo help::render('first_assumed_host').' '._('First assumed host state') ?></td>
							<td>&nbsp;</td>
							<td style="padding-top: 10px"><?php echo help::render('first_assumed_service').' '._('First assumed service state') ?></td>
						</tr>
						<tr id="assumed_service_state">
							<td>
								<select name="initialassumedhoststate">
								<?php
									foreach($options->get_alternatives('initialassumedhoststate') as $host_state_value => $host_state_txt) {
										$sel = ($host_state_value == $options['initialassumedhoststate'] ? ' selected="selected"':'');
										print '<option value="'.$host_state_value.'"'.$sel.'>'.$host_state_txt.'</option>'."\n";
									}
								 ?>
								</select>
							</td>
							<td>&nbsp;</td>
							<td>
								<select name="initialassumedservicestate">
								<?php
									foreach($options->get_alternatives('initialassumedservicestate') as $service_state_value => $service_state_txt){
										$sel = ($service_state_value == $options['initialassumedservicestate'] ? ' selected="selected"':'');
										print '<option value="'.$service_state_value.'"'.$sel.'>'.$service_state_txt.'</option>'."\n";
									}
								 ?>
								</select>
							</td>
						</tr>
						<?php if (isset($extra_options)) {
							echo $extra_options;
						} ?>
						<tr>
							<td>
								<?php echo help::render('save_report') ?>
								<input type="hidden" name="saved_report_id" value="<?php echo $options['report_id'] ?>" />
								<input type="checkbox" class="checkbox" name="save_report_settings" id="save_report_settings" value="1" onclick="toggle_field_visibility(this.checked, 'report_save_information');toggle_label_weight(this.checked, 'save_report_label')" />
								<label for="save_report_settings" id="save_report_label"><?php echo _('Save report') ?></label>
								<br />
								<span id="report_save_information">
									<input type="text" name="report_name" id="report_name" value="" maxlength="255" />
								</span>
								<input type="hidden" name="old_report_name" value="<?php echo ($type == 'avail' ? $options['report_name'] : $options['sla_name']) ?>" />
							</td>
							<td>&nbsp;</td>
							<td class="avail_display">
								<?php if('avail' == $type) { ?>
								<?php echo help::render('include_trends') ?>
								<input type="checkbox" class="checkbox" value="1" id="include_trends" name="include_trends"
										onchange="toggle_label_weight(this.checked, 'include_trends');" <?php print $options['include_trends']?'checked="checked"':''; ?> />
								<label for="include_trends"><?php echo _('Include trends graph') ?></label>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td id="csv_cell" style="vertical-align: top">
								<?php echo help::render('csv_format') ?>
								<input type="checkbox" class="checkbox" value="1" id="csvoutput" name="csvoutput"
										onchange="toggle_label_weight(this.checked, 'csvout');" <?php print $options['csv_output']?'checked="checked"':''; ?> />
								<label for="csvoutput" id="csvout"><?php echo _('Output in CSV format') ?></label>
							</td>
							<td></td>
							<td></td>
						</tr>
					</table>
				</div>
				<br />
				<div class="setup-table<?php if ($type != 'sla') { ?> hidden<?php } ?>" id="enter_sla">
					<table style="width: 810px">
						<tr class="sla_values" <?php if (!$saved_reports_exists) { ?>style="display:none"<?php } ?>>
							<td style="padding-left: 0px" colspan="12"><?php echo help::render('use-sla-values'); ?> <?php echo _('Use SLA-values from saved report') ?></td>
						</tr>
						<tr class="sla_values" <?php if (!$saved_reports_exists) { ?>style="display:none"<?php } ?>>
							<td style="padding-left: 0px" colspan="12">
								<select name="sla_report_id" id="sla_report_id" onchange="get_sla_values()">
									<option value=""> - <?php echo _('Select saved report') ?> - </option>
									<?php	$sched_str = "";
									foreach ($saved_reports as $info) {
										$sched_str = in_array($info->id, $scheduled_ids) ? " ( *"._('Scheduled')."* )" : "";
										if (in_array($info->id, $scheduled_ids)) {
											$sched_str = " ( *"._('Scheduled')."* )";
											$title_str = $scheduled_periods[$info->id]." "._('schedule');
										} else {
											$sched_str = "";
											$title_str = "";
										}
										echo '<option title="'.$title_str.'" '.(($options['report_id'] == $info->id) ? 'selected="selected"' : '').
											' value="'.$info->id.'">'.($type == 'avail' ? $info->report_name : $info->sla_name).$sched_str.'</option>'."\n";
									}  ?>
								</select>
							</td>
						</tr>
						<tr>
							<td style="padding-left: 0px" colspan="12"><?php echo help::render('enter-sla').' '._('Enter SLA') ?></td>
						</tr>
						<tr>
							<?php foreach ($months as $key => $month) { ?>
							<td style="padding-left: 0px">
								<?php echo html::image($this->add_path('icons/16x16/copy.png'),
									array(
										'id' => 'month_'.($key+1),
										'alt' => _('Click to propagate this value to all months'),
										'title' => _('Click to propagate this value to all months'),
										'style' => 'cursor: pointer; margin-bottom: -4px',
										'class' => 'autofill')
									) ?>
								<?php echo $month ?><br />
								<input type="text" size="2" class="sla_month" id="sla_month_<?php echo ($key+1) ?>" name="month_<?php echo ($key+1) ?>" value="<?php echo arr::search($options, 'month_'.($key + 1), '') ?>" maxlength="6" /> %
							</td>
							<?php	} ?>
						</tr>
					</table>
				</div>

				<div class="setup-table">
					<input id="reports_submit_button" type="submit" name="" value="<?php echo _('Create report') ?>" class="button create-report" />
				</div>
			</form>
		</div>
	</div>
	<div id="schedule-tab"><?php echo (isset($available_schedules) && !empty($available_schedules) ) ? $available_schedules : _('There are no scheduled reports'); ?></div>
</div>
