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

<style>
	table td {
		border: none;
	}
</style>

<div>

	<h1><?php echo _('Event History Report') ?></h1>

	<div id="histogram_report">
	<?php	echo form::open('histogram/generate', array('id' => 'histogram_form')); ?>
			<table style="border: none;" id="history_report_table" style="width: auto">
				<tr>
					<td colspan="3">
						<select name="report_type" id="report_type">
							<option value="hostgroups"><?php echo _('Hostgroups') ?></option>
							<option value="hosts"><?php echo _('Hosts') ?></option>
							<option value="servicegroups"><?php echo _('Servicegroups') ?></option>
							<option value="services"><?php echo _('Services') ?></option>
						</select>
						<input type="button" id="sel_report_type" class="button select20" value="<?php echo _('Select') ?>" />
					</td>
				</tr>
				<tr data-show-for="hostgroups">
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
				<tr data-show-for="servicegroups">
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
				<tr data-show-for="hosts">
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
				<tr data-show-for="services">
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

		<div class="setup-table" >
			<table id="settings_table">
				<tr>
					<td>
						<?php echo _('Report Period') ?><br />
						<?php echo form::dropdown(array('name' => 'report_period'), $options->get_alternatives('report_period'), $options['report_period']); ?>
					</td>
					<td style="width: 18px">&nbsp;</td>
					<td>
						<?php echo _('State Types To Graph') ?><br />
						<?php echo form::dropdown('state_types', $options->get_alternatives('state_types')) ?>
					</td>

				</tr>
				<tr id="display" style="display: none; clear: both;">
					<td><?php echo help::render('start-date').' '._('Start date') ?> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
						<input type="hidden" name="start_time" id="start_time" value=""/>
						<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="08:00">
					</td>
					<td>&nbsp;</td>
					<td><?php echo help::render('end-date').' '._('End date') ?> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
						<input type="hidden" name="end_time" id="end_time" value="" />
						<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="09:00">
					</td>
				</tr>
				<tr>
					<td>
						<?php echo _('Statistics Breakdown') ?><br />
						<?php echo form::dropdown('breakdown', $options->get_alternatives('breakdown'), $options['breakdown']) ?>
					</td>
					<td style="width: 18px">&nbsp;</td>
					<td>
						<div data-show-for="hosts hostgroups">
							<?php echo _('Events To Graph') ?><br />
							<?php echo form::dropdown('host_states', $options->get_alternatives('host_states')) ?>
						</div>
						<div data-show-for="services servicegroups">
							<?php echo _('Events To Graph') ?><br />
							<?php echo form::dropdown('service_states', $options->get_alternatives('service_states')) ?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<?php echo form::checkbox('newstatesonly', 1, $options['newstatesonly']); ?>
					<?php echo _('Ignore Repeated States') ?>
					</td>
				</tr>
				<tr>
					<td colspan="3"><input id="reports_submit_button" type="submit" name="" value="<?php echo _('Create report') ?>" class="button create-report" /></td>
				</tr>
			</table>
		</div>
	<?php echo form::close(); ?>
	</div>
