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
			<label for="report_period"><?php echo _('Reporting period') ?></label><br />
			<?php echo form::dropdown(array('name' => 'report_period', 'onchange' => 'show_calendar(this.value);'), $options->get_alternatives('report_period'), $options['report_period']); ?>
		</td>
		<td style="width: 18px">&nbsp;</td>
		<td>
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
		<td style="width: 18px">&nbsp;</td>
		<td>
			<label for="cal_end"><?php echo help::render('end-date').' '._('End date') ?> (<em><?php echo _('Click calendar to select date') ?></em>)</label><br />
			<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $options->get_date('end_time') ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
			<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $options->get_time('end_time') ?>">
		</td>
	</tr>
	<tr>
		<td>
			<label for="state_types"><?php echo _('State types to graph') ?></label><br />
			<?php echo form::dropdown('state_types', $options->get_alternatives('state_types'), $options['state_types']) ?>
		</td>
		<td style="width: 18px">&nbsp;</td>
		<td>
			<?php echo _('Events to graph') ?><br />
			<div data-show-for="hosts hostgroups">
				<?php echo form::dropdown('host_states', $options->get_alternatives('host_states'), $options['host_states']); ?>
			</div>
			<div data-show-for="services servicegroups">
				<?php echo form::dropdown('service_states', $options->get_alternatives('service_states'), $options['service_states']); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td>
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

