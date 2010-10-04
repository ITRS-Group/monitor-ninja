<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>
<div id="response"></div>

<div class="widget w98 left">
		<span id="view_add_schedule"<? if (!$report_id) {?> style="display: none;"<?php } ?> style="float: right; right: 1%; top: 0px;">
		<a id="new_schedule_btn" href="#new_schedule_form_area" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-add-schedule.png'), array('alt' => $this->translate->_('Add').' '. strtolower($label_new_schedule), 'title' => $this->translate->_('Add').' '. strtolower($label_new_schedule))); ?></a>
		<a id="show_schedule" href="#schedule_report"<?php echo (empty($scheduled_info)) ? ' style="display:none;"' : ''; ?> class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-view-schedule.png'), array('alt' => $label_view_schedule, 'title' => $label_view_schedule)); ?></a>
	</span>

		<?php if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
			echo form::open('summary/index', array('id' => 'saved_report_form', 'style' => 'margin-top: 7px;'));
		 ?>
			<div style="width: 100%; padding-left: 0px">
				<!--	onchange="check_and_submit(this.form)"	-->
				<?php echo help::render('saved_reports', 'reports') ?> <?php echo $label_saved_reports ?><br />
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
							' value="'.$info->id.'">'.$info->report_name.$sched_str.'</option>'."\n";
					}  ?>
				</select>
				<input type="hidden" name="type" value="<?php echo $type ?>" />
				<input type="submit" class="button select" value="<?php echo $label_select ?>" name="fetch_report" />
				<input type="button" class="button new" value="<?php echo $label_new ?>" name="new_report" title="<?php echo $new_saved_title ?>" id="new_report" />
				<input type="button" class="button delete" value="Delete" name="delete_report" title="<?php echo $label_delete ?>" id="delete_report" />
				<span id="autoreport_periods" style="display:none"><?php echo $json_periods ?></span>
				<?php if (isset($is_scheduled) && $is_scheduled) { ?>
				<div id="single_schedules" style="display:inline">
					<span id="is_scheduled" title="<?php echo $is_scheduled_clickstr ?>">
						<?php echo $is_scheduled_report ?>
					</span>
				</div>
			<?php	} ?>
		</div>
		<?php echo form::close(); } ?>



	<h1><?php echo $label_create_new ?></h1>

	<form onsubmit="return false;">
		<table id="report_mode_select">
			<caption><?php echo $label_report_mode ?></caption>
			<tr>
				<td id="td_std"><?php echo form::radio(array('name' => 'report_mode', 'id' => 'report_mode_standard'), 'standard', true); ?> <?php echo $label_report_mode_standard ?></td>
				<td id="td_cust"><?php echo form::radio(array('name' => 'report_mode', 'id' => 'report_mode_custom'), 'custom'); ?> <?php echo $label_report_mode_custom ?></td>
			</tr>
		</table>
	</form>
	<br />

	<?php	echo form::open('summary/generate', array('id' => 'summary_form_std')); ?>
	<table id="std_report_table">
		<tr>
			<td>
				<?php echo $label_reporttype ?><br />
				<?php echo form::dropdown(array('name' => 'standardreport'), $standardreport); ?>
			</td>
			<td>
				<?php echo $label_show_items ?><br />
				<?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $label_default_show_items) ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php echo help::render('save_report', 'reports') ?>
				<input type="hidden" name="saved_report_id" value="<?php echo $report_id ?>" />
				<input type="checkbox" class="checkbox" name="save_report_settings" id="save_report_settings" value="1" onclick="toggle_field_visibility(this.checked, 'report_save_information');toggle_label_weight(this.checked, 'save_report_label')" />
				<label for="save_report_settings" id="save_report_label"><?php echo $label_save_report ?></label>
				<br />
				<span id="report_save_information" style="display:none">
					<input type="text" name="report_name" id="report_name" value="<?php echo $report_name ?>" maxlength="255" />
				</span>
				<input type="hidden" name="old_report_name" value="<?php echo $report_name ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><?php echo form::submit('create_report', $label_create_report) ?></td>
		</tr>
	</table>

	<?php echo form::close(); ?>

	<div id="custom_report">
	<?php	echo form::open('summary/generate', array('id' => 'summary_form')); ?>
			<input type="hidden" name="new_report_setup" value="1" />
			<table id="std_report_table">
				<tr>
				<!--<caption><?php echo $label_customreport_options ?></caption>-->
					<td colspan="3">
						<select name="report_type" id="report_type" onchange="set_selection(this.value);">
							<option value="hostgroups"><?php echo $label_hostgroups ?></option>
							<option value="hosts"><?php echo $label_hosts ?></option>
							<option value="servicegroups"><?php echo $label_servicegroups ?></option>
							<option value="services"><?php echo $label_services ?></option>
						</select>
						<input type="button" id="sel_report_type" class="button select20" onclick="set_selection(document.forms['summary_form'].report_type.value);" value="<?php echo $label_select ?>" /><div id="progress"></div>
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

		<div class="setup-table" >
			<table id="settings_table">
				<tr>
					<td>
						<?php echo $label_rpttimeperiod ?><br />
						<?php echo form::dropdown(array('name' => 'report_period'), $report_periods, $sel_reportperiod); ?>
					</td>
					<td style="width: 18px">&nbsp;</td>
					<td>
						<?php echo $label_reporttype ?><br />
						<?php echo form::dropdown('report_type', $report_types) ?>
					</td>
				</tr>
				<tr id="display" style="display: none; clear: both;">
					<td><?php echo help::render('start-date', 'reports').' '.$label_startdate ?> (<em id="start_time_tmp"><?php echo $label_click_calendar ?></em>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick datepick-start" title="<?php echo $label_startdate_selector ?>" />
						<input type="hidden" name="start_time" id="start_time" value=""/>
						<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="08:00">
					</td>
					<td>&nbsp;</td>
					<td><?php echo help::render('end-date', 'reports').' '.$label_enddate ?> (<em id="end_time_tmp"><?php echo $label_click_calendar ?></em>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick datepick-end" title="<?php echo $label_enddate_selector ?>" />
						<input type="hidden" name="end_time" id="end_time" value="" />
						<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="09:00">
					</td>
				</tr>

				<tr>
					<td>
						<?php echo $label_alert_type ?><br />
						<?php echo form::dropdown('alert_types', $alerttypes, $sel_alerttype) ?>
					</td>
					<td>&nbsp;</td>
					<td>
						<?php echo $label_state_type ?><br />
						<?php echo form::dropdown('state_types', $statetypes, $sel_statetype) ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo $label_host_state ?><br />
						<?php echo form::dropdown('host_states', $hoststates, $sel_hoststate) ?>
					</td>
					<td>&nbsp;</td>
					<td>
						<?php echo $label_service_state ?><br />
						<?php echo form::dropdown('service_states', $servicestates, $sel_svcstate) ?>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<?php echo $label_show_items ?><br />
						<?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $label_default_show_items) ?>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<?php echo help::render('save_report', 'reports') ?>
						<input type="hidden" name="saved_report_id" value="<?php echo $report_id ?>" />
						<input type="checkbox" class="checkbox" name="save_report_settings" id="save_report_settings" value="1" onclick="toggle_field_visibility(this.checked, 'report_save_information2');toggle_label_weight(this.checked, 'save_report_label')" />
						<label for="save_report_settings" id="save_report_label"><?php echo $label_save_report ?></label>
						<br />
						<span id="report_save_information2" style="display:none">
							<input type="text" name="report_name" id="report_name" value="<?php echo $report_name ?>" maxlength="255" />
						</span>
						<input type="hidden" name="old_report_name" value="<?php echo $report_name ?>" />
					</td>
				</tr>
				<tr>
					<td colspan="3"><input id="reports_submit_button" type="submit" name="" value="<?php echo $label_create_report ?>" class="button create-report" /></td>
				</tr>
			</table>
		</div>
	<?php echo form::close(); ?>
	</div>

	<div id="new_schedule_form_area" style="display:none">
	<?php	echo form::open('reports/schedule', array('id' => 'schedule_report_form', 'onsubmit' => 'return trigger_schedule_save(this);'));
		?>
		<h1><?php echo $label_new_schedule ?></h1>
		<table id="new_schedule_report_table" cellpadding="0" cellspacing="0" style="margin-left: -3px">
			<?php if (!empty($available_schedule_periods)) { ?>
			<tr class="none">
				<td><?php echo $label_interval ?></td>
			</tr>
			<tr class="none">
				<td>
					<select name="period" id="period">
					<?php	foreach ($available_schedule_periods as $id => $period) { ?>
					<option value="<?php echo $id ?>"><?php echo $period ?></option>
					<?php	} ?>
					</select>
				</td>
			</tr>
			<?php } ?>
			<tr class="none">
				<td><?php echo $label_recipients ?><br /><input type="text" class="schedule" name="recipients" id="recipients" value="" /></td>
			</tr>
			<tr class="none">
				<td><?php echo $label_filename ?><br /><input type="text" class="schedule" name="filename" id="filename" value="" /></td>
			</tr>
			<tr class="none">
				<td><?php echo $label_description ?><br /><textarea cols="31" rows="3" id="description" name="description"></textarea></td>
			</tr>
			<tr class="none">
				<td id="scheduled_btn_ctrl">
					<input type="submit" name="sched_subm" id="sched_subm" value="<?php echo $label_save ?>" />
					<input type="reset" name="reset_frm" id="reset_frm" value="<?php echo $label_clear ?>" />
				</td>
			</tr>
		</table>
		<div><input type="hidden" name="saved_report_id" id="saved_report_id" value="<?php echo $report_id ?>" />
		<input type="hidden" name="type" value="summary" />
		<input type="hidden" name="module_save" id="module_save" value="1" /></div>
		</form>
	</div>

	<div id="schedule_report" style="display:none">
		<table id="schedule_report_table">
				<caption><?php echo $lable_schedules ?> (<span id="scheduled_report_name"><?php echo !empty($report_info) ? (!empty($report_info['report_name']) ?
									$report_info['report_name'] : $report_info['sla_name']) : '' ?></span>)</caption>
				<tr id="schedule_header">
					<th class="headerNone left"><?php echo $label_interval ?></th>
					<th class="headerNone left"><?php echo $label_recipients ?></th>
					<th class="headerNone left"><?php echo $label_filename ?></th>
					<th class="headerNone left"><?php echo $label_description ?></th>
					<th class="headerNone left" style="width: 45px"><?php echo $this->translate->_('Actions') ?></th>
				</tr>
			<?php if (!empty($scheduled_info)) {
				$i = 0;
				foreach ($scheduled_info as $schedule) {
					$i++;
					$schedule = (object)$schedule;
					$recipients = str_replace(' ', '', $schedule->recipients);
					$recipients = str_replace(',', ', ', $recipients); ?>
			<tr id="report-<?php echo $schedule->id ?>" class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
				<td class="period_select" title="<?php echo $label_dblclick ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
				<td class="iseditable" title="<?php echo $label_dblclick ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
				<td class="iseditable" title="<?php echo $label_dblclick ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
				<td class="iseditable_txtarea" title="<?php echo $label_dblclick ?>" id="description-<?php echo $schedule->id ?>"><?php echo utf8_decode($schedule->description) ?></td>
				<td>
					<form><input type="button" class="send_report_now" onclick="send_report_now('summary', <?php echo $schedule->id ?>)" id="send_now_summary_<?php echo $schedule->id ?>" title="<?php echo $this->translate->_('Send this report now') ?>" value="&nbsp;"></form>
					<div class="delete_schedule <?php echo $type ?>_del" onclick="schedule_delete(<?php echo $schedule->id ?>, 'summary')" id="delid_<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'), array('alt' => $this->translate->_('Delete scheduled report'), 'title' => $this->translate->_('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
				</td>
			</tr>
			<?php }	} ?>
		</table>
	</div>
