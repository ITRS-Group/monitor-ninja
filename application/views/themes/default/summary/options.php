<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<form action="<?php echo url::base(true) ?>/summary/generate" method="post" class="to_check">
	<table class="setup-tbl standard">
		<tr>
			<td>
				<?php echo help::render('standardreport') ?>
				<label for="standardreport"><?php echo _('Report Type') ?></label>
			</td>
			<td></td>
			<td>
				<?php echo help::render('summary_items') ?>
				<label for="summary_items"><?php echo _('Items to show') ?></label>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo form::dropdown(array('name' => 'standardreport'), $options->get_alternatives('standardreport'), $options['standardreport']); ?>
			</td>
			<td></td>
			<td>
				<?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $options['summary_items']) ?>
			</td>
		</tr>
		<tr>
			<td colspan="3"><input id="reports_submit_button" type="submit" name="" value="<?php echo _('Show report') ?>" class="button create-report" /></td>
		</tr>
	</table>
</form>
<form action="<?php echo url::base(true) ?>/summary/generate" method="post" class="to_check">
	<table class="setup-tbl custom">
		<tr>
			<td colspan="3">
				<select name="report_type" id="report_type" onchange="set_selection(this.value);">
					<option value="hostgroups"><?php echo _('Hostgroups') ?></option>
					<option value="hosts"><?php echo _('Hosts') ?></option>
					<option value="servicegroups"><?php echo _('Servicegroups') ?></option>
					<option value="services"><?php echo _('Services') ?></option>
				</select>
				<input type="button" id="sel_report_type" class="button select20" onclick="set_selection(document.forms['summary_form'].report_type.value);" value="<?php echo _('Select') ?>" /><div id="progress"></div>
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
		<tr>
			<td>
				<?php echo _('Report Period') ?><br />
				<?php echo form::dropdown('report_period', $options->get_alternatives('report_period'), $options['report_period']); ?>
			</td>
			<td style="width: 18px">&nbsp;</td>
			<td>
				<?php echo _('Report Type') ?><br />
				<?php echo form::dropdown('summary_type', $options->get_alternatives('summary_type')) ?>
			</td>
		</tr>
		<tr id="display" style="display: none; clear: both;">
			<td><?php echo help::render('start-date', 'reports').' '._('Start date') ?> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
				<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
				<input type="hidden" name="start_time" id="start_time" value=""/>
				<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="08:00">
			</td>
			<td>&nbsp;</td>
			<td><?php echo help::render('end-date', 'reports').' '._('End date') ?> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
				<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
				<input type="hidden" name="end_time" id="end_time" value="" />
				<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="09:00">
			</td>
		</tr>

		<tr>
			<td>
				<?php echo _('Alert Types') ?><br />
				<?php echo form::dropdown('alert_types', $options->get_alternatives('alert_types'), $options['alert_types']) ?>
			</td>
			<td>&nbsp;</td>
			<td>
				<?php echo _('State Types') ?><br />
				<?php echo form::dropdown('state_types', $options->get_alternatives('state_types'), $options['state_types']) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _('Host States') ?><br />
				<?php echo form::dropdown('host_states', $options->get_alternatives('host_states'), $options['host_states']) ?>
			</td>
			<td>&nbsp;</td>
			<td>
				<?php echo _('Service States') ?><br />
				<?php echo form::dropdown('service_states', $options->get_alternatives('service_states'), $options['service_states']) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo help::render('skin') ?>
				<label for="skin" id="skin_lbl"><?php echo _('Skin') ?></label>
			</td>
			<td></td>
			<td>
				<?php echo help::render('summary_items') ?>
				<label for="summary_items"><?php echo _('Items to show') ?></label>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo form::dropdown(array('name' => 'skin'), ninja::get_skins(), $options['skin']); ?>
			</td>
			<td></td>
			<td>
				<?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $options['summary_items']) ?>
			</td>
		</tr>
		<tr>
			<td colspan="3"><input id="reports_submit_button" type="submit" name="" value="<?php echo _('Show report') ?>" class="button create-report" /></td>
		</tr>
	</table>
</form>
