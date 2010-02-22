<?php defined('SYSPATH') or die('No direct access allowed.');
	echo html::script('application/media/js/jscalendar/calendar.js');
	echo html::script('application/media/js/jscalendar/lang/calendar-en.js');
	echo html::script('application/media/js/jscalendar/calendar-setup.js');
	echo html::script('application/media/js/calendar-extras.js');
	echo html::stylesheet('application/media/js/jscalendar/skins/aqua/theme.css');
	$x = $this->translate;
	//echo "\$options = ".Kohana::debug($options);
	echo form::open();
?>
<div class="widget left w98">
	<table class="showlog">
		<tr>
			<td>
	<h3><?php echo $x->_('State type options'); ?></h3>
	<?php echo form::checkbox('state_types[soft]', isset($options['state_types']['soft'])).' '.$x->_('Soft states'); ?><br />
	<?php echo form::checkbox('state_types[hard]', isset($options['state_types']['hard'])).' '.$x->_('Hard states'); ?>
	</td>
	<td>
	<h3><?php echo $x->_('Host state options'); ?></h3>
		<?php
			$i = 0;
			foreach ($host_state_options as $k => $v) {
				$i++;
				$set = isset($options['host_state_options'][$v]);
				$name = 'host_state_options[' . $v . ']';
				//echo ($i%2 == 0) ? '': '<tr>';
				echo form::checkbox($name, $set).' '.$k.'<br />';
				//echo ($i%2 == 0) ? '</tr>': ''."\n";
			}
		?>
		</td><td>
		<h3><?php echo $x->_('Service state options'); ?></h3>
		<?php
			$i = 0;
			foreach ($service_state_options as $k => $v) {
				$set = isset($options['service_state_options'][$v]);
				$i++;
				$name = 'service_state_options[' . $v . ']';
				//echo ($i%2 == 0) ? '': '<tr>';
				echo form::checkbox($name, $set).' '.$k.'<br />';
				//echo ($i%2 == 0) ? '</tr>': ''."\n";
			}
		?>
		</td><td>
		<h3><?php echo $x->_('General options'); ?></h3>
		<?php echo form::checkbox('hide_flapping').' '.$x->_('Hide flapping alerts'); ?><br />
		<?php echo form::checkbox('hide_downtime').' '.$x->_('Hide downtime alerts'); ?><br />
		<?php echo form::checkbox('hide_process').' '.$x->_('Hide process messages'); ?><br />
		<?php echo form::checkbox('parse_forward').' '.$x->_('Older entries first'); ?>
		</td>
		</tr>
		<tr>
		<td colspan="2">
			<h3><?php echo $x->_('First time').'</h3>'.form::input('first'); ?>
			<?php echo html::image($this->add_path('icons/16x16/calendar.png'),'First'); ?>
				<script type="text/javascript">
				Calendar.setup({
					inputField      : 'first',           // id of the input field
					ifFormat        : '%Y-%m-%d %H:%M',  // format of the input field
					daFormat        : '%Y-%m-%d %H:%M',  // format of the displayed field
					button          : 'f_trigger_start', // trigger for the calendar (button ID)
					align           : 'Bl',              // alignment (defaults to "Bl")
					timeFormat      : '24',
					showsTime       : true,
					displayArea     : 'start_time_tmp',
					firstDay        : 1,
					onClose         : store_start_date,
					singleClick     : true
				});
				</script>
			</td>
			<td colspan="2">
				<h3><?php echo $x->_('Last time').'</h3>'.form::input('last'); ?>
				<?php echo html::image($this->add_path('icons/16x16/calendar.png'),'First'); ?>
				<script type="text/javascript">
				Calendar.setup({
					inputField      : 'last',            // id of the input field
					ifFormat        : '%Y-%m-%d %H:%M',  // format of the input field
					daFormat        : '%Y-%m-%d %H:%M',  // format of the displayed field
					button          : 'f_trigger_end',   // trigger for the calendar (button ID)
					align           : 'Bl',              // alignment (defaults to "Bl")
					timeFormat      : '24',
					showsTime       : true,
					displayArea     : 'end_time_tmp',
					firstDay        : 1,
					onClose         : check_start_date,
					singleClick     : true
				});
				</script>
			</td>
		</tr>
		<tr>
			<td colspan="4">
			<?php
				echo form::hidden('have_options', 1);
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