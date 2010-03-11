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
		<!--<form onsubmit="return false;" action=""><div><input type="text" size="60" name="filterbox" id="filterbox" value="Enter text to filter" /></div></form>-->
		<table id="log-table" <?php echo ($create_pdf) ? 'style="border: 1px solid #cdcdcd" cellpadding="5"' : '';?>>
			<caption><?php echo help::render('log_entries').' '.ucfirst($type) ?> <?php echo $label_entries ?> <?php echo $source; ?><br /></caption>
			<thead>
			<tr>
				<th <?php echo ($create_pdf) ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left"';?>><?php echo $this->translate->_('Status');?></th>
				<th <?php echo ($create_pdf) ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left" style="width: 110px"';?>><?php echo $this->translate->_('Start time');?></th>
				<th <?php echo ($create_pdf) ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left" style="width: 110px"';?>><?php echo $this->translate->_('End time');?></th>
				<th <?php echo ($create_pdf) ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left" style="width: 110px"';?>><?php echo $this->translate->_('Duration');?></th>
				<th <?php echo ($create_pdf) ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left"';?>><?php echo $this->translate->_('Log message');?></th>
			</tr>
			</thead>
			<tbody>
			<?php //
			$i = 0;
			foreach ($log as $key => $value) {
				$i++;
				if (isset($value['state'])) {
				$event_end_time = $value['the_time'] + $value['duration'];
			?>
			<?php $bg_color = ($i%2 == 0) ? '#fafafa' : '#f2f2f2'; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
				<td <?php echo ($create_pdf) ? 'style="font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>>
					<?php
						echo html::image($this->add_path('icons/12x12/shield-'.strtolower(${$type.'_state_txt'}[$value['state']]).'.png'),
								 array('alt' => strtolower(${$type.'_state_txt'}[$value['state']]),'title' => strtolower(${$type.'_state_txt'}[$value['state']]),'style' => 'margin-bottom: -1px'));
						echo '&nbsp;'.ucfirst(strtolower(${$type.'_state_txt'}[$value['state']]));
					?>
				</td>
				<td <?php echo ($create_pdf) ? 'style="font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo date('Y-m-d, H:i:s', $value['the_time']); ?></td>
				<td <?php echo ($create_pdf) ? 'style="font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo date('Y-m-d, H:i:s', $event_end_time); ?></td>
				<td <?php echo ($create_pdf) ? 'style="font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo time::to_string($value['duration']); ?></td>
				<td <?php echo ($create_pdf) ? 'style="font-size: 0.9em; background-color: '.$bg_color.'"' : 'style="white-space: normal"'; ?>><?php echo $value['output']; ?></td>
			</tr>
			<?php } } ?>
			</tbody>
		</table>
	</div>
<?php } ?>