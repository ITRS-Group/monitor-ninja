<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}

$report_name = false;
if (isset($report_info)) {
	if (isset($report_info['report_name'])) {
		$report_name = $report_info['report_name'];
	} elseif (isset($report_info['sla_name'])) {
		$report_name = $report_info['sla_name'];
	}
}
?>

<div id="progress"></div>
<div id="report-tabs">
	<ul>
		<li><a href="#report-tab" style="border: 0px"><?php echo $this->translate->_('Reports') ?></a></li>
		<li><a href="#schedule-tab" style="border: 0px"><?php echo $this->translate->_('Schedules') ?></a></li>
	</ul>
	<div id="report-tab">

<div class="report-page-setup">
	<div id="response"></div>
	<div class="setup-table">

		<div class="setup-table">
			<a id="old_avail_link" style="position: absolute; right: 1%; top: 54px;border: 0px; margin-left: 4px;<?php if ($type=='sla') {?>display:none<?php } ?>" href="<?php echo $old_avail_link ?>" target="_blank"><?php echo html::image($this->add_path('/icons/32x32/old-availability.png'),array('alt' => $this->translate->_('Old availability'), 'title' => $this->translate->_('Old availability'))); ?></a>
			<h1 id="report_type_label"><?php echo $label_create_new ?></h1>

			<div id="switcher" style="margin-top: -7gpx; padding-bottom: 15px;">
				<a id="switch_report_type" href="" style="border: 0px; float: left; margin-right: 5px">
				<?php
					echo $type == 'avail' ?
					html::image($this->add_path('icons/16x16/sla.png'), array('alt' => $label_sla, 'title' => $label_sla, 'ID' => 'switcher_image')) :
					html::image($this->add_path('icons/16x16/availability.png'), array('alt' => $label_avail, 'title' => $label_avail, 'ID' => 'switcher_image'));
				?>
				<span id="switch_report_type_txt" style="border-bottom: 1px dotted #777777">
				<?php echo $type == 'avail' ? $label_switch_to.' '.$label_sla :$label_switch_to.' '.$label_avail; ?>
				<?php echo ' '.$label_report; ?>
				</span>
				</a>
			</div><br />

			<?php if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
			echo form::open('reports/index', array('id' => 'saved_report_form', 'style' => 'margin-top: 7px;'));
		 ?>
			<div style="width: 100%; padding-left: 0px">
				<!--	onchange="check_and_submit(this.form)"	-->
				<?php echo help::render('saved_reports') ?> <?php echo $label_saved_reports ?><br />
				<select name="report_id" id="report_id">
					<option value=""> - <?php echo $this->translate->_('Select saved report') ?> - </option>
					<?php	$sched_str = "";
					foreach ($saved_reports as $info) {
						$sched_str = in_array($info->id, $scheduled_ids) ? " ( *".$scheduled_label."* )" : "";
						if (in_array($info->id, $scheduled_ids)) {
							$sched_str = " ( *".$scheduled_label."* )";
							$title_str = $scheduled_periods[$info->id]." ".$title_label;
						} else {
							$sched_str = "";
							$title_str = "";
						}
						echo '<option title="'.$title_str.'" '.(($report_id == $info->id) ? 'selected="selected"' : '').
							' value="'.$info->id.'">'.($type == 'avail' ? $info->report_name : $info->sla_name).$sched_str.'</option>'."\n";
					}  ?>
				</select>
				<input type="hidden" name="type" value="<?php echo $type ?>" />
				<input type="submit" class="button select" value="<?php echo $label_select ?>" name="fetch_report" />
				<input type="button" class="button new" value="<?php echo $label_new ?>" name="new_report" title="<?php echo $new_saved_title ?>" id="new_report" />
				<input type="button" class="button delete" value="Delete" name="delete_report" title="<?php echo $label_delete ?>" id="delete_report" />
				<span id="autoreport_periods"><?php echo $json_periods ?></span>
				<?php if (isset($is_scheduled) && $is_scheduled) { ?>
				<div id="single_schedules" style="display:inline">
					<span id="is_scheduled" title="<?php echo $is_scheduled_clickstr ?>">
						<?php echo $is_scheduled_report ?>
						<a href="#" id="show_scheduled" class="help">[<?php echo $edit_str ?>]</a>
					</span>
				</div>
			<?php	} ?>
		</div>
		<?php echo form::close(); } ?>
	</div>

	<!--<h1><?php echo $label_create_new ?></h1>-->


	<?php	echo form::open('reports/generate', array('id' => 'report_form')); ?>
			<input type="hidden" name="new_report_setup" value="1" />
			<input type="hidden" name="type" value="<?php echo $type ?>" />
			<table summary="Select report type" class="setup-tbl"><!--id="main_table"-->
				<tr>
					<td colspan="3">
						<?php echo help::render('report-type').' '.$this->translate->_('Report type'); ?><br />
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

		<div class="setup-table" id="settings_table">
			<table class="setup-tbl">
				<tr>
					<td><?php echo help::render('reporting_period').' '.$label_report_period ?></td>
					<td style="width: 18px">&nbsp;</td>
					<td><?php echo help::render('report_time_period').' '.$label_rpttimeperiod ?></td>
				</tr>
				<tr>
					<td><?php echo form::dropdown(array('name' => 'report_period'), $report_periods, $selected); ?></td>
					<td>&nbsp;</td>
					<td>
						<select name="rpttimeperiod">
							<option value=""></option>
							<?php echo $reporting_periods ?>
						</select>
					</td>
				</tr>
				<tr id="display" style="display: none; clear: both;">
					<td class="avail_display"<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>><?php echo help::render('start-date').' '.$label_startdate ?> (<em id="start_time_tmp"><?php echo $label_click_calendar ?></em>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo isset($start_date) ? $start_date : '' ?>" class="date-pick datepick-start" title="<?php echo $label_startdate_selector ?>" />
						<input type="hidden" name="start_time" id="start_time"  value="<?php echo isset($start_date) ? $start_date : '' ?>"/>
						<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo isset($start_time) ? $start_time : '08:00' ?>">
					</td>
					<td class="avail_display"<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>>&nbsp;</td>
					<td class="avail_display"<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>><?php echo help::render('end-date').' '.$label_enddate ?> (<em id="end_time_tmp"><?php echo $label_click_calendar ?></em>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo isset($end_date) ? $end_date : '' ?>" class="date-pick datepick-end" title="<?php echo $label_enddate_selector ?>" />
						<input type="hidden" name="end_time" id="end_time" value="<?php echo isset($end_date) ? $end_date : '' ?>" />
						<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo isset($end_time) ? $end_time : '09:00' ?>">
					</td>
					<td class="sla_display"<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>>
						<?php echo help::render('start-date').' '.$label_startdate ?>
						<table summary="Reporting time" style="margin-left: -4px">
							<tr>
								<td><?php echo $label_start_year ?></td>
								<td><select name="start_year" id="start_year"  style="width: 50px" onchange="js_print_date_ranges(this.value, 'start', 'month');"><option value=""></option></select></td>
								<td><?php echo $label_start_month ?></td>
								<td><select name="start_month" id="start_month" style="width: 50px" onchange="check_custom_months();"><option value=""></option></select></td>
							</tr>
						</table>
					</td>
					<td class="sla_display"<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>>&nbsp;</td>
					<td class="sla_display"<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>><?php echo help::render('end-date').' '.$label_enddate ?>
						<input type="hidden" name="start_time" id="start_time" value="" />
						<input type="hidden" name="end_time" id="end_time" value="" />
						<table summary="Reporting time" style="margin-left: -4px">
							<tr>
								<td><?php echo $label_end_year ?></td>
								<td><select name="end_year" id="end_year" style="width: 50px" onchange="js_print_date_ranges(this.value, 'end', 'month');"><option value=""></option></select></td>
								<td><?php echo $label_end_month ?></td>
								<td><select name="end_month" id="end_month" style="width: 50px" onchange="check_custom_months();"><option value=""></option></select></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<?php echo help::render('use_average').' '.$label_sla_calc_method ?><br />
						<select name='use_average'>
							<option value='0' <?php print $use_average_no_selected ?>><?php echo $label_avg_sla ?></option>
							<option value='1' <?php print $use_average_yes_selected ?>><?php echo $label_avg ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo help::render('scheduled_downtime') ?>
						<input type="checkbox" class="checkbox" value="1" id="scheduleddowntimeasuptime" name="scheduleddowntimeasuptime"
								onchange="toggle_label_weight(this.checked, 'sched_downt')" <?php echo $scheduled_downtime_as_uptime_checked ?> />
						<label for="scheduleddowntimeasuptime" id="sched_downt"><?php echo $label_scheduleddowntimeasuptime ?></label>
					</td>
					<td>&nbsp;</td>
					<td>
						<?php echo help::render('use_alias') ?>
						<input type="checkbox" class="checkbox" value="1" id="use_alias" name="use_alias"
								onchange="toggle_label_weight(this.checked, 'usealias');" <?php print $use_alias_checked; ?> />
						<label for="use_alias" id="usealias"><?php echo $label_use_alias ?></label>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo help::render('stated_during_downtime') ?>
						<input type="checkbox" class="checkbox" value="1" id="assumestatesduringnotrunning" name="assumestatesduringnotrunning"
								onchange="toggle_label_weight(this.checked, 'assume_progdown');" <?php echo $assume_states_during_not_running_checked; ?> />
						<label for="assumestatesduringnotrunning" id="assume_progdown"><?php echo $label_assumestatesduringnotrunning ?></label>
					</td>
					<td>&nbsp;</td>
					<td id="csv_cell">
						<?php echo help::render('csv_format') ?>
						<input type="checkbox" class="checkbox" value="1" id="csvoutput" name="csvoutput"
								onchange="toggle_label_weight(this.checked, 'csvout');" <?php print $csv_output_checked; ?> />
						<label for="csvoutput" id="csvout"><?php echo $label_csvoutput ?></label>
					</td>
				</tr>



				<tr>
					<td>
						<?php echo help::render('initial_states') ?>
						<input type="checkbox" class="checkbox" value="1" id="assumeinitialstates" name="assumeinitialstates"
								onchange="show_state_options(this.checked);toggle_label_weight(this.checked, 'assume_initial');" <?php print $assume_initial_states_checked ?> />
						<label for="assumeinitialstates" id="assume_initial"><?php echo $label_assumeinitialstates ?></label>
					</td>
					<td>&nbsp;</td>
					<td style="vertical-align:top">
						<?php echo help::render('include_soft_states') ?>
						<input type="checkbox" class="checkbox" value="1" id="includesoftstates" name="includesoftstates"
								onchange="toggle_label_weight(this.checked, 'include_softstates');" <?php echo $include_soft_states_checked; ?> />
						<label for="includesoftstates" id="include_softstates"><?php echo $label_includesoftstates ?></label>
					</td>
				</tr>
				<tr id="assumed_host_state">
					<td style="padding-top: 10px"><?php echo help::render('first_assumed_host').' '.$label_initialassumedhoststate ?></td>
					<td>&nbsp;</td>
					<td style="padding-top: 10px"><?php echo help::render('first_assumed_service').' '.$label_initialassumedservicestate ?></td>
				</tr>
				<tr id="assumed_service_state">
					<td>
						<select name="initialassumedhoststate">
						<?php
							foreach($initial_assumed_host_states as $host_state_value => $host_state_txt) {
								$sel = ($host_state_value == $initial_assumed_host_state_selected ? ' selected="selected"':'');
								print '<option value="'.$host_state_value.'"'.$sel.'>'.$host_state_txt.'</option>'."\n";
							}
						 ?>
						</select>
					</td>
					<td>&nbsp;</td>
					<td>
						<select name="initialassumedservicestate">
						<?php
							foreach($initial_assumed_service_states as $service_state_value => $service_state_txt){
								$sel = ($service_state_value == $initial_assumed_service_state_selected ? ' selected="selected"':'');
								print '<option value="'.$service_state_value.'"'.$sel.'>'.$service_state_txt.'</option>'."\n";
							}
						 ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo help::render('save_report') ?>
						<input type="hidden" name="saved_report_id" value="<?php echo $report_id ?>" />
						<input type="checkbox" class="checkbox" name="save_report_settings" id="save_report_settings" value="1" onclick="toggle_field_visibility(this.checked, 'report_save_information');toggle_label_weight(this.checked, 'save_report_label')" />
						<label for="save_report_settings" id="save_report_label"><?php echo $label_save_report ?></label>
						<br />
						<span id="report_save_information">
							<input type="text" name="report_name" id="report_name" value="" maxlength="255" />
						</span>
						<input type="hidden" name="old_report_name" value="<?php echo $report_name ?>" />
					</td>
					<td>&nbsp;</td>
					<td style="vertical-align:top">
						<?php echo help::render('cluster_mode') ?>
						<input type="checkbox" class="checkbox" value="0" id="cluster_mode" name="cluster_mode"
								onchange="toggle_label_weight(this.checked, 'cluster_mode');" <?php print $cluster_mode_checked ?> />
						<label for="cluster_mode" id="cluster_mode"><?php echo $label_cluster_mode ?></label>
					</td>
				</tr>
			</table>
		</div>
		<br />

		<div class="setup-table<?php if ($type != 'sla') { ?> hidden<?php } ?>" id="enter_sla">
			<table style="width: 810px">
				<caption><?php echo help::render('enter-sla').' '.$label_enter_sla ?></caption>
				<tr>
					<?php foreach ($months as $key => $month) { ?>
					<td style="padding-left: 0px">
						<?php echo html::image($this->add_path('icons/16x16/copy.png'),
							array(
								'id' => 'month_'.($key+1),
								'alt' => $label_propagate,
								'title' => $label_propagate,
								'style' => 'cursor: pointer; margin-bottom: -4px',
								'class' => 'autofill')
							) ?>
						<?php echo $month ?><br />
						<input type="text" size="2" class="sla_month" name="month_<?php echo ($key+1) ?>"
								value="<?php echo arr::search($report_info, 'month_'.($key + 1))!==false ? $report_info['month_'.($key + 1)] : "" ?>" maxlength="6" /> %
					</td>
					<?php	} ?>
				</tr>
			</table>
		</div>

		<div class="setup-table">
			<input id="reports_submit_button" type="submit" name="" value="<?php echo $label_create_report ?>" class="button create-report" />
		</div>
	</form>
</div>

	</div>
	<div id="schedule-tab"><?php echo (isset($available_schedules) && !empty($available_schedules) ) ? $available_schedules : $label_no_schedules; ?></div>
</div>
