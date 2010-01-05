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
		<h1 id="log-h1" onclick="show_hide('log',this)"><?php echo ucfirst($type) ?> <?php echo $label_entries ?> <?php echo $source; ?></h1>
		<div class="icon-help" onclick="general_help('log_entries')"></div>
		<fieldset id="log">
			<form onsubmit="return false;" action=""><div><input type="text" size="60" name="filterbox" id="filterbox" value="Enter text to filter" /></div></form>
			<table id="log-table">
				<colgroup>
					<col class="col_label" />
					<col class="col_date" />
					<col class="col_date" />
					<col class="col_duration" />
					<col class="col_information" />
				</colgroup>
				<thead>
				<tr>
					<th>&nbsp;</th>
					<th>Start time</th>
					<th>End time</th>
					<th>Duration</th>
					<th>Information</th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ($log as $key => $value) {
					if (isset($value['state'])) {
					$event_end_time = $value['the_time'] + $value['duration'];
				?>
				<tr>
					<td class="label <? echo strtolower(${$type.'_state_txt'}[$value['state']]); ?>-left">
						<?php echo ucfirst(strtolower(${$type.'_state_txt'}[$value['state']])); ?>
					</td>
					<td><?php echo date('Y-m-d, H:i:s', $value['the_time']); ?></td>
					<td><?php echo date('Y-m-d, H:i:s', $event_end_time); ?></td>
					<td><?php echo time::to_string($value['duration']); ?></td>
					<td class="border-right"><?php echo $value['output']; ?></td>
				</tr>
				<?php } } ?>
				</tbody>
			</table>
		</fieldset>
	</div>
	<script type="text/javascript">
		document.getElementById('log').style.display='none';
		document.getElementById('log-h1').style.background = 'url(images/icons/arrows/grey.gif) 11px 3px no-repeat';
	</script>
<?php } ?>
