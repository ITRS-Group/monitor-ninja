
<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

	<div class="widget left w98"><h2><?php echo $title ?></h2></div>

	<div class="widget left w48">
		<strong><?php echo _('Services actively checked') ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Time frame') ?></th>
				<th class="headerNone"><?php echo _('Services checked') ?></th>
			</tr>
			<tr class="even">
				<td>&le; 1 <?php echo _('minute') ?></td>
				<td><?php echo $svc_active_1min ?> (<?php echo $svc_active_1min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 5 <?php echo _('minutes') ?></td>
				<td><?php echo $svc_active_5min ?> (<?php echo $svc_active_5min_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td>&le; 15 <?php echo _('minutes') ?></td>
				<td><?php echo $svc_active_15min ?> (<?php echo $svc_active_15min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 1 <?php echo _('hour') ?></td>
				<td><?php echo $svc_active_1hour ?> (<?php echo $svc_active_1hour_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td><?php echo _('Since program start') ?>&nbsp;&nbsp;</td>
				<td><?php echo $svc_active_start ?> (<?php echo $svc_active_start_perc ?> %)</td>
			</tr>
		</table>
		<br />
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Metric') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Min.') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Max.') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Average') ?></th>
			</tr>
			<tr class="even">
				<td><?php echo _('Check execution Time') ?></td>
				<td><?php echo $min_service_execution_time ?> <?php echo _('sec') ?></td>
				<td><?php echo $max_service_execution_time ?> <?php echo _('sec') ?></td>
				<td><?php echo $svc_average_execution_time ?> <?php echo _('sec') ?></td>
			</tr>
			<tr class="odd">
				<td><?php echo _('Check latency') ?></td>
				<td><?php echo $min_service_latency ?> <?php echo _('sec') ?></td>
				<td><?php echo $max_service_latency ?> <?php echo _('sec') ?></td>
				<td><?php echo $average_service_latency ?> <?php echo _('sec') ?></td>
			</tr>
			<tr class="even">
				<td><?php echo _('Percent state change') ?></td>
				<td><?php echo $min_service_percent_change_a ?> %</td>
				<td><?php echo $max_service_percent_change_a ?> %</td>
				<td><?php echo $average_service_percent_change ?> %</td>
			</tr>
		</table>
	</div>

	<div class="widget left w49">
		<strong><?php echo _('Services passively checked') ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Time frame') ?></th>
				<th class="headerNone"><?php echo _('Services checked') ?></th>
			</tr>
			<tr class="even">
				<td>&le; 1 <?php echo _('minute') ?></td>
				<td><?php echo $svc_passive_1min ?> (<?php echo $svc_passive_1min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 5 <?php echo _('minutes') ?></td>
				<td><?php echo $svc_passive_5min ?> (<?php echo $svc_passive_5min_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td>&le; 15 <?php echo _('minutes') ?></td>
				<td><?php echo $svc_passive_15min ?> (<?php echo $svc_passive_15min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 1 <?php echo _('hour') ?></td>
				<td><?php echo $svc_passive_1hour ?> (<?php echo $svc_passive_1hour_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td><?php echo _('Since program start') ?></td>
				<td><?php echo $svc_passive_start ?> (<?php echo $svc_passive_start_perc ?> %)
				</td>
			</tr>
		</table>
		<br />
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Metric') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Min.') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Max.') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Average') ?></th>
			</tr>
			<tr class="even">
				<td><?php echo _('Percent state change') ?>&nbsp;&nbsp;</td>
				<td><?php echo $min_service_percent_change_b ?> %</td>
				<td><?php echo $max_service_percent_change_b ?> %</td>
				<td><?php echo $average_service_percent_change ?> %</td>
			</tr>
		</table>
	</div>

	<div style="clear: both"></div>
	<br />

	<div class="widget w48 left">
		<strong><?php echo _('Hosts actively checked') ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Time frame') ?></th>
				<th class="headerNone"><?php echo _('Hosts checked') ?></th>
			</tr>
			<tr class="even">
				<td>&le; 1 <?php echo _('minute') ?></td>
				<td><?php echo $hst_active_1min ?> (<?php echo $hst_active_1min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 5 <?php echo _('minutes') ?></td>
				<td><?php echo $hst_active_5min ?> (<?php echo $hst_active_5min_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td>&le; 15 <?php echo _('minutes') ?></td>
				<td><?php echo $hst_active_15min ?> (<?php echo $hst_active_15min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 1 <?php echo _('hour') ?></td>
				<td><?php echo $hst_active_1hour ?> (<?php echo $hst_active_1hour_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td><?php echo _('Since program start') ?></td>
				<td><?php echo $hst_active_start ?> (<?php echo $hst_active_start_perc ?> %)</td>
			</tr>
		</table>
		<br />
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Metric') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Min.') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Max.') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Average') ?></th>
			</tr>
			<tr class="even">
				<td><?php echo _('Check execution Time') ?></td>
				<td><?php echo $min_host_execution_time ?> <?php echo _('sec') ?></td>
				<td><?php echo $max_host_execution_time ?> <?php echo _('sec') ?></td>
				<td><?php echo $average_host_execution_time ?> <?php echo _('sec') ?></td>
			</tr>
			<tr class="odd">
				<td><?php echo _('Check latency') ?></td>
				<td><?php echo $min_host_latency ?> <?php echo _('sec') ?></td>
				<td><?php echo $max_host_latency ?> <?php echo _('sec') ?></td>
				<td><?php echo $average_host_latency ?> <?php echo _('sec') ?></td>
			</tr>
			<tr class="even">
				<td><?php echo _('Percent state change') ?></td>
				<td><?php echo $min_host_percent_change_a ?> %</td>
				<td><?php echo $max_host_percent_change_a ?> %</td>
				<td><?php echo $average_host_percent_change ?> %</td>
			</tr>
		</table>
	</div>

	<div class="widget left w49">
		<strong><?php echo _('Hosts passively checked') ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Time frame') ?></th>
				<th class="headerNone"><?php echo _('Hosts checked') ?></th>
			</tr>
			<tr class="even">
				<td>&le; 1 <?php echo _('minute') ?></td>
				<td><?php echo $hst_passive_1min ?> (<?php echo $hst_passive_1min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 5 <?php echo _('minutes') ?></td>
				<td><?php echo $hst_passive_5min ?> (<?php echo $hst_passive_5min_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td>&le; 15 <?php echo _('minutes') ?></td>
				<td><?php echo $hst_passive_15min ?> (<?php echo $hst_passive_15min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 1 <?php echo _('hour') ?></td>
				<td><?php echo $hst_passive_1hour ?> (<?php echo $hst_passive_1hour_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td><?php echo _('Since program start') ?></td>
				<td><?php echo $hst_passive_start ?> (<?php echo $hst_passive_start_perc ?> %)</td>
			</tr>
		</table>
		<br />
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Metric') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Min.') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Max.') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Average') ?></th>
			</tr>
			<tr class="even">
				<td><?php echo _('Percent state change') ?>&nbsp;&nbsp;</td>
				<td><?php echo $min_host_percent_change_b ?> %</td>
				<td><?php echo $max_host_percent_change_b ?> %</td>
				<td><?php echo $average_host_percent_change ?> %</td>
			</tr>
		</table>
	</div>


<div style="clear: both"></div>

	<div class="widget left w48">
		<br />
		<strong><?php echo _('Check statistics') ?></strong>
		<table style="margin-bottom: 15px">
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Type') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Last 1 min') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Last 5 min') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Last 15 min') ?></th>
			</tr>
			<tr class="even">
				<td><?php echo _('Active scheduled host checks') ?></td>
				<?php if (isset($active_scheduled_host_check_stats) && !empty($active_scheduled_host_check_stats)) {
					foreach ($active_scheduled_host_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="odd">
				<td><?php echo _('Active on-demand host checks') ?></td>
				<?php if (isset($active_ondemand_host_check_stats) && !empty($active_ondemand_host_check_stats)) {
					foreach ($active_ondemand_host_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="even">
				<td><?php echo _('Parallel host checks') ?></td>
				<?php if (isset($parallel_host_check_stats) && !empty($parallel_host_check_stats)) {
					foreach ($parallel_host_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="odd">
				<td><?php echo _('Serial host checks') ?></td>
				<?php if (isset($serial_host_check_stats) && !empty($serial_host_check_stats)) {
					foreach ($serial_host_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="even">
				<td><?php echo _('Cached host checks') ?></td>
				<?php if (isset($cached_host_check_stats) && !empty($cached_host_check_stats)) {
					foreach ($cached_host_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="odd">
				<td><?php echo _('Passive host checks') ?></td>
				<?php if (isset($passive_host_check_stats) && !empty($passive_host_check_stats)) {
					foreach ($passive_host_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="even">
				<td><?php echo _('Active scheduled service checks') ?></td>
				<?php if (isset($active_scheduled_service_check_stats) && !empty($active_scheduled_service_check_stats)) {
					foreach ($active_scheduled_service_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="odd">
				<td><?php echo _('Active on-demand service checks') ?></td>
				<?php if (isset($active_ondemand_service_check_stats) && !empty($active_ondemand_service_check_stats)) {
					foreach ($active_ondemand_service_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="even">
				<td><?php echo _('Cached service checks') ?></td>
				<?php if (isset($cached_service_check_stats) && !empty($cached_service_check_stats)) {
					foreach ($cached_service_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="odd">
				<td><?php echo _('Passive service checks') ?></td>
				<?php if (isset($passive_service_check_stats) && !empty($passive_service_check_stats)) {
					foreach ($passive_service_check_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
			<tr class="even">
				<td><?php echo _('External commands') ?></td>
				<?php if (isset($external_command_stats) && !empty($external_command_stats)) {
					foreach ($external_command_stats as $val) {?>
					<td><?php echo $val ?></td>
				<?php }
					} else echo '<td>0</td><td>0</td><td>0</td>' ?>
			</tr>
		</table>
	</div>

		<div class="widget left w49">
		<br />
		<strong><?php echo _('Buffer usage') ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo _('Type') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('In use') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Max used') ?></th>
				<th style="width: 20%" class="headerNone"><?php echo _('Total available') ?></th>
			</tr>
			<tr class="even">
				<td><?php echo _('External commands') ?></td>
				<td><?php echo isset($used_external_command_buffer_slots) && !empty($used_external_command_buffer_slots) ? $used_external_command_buffer_slots : 0 ?></td>
				<td><?php echo isset($high_external_command_buffer_slots) && !empty($high_external_command_buffer_slots) ? $high_external_command_buffer_slots : 0 ?></td>
				<td><?php echo isset($total_external_command_buffer_slots) && !empty($total_external_command_buffer_slots) ? $total_external_command_buffer_slots : 0 ?></td>
			</tr>
		</table>
	</div>
