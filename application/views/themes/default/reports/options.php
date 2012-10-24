<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
$saved_reports_exists = false;
if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
	$saved_reports_exists = true;
}
?>
<div class="setup-table" id="settings_table">
<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
<h2><?php echo _('Report Settings'); ?></h2>
<hr />
<table id="report" class="setup-tbl">
	<caption><?php echo _('Enter the settings for your report') ?></caption>
	<tr>
		<td><?php echo help::render('reporting_period').' '._('Reporting period') ?></td>
		<td style="width: 18px">&nbsp;</td>
		<td><?php echo help::render('report_time_period').' '._('Report time period') ?></td>
	</tr>
	<tr>
		<td><?php echo form::dropdown(array('name' => 'report_period'), $options->get_alternatives('report_period'), $options['report_period']); ?></td>
		<td>&nbsp;</td>
		<td><?php echo form::dropdown(array('name' => 'rpttimeperiod'), $options->get_alternatives('rpttimeperiod'), $options['rpttimeperiod']); ?></td>
	</tr>
	<tr id="display" style="display: none; clear: both;">
		<td class="avail_display"<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>><?php echo help::render('start-date').' '._('Start date') ?> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
			<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('start_time') ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
			<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $options->get_time('start_time') ?>">
			<input type="hidden" name="start_time" id="start_time" value="<?php echo $options['start_time'] ?>" />
		</td>
		<td class="avail_display"<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>>&nbsp;</td>
		<td class="avail_display"<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>><?php echo help::render('end-date').' '._('End date') ?> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
			<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('end_time') ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
			<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $options->get_time('end_time') ?>">
			<input type="hidden" name="end_time" id="end_time" value="<?php echo $options['end_time'] ?>" />
		</td>
		<td class="sla_display"<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>>
			<?php echo help::render('start-date').' '._('Start date') ?>
			<table summary="Reporting time" style="margin-left: -4px">
				<tr>
					<td><?php echo _('Start year') ?></td>
					<td><select name="start_year" id="start_year"  style="width: 50px" onchange="js_print_date_ranges(this.value, 'start', 'month');"><option value=""></option></select></td>
					<td><?php echo _('Start month') ?></td>
					<td><select name="start_month" id="start_month" style="width: 50px" onchange="check_custom_months();"><option value=""></option></select></td>
				</tr>
			</table>
		</td>
		<td class="sla_display"<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>>&nbsp;</td>
		<td class="sla_display"<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>><?php echo help::render('end-date').' '._('End date') ?>
			<table summary="Reporting time" style="margin-left: -4px">
				<tr>
					<td><?php echo _('End year') ?></td>
					<td><select name="end_year" id="end_year" style="width: 50px" onchange="js_print_date_ranges(this.value, 'end', 'month');"><option value=""></option></select></td>
					<td><?php echo _('End month') ?></td>
					<td><select name="end_month" id="end_month" style="width: 50px" onchange="check_custom_months();"><option value=""></option></select></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo help::render('use_average').' '._('SLA calculation method') ?><br />
			<select name='use_average'>
				<option value='0' <?php print $options['use_average']?'':'selected="selected"' ?>><?php echo _('Group availability (SLA)') ?></option>
				<option value='1' <?php print $options['use_average']?'selected="selected"':'' ?>><?php echo _('Average') ?></option>
			</select>
		</td>
		<td>&nbsp;</td>
		<td>
			<?php echo help::render('status_to_display') ?>
			<?php echo _('States to hide'); ?><br>
			<div id="display_host_status">
			<?php
			foreach (Reports_Model::$host_states as $id => $name) {
				if ($name === 'excluded')
					continue;
				echo "<input type=\"checkbox\" name=\"host_filter_status[$id]\" id=\"host_filter_status[$id]\" value=\"".($type == 'sla'?0:-2).'" '.(isset($options['host_filter_status'][$id])?'checked="checked"':'')." style=\"margin-top: 4px; margin-left: 14px\"> <label for=\"host_filter_status[$id]\">".ucfirst($name)."</label>\n";
			} ?>
			</div>
			<div id="display_service_status">
			<?php
			foreach (Reports_Model::$service_states as $id => $name) {
				if ($name === 'excluded')
					continue;
				echo "<input type=\"checkbox\" name=\"service_filter_status[$id]\" id=\"service_filter_status[$id]\" value=\"".($type == 'sla'?0:-2).'" '.(isset($options['service_filter_status'][$id])?'checked="checked" ':'')." style=\"margin-top: 4px; margin-left: 14px\"> <label for=\"service_filter_status[$id]\">".ucfirst($name)."</label>\n";
			} ?>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo help::render('scheduled_downtime').' '._('Count scheduled downtime as')?>
		</td>
		<td>&nbsp;</td>
		<td>
			<?php echo help::render('use_alias') ?>
			<input type="checkbox" class="checkbox" value="1" id="use_alias" name="use_alias"
					onchange="toggle_label_weight(this.checked, 'usealias');" <?php print $options['use_alias']?'checked="checked"':'' ?> />
			<label for="use_alias" id="usealias"><?php echo _('Use alias') ?></label>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo form::dropdown(array('name' => 'scheduleddowntimeasuptime'), $options->get_alternatives('scheduleddowntimeasuptime'), $options['scheduleddowntimeasuptime']) ?>
		</td>
		<td>&nbsp;</td>
		<td>
			<?php echo help::render('cluster_mode') ?>
			<input type="checkbox" class="checkbox" value="1" id="cluster_mode" name="cluster_mode"
				onchange="toggle_label_weight(this.checked, 'clusterlbl');" <?php print $options['cluster_mode']?'checked="checked"':'' ?> />
			<label for="cluster_mode" id="clusterlbl"><?php echo _('Use cluster mode') ?></label>
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
			<?php echo help::render('includesoftstates') ?>
			<input type="checkbox" class="checkbox" value="1" id="includesoftstates" name="includesoftstates"
					onchange="toggle_label_weight(this.checked, 'include_softstates');" <?php echo $options['includesoftstates']?'checked="checked"':''; ?> />
			<label for="includesoftstates" id="include_softstates"><?php echo _('Include soft states') ?></label>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo help::render('include_alerts') ?>
			<input type="checkbox" class="checkbox" value="1" id="include_alerts" name="include_alerts"
					onchange="toggle_label_weight(this.checked, 'include_alerts');" <?php print $options['include_alerts']?'checked="checked"':''; ?> />
			<label for="include_alerts"><?php echo _('Include alerts log') ?></label>
		</td>
		<td></td>
		<td class="avail_display">
			<?php echo help::render('include_trends') ?>
			<input type="checkbox" class="checkbox" value="1" id="include_trends" name="include_trends"
					onchange="toggle_label_weight(this.checked, 'include_trends');" <?php print $options['include_trends']?'checked="checked"':''; ?> />
			<label for="include_trends"><?php echo _('Include trends graph') ?></label>
		</td>
	</tr>
	<?php if (isset($extra_content)) {
		echo $extra_content;
	} ?>
	<tr>
		<td>
			<?php echo help::render('skin') ?>
			<label for="skin" id="skin_lbl"><?php echo _('Skin') ?></label>
		</td>
		<td></td>
		<td>
			<?php echo help::render('description') ?>
			<label for="description" id="descr_lbl"><?php echo _('Description') ?></label>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo form::dropdown(array('name' => 'skin'), ninja::get_skins(), $options['skin']); ?>
		</td>
		<td></td>
		<td>
			<?php echo form::textarea('description', $options['description']); ?>
		</td>
	</tr>
</table>
</div>
<br />
<div class="setup-table<?php if ($type != 'sla') { ?> ui-helper-hidden<?php } ?>" id="enter_sla">
	<table style="width: 810px">
		<tr class="sla_values" <?php if (!$saved_reports_exists) { ?>style="display:none"<?php } ?>>
			<td style="padding-left: 0px" colspan="12"><?php echo help::render('use-sla-values'); ?> <?php echo _('Use SLA-values from saved report') ?></td>
		</tr>
		<tr class="sla_values" <?php if (!$saved_reports_exists) { ?>style="display:none"<?php } ?>>
			<td style="padding-left: 0px" colspan="12">
				<select name="sla_report_id" id="sla_report_id" onchange="get_sla_values()">
					<option value=""> - <?php echo _('Select saved report') ?> - </option>
					<?php
					foreach ($saved_reports as $info) {
						echo '<option '.(($options['report_id'] == $info->id) ? 'selected="selected"' : '').
							' value="'.$info->id.'">'.$info->report_name.'</option>'."\n";
					}  ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="padding-left: 0px" colspan="12"><?php echo help::render('enter-sla').' '._('Enter SLA') ?></td>
		</tr>
		<tr>
			<?php foreach ($months as $key => $month) { ?>
			<td style="padding-left: 0px">
				<?php echo html::image($this->add_path('icons/16x16/copy.png'),
					array(
						'id' => 'month_'.($key+1),
						'alt' => _('Click to propagate this value to all months'),
						'title' => _('Click to propagate this value to all months'),
						'style' => 'cursor: pointer; margin-bottom: -4px',
						'class' => 'autofill')
					) ?>
				<?php echo $month ?><br />
				<input type="text" size="2" class="sla_month" id="sla_month_<?php echo ($key+1) ?>" name="month_<?php echo ($key+1) ?>" value="<?php echo arr::search($options['months'], $key + 1, '') ?>" maxlength="6" /> %
			</td>
			<?php	} ?>
		</tr>
	</table>
</div>

<div class="setup-table">
	<input id="reports_submit_button" type="submit" name="" value="<?php echo _('Show report') ?>" class="button create-report" />
</div>
