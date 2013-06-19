<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
$saved_reports_exists = false;
if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
	$saved_reports_exists = true;
}
if($options['report_id']) { ?>
<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
<?php } ?>
<div class="setup-table" id="settings_table">
<h2><?php echo _('Report Settings'); ?></h2>
<hr />
<table id="report" class="setup-tbl">
	<caption><?php echo _('Enter the settings for your report') ?></caption>
	<tr>
		<td><label for="report_period"><?php echo help::render('reporting_period').' '._('Reporting period') ?></label></td>
		<td style="width: 18px">&nbsp;</td>
		<td><label for="rpttimeperiod"><?php echo help::render('report_time_period').' '._('Report time period') ?></label></td>
	</tr>
	<tr>
		<td><?php echo form::dropdown(array('name' => 'report_period'), $options->get_alternatives('report_period'), $options['report_period']); ?></td>
		<td>&nbsp;</td>
		<td><?php echo form::dropdown(array('name' => 'rpttimeperiod'), $options->get_alternatives('rpttimeperiod'), $options['rpttimeperiod']); ?></td>
	</tr>
	<tr id="display" style="display: none; clear: both;">
		<td <?php if ($type == 'sla') { ?> style="display:none"<?php } ?>><label for="cal_start"><?php echo help::render('start-date').' '._('Start date') ?></label> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
			<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" value="<?php echo $options->get_date('start_time') ?>" />
			<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $options->get_time('start_time') ?>" />
		</td>
		<td<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>>&nbsp;</td>
		<td<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>><label for="cal_end"><?php echo help::render('end-date').' '._('End date') ?></label> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
			<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" value="<?php echo $options->get_date('end_time') ?>" />
			<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $options->get_time('end_time') ?>" />
		</td>
		<td<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>>
			<?php echo help::render('start-date').' '._('Start date') ?>
			<table summary="Reporting time" style="margin-left: -4px">
				<tr>
					<td><label for="start_year"><?php echo _('Start year') ?></label></td>
					<td><select name="start_year" id="start_year" class="auto" onchange="js_print_date_ranges(this.value, 'start', 'month');"><option value=""></option></select></td>
					<td><label for="start_month"><?php echo _('Start month') ?></label></td>
					<td><select name="start_month" id="start_month" class="auto" onchange="check_custom_months();"><option value=""></option></select></td>
				</tr>
			</table>
		</td>
		<td<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>>&nbsp;</td>
		<td<?php if ($type == 'avail') { ?> style="display:none"<?php } ?>><?php echo help::render('end-date').' '._('End date') ?>
			<table summary="Reporting time" style="margin-left: -4px">
				<tr>
					<td><label for="end_year"><?php echo _('End year') ?></label></td>
					<td><select name="end_year" id="end_year" class="auto" onchange="js_print_date_ranges(this.value, 'end', 'month');"><option value=""></option></select></td>
					<td><label for="end_month"><?php echo _('End month') ?></label></td>
					<td><select name="end_month" id="end_month" class="auto" onchange="check_custom_months();"><option value=""></option></select></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<label for="sla_mode"><?php echo help::render('sla_mode').' '._('SLA calculation method') ?></label><br />
			<select id="sla_mode" name='sla_mode'>
				<option value='0' <?php print $options['sla_mode'] == 0?'selected="selected"':'' ?>><?php echo _('Group availability (worst state)') ?></option>
				<option value='1' <?php print $options['sla_mode'] == 1?'selected="selected"':'' ?>><?php echo _('Average') ?></option>
				<option value='2' <?php print $options['sla_mode'] == 2?'selected="selected"':'' ?>><?php echo _('Cluster mode (best state)') ?></option>
			</select>
		</td>
		<td>&nbsp;</td>
		<td>
			<?php echo help::render('status_to_display') ?>
			<?php echo _('States to hide'); ?><br>
			<div data-show-for="hosts hostgroups">
			<?php
			foreach (Reports_Model::$host_states as $id => $name) {
				if ($name === 'excluded')
					continue;
				echo "<input type=\"checkbox\" name=\"host_filter_status[$id]\" id=\"host_filter_status[$id]\" value=\"".($type == 'sla'?0:Reports_Model::HOST_EXCLUDED).'" '.(isset($options['host_filter_status'][$id])?'checked="checked"':'')." style=\"margin-top: 4px; margin-left: 14px\"> <label for=\"host_filter_status[$id]\">".ucfirst($name)."</label>\n";
			} ?>
			</div>
			<div data-show-for="services servicegroups">
			<?php
			foreach (Reports_Model::$service_states as $id => $name) {
				if ($name === 'excluded')
					continue;
				echo "<input type=\"checkbox\" name=\"service_filter_status[$id]\" id=\"service_filter_status[$id]\" value=\"".($type == 'sla'?0:Reports_Model::SERVICE_EXCLUDED).'" '.(isset($options['service_filter_status'][$id])?'checked="checked" ':'')." style=\"margin-top: 4px; margin-left: 14px\"> <label for=\"service_filter_status[$id]\">".ucfirst($name)."</label>\n";
			} ?>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<label for="scheduleddowntimeasuptime"><?php echo help::render('scheduled_downtime').' '._('Count scheduled downtime as')?></label>
		</td>
		<td>&nbsp;</td>
		<td>
			<label for="assumestatesduringnotrunning"><?php echo help::render('stated_during_downtime').' '._('Count program downtime as')?></label>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo form::dropdown(array('name' => 'scheduleddowntimeasuptime'), $options->get_alternatives('scheduleddowntimeasuptime'), $options['scheduleddowntimeasuptime']) ?>
		</td>
		<td>&nbsp;</td>
		<td>
		<?php
			echo form::dropdown(array('name' => 'assumestatesduringnotrunning'), array(0 => 'Undetermined', 1 => 'Assume previous state'), (int)$options['assumestatesduringnotrunning']);
		?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo help::render('includesoftstates') ?>
			<input type="checkbox" class="checkbox" value="1" id="includesoftstates" name="includesoftstates"
					onchange="toggle_label_weight(this.checked, 'include_softstates');" <?php echo $options['includesoftstates']?'checked="checked"':''; ?> />
			<label for="includesoftstates" id="include_softstates"><?php echo _('Include soft states') ?></label>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			<?php echo help::render('include_alerts') ?>
			<input type="checkbox" class="checkbox" value="1" id="include_alerts" name="include_alerts"
					onchange="toggle_label_weight(this.checked, 'include_alerts');" <?php print $options['include_alerts']?'checked="checked"':''; ?> />
			<label for="include_alerts"><?php echo _('Include alerts log') ?></label>
		</td>
		<td></td>
		<td>
			<?php echo help::render('use_alias') ?>
			<input type="checkbox" class="checkbox" value="1" id="use_alias" name="use_alias"
					onchange="toggle_label_weight(this.checked, 'usealias');" <?php print $options['use_alias']?'checked="checked"':'' ?> />
			<label for="use_alias" id="usealias"><?php echo _('Use alias') ?></label>
		</td>
	</tr>
	<tr>
		<td<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>>
			<?php echo help::render('include_trends') ?>
			<input type="checkbox" class="checkbox" value="1" id="include_trends" name="include_trends"
					onchange="toggle_label_weight(this.checked, 'include_trends');" <?php print $options['include_trends']?'checked="checked"':''; ?> />
			<label for="include_trends"><?php echo _('Include trends graph') ?></label><br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo help::render('include_trends_scaling') ?> <input type="checkbox" class="checkbox" value="1" id="include_trends_scaling" name="include_trends_scaling"
					onchange="toggle_label_weight(this.checked, 'include_trends_scaling');" disabled="true" <?php print $options['include_trends_scaling']?'checked="checked"':''; ?> />
			<label for="include_trends_scaling"><?php echo _('Show trends re-scaling') ?></label>
		</td>
		<td></td>
		<td<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>>
			<?php echo help::render('piechart') ?>
			<input type="checkbox" class="checkbox" value="1" id="include_pie_charts" name="include_pie_charts"
					onchange="toggle_label_weight(this.checked, 'include_pie_charts');" <?php print $options['include_pie_charts']?'checked="checked"':'' ?> />
			<label for="include_pie_charts" id="include_pie_charts"><?php echo _('Include pie charts') ?></label>
		</td>
	</tr>
	<?php
	if(ninja::has_module('synergy')) { ?>
	<tr>
		<td>
			<?php echo help::render('synergy_events'); ?>
			<input type="checkbox" name="include_synergy_events" id="include_synergy_events" <?php echo $options['include_synergy_events'] ? 'checked="checked"' : null ?> />
			<label for="include_synergy_events"><?php echo _('Include BSM events'); ?></label>
		</td>
		<td></td>
		<td></td>
	</tr>
	<?php
	}
	if (isset($extra_content)) {
		echo $extra_content;
	} ?>
	<tr>
		<td>
			<br />
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
		<td style="vertical-align: top;">
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
				<a href="#" title="Click to propagate this value to all months" class="autofill">
					<img src="<?php echo $this->add_path('icons/16x16/copy.png') ?>" alt="Click to propagate this value to all months" />
				</a>
				<label for="month_<?php echo $key+1 ?>"><?php echo $month ?></label><br />
				<input type="text" size="2" class="sla_month" id="month_<?php echo ($key+1) ?>" name="month_<?php echo ($key+1) ?>" value="<?php echo arr::search($options['months'], $key + 1, '') ?>" maxlength="6" /> %
			</td>
			<?php	} ?>
		</tr>
	</table>
</div>

<div class="setup-table">
	<input id="reports_submit_button" type="submit" name="" value="<?php echo _('Show report') ?>" class="button create-report" />
</div>
