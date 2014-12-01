<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
			<tr>
				<td><label for="report_period"><?php echo help::render('reporting_period').' '._('Reporting period') ?></label></td>
				<td>&nbsp;</td>
				<td><label for="rpttimeperiod"><?php echo help::render('report_time_period').' '._('Report time period') ?></label></td>
			</tr>
			<tr>
				<td><?php echo form::dropdown('report_period', $options->get_alternatives('report_period'), $options['report_period']); ?></td>
				<td>&nbsp;</td>
				<td><?php echo form::dropdown(array('name' => 'rpttimeperiod'), $options->get_alternatives('rpttimeperiod'), $options['rpttimeperiod']); ?></td>
			</tr>
			<tr id="custom_time" style="display: none; clear: both;">
				<td><label for="cal_start"><?php echo help::render('start-date', 'reports').' '._('Start date') ?></label> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
					<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('start_time') ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
					<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $options->get_time('start_time') ?>">
				</td>
				<td>&nbsp;</td>
				<td><label for="cal_end"><?php echo help::render('end-date', 'reports').' '._('End date') ?></label> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
					<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('end_time') ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
					<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $options->get_time('end_time') ?>">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo help::render('host_states') ?><?php echo _('Host states'); ?><br/>
				<?php
					foreach ($options->get_alternatives('host_filter_status') as $id => $name) {
						echo "<span class=\"filter_map\" id=\"host_filter_map_$name\">";
						echo "<input type=\"hidden\" name=\"host_filter_status[$id]\" value=\"-2\"/>";
						echo "</span>";
						echo "<input type=\"checkbox\" class=\"filter-status\" data-which=\"host_filter_map_$name\" id=\"host_filter_status_$name\" ".(isset($options['host_filter_status'][$id])?'':'checked="checked"')."/>";
						echo "<label for=\"host_filter_status_$name\">".ucfirst($name)."</label>\n";
					} ?>
				</td>
				<td>&nbsp;</td>
				<td>
					<?php echo help::render('service_states').' '._('Service states') ?><br />
					<?php
					foreach ($options->get_alternatives('service_filter_status') as $id => $name) {
						echo "<span class=\"filter_map\" id=\"service_filter_map_$name\">";
						echo "<input type=\"hidden\" name=\"service_filter_status[$id]\" value=\"-2\"/>";
						echo "</span>";
						echo "<input type=\"checkbox\" class=\"filter-status\" data-which=\"service_filter_map_$name\" id=\"service_filter_status_$name\" ".(isset($options['service_filter_status'][$id])?'':'checked="checked" ')." />";
						echo "<label for=\"service_filter_status_$name\">".ucfirst($name)."</label>\n";
					} ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="state_types"><?php echo help::render('state_types').' '._('State types') ?></label><br />
					<?php echo form::dropdown('state_types', $options->get_alternatives('state_types'), $options['state_types']) ?>
				</td>
				<td>&nbsp;</td>
				<td>
					<?php echo help::render('include_long_output') ?>
					<input type="checkbox" name="include_long_output" id="include_long_output" <?php echo $options['include_long_output'] ? 'checked="checked"' : null ?> />
					<label for="include_long_output"><?php echo _('Include full output') ?></label>
				</td>
			</tr>
