<div style="display:none" id="link_container"></div>
<span id="save_to_schedule"><?php echo (!$options['report_id']) ? '<em>'._('To schedule this report, save it first').'</em>' : ''; ?></span>
<div style="display: none;">
<?php
if ($type == 'avail') { ?>
<div id="save_report">
<?php echo form::open($type.'/generate', array('id' => 'save_report_form', 'onsubmit' => 'return validate_report_form(this);'));?>
	<h1><?php echo _('Save report') ?></h1>
	<table style="width: 350px">
		<tr class="none">
			<td style="vertical-align:middle"><label for="report_name" id="save_report_label"><?php echo _('Save as') ?></label></td>
			<td><div id="report_setup">
					<input type="text" name="report_name" id="report_name" class="input-save-name"
						value="<?php echo $options['report_name'] ?>" maxlength="255" style="margin: 0px" />
					<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
					<input type="hidden" name="include_trends" value="<?php echo $options['include_trends'] ?>" />
					<input type="button" name="s1" value="<?php echo (!empty($options['report_id'])) ? _('Update report') : _('Save') ?>" class="button update-report20 save_report_btn" />
				</div>
			</td>
		</tr>
	</table>
	<?php echo $options->as_form(false, true); ?>
	<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
</form>
</div>
<div id="options" style="overflow : auto">
<?php echo form::open($type.'/generate', array('id' => 'report_form', 'onsubmit' => 'return validate_report_form(this);'));?>
			<h1><?php echo _('Report settings') ?></h1>
			<table summary="Report settings" id="report" style="width: 350px">
				<tr class="none">
					<td>
						<?php echo _('Reporting period') ?><br />
						<?php echo form::dropdown(array('name' => 'report_period', 'onchange' => 'show_calendar(this.value);'), $options->get_alternatives('report_period'), $options['report_period']); ?>
					</td>
				</tr>
				<tr id="display" style="display: none; clear: both;" class="none fancydisplay">
					<td>
						<?php echo _('Start date') ?> (<span id="start_time_tmp"><?php echo _('Click calendar to select date') ?></span>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('start_time') ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
						<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $options->get_time('start_time') ?>">
						<br />
						<?php echo _('End date') ?> (<span id="end_time_tmp"><?php echo _('Click calendar to select date') ?></span>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('end_time') ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
						<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $options->get_time('end_time') ?>">
					</td>
				</tr>
				<tr class="none">
					<td><?php echo _('SLA calculation method') ?><br />
						<?php echo form::dropdown(array('name' => 'use_average'), $options->get_alternatives('use_average'), $options['use_average']) ?>
					</td>
				</tr>
				<tr class="none">
					<td>
						<label for="count" id="sched_downt" onclick="toggle_state('count');"><?php echo _('Count scheduled downtime as') ?></label><br />
						<?php echo form::dropdown(array('name' => 'scheduleddowntimeasuptime'), $options->get_alternatives('scheduleddowntimeasuptime'), $options['scheduleddowntimeasuptime']) ?>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="checkbox" value="1" class="checkbox" id="cluster_mode" name="cluster_mode" onchange="toggle_label_weight(this.checked, 'cluster_mode_label');" />
						<label for="cluster_mode" id="cluster_mode_label"><?php echo _('Cluster mode') ?></label>
					</td>
				</tr>
				<?php if (isset($extra_options)) {
					echo $extra_options;
				} ?>
				<tr class="none">
					<td>
						<div id="report_setup">
							<label for="report_name">Report name</label><br/><input type="text" name="report_name" id="report_name" class="input-save-name"
								value="<?php echo $options['report_name'] ?>" maxlength="255" />

							<?php
							$set_opts = $options->get_value('host_filter_status');
							foreach (Reports_Model::$host_states as $key => $name) {
								echo '<input type="hidden" name="host_filter_status['.$key.']" value="'.isset($set_opts[$key])."\"/>\n";
							}
							$set_opts = $options->get_value('service_filter_status');
							foreach (Reports_Model::$service_states as $key => $name) {
								echo '<input type="hidden" name="service_filter_status['.$key.']" value="'.isset($set_opts[$key])."\"/>\n";
							} ?>
						</div>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="button" name="s1" value="<?php echo _('Update report') ?>" class="button update-report20 save_report_btn" />
					</td>
				</tr>
			</table>
		<?php echo $options->as_form(false, true); ?>
		<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
	</form>
	</div>
<?php
# SLA form - only save report. No "update"
} else { ?>
<div id="sla_options">
<?php echo form::open($type.'/save', array('id' => 'report_form_sla', 'onsubmit' => 'return trigger_ajax_save(this);'));?>
	<h1><?php echo _('Save report') ?></h1>
	<table style="width: 350px">
		<tr class="none">
			<td style="vertical-align:middle"><label for="report_name" id="save_report_label"><?php echo _('Save as') ?></label></td>
			<td><div id="report_setup">
						<input type="text" name="report_name" id="report_name" class="input-save-name"
						value="<?php echo $options['report_name'] ?>" maxlength="255" style="margin: 0px" />
					<input type="button" name="s1" value="<?php echo (!empty($options['report_id'])) ? _('Update report') : _('Save') ?>" class="button update-report20 save_report_btn" />
				</div>
			</td>
		</tr>

	</table>
	<?php echo $options->as_form(false, true); ?>
	<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
</form>
</div>
<?php } ?>
</div>
