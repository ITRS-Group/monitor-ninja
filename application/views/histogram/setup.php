<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"></div>
<div id="progress"></div>

<style>
	table td {
		border: none;
	}
</style>

<div>

	<h1><?php echo _('Event History Report') ?></h1>

	<div id="histogram_report">
	<?php	echo form::open('histogram/generate', array('id' => 'histogram_form')); ?>
		<?php echo new View('reports/objselector'); ?>

		<div class="setup-table" >
			<table>
				<tr>
					<td>
						<?php echo _('Report Period') ?><br />
						<?php echo form::dropdown(array('name' => 'report_period'), $options->get_alternatives('report_period'), $options['report_period']); ?>
					</td>
					<td style="width: 18px">&nbsp;</td>
					<td>
						<?php echo _('State Types To Graph') ?><br />
						<?php echo form::dropdown('state_types', $options->get_alternatives('state_types')) ?>
					</td>

				</tr>
				<tr id="custom_time" style="display: none; clear: both;">
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
						<?php echo _('Statistics Breakdown') ?><br />
						<?php echo form::dropdown('breakdown', $options->get_alternatives('breakdown'), $options['breakdown']) ?>
					</td>
					<td style="width: 18px">&nbsp;</td>
					<td>
						<div data-show-for="hosts hostgroups">
							<?php echo _('Events To Graph') ?><br />
							<?php echo form::dropdown('host_states', $options->get_alternatives('host_states')) ?>
						</div>
						<div data-show-for="services servicegroups">
							<?php echo _('Events To Graph') ?><br />
							<?php echo form::dropdown('service_states', $options->get_alternatives('service_states')) ?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<?php echo form::checkbox('newstatesonly', 1, $options['newstatesonly']); ?>
					<?php echo _('Ignore Repeated States') ?>
					</td>
				</tr>
				<tr>
					<td colspan="3"><input id="reports_submit_button" type="submit" name="" value="<?php echo _('Create report') ?>" class="button create-report" /></td>
				</tr>
			</table>
		</div>
	<?php echo form::close(); ?>
	</div>
