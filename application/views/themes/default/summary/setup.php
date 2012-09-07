<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"></div>

<div class="widget w98 left">
	<span id="view_add_schedule"<?php if (!$options['report_id']) {?> style="display: none;"<?php } ?> style="float: right; right: 1%; top: 0px;">
		<a id="new_schedule_btn" href="#new_schedule_form_area" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-add-schedule.png'), array('alt' => _('Add').' '. strtolower(_('New schedule')), 'title' => _('Add').' '. strtolower(_('New schedule')))); ?></a>
		<a id="show_schedule" href="#schedule_report"<?php echo (empty($scheduled_info)) ? ' style="display:none;"' : ''; ?> class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-view-schedule.png'), array('alt' => _('View schedule'), 'title' => _('View schedule'))); ?></a>
	</span>

		<?php if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
			echo form::open('summary/index', array('id' => 'saved_report_form', 'style' => 'margin-top: 7px;'));
		 ?>
			<div style="width: 100%; padding-left: 0px">
				<!--	onchange="check_and_submit(this.form)"	-->
				<?php echo help::render('saved_reports', 'reports') ?> <?php echo _('Saved reports') ?><br />
				<select name="report_id" id="report_id">
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
							' value="'.$info->id.'">'.$info->report_name.$sched_str.'</option>'."\n";
					}  ?>
				</select>
				<input type="hidden" name="summary_type" value="<?php echo $options['summary_type'] ?>" />
				<input type="submit" class="button select" value="<?php echo _('Select') ?>" name="fetch_report" />
				<input type="button" class="button new" value="<?php echo _('New') ?>" name="new_report" title="<?php echo _('Create new saved Summary report') ?>" id="new_report" />
				<input type="button" class="button delete" value="Delete" name="delete_report" title="<?php echo _('Delete report') ?>" id="delete_report" />
				<?php if (isset($is_scheduled) && $is_scheduled) { ?>
				<div id="single_schedules" style="display:inline">
					<span id="is_scheduled" title="<?php echo _('This report has been scheduled. Click the icons below to change settings') ?>">
						<?php echo _('This is a scheduled report') ?>
					</span>
				</div>
			<?php	} ?>
		</div>
		<?php echo form::close(); } ?>



	<h1><?php echo _('Alert Summary Report') ?></h1>
	<h2><?php echo _('Report Mode') ?></h2>
	<form action="" onsubmit="return false;">
	<p><label><?php echo form::radio(array('name' => 'report_mode', 'id' => 'report_mode_standard'), 'standard', true); ?> <?php echo _('Standard') ?></label><br /><label><?php echo form::radio(array('name' => 'report_mode', 'id' => 'report_mode_custom'), 'custom'); ?> <?php echo _('Custom') ?></label>
	</form>
	<br />

	<form action="summary/generate" method="post" id="summary_form_std">
		<table id="std_report_table">
			<tr>
				<td>
					<?php echo _('Report Type') ?><br />
					<?php echo form::dropdown(array('name' => 'standardreport'), $options->get_alternatives('standardreport')); ?>
				</td>
				<td>
					<?php echo _('Items to show') ?><br />
					<?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $options['summary_items']) ?>
				</td>
			</tr>
		</table>
		<div class="setup-table" id="meta_table">
			<table class="setup-tbl meta">
				<tr>
					<td><?php echo help::render('save_report').' '._('Save report') ?></td>
				</tr>
				<tr>
					<td>
						<input type="hidden" name="saved_report_id" value="<?php echo $options['report_id'] ?>" />
						<span id="report_save_information">
							<input type="text" name="report_name" id="report_name" value="" maxlength="255" />
							<input type="button" name="save_report_btn" class="save_report_btn" value="Save" />
						</span>
						<input type="hidden" name="old_report_name" value="<?php echo $options['report_name'] ?>" />
					</td>
				<tr>
					<td><?php echo help::render('output_format') ?><label for="output_format" id="outfmt"><?php echo _('Output format') ?></label></td>
				</tr>
				<tr>
					<td>
						<select id="output_format" name="output_format">
							<option selected="selected" value="html"><?php echo _('HTML') ?></label>
							<option value="pdf"><?php echo _('PDF') ?></label>
							<option value="csv"><?php echo _('CSV') ?></label>
						</select>
					</td>
				</tr>
			</table>
			<table>
				<tr>
					<td colspan="2"><?php echo form::submit('create_report', _('Create report')) ?></td>
				</tr>
			</table>
		</div>
	</form>

	<div id="custom_report">
		<form action="summary/generate" method="post" id="summary_form">
			<table id="std_report_table">
				<tr>
				<!--<caption><?php echo _('Custom Report Options') ?></caption>-->
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
			</table>

			<div class="setup-table" >
				<table id="settings_table">
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
						<td colspan="3">
							<?php echo _('Items to show') ?><br />
							<?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $options['summary_items']) ?>
						</td>
					</tr>
					<tr>
						<td colspan="3"><input id="reports_submit_button" type="submit" name="" value="<?php echo _('Create report') ?>" class="button create-report" /></td>
					</tr>
				</table>
			</div>
			<div class="setup-table" id="meta_table">
				<table class="setup-tbl meta">
					<caption>Meta</caption>
					<tr>
						<td><?php echo help::render('save_report').' '._('Save report') ?></td>
						<td></td>
					</tr>
					<tr>
						<td>
							<input type="hidden" name="saved_report_id" value="<?php echo $options['report_id'] ?>" />
							<span id="report_save_information">
								<input type="text" name="report_name" id="report_name" value="" maxlength="255" />
								<input type="button" name="save_report_btn" class="save_report_btn" value="Save" />
							</span>
							<input type="hidden" name="old_report_name" value="<?php echo $options['report_name'] ?>" />
						</td>
						<td style="vertical-align: top">
							<?php echo help::render('output_format') ?>
							<label for="output_format" id="outfmt"><?php echo _('Output format') ?></label>
							<select id="output_format" name="output_format">
								<option selected="selected" value="html"><?php echo _('HTML') ?></label>
								<option value="pdf"><?php echo _('PDF') ?></label>
								<option value="csv"><?php echo _('CSV') ?></label>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</div>
