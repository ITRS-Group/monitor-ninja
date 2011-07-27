<?php defined('SYSPATH') or die('No direct access allowed.');
	$date_format = cal::get_calendar_format(true);
	$x = $this->translate; ?>
<div id="response"></div>
<?php echo form::open('showlog/'.Router::$method, array('id' => 'summary_form')); ?>
<div class="widget left w98">
	<table class="showlog">
		<tr>
			<td>
	<h3><?php echo $x->_('State type options'); ?></h3>
	<?php echo form::checkbox('state_type[soft]', 1, isset($options['state_type']['soft'])).' '.$x->_('Soft states'); ?><br />
	<?php echo form::checkbox('state_type[hard]', 1, isset($options['state_type']['hard'])).' '.$x->_('Hard states'); ?>
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
				echo form::checkbox($name, 1, $set).' '.$k.'<br />';
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
				echo form::checkbox($name,1, $set).' '.$k.'<br />';
				//echo ($i%2 == 0) ? '</tr>': ''."\n";
			}
		?>
		</td><td>
		<h3><?php echo $x->_('General options'); ?></h3>
		<?php echo form::checkbox('hide_downtime', 1, isset($options['hide_downtime'])).' '.$x->_('Hide downtime alerts'); ?><br />
		<?php echo $is_authorized ? form::checkbox('hide_process', 1, isset($options['hide_process'])).' '.$x->_('Hide process messages').'<br />' : ''; ?>
		<?php echo form::checkbox('parse_forward', 1, isset($options['parse_forward'])).' '.$x->_('Older entries first'); ?>
		</td>
		</tr>
		<tr>
			<td colspan="2">
				<h3><?php echo $x->_('First time') ?></h3> (<em id="start_time_tmp"><?php echo $x->_('Click calendar to select date') ?></em>)<br />
				<input type="text" value="<?php echo isset($options['first']) && !empty($options['first']) ? date($date_format, $options['first']) : ''; ?>" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick datepick-start" title="<?php echo $x->_('Date Start selector') ?>" />
				<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo isset($options['first']) && !empty($options['first']) ? date('H:i', $options['first']) : ''; ?>">
			</td>
			<td colspan="2">
				<h3><?php echo $x->_('Last time') ?></h3> (<em id="end_time_tmp"><?php echo $x->_('Click calendar to select date') ?></em>)<br />
				<input type="text" value="<?php echo isset($options['last']) && !empty($options['last']) ? date($date_format, $options['last']) : ''; ?>" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick datepick-end" title="<?php echo $x->_('Date Start selector') ?>" />
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

<?php echo (isset($pagination)) ? $pagination : ''; ?>
<table>
<?php
$headers = '<tr><th></th><th>%s:00</th><th>Alert type</th><th>Object</th><th>State</th><th>Hard?</th><th>Attempt</th><th>Output</th></tr>';
$timeformat = nagstat::date_format();
$headertimestamp = substr($timeformat, 0, -4); // all known timeformats end with :i:s - strip
$lastheader = false;
$evenodd = 'even';
foreach ($entries as $entry) {
	$parts = alertlog::get_user_friendly_representation($entry);
	$newheader = date($headertimestamp, $entry->timestamp + 3599);
	if ($newheader !== $lastheader) {
		printf($headers, $newheader);
		$lastheader = $newheader;
		$evenodd = 'even';
	}
	echo '<tr class="'.$evenodd.'">';
	echo "<td>{$parts['image']}</td>";
	echo '<td>'.date($timeformat, $entry->timestamp)."</td>\n";
	echo "<td>{$parts['type']}</td>\n";
	echo "<td>{$parts['obj_name']}</td>\n";
	echo "<td>".strtoupper($parts['state'])."</td>\n";
	echo "<td>{$parts['softorhard']}</td>\n";
	echo "<td>{$parts['retry']}</td>\n";
	echo "<td>$entry->output</td>\n";
	echo "</tr>\n";
	$evenodd = $evenodd === 'even' ? 'odd' : 'even';
}
?>
</table>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>
