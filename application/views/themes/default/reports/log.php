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

		<!--<div class="icon-help" onclick="general_help('log_entries')"></div>-->
			<!--<form onsubmit="return false;" action=""><div><input type="text" size="60" name="filterbox" id="filterbox" value="Enter text to filter" /></div></form>-->
			<table id="log-table">
				<caption><?php echo ucfirst($type) ?> <?php echo $label_entries ?> <?php echo $source; ?></caption>
				<thead>
				<tr>
					<th class="headerNone left"><?php echo $this->translate->_('Status');?></th>
					<th class="headerNone left" style="width: 110px"><?php echo $this->translate->_('Start time');?></th>
					<th class="headerNone left" style="width: 110px"><?php echo $this->translate->_('End time');?></th>
					<th class="headerNone left" style="width: 100px"><?php echo $this->translate->_('Duration');?></th>
					<th class="headerNone left"><?php echo $this->translate->_('Log message');?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				$i = 0;
				foreach ($log as $key => $value) {
					$i++;
					if (isset($value['state'])) {
					$event_end_time = $value['the_time'] + $value['duration'];
				?>
				<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
					<td>
						<?php
							echo html::image($this->add_path('icons/12x12/shield-'.strtolower(${$type.'_state_txt'}[$value['state']]).'.png'),
									 array('alt' => strtolower(${$type.'_state_txt'}[$value['state']]),'title' => strtolower(${$type.'_state_txt'}[$value['state']]),'style' => 'margin-bottom: -1px'));
							echo '&nbsp;'.ucfirst(strtolower(${$type.'_state_txt'}[$value['state']]));
						?>
					</td>
					<td><?php echo date('Y-m-d, H:i:s', $value['the_time']); ?></td>
					<td><?php echo date('Y-m-d, H:i:s', $event_end_time); ?></td>
					<td><?php echo time::to_string($value['duration']); ?></td>
					<td style="white-space: normal"><?php echo $value['output']; ?></td>
				</tr>
				<?php } } ?>
				</tbody>
			</table>
	</div>
	<script type="text/javascript">
		document.getElementById('log').style.display='none';
		document.getElementById('log-h1').style.background = 'url(images/icons/arrows/grey.gif) 11px 3px no-repeat';
	</script>
<?php } ?>
