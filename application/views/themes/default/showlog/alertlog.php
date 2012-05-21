<?php defined('SYSPATH') or die('No direct access allowed.');
	$date_format = cal::get_calendar_format(true); ?>
<div id="response"></div>
<div class="widget left w98">
<h1>Alert history
<?php
	$hosts_shown = true;
	if (!empty($options['hosts'])) {
		echo " for host " . $options['hosts'][0];
		$remember_object = form::hidden('host', $options['hosts'][0]);
	}
	if (!empty($options['hostgroups'])) {
		echo " for hostgroup " . $options['hostgroups'][0];
		$remember_object = form::hidden('hostgroup', $options['hostgroups'][0]);
	}
	if (!empty($options['services'])) {
		echo " for service " . $options['services'][0];
		$remember_object = form::hidden('service', $options['services'][0]);
		$hosts_shown = false;
	}
	if (!empty($options['servicegroups'])) {
		echo " for servicegroup " . $options['servicegroups'][0];
		$remember_object = form::hidden('servicegroup', $options['servicegroups'][0]);
		$hosts_shown = false;
	}
?>
</h1>
<?php
	echo form::open('showlog/'.Router::$method, array('id' => 'summary_form', 'method' => 'get'));
	if (isset($remember_object))
		echo $remember_object;
?>
	<table class="showlog">
		<tr>
			<td>
	<h3><?php echo _('State type options'); ?></h3>
	<label><?php echo form::checkbox('state_type[soft]', 1, isset($options['state_type']['soft'])).' '._('Soft states').'</label>'; ?><br />
	<label><?php echo form::checkbox('state_type[hard]', 1, isset($options['state_type']['hard'])).' '._('Hard states').'</label>'; ?>
	</td>
	<td>
	<h3><?php echo _('Host state options'); ?></h3>
		<?php
			$i = 0;
			foreach ($host_state_options as $k => $v) {
				$i++;
				$set = $hosts_shown && isset($options['host_state_options'][$v]);
				$name = 'host_state_options[' . $v . ']';
				echo '<label>'.form::checkbox($name, 1, $set, $hosts_shown ? false : 'disabled="disabled"').' '.$k.'</label><br />';
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
				echo '<label>'.form::checkbox($name,1, $set).' '.$k.'</label><br />';
			}
		?>
		</td><td>
		<h3><?php echo _('General options'); ?></h3>
		<?php echo '<label>'.form::checkbox('hide_downtime', 1, isset($options['hide_downtime'])).' '._('Hide downtime alerts'); ?></label><br />
		<?php echo $is_authorized ? '<label>'.form::checkbox('hide_process', 1, isset($options['hide_process'])).' '._('Hide process messages').'</label><br />' : ''; ?>
		<?php echo '<label>'.form::checkbox('parse_forward', 1, isset($options['parse_forward'])).' '._('Older entries first').'</label>'; ?>
		</td>
		</tr>
		<tr>
			<td colspan="2">
				<h3><?php echo _('First time') ?></h3> (<em id="start_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
				<input type="text" value="<?php echo isset($options['first']) && !empty($options['first']) ? date($date_format, $options['first']) : ''; ?>" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick datepick-start" title="<?php echo _('Date Start selector') ?>" />
				<input type="text" maxlength="5" name="time_start" id="time_start" class="time_start" value="<?php echo isset($options['first']) && !empty($options['first']) ? date('H:i', $options['first']) : ''; ?>">
			</td>
			<td colspan="2">
				<h3><?php echo _('Last time') ?></h3> (<em id="end_time_tmp"><?php echo _('Click calendar to select date') ?></em>)<br />
				<input type="text" value="<?php echo isset($options['last']) && !empty($options['last']) ? date($date_format, $options['last']) : ''; ?>" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick datepick-end" title="<?php echo _('Date Start selector') ?>" />
				<input type="text" maxlength="5" name="time_end" id="time_end" class="time_end" value="<?php echo isset($options['last']) && !empty($options['last']) ? date('H:i', $options['last']) : ''; ?>">
			</td>
		</tr>
		<tr>
			<td colspan="4">
			<?php
				echo form::hidden('first', '');
				echo form::hidden('last', '');
				echo form::hidden('have_options', 1);
				if (!empty($options['hosts'])) {
					foreach ($options['hosts'] as $h)
					echo form::hidden('host[]', $h);
				}
				if (!empty($options['services'])) {
					foreach ($options['services'] as $s) {
						echo form::hidden('service[]', $s);
					}
				}
				echo form::submit('Update', 'Update');
			?>
			</td>
		</tr>
		</table>
	</form>
	<form action="">
	<?php
		echo form::input(array('id' => 'filterbox', 'style' => 'color:grey', 'class' => 'filterboxfield'), $filter_string);
		echo form::button('clearfilterbox', _('Clear'));
	?>
	</form>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
<table id="showlog">
<?php
$headers = '<tr class="alertlog_header"><th></th><th colspan="7">%s:00</th></tr>';
$timeformat = nagstat::date_format();
$headertimestamp = substr($timeformat, 0, -4); // all known timeformats end with :i:s - strip
$lastheader = false;
if (count($entries) > 1)
	$lastheader = date($headertimestamp, $entries->current()->timestamp + 3599);
	printf('<tr class="alertlog_header"><th></th><th>%s:00</th><th>Alert type</th><th>Object</th><th>State</th><th>Hard?</th><th>Attempt</th><th>Output</th></tr>', $lastheader);
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
