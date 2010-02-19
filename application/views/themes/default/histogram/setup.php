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

<div class="widget w98 left">

	<h1><?php echo $label_create_new ?></h1>

	<div id="histogram_report">
	<?php	echo form::open('histogram/generate', array('id' => 'histogram_form')); ?>
			<table id="history_report_table">
					<td colspan="3">
						<select name="report_type" id="report_type" onchange="set_selection(this.value);">
							<option value="hostgroups"><?php echo $label_hostgroups ?></option>
							<option value="hosts"><?php echo $label_hosts ?></option>
							<option value="servicegroups"><?php echo $label_servicegroups ?></option>
							<option value="services"><?php echo $label_services ?></option>
						</select>
						<input type="button" id="sel_report_type" class="button select20" onclick="set_selection($('#report_type').val());" value="<?php echo $label_select ?>" />
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

		<div class="setup-table" >
			<table id="settings_table">
				<tr>
					<td>
						<?php echo $label_rpttimeperiod ?><br />
						<?php echo form::dropdown(array('name' => 'report_period'), $report_periods, $selected_report_period); ?>
					</td>
					<td style="width: 18px">&nbsp;</td>
					<td>
						<?php echo $label_statetypes_to_graph ?><br />
						<?php echo form::dropdown('state_types', $statetypes) ?>
					</td>

				</tr>
				<tr id="display" style="display: none; clear: both;">
					<td><?php echo help::render('start-date').' '.$label_startdate ?> (<em id="start_time_tmp"><?php echo $label_click_calendar ?></em>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick" title="<?php echo $label_startdate_selector ?>" />
						<input type="hidden" name="start_time" id="start_time" value=""/>
						<input type="text" maxlength="5" name="time_start" id="time_start" value="08:00">
					</td>
					<td>&nbsp;</td>
					<td><?php echo help::render('end-date').' '.$label_enddate ?> (<em id="end_time_tmp"><?php echo $label_click_calendar ?></em>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick" title="<?php echo $label_enddate_selector ?>" />
						<input type="hidden" name="end_time" id="end_time" value="" />
						<input type="text" maxlength="5" name="time_end" id="time_end" value="09:00">
					</td>
				</tr>
				</tr>
				<tr>
					<td>
						<?php echo $label_breakdown ?><br />
						<?php echo form::dropdown('breakdown', $breakdown, 'dayofmonth') ?>
					</td>
					<td style="width: 18px">&nbsp;</td>
					<td>
						<div id="block_host_states">
							<?php echo $label_events_to_graph ?><br />
							<?php echo form::dropdown('host_states', $hoststates) ?>
						</div>
						<div id="block_service_states">
							<?php echo $label_events_to_graph ?><br />
							<?php echo form::dropdown('service_states', $servicestates) ?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<?php echo form::checkbox('newstatesonly', 1, true); ?>
					<?php echo $label_newstatesonly ?>
					</td>
				</tr>
				<tr>
					<td colspan="3"><input id="reports_submit_button" type="submit" name="" value="<?php echo $label_create_report ?>" class="button create-report" /></td>
				</tr>
			</table>
		</div>
	<?php echo form::close(); ?>
	</div>