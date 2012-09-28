<?php defined('SYSPATH') or die('No direct access allowed.');
	$date_format = cal::get_calendar_format(true); ?>
<div id="response"></div>
<?php echo form::open('showlog/'.Router::$method, array('id' => 'summary_form', 'method' => 'get')); ?>
<div class="widget left w98">
	<table class="showlog">
		<tr>
			<td>
	<h3><?php echo _('State type options'); ?></h3>
	<label><?php echo form::checkbox('state_type[soft]', 1, isset($options['state_type']['soft'])).' '._('Soft states'); ?></label><br />
	<label><?php echo form::checkbox('state_type[hard]', 1, isset($options['state_type']['hard'])).' '._('Hard states'); ?></label>
	</td>
	<td>
	<h3><?php echo _('Host state options'); ?></h3>
		<?php
			$i = 0;
			foreach ($host_state_options as $k => $v) {
				$i++;
				$set = isset($options['host_state_options'][$v]);
				$name = 'host_state_options[' . $v . ']';
				//echo ($i%2 == 0) ? '': '<tr>';
				echo "<label>".form::checkbox($name, 1, $set).' '.$k.'</label><br />';
				//echo ($i%2 == 0) ? '</tr>': ''."\n";
			}
		?>
		</td><td>
		<h3><?php echo _('Service state options'); ?></h3>
		<?php
			$i = 0;
			foreach ($service_state_options as $k => $v) {
				$set = isset($options['service_state_options'][$v]);
				$i++;
				$name = 'service_state_options[' . $v . ']';
				//echo ($i%2 == 0) ? '': '<tr>';
				echo '<label>'.form::checkbox($name,1, $set).' '.$k.'</label><br />';
				//echo ($i%2 == 0) ? '</tr>': ''."\n";
			}
		?>
		</td><td>
		<h3><?php echo _('General options'); ?></h3>
		<label><?php echo form::checkbox('hide_flapping', 1, isset($options['hide_flapping'])).' '._('Hide flapping alerts'); ?></label><br />
		<label><?php echo form::checkbox('hide_downtime', 1, isset($options['hide_downtime'])).' '._('Hide downtime alerts'); ?></label><br />
		<?php echo $is_authorized ? '<label>'.form::checkbox('hide_process', 1, isset($options['hide_process'])).' '._('Hide process messages').'</label><br />' : ''; ?>
		<label><?php echo form::checkbox('hide_initial', 1, isset($options['hide_initial'])).' '._('Hide initial and current states'); ?></label><br />
		<label><?php echo form::checkbox('hide_logrotation', 1, isset($options['hide_logrotation'])).' '._('Hide logrotation messages'); ?></label><br />
		<?php echo $is_authorized ? '<label>'.form::checkbox('hide_commands', 1, isset($options['hide_commands'])).' '._('Hide external commands').'</label><br />' : ''; ?>
		<label><?php echo form::checkbox('parse_forward', 1, isset($options['parse_forward'])).' '._('Older entries first'); ?>
		</td>
		</tr>
		<tr>
			<td colspan="2">
				<h3><?php echo _('First time') ?></h3> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
				<input type="text" value="<?php echo isset($options['first']) && !empty($options['first']) ? date($date_format, $options['first']) : ''; ?>" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick datepick-start" title="<?php echo _('Start date') ?>" />
				<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo isset($options['first']) && !empty($options['first']) ? date('H:i', $options['first']) : ''; ?>">
			</td>
			<td colspan="2">
				<h3><?php echo _('Last time') ?></h3> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
				<input type="text" value="<?php echo isset($options['last']) && !empty($options['last']) ? date($date_format, $options['last']) : ''; ?>" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick datepick-end" title="<?php echo _('Start date') ?>" />
				<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo isset($options['last']) && !empty($options['last']) ? date('H:i', $options['last']) : ''; ?>">
			</td>
		</tr>
		<tr>
			<td colspan="4">
			<?php
				echo form::hidden('first', '');
				echo form::hidden('last', '');
				echo form::hidden('have_options', 1);
				if (isset($options['host'])) {
					foreach ($options['host'] as $h)
					echo form::hidden('host[]', $h);
				}
				if (isset($options['service'])) {
					foreach ($options['service'] as $s) {
						echo form::hidden('service[]', $s);
					}
				}
				echo form::submit('Update', 'Update');
			?>
			</td>
		</tr>
		</table>
	<?php echo form::close(); ?>

<?php
	# this hidden thing marks "no options chosen" as a chosen set of options
	# so we avoid overriding "no options" with the default ones

	$this->_show_log_entries();
?>
</div>
