<div class="report_block">
<h2><?php echo _('Report Mode'); ?></h2>
<hr />
<input type="hidden" name="type" value="<?php echo $type ?>" />
<?php echo new View('reports/objselector'); ?>
</div>

<?php
if($options['report_id']) { ?>
<input type="hidden" name="report_id" value="<?php echo $options['report_id'] ?>" />
<?php } ?>

<div class="report-block">
<h2><?php echo _('Report Settings'); ?></h2>
<hr />
<table id="report" class="setup-tbl">
	<tr>
		<td>
			<?php echo help::render('reporting_period') ?>
			<label for="report_period"><?php echo _('Reporting period') ?></label><br />
			<?php echo form::dropdown(array('name' => 'report_period', 'onchange' => 'show_calendar(this.value);'), $options->get_alternatives('report_period'), $options['report_period']); ?>
		</td>
		<td>&nbsp;</td>
		<td>
			<?php echo help::render('breakdown') ?>
			<label for="breakdown"><?php echo _('Statistics breakdown')?></label><br />
			<?php echo form::dropdown('breakdown', $options->get_alternatives('breakdown'), $options['breakdown']) ?>
		</td>
	</tr>
	<tr id="custom_time" style="display: none; clear: both;">
		<td>
			<label for="cal_start"><?php echo help::render('start-date').' '._('Start date') ?> (<em><?php echo _('Click calendar to select date') ?></em>)</label><br />
			<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('start_time') ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
			<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $options->get_time('start_time') ?>">
		</td>
		<td>&nbsp;</td>
		<td>
			<label for="cal_end"><?php echo help::render('end-date').' '._('End date') ?> (<em><?php echo _('Click calendar to select date') ?></em>)</label><br />
			<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('end_time') ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
			<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $options->get_time('end_time') ?>">
		</td>
	</tr>
	<tr>
		<td>
			<label for="state_types"><?php echo help::render('state_types').' '._('State types') ?></label><br />
			<?php echo form::dropdown('state_types', $options->get_alternatives('state_types'), $options['state_types']) ?>
		</td>
		<td>&nbsp;</td>
		<td>
			<div data-show-for="hosts hostgroups">
				<?php
				echo help::render('host_states');
				echo _('Events to graph').'<br />';
				foreach ($options->get_alternatives('host_filter_status') as $id => $name) {
					echo "<span class=\"filter_map\" id=\"host_filter_map_$name\">";
					echo "<input type=\"hidden\" name=\"host_filter_status[$id]\" value=\"-2\"/>";
					echo "</span>";
					echo "<input type=\"checkbox\" class=\"filter-status\" data-which=\"host_filter_map_$name\" id=\"host_filter_status_$name\" ".(isset($options['host_filter_status'][$id])?'':'checked="checked"')."/>";
					echo "<label for=\"host_filter_status_$name\">".ucfirst($name)."</label>\n";
				} ?>
			</div>
			<div data-show-for="services servicegroups">
				<?php
				echo help::render('service_states');
				echo _('Events to graph').'<br />';
				foreach ($options->get_alternatives('service_filter_status') as $id => $name) {
					echo "<span class=\"filter_map\" id=\"service_filter_map_$name\">";
					echo "<input type=\"hidden\" name=\"service_filter_status[$id]\" value=\"-2\"/>";
					echo "</span>";
					echo "<input type=\"checkbox\" class=\"filter-status\" data-which=\"service_filter_map_$name\" id=\"service_filter_status_$name\" ".(isset($options['service_filter_status'][$id])?'':'checked="checked" ')." />";
					echo "<label for=\"service_filter_status_$name\">".ucfirst($name)."</label>\n";
				} ?>
			</div>
		</td>
	</tr>
	<tr>
		<td>
		<?php echo help::render('newstatesonly') ?>
		<?php echo form::checkbox('newstatesonly', 1, $options['newstatesonly']); ?>
		<label for="newstatesonly"><?php echo _('Ignore repeated states') ?></label>
		</td>
	</tr>
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

<div class="setup-table">
<input id="reports_submit_button" type="submit" name="" value="<?php echo _('Show report') ?>" class="button create-report" />
</div>

