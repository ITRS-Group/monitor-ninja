<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

	<div class="widget left w98"><h2><?php echo $title ?></h2></div>

	<div class="widget left w49">
		<strong><?php echo $label_svc_actively_checked ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_time_frame ?></th>
				<th class="headerNone"><?php echo $label_services_checked ?></th>
			</tr>
			<tr class="even">
				<td>&le; 1 <?php echo $label_minute ?></td>
				<td><?php echo $svc_active_1min ?> (<?php echo $svc_active_1min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 5 <?php echo $label_minutes ?></td>
				<td><?php echo $svc_active_5min ?> (<?php echo $svc_active_5min_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td>&le; 15 <?php echo $label_minutes ?></td>
				<td><?php echo $svc_active_15min ?> (<?php echo $svc_active_15min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 1 <?php echo $label_hour ?></td>
				<td><?php echo $svc_active_1hour ?> (<?php echo $svc_active_1hour_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_since_program_start ?>&nbsp;&nbsp;</td>
				<td><?php echo $svc_active_start ?> (<?php echo $svc_active_start_perc ?> %)</td>
			</tr>
		</table>
		<br />
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_metric ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_min ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_max ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_average ?></th>
			</tr>
			<tr class="even">
				<td><?php echo $label_check_execution_time ?></td>
				<td><?php echo $min_service_execution_time ?> <?php echo $label_sec ?></td>
				<td><?php echo $max_service_execution_time ?> <?php echo $label_sec ?></td>
				<td><?php echo $svc_average_execution_time ?> <?php echo $label_sec ?></td>
			</tr>
			<tr class="odd">
				<td><?php echo $label_check_latency ?></td>
				<td><?php echo $min_service_latency ?> <?php echo $label_sec ?></td>
				<td><?php echo $min_service_latency ?> <?php echo $label_sec ?></td>
				<td><?php echo $average_service_latency ?> <?php echo $label_sec ?></td>
			</tr>
			<tr class="even">
				<td><?php echo $label_percent_state_change ?></td>
				<td><?php echo $min_service_percent_change_a ?> %</td>
				<td><?php echo $max_service_percent_change_a ?> %</td>
				<td><?php echo $average_service_percent_change ?> %</td>
			</tr>
		</table>
	</div>

	<div class="widget left w49">
		<strong><?php echo $label_svc_passively_checked ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_time_frame ?></th>
				<th class="headerNone"><?php echo $label_services_checked ?></th>
			</tr>
			<tr class="even">
				<td>&le; 1 <?php echo $label_minute ?></td>
				<td><?php echo $svc_passive_1min ?> (<?php echo $svc_passive_1min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 5 <?php echo $label_minutes ?></td>
				<td><?php echo $svc_passive_5min ?> (<?php echo $svc_passive_5min_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td>&le; 15 <?php echo $label_minutes ?></td>
				<td><?php echo $svc_passive_15min ?> (<?php echo $svc_passive_15min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 1 <?php echo $label_hour ?></td>
				<td><?php echo $svc_passive_1hour ?> (<?php echo $svc_passive_1hour_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_since_program_start ?></td>
				<td><?php echo $svc_passive_start ?> (<?php echo $svc_passive_start_perc ?> %)
				</td>
			</tr>
		</table>
		<br />
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_metric ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_min ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_max ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_average ?></th>
			</tr>
			<tr class="even">
				<td><?php echo $label_percent_state_change ?>&nbsp;&nbsp;</td>
				<td><?php echo $min_service_percent_change_b ?> %</td>
				<td><?php echo $max_service_percent_change_b ?> %</td>
				<td><?php echo $average_service_percent_change ?> %</td>
			</tr>
		</table>
	</div>

	<div style="clear: both"></div>
	<br />

	<div class="widget w49 left">
		<strong><?php echo $label_hosts_actively_checked ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_time_frame ?></th>
				<th class="headerNone"><?php echo $label_hosts_checked ?></th>
			</tr>
			<tr class="even">
				<td>&le; 1 <?php echo $label_minute ?></td>
				<td><?php echo $hst_active_1min ?> (<?php echo $hst_active_1min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 5 <?php echo $label_minutes ?></td>
				<td><?php echo $hst_active_5min ?> (<?php echo $hst_active_5min_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td>&le; 15 <?php echo $label_minutes ?></td>
				<td><?php echo $hst_active_15min ?> (<?php echo $hst_active_15min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 1 <?php echo $label_hour ?></td>
				<td><?php echo $hst_active_1hour ?> (<?php echo $hst_active_1hour_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_since_program_start ?></td>
				<td><?php echo $hst_active_start ?> (<?php echo $hst_active_start_perc ?> %)</td>
			</tr>
		</table>
		<br />
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_metric ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_min ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_max ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_average ?></th>
			</tr>
			<tr class="even">
				<td><?php echo $label_check_execution_time ?></td>
				<td><?php echo $min_host_execution_time ?> <?php echo $label_sec ?></td>
				<td><?php echo $max_host_execution_time ?> <?php echo $label_sec ?></td>
				<td><?php echo $average_host_execution_time ?> <?php echo $label_sec ?></td>
			</tr>
			<tr class="odd">
				<td><?php echo $label_check_latency ?></td>
				<td><?php echo $min_host_latency ?> <?php echo $label_sec ?></td>
				<td><?php echo $max_host_latency ?> <?php echo $label_sec ?></td>
				<td><?php echo $average_host_latency ?> <?php echo $label_sec ?></td>
			</tr>
			<tr class="even">
				<td><?php echo $label_percent_state_change ?></td>
				<td><?php echo $min_host_percent_change_a ?> %</td>
				<td><?php echo $max_host_percent_change_a ?> %</td>
				<td><?php echo $average_host_percent_change ?> %</td>
			</tr>
		</table>
	</div>

	<div class="widget left w49">
		<strong><?php echo $label_hosts_passively_checked ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_time_frame ?></th>
				<th class="headerNone"><?php echo $label_hosts_checked ?></th>
			</tr>
			<tr class="even">
				<td>&le; 1 <?php echo $label_minute ?></td>
				<td><?php echo $hst_passive_1min ?> (<?php echo $hst_passive_1min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 5 <?php echo $label_minutes ?></td>
				<td><?php echo $hst_passive_5min ?> (<?php echo $hst_passive_5min_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td>&le; 15 <?php echo $label_minutes ?></td>
				<td><?php echo $hst_passive_15min ?> (<?php echo $hst_passive_15min_perc ?> %)</td>
			</tr>
			<tr class="odd">
				<td>&le; 1 <?php echo $label_hour ?></td>
				<td><?php echo $hst_passive_1hour ?> (<?php echo $hst_passive_1hour_perc ?> %)</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_since_program_start ?></td>
				<td><?php echo $hst_passive_start ?> (<?php echo $hst_passive_start_perc ?> %)</td>
			</tr>
		</table>
		<br />
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_metric ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_min ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_max ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_average ?></th>
			</tr>
			<tr class="even">
				<td><?php echo $label_percent_state_change ?>&nbsp;&nbsp;</td>
				<td><?php echo $min_host_percent_change_b ?> %</td>
				<td><?php echo $max_host_percent_change_b ?> %</td>
				<td><?php echo $average_host_percent_change ?> %</td>
			</tr>
		</table>
	</div>

	<!--
	<div class="widget left w32">
		<br /><br />
		<strong><?php echo $label_check_statistics ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_type ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_last_1_min ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_last_5_min ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_last_15_min ?></th>
			</tr>
			<tr class="even">
				<td><?php echo $label_active_scheduled_host_check ?></td>
				<td>1</td>
				<td>9</td>
				<td>28</td>
			</tr>
			<tr class="odd">
				<td><?php echo $label_active_ondemand_host_check ?></td>
				<td>3</td>
				<td>14</td>
				<td>44</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_parallel_host_check ?></td>
				<td>1</td>
				<td>9</td>
				<td>28</td>
			</tr>
			<tr class="odd">
				<td><?php echo $label_serial_host_check ?></td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_cached_host_check ?></td>
				<td>3</td>
				<td>14</td>
				<td>44</td>
			</tr>
			<tr class="odd">
				<td><?php echo $label_passive_host_check ?></td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_active_scheduled_service_check ?></td>
				<td>10</td>
				<td>42</td>
				<td>130</td>
			</tr>
			<tr class="odd">
				<td><?php echo $label_active_ondemand_service_check ?></td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_cached_service_check ?></td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
			</tr>
			<tr class="odd">
				<td><?php echo $label_passive_service_check ?></td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_external_commands ?></td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
			</tr>
		</table>
	</div>

	<div class="widget left w32">
		<br /><br />
		<strong><?php echo $label_buffer_usage ?></strong>
		<table>
			<tr>
				<th style="width: 40%" class="headerNone"><?php echo $label_type ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_in_use ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_max_used ?></th>
				<th style="width: 20%" class="headerNone"><?php echo $label_total_available ?></th>
			</tr>
			<tr class="even">
				<td><?php echo $label_external_commands ?></td>
				<td>0</td>
				<td>0</td>
				<td>4096</td>
			</tr>
		</table>
	</div>-->