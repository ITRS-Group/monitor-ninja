<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="report_block">
<h2><?php echo _('Report Mode'); ?></h2>
<hr />
<input type="hidden" name="type" value="<?php echo html::specialchars($type) ?>" />
<?php echo new View('reports/objselector'); ?>
</div>
<?php
if($options['report_id']) { ?>
<input type="hidden" name="report_id" value="<?php echo html::specialchars($options['report_id']) ?>" />
<?php } ?>
<div class="report-block">
<h2><?php echo _('Report Settings'); ?></h2>
<hr />
<table id="report" class="setup-tbl">
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
	<tr id="custom_time" style="display: none; clear: both;">
	<?php if ($type == 'avail') { ?>
		<td><label for="cal_start"><?php echo help::render('start-date').' '._('Start date') ?></label> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
			<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" value="<?php echo html::specialchars($options->get_date('start_time')) ?>" />
			<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo html::specialchars($options->get_time('start_time')) ?>" />
		</td>
		<td>&nbsp;</td>
		<td><label for="cal_end"><?php echo help::render('end-date').' '._('End date') ?></label> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
			<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" value="<?php echo html::specialchars($options->get_date('end_time')) ?>" />
			<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo html::specialchars($options->get_time('end_time')) ?>" />
		</td>
	<?php } else { ?>
		<td>
			<?php echo help::render('start-date').' '._('Start date') ?>
			<table summary="Reporting time" style="margin-left: -4px">
				<tr>
					<td><label for="start_year"><?php echo _('Start year') ?></label></td>
					<td><select name="start_year" id="start_year" class="auto"></select></td>
					<td><label for="start_month"><?php echo _('Start month') ?></label></td>
					<td><select name="start_month" id="start_month" class="auto"></select></td>
				</tr>
			</table>
			<?php echo help::render('end-date').' '._('End date') ?>
			<table summary="Reporting time" style="margin-left: -4px">
				<tr>
					<td><label for="end_year"><?php echo _('End year') ?></label></td>
					<td><select name="end_year" id="end_year" class="auto"></select></td>
					<td><label for="end_month"><?php echo _('End month') ?></label></td>
					<td><select name="end_month" id="end_month" class="auto"></select></td>
				</tr>
			</table>
		</td>
		<td colspan="2">&nbsp;</td>
	<?php } ?>
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
		<td class="states-to-include">
			<div data-show-for="hosts hostgroups">
				<?php
				echo help::render('host_states').' ';
				echo _('Host states to include').'<br/>';

				foreach ($options->get_alternatives('host_filter_status') as $id => $name) {
					$name = html::specialchars($name);
					$id = html::specialchars($id);
					echo "<input type=\"checkbox\" class=\"filter-status\" data-which=\"host_filter_map_$name\" id=\"host_filter_status_$name\" ".(isset($options['host_filter_status'][$id])?'':'checked="checked"')."/>";
					echo "<label for=\"host_filter_status_$name\">".ucfirst($name)."</label>\n";
				}
				echo '<div class="configure_mapping">';
				echo help::render('map_states');
				echo _("Mapping for excluded states")."<br/>";
				foreach ($options->get_alternatives('host_filter_status') as $id => $name) {
					$name = html::specialchars($name);
					$id = html::specialchars($id);
					echo "<div class=\"filter_map\" id=\"host_filter_map_$name\">";
					echo "<label for=\"host_filter_status[$id]\">".sprintf(_('Map %s to'), $name).'</label> ';
					$default = $type == 'sla' ? 0 : -2;
					if (isset($options['host_filter_status'][$id]))
						$default = $options['host_filter_status'][$id];
					echo form::dropdown(array('id' => "host_filter_status[$id]", 'name' => "host_filter_status[$id]", "style" => "width: auto;"), array_map('ucfirst', Reports_Model::$host_states), $default);
					echo '</div>';
				}
				echo '</div>';
				?>
			</div>
			<div data-show-for="services servicegroups">
				<?php
				echo help::render('service_states');
				echo _('Service states to include').'<br/>';

				foreach ($options->get_alternatives('service_filter_status') as $id => $name) {
					$name = html::specialchars($name);
					$id = html::specialchars($id);
					echo "<input type=\"checkbox\" class=\"filter-status\" data-which=\"service_filter_map_$name\" id=\"service_filter_status_$name\" ".(isset($options['service_filter_status'][$id])?'':'checked="checked" ')." />";
					echo "<label for=\"service_filter_status_$name\">".ucfirst($name)."</label>\n";
				}
				echo '<div class="configure_mapping">';
				echo help::render('map_states');
				echo _("Mapping for excluded states")."<br/>";
				foreach ($options->get_alternatives('service_filter_status') as $id => $name) {
					$name = html::specialchars($name);
					$id = html::specialchars($id);
					echo "<div class=\"filter_map\" id=\"service_filter_map_$name\">";
					echo "<label for=\"service_filter_status[$id]\">".sprintf(_('Map %s to'), ucfirst($name)).'</label> ';
					$default = $type == 'sla' ? 0 : -2;
					if (isset($options['service_filter_status'][$id]))
						$default = $options['service_filter_status'][$id];
					echo form::dropdown(array('id' => "service_filter_status[$id]",'name' => "service_filter_status[$id]", "style" => "width: auto;"), array_map('ucfirst', Reports_Model::$service_states), $default);
					echo '</div>';
				}
				echo '</div>';
				?>
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
			<label for="state_types"><?php echo help::render('state_types').' '._('State types') ?></label><br />
			<?php echo form::dropdown('state_types', $options->get_alternatives('state_types'), $options['state_types']) ?>
		</td>
		<td>&nbsp;</td>
		<td<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>>
			<label for="time_format"><?php echo help::render('time_format').' '._('Format time as') ?></label><br />
			<?php echo form::dropdown('time_format', $options->get_alternatives('time_format'), $options['time_format']) ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo help::render('include_alerts') ?>
			<input type="checkbox" class="checkbox" value="1" id="include_alerts" name="include_alerts"
					<?php print $options['include_alerts']?'checked="checked"':''; ?> />
			<label for="include_alerts"><?php echo _('Include alerts log') ?></label>
		</td>
		<td></td>
		<td>
			<?php echo help::render('use_alias') ?>
			<input type="checkbox" class="checkbox" value="1" id="use_alias" name="use_alias"
					<?php print $options['use_alias']?'checked="checked"':'' ?> />
			<label for="use_alias" id="usealias"><?php echo _('Use alias') ?></label>
		</td>
	</tr>
	<tr>
		<td<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>>
			<?php echo help::render('include_trends') ?>
			<input type="checkbox" class="checkbox" value="1" id="include_trends" name="include_trends"
					<?php print $options['include_trends']?'checked="checked"':''; ?> />
			<label for="include_trends"><?php echo _('Include trends graph') ?></label>
		</td>
		<td></td>
		<td<?php if ($type == 'sla') { ?> style="display:none"<?php } ?>>
			<?php echo help::render('piechart') ?>
			<input type="checkbox" class="checkbox" value="1" id="include_pie_charts" name="include_pie_charts"
					<?php print $options['include_pie_charts']?'checked="checked"':'' ?> />
			<label for="include_pie_charts" id="include_pie_charts_lbl"><?php echo _('Include pie charts') ?></label>
		</td>
	</tr>
	<tr<?php if ($type == 'sla' || !$options['include_pie_charts']) { ?> style="display:none"<?php } ?> class="trend_options">
		<td>
			<?php echo help::render('include_trends_scaling') ?> <input type="checkbox" class="checkbox" value="1" id="include_trends_scaling" name="include_trends_scaling" <?php print $options['include_trends_scaling']?'checked="checked"':''; ?> />
			<label for="include_trends_scaling"><?php echo _('Show trends re-scaling') ?></label>
		</td>
		<td></td>
		<td>
			<?php echo help::render('collapse_green_trends') ?> <input type="checkbox" class="checkbox" value="1" id="collapse_green_trends" name="collapse_green_trends" <?php print $options['collapse_green_trends']?'checked="checked"':''; ?> />
			<label for="collapse_green_trends"><?php echo _('Hide trends that are solid green') ?></label>
		</td>
	<?php
	if(ninja::has_module('synergy')) { ?>
	<tr>
		<td>
			<?php echo help::render('synergy_events'); ?>
			<input type="checkbox" name="include_synergy_events" id="include_synergy_events" value="1" <?php echo $options['include_synergy_events'] ? 'checked="checked"' : null ?> />
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
		<tr class="sla_values" <?php if (!$saved_reports) { ?>style="display:none"<?php } ?>>
			<td style="padding-left: 0px" colspan="12"><?php echo help::render('use-sla-values'); ?> <?php echo _('Use SLA-values from saved report') ?></td>
		</tr>
		<tr class="sla_values" <?php if (!$saved_reports) { ?>style="display:none"<?php } ?>>
			<td style="padding-left: 0px" colspan="12">
				<select name="sla_report_id" id="sla_report_id">
					<option value=""> - <?php echo _('Select saved report') ?> - </option>
					<?php
					foreach ($saved_reports as $id => $report_name) {
						echo '<option '.(($options['report_id'] == $id) ? 'selected="selected"' : '').
							' value="'.html::specialchars($id).'">'.html::specialchars($report_name).'</option>'."\n";
					}  ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="padding-left: 0px" colspan="12"><?php echo help::render('enter-sla').' '._('Enter SLA') ?></td>
		</tr>
		<tr>
		<?php
		foreach ($months as $key => $month_name) {
			$month_index = $key + 1;
			$val = arr::search($options['months'], $month_index, '');
			?>
			<td style="padding-left: 0px">
				<a href="#" title="Click to propagate this value to all months" class="autofill">
					<img src="<?php echo ninja::add_path('icons/16x16/copy.png') ?>" alt="Click to propagate this value to all months" />
				</a>
				<label for="month_<?php echo html::specialchars($month_index) ?>"><?php echo html::specialchars($month_name) ?></label><br />
				<input type="text" size="2" class="sla_month" id="month_<?php echo html::specialchars($month_index) ?>" name="month_<?php echo html::specialchars($month_index) ?>" value="<?php echo $val > 0 ? html::specialchars($val) : ''; ?>" maxlength="6" /> %
			</td>
		<?php } ?>
		</tr>
	</table>
</div>

<div class="setup-table">
	<input id="reports_submit_button" type="submit" name="" value="<?php echo _('Show report') ?>" class="button create-report" />
</div>
