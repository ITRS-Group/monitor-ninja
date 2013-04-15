<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<form action="<?php echo url::base(true) ?>summary/generate" method="post" class="to_check">
	<div class="standard setup-table">
		<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
		<table class="setup-tbl report_block auto_width">
			<tr>
				<td>
					<?php echo help::render('standardreport') ?>
					<label for="standardreport"><?php echo _('Standard type') ?></label>
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
	</div>
</form>
<form action="<?php echo url::base(true) ?>summary/generate" method="post" class="to_check" id="report_form">
	<div class="custom setup-table">
		<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
		<table class="setup-tbl custom report_block">
			<tr>
				<td colspan="3">
					<label for="report_type"><?php echo help::render('report-type').' '._('Report type'); ?></label><br />
					<select name="report_type" id="report_type">
						<option value="hostgroups"><?php echo _('Hostgroups') ?></option>
						<option value="hosts"><?php echo _('Hosts') ?></option>
						<option value="servicegroups"><?php echo _('Servicegroups') ?></option>
						<option value="services"><?php echo _('Services') ?></option>
					</select>
					<input type="button" id="sel_report_type" class="button select20" value="<?php echo _('Select') ?>" /><div id="progress"></div>
				</td>
			</tr>
			<tr id="filter_row">
				<td colspan="3">
					<?php echo _('Filter:') ?><br />
					<input type="text" name="filter_field" id="filter_field" autocomplete=off size="10" value="">
					<input type="button" name="clear_filter" id="clear_filter" value="<?php echo _('Clear') ?>">
				</td>
			</tr>
			<tr data-show-for="hostgroups">
				<td colspan="3">
					<div class="left" style="width: 40%">
						<label for="hostgroup_tmp"><?php echo _('Available hostgroups') ?></label><br />
						<select name="hostgroup_tmp[]" id="hostgroup_tmp" multiple="multiple" size='8' class="multiple" style="width: 100%">
						</select>
					</div>
					<div class="left" style="padding-top: 40px;">
						<input type="button" value="&gt;" id="mv_hg_r" class="button arrow-right" /><br />
						<input type="button" value="&lt;" id="mv_hg_l" class="button arrow-left" />
					</div>
					<div class="left" style="width: 40%">
						<label for="hostgroup"><?php echo _('Selected hostgroups') ?></label><br />
						<select name="hostgroup[]" id="hostgroup" multiple="multiple" size="8" class="multiple" style="width: 100%">
						</select>
					</div>
				</td>
			</tr>
			<tr data-show-for="servicegroups">
				<td colspan="3">
					<div class="left" style="width: 40%">
						<label for="servicegroup_tmp"><?php echo _('Available servicegroups') ?></label><br />
						<select name="servicegroup_tmp[]" id="servicegroup_tmp" multiple="multiple" size='8' class="multiple" style="width: 100%">
						</select>
					</div>
					<div class="left" style="padding-top: 40px;">
						<input type="button" value="&gt;" id="mv_sg_r" class="button arrow-right" /><br />
						<input type="button" value="&lt;" id="mv_sg_l" class="button arrow-left" />
					</div>
					<div class="left" style="width: 40%">
						<label for="servicegroup"><?php echo _('Selected servicegroups') ?></label><br />
						<select name="servicegroup[]" id="servicegroup" multiple="multiple" size="8" class="multiple" style="width: 100%">
						</select>
					</div>
				</td>
			</tr>
			<tr data-show-for="hosts">
				<td colspan="3">
					<div class="left" style="width: 40%">
						<label for="host_tmp"><?php echo _('Available hosts') ?></label><br />
						<select name="host_tmp[]" id="host_tmp" multiple="multiple" size="8" class="multiple" style="width: 100%">
						</select>
					</div>
					<div class="left" style="padding-top: 40px;">
						<input type="button" value="&gt;" id="mv_h_r" class="button arrow-right" /><br />
						<input type="button" value="&lt;" id="mv_h_l" class="button arrow-left" />
					</div>
					<div class="left" style="width: 40%">
						<label for="host_name"><?php echo _('Selected hosts') ?></label><br />
						<select name="host_name[]" id="host_name" multiple="multiple" size="8" class="multiple" style="width: 100%">
						</select>
					</div>
				</td>
			</tr>
			<tr data-show-for="services">
				<td colspan="3">
					<div class="left" style="width: 40%">
						<label for="service_tmp"><?php echo _('Available services') ?></label><br />
						<select name="service_tmp[]" id="service_tmp" multiple="multiple" size="8" class="multiple" style="width: 100%">
						</select>
					</div>
					<div class="left" style="padding-top: 40px;">
						<input type="button" value="&gt;" id="mv_s_r" class="button arrow-right" /><br />
						<input type="button" value="&lt;" id="mv_s_l" class="button arrow-left"  />
					</div>
					<div class="left" style="width: 40%">
						<label for="service_description"><?php echo _('Selected services') ?></label><br />
						<select name="service_description[]" id="service_description" multiple="multiple" size="8" class="multiple" style="width: 100%">
						</select>
					</div>
				</td>
			</tr>
		</table>
		<h2><?php echo _('Report Settings'); ?></h2>
		<hr />
		<table id="report" class="setup-tbl custom report_block">
			<tr>
				<td>
					<label for="summary_type"><?php echo help::render('summary_type').' '._('Summary type') ?></label>
				</td>
				<td></td>
				<td>
					<?php echo help::render('summary_items') ?>
					<label for="summary_items"><?php echo _('Items to show') ?></label>
				</td>
			</tr>
			<tr>
				<td><?php echo form::dropdown('summary_type', $options->get_alternatives('summary_type'), $options['summary_type']) ?></td>
				<td style="width: 10px">&nbsp;</td>
				<td>
					<?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $options['summary_items']) ?>
				</td>
			</tr>
			<tr>
				<td><label for="report_period"><?php echo help::render('reporting_period').' '._('Reporting period') ?></label></td>
				<td style="width: 10px">&nbsp;</td>
				<td><label for="rpttimeperiod"><?php echo help::render('report_time_period').' '._('Report time period') ?></label></td>
			</tr>
			<tr>
				<td><?php echo form::dropdown('report_period', $options->get_alternatives('report_period'), $options['report_period']); ?></td>
				<td style="width: 18px">&nbsp;</td>
				<td><?php echo form::dropdown(array('name' => 'rpttimeperiod'), $options->get_alternatives('rpttimeperiod'), $options['rpttimeperiod']); ?></td>
			</tr>
			<tr id="display" style="display: none; clear: both;">
				<td><label for="cal_start"><?php echo help::render('start-date', 'reports').' '._('Start date') ?></label> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
					<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('start_time') ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
					<input type="hidden" name="start_time" id="start_time" value="<?php echo $options['start_time'] ?>"/>
					<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $options->get_time('start_time') ?>">
				</td>
				<td>&nbsp;</td>
				<td><label for="cal_end"><?php echo help::render('end-date', 'reports').' '._('End date') ?></label> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
					<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('end_time') ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
					<input type="hidden" name="end_time" id="end_time" value="<?php echo $options['end_time'] ?>" />
					<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $options->get_time('end_time') ?>">
				</td>
			</tr>

			<tr>
				<td>
					<?php echo help::render('alert_types').' '._('Alert types') ?><br />
					<?php echo form::dropdown('alert_types', $options->get_alternatives('alert_types'), $options['alert_types']) ?>
				</td>
				<td>&nbsp;</td>
				<td>
					<?php echo help::render('state_types').' '._('State types') ?><br />
					<?php echo form::dropdown('state_types', $options->get_alternatives('state_types'), $options['state_types']) ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo help::render('host_states').' '._('Host states') ?><br />
					<?php echo form::dropdown('host_states', $options->get_alternatives('host_states'), $options['host_states']) ?>
				</td>
				<td>&nbsp;</td>
				<td>
					<?php echo help::render('service_states').' '._('Service states') ?><br />
					<?php echo form::dropdown('service_states', $options->get_alternatives('service_states'), $options['service_states']) ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo help::render('skin') ?>
					<label for="skin" id="skin_lbl"><?php echo _('Skin') ?></label>
				</td>
				<td></td>
			</tr>
			<tr>
				<td>
					<?php echo form::dropdown(array('name' => 'skin'), ninja::get_skins(), $options['skin']); ?>
				</td>
				<td></td>
			</tr>
			<tr>
				<td>
					<?php echo help::render('description') ?>
					<label for="description" id="descr_lbl"><?php echo _('Description') ?></label>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo form::textarea('description', $options['description']); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input id="reports_submit_button" type="submit" name="" value="<?php echo _('Show report') ?>" class="button create-report" /></td>
			</tr>
		</table>
	</div>
</form>
