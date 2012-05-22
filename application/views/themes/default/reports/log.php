<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($log)) {
	# *************************************************************
	# NOTE!
	#	These values can't be used as labels AND class names
	#	as before since they should be translated
	# *************************************************************
	$host_state_txt 	= array(-1 => 'PENDING', 0 => 'UP', 1 => 'DOWN', 2 => 'UNREACHABLE');
	$service_state_txt 	= array(-1 => 'PENDING', 0 => 'OK', 1 => 'WARNING', 2 => 'CRITICAL', 3 => 'UNKNOWN');
?>
	<div id="log_entries">
		<table id="log-table">
			<caption style="font-weight: bold"><?php echo help::render('log_entries').' '.ucfirst($type) ?> <?php echo $label_entries ?> <?php echo $source; ?><br /></caption>
			<thead>
			<tr>
				<th class="headerNone left"<?php echo _('Status');?></th>
				<th class="headerNone left" style="width: 110px"<?php echo _('Start time');?></th>
				<th class="headerNone left" style="width: 110px"<?php echo _('End time');?></th>
				<th class="headerNone left" style="width: 110px"<?php echo _('Duration');?></th>
				<th class="headerNone left"<?php echo _('Log message');?></th>
			</tr>
			</thead>
			<tbody>
			<?php //
			$i = 0;
			foreach ($log as $key => $value) {
				$i++;
				if (isset($value['state']) && $value['state'] != -2) {
				$event_end_time = $value['the_time'] + $value['duration'];
			?>
			<?php $bg_color = ($i%2 != 0) ? '#ffffff' : '#f2f2f2'; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
				<td>
					<?php
						echo html::image($this->add_path('icons/12x12/shield-'.strtolower(${$type.'_state_txt'}[$value['state']]).'.png'),
								 array('alt' => strtolower(${$type.'_state_txt'}[$value['state']]),'title' => strtolower(${$type.'_state_txt'}[$value['state']]),'style' => 'margin-bottom: -1px'));
						echo '&nbsp;'.ucfirst(strtolower(${$type.'_state_txt'}[$value['state']]));
					?>
				</td>
				<td><?php echo date($date_format_str, $value['the_time']); ?></td>
				<td><?php echo date($date_format_str, $event_end_time); ?></td>
				<td><?php echo time::to_string($value['duration']); ?></td>
				<td style="white-space: normal"><?php echo htmlspecialchars($value['output']); ?></td>
			</tr>
			<?php } } ?>
			</tbody>
		</table>
	</div>
<?php } ?>
