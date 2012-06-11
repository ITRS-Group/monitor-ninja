<a href="#options" class="fancybox">
	<?php
		echo html::image($this->add_path('icons/32x32/square-edit.png'),
		array('alt' => _('edit settings'), 'title' => _('edit settings'), 'style' => 'position: absolute; right: 0px; top: 10px'))
	?>
</a>


<div id="options_container">
	<div id="options">
<?php	echo form::open('histogram/generate', array('id' => 'histogram_form', 'onsubmit' => 'return check_form_values(this);')); ?>
			<table summary="Report settings" id="report">
				<tr class="none">
					<td><?php echo form::dropdown(array('name' => 'report_period', 'onchange' => 'show_calendar(this.value);'), $options->get_alternatives('report_period'), $options['report_period']); ?></td>
				</tr>
				<tr>
					<td>
						<?php echo _('State Types To Graph') ?><br />
						<?php echo form::dropdown('state_types', $options->get_alternatives('state_types'), $options['state_types']) ?>
					</td>
				</tr>
				<tr id="display" style="display: none; clear: both;" class="none fancydisplay">
					<td>
						<?php echo _('Start date') ?> (<span id="start_time_tmp"><?php echo _('Click calendar to select date') ?></span>)<br />
						<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" value="<?php echo $start_date ?>" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
						<input type="hidden" name="start_time" id="start_time" value="<?php echo $start_date ?>" />
						<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo $start_time ?>">
						<br />
						<?php echo _('End date') ?> (<span id="end_time_tmp"><?php echo _('Click calendar to select date') ?></span>)<br />
						<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" value="<?php echo $end_date ?>" class="date-pick datepick-end" title="<?php echo _('Date End selector') ?>" />
						<input type="hidden" name="end_time" id="end_time" value="<?php echo $end_date ?>" />
						<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo $end_time ?>">
					</td>
				</tr>
				<tr>
					<td>
						<?php echo _('Statistics Breakdown')?><br />
						<?php echo form::dropdown('breakdown', $options->get_alternatives('breakdown'), $options['breakdown']) ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo _('Events To Graph') ?><br />
						<?php if ($sub_type == 'host') { ?>
							<?php echo form::dropdown('host_states', $options->get_alternatives('host_states'), $options['host_states']);
							} else { ?>
							<?php echo form::dropdown('service_states', $options->get_alternatives('service_states'), $options['service_states']);
							} ?>
						</div>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo form::checkbox('newstatesonly', 1, $options['newstatesonly']); ?>
					<?php echo _('Ignore Repeated States') ?>
					</td>
				</tr>
				<tr class="none">
					<td>
						<input type="submit" name="s1" value="<?php echo _('Update report') ?>" class="button update-report20" id="options_submit" />
					</td>
				</tr>
			</table>
			<?php echo $options->as_form(); ?>
		</form>
	</div>
</div>
