<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>
<div id="response"></div>

<div class="report-page-setup">
	<div class="setup-table">

		<div class="setup-table">
			<h1 id="report_type_label"><?php echo _('Trends report'); ?></h1>
	</div>

	<?php	echo form::open('trends/generate', array('id' => 'report_form')); ?>
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
		</div>

		<div class="setup-table" id="settings_table">
			<table class="setup-tbl">
				<tr>
					<td><?php echo help::render('reporting_period').' '._('Reporting period') ?></td>
					<td style="width: 18px">&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td><?php echo form::dropdown(array('name' => 'report_period'), $options->get_alternatives('report_period'), $options['report_period']); ?></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
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
						<?php echo help::render('stated_during_downtime') ?>
						<input type="checkbox" class="checkbox" value="1" id="assumestatesduringnotrunning" name="assumestatesduringnotrunning"
								onchange="toggle_label_weight(this.checked, 'assume_progdown');" <?php echo $options['assumestatesduringnotrunning']?'checked="checked"':''; ?> />
						<label for="assumestatesduringnotrunning" id="assume_progdown"><?php echo _('Assume states during program downtime') ?></label>
					</td>
					<td>&nbsp;</td>
					<td style="vertical-align:top">
						<?php echo help::render('include_soft_states') ?>
						<input type="checkbox" class="checkbox" value="1" id="includesoftstates" name="includesoftstates"
								onchange="toggle_label_weight(this.checked, 'include_softstates');" <?php echo $options['include_soft_states']?'checked="checked"':''; ?> />
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
					<td colspan="2">&nbsp;</td>
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
				<tr>
					<td colspan="3"><input id="reports_submit_button" type="submit" name="" value="<?php echo _('Create report') ?>" class="button create-report" /></td>
				</tr>
			</table>
		</div>
	</form>
</div>
