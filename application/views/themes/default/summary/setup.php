<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>
<div id="response"></div>
<div id="progress"></div>

<div class="summary-page">
	<div class="setup-table">

	<h1><?php echo $label_create_new ?></h1>

	<form onsubmit="return false;">
		<table id="report_mode_select" style="width:auto">
			<tr>
				<td colspan="2"><?php echo $label_report_mode ?>:</td>
			</tr>
			<tr>
				<td id="td_std"><?php echo form::radio(array('name' => 'report_mode', 'id' => 'report_mode_standard'), 'standard', true); ?> <?php echo $label_report_mode_standard ?></td>
				<td id="td_cust"><?php echo form::radio(array('name' => 'report_mode', 'id' => 'report_mode_custom'), 'custom'); ?> <?php echo $label_report_mode_custom ?></td>
			</tr>
		</table>
	</form>
	<br />

	<?php	echo form::open('summary/generate', array('id' => 'summary_form_std')); ?>
	<table style="width: 700px" id="std_report_table">
		<tr>
			<td colspan="2"><?php echo $label_standardreport ?>:</td>
		</tr>
		<tr>
			<td><?php echo $label_reporttype ?></td>
			<td><?php echo form::dropdown(array('name' => 'standardreport'), $standardreport); ?></td>
		</tr>
		<tr>
			<td><?php echo $label_show_items ?></td>
			<td><?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $label_default_show_items) ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php echo form::submit('create_report', $label_create_report) ?></td>
		</tr>
	</table>

	<?php echo form::close(); ?>
<br /><br />

	<div id="custom_report">
	<?php	echo form::open('summary/generate', array('id' => 'summary_form')); ?>
			<input type="hidden" name="new_report_setup" value="1" />
			<table style="width: 700px">
				<tr>
					<td colspan="3"><?php echo $label_customreport_options ?></td>
				</tr>
				<tr>
					<td colspan="3">
						<select name="report_type" id="report_type" onchange="set_selection(this.value);">
							<option value="hostgroups"><?php echo $label_hostgroups ?></option>
							<option value="hosts"><?php echo $label_hosts ?></option>
							<option value="servicegroups"><?php echo $label_servicegroups ?></option>
							<option value="services"><?php echo $label_services ?></option>
						</select>
						<input type="button" id="sel_report_type" class="button select20" onclick="set_selection(document.forms['summary_form'].report_type.value);" value="<?php echo $label_select ?>" />
					</td>
				</tr>
				<tr id="hostgroup_row">
					<td>
						<?php echo $label_available.' '.$label_hostgroups ?><br />
						<select name="hostgroup_tmp[]" id="hostgroup_tmp" multiple="multiple" size='8' class="multiple" />
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
						<select name="servicegroup_tmp[]" id="servicegroup_tmp" multiple="multiple" size='8' class="multiple" />
						</select>
					</td>
					<td class="move-buttons">
						<input type="button" value="&gt;" id="mv_sg_r" class="button arrow-right" /><br />
						<input type="button" value="&lt;" id="mv_sg_l" class="button arrow-left" />
					</td>
					<td>
						<?php echo $label_selected.' '.$label_servicegroups ?><br />
						<select name="servicegroup[]" id="servicegroup" multiple="multiple" size="8" class="multiple" />
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
						<select name="host_name[]" id="host_name" multiple="multiple" size="8" class="multiple" />
						</select>
					</td>
				</tr>
				<tr id="service_row_2">
					<td>
						<?php echo $label_available.' '.$label_services ?><br />
						<select name="service_tmp[]" id="service_tmp" multiple="multiple" size="8" class="multiple" />
						</select>
					</td>
					<td class="move-buttons">
						<input type="button" value="&gt;" id="mv_s_r" class="button arrow-right" /><br />
						<input type="button" value="&lt;" id="mv_s_l" class="button arrow-left"  />
					</td>
					<td>
						<?php echo $label_selected.' '.$label_services ?><br />
						<select name="service_description[]" id="service_description" multiple="multiple" size="8" class="multiple" />
						</select>
					</td>
				</tr>
			</table>

		<div class="setup-table" id="settings_table">
			<table style="width: 742px">
				<tr>
					<td colspan="3"><?php echo $label_rpttimeperiod ?></td>
				</tr>
				<tr>
					<td><?php echo form::dropdown(array('name' => 'report_period'), $report_periods); ?></td>
				</tr>
				<tr>
					<td><?php echo $label_reporttype ?></td>
					<td>&nbsp;</td>
					<td><?php echo form::dropdown('report_type', $report_types) ?></td>
				</tr>
				<tr id="display" style="display: none; clear: both;">
					<td><?php echo help::render('start-date').' '.$label_startdate ?> (<em id="start_time_tmp"><?php echo $label_click_calendar ?></em>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick" title="<?php echo $label_startdate_selector ?>" />
						<input type="hidden" name="start_time" id="start_time" value=""/>
					</td>
					<td>&nbsp;</td>
					<td><?php echo help::render('end-date').' '.$label_enddate ?> (<em id="end_time_tmp"><?php echo $label_click_calendar ?></em>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick" title="<?php echo $label_enddate_selector ?>" />
						<input type="hidden" name="end_time" id="end_time" value="" />
					</td>
				</tr>
				<tr>
					<td><?php echo $label_alert_type ?></td>
					<td>&nbsp;</td>
					<td><?php echo form::dropdown('alert_types', $alerttypes) ?></td>
				</tr>
				<tr>
					<td><?php echo $label_state_type ?></td>
					<td>&nbsp;</td>
					<td><?php echo form::dropdown('state_types', $statetypes) ?></td>
				</tr>
				<tr>
					<td><?php echo $label_host_state ?></td>
					<td>&nbsp;</td>
					<td><?php echo form::dropdown('host_states', $hoststates) ?></td>
				</tr>
				<tr>
					<td><?php echo $label_service_state ?></td>
					<td>&nbsp;</td>
					<td><?php echo form::dropdown('service_states', $servicestates) ?></td>
				</tr>
				<tr>
					<td><?php echo $label_show_items ?></td>
					<td>&nbsp;</td>
					<td><?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $label_default_show_items) ?></td>
				</tr>

			</table>
		</div>

		<div class="setup-table">
			<input id="reports_submit_button" type="submit" name="" value="<?php echo $label_create_report ?>" class="button create-report" />
		</div>
	<?php echo form::close(); ?>
	</div>

</div>
