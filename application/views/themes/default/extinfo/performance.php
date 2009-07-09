<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<br />
<div align="center">
	<div class='dataTitle'>
		<?php echo $title ?>
	</div>

	<table border='0' cellpadding='10'>
		<tr>
			<td valign="center">
				<div class='perfTypeTitle'>
					<?php echo $label_svc_actively_checked ?>:
				</div>
			</td>
			<td valign="top">
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable1'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_time_frame ?>
									</th>
									<th class='data'>
										<?php echo $label_services_checked ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 1 <?php echo $label_minute ?>:
									</td>
									<td class='dataVal'>
										<?php echo $svc_active_1min ?> (<?php echo $svc_active_1min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
									&lt;= 5 <?php echo $label_minutes ?>:
									</td>
									<td class='dataVal'>
									<?php echo $svc_active_5min ?> (<?php echo $svc_active_5min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 15 <?php echo $label_minutes ?>:
									</td>
									<td class='dataVal'>
										<?php echo $svc_active_15min ?> (<?php echo $svc_active_15min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 1 <?php echo $label_hour ?>:
									</td>
									<td class='dataVal'>
										<?php echo $svc_active_1hour ?> (<?php echo $svc_active_1hour_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_since_program_start ?>:&nbsp;&nbsp;
									</td>
									<td class='dataVal'>
										<?php echo $svc_active_start ?> (<?php echo $svc_active_start_perc ?>%)
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
			<td valign="top">
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable2'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_metric ?>
									</th>
									<th class='data'>
										<?php echo $label_min ?>
									</th>
									<th class='data'>
										<?php echo $label_max ?>
									</th>
									<th class='data'>
									<?php echo $label_average ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_check_execution_time ?>:&nbsp;&nbsp;
									</td>
									<td class='dataVal'>
										<?php echo $min_service_execution_time ?> <?php echo $label_sec ?>
									</td>
									<td class='dataVal'>
										<?php echo $max_service_execution_time ?> <?php echo $label_sec ?>
									</td>
									<td class='dataVal'>
										<?php echo $svc_average_execution_time ?> <?php echo $label_sec ?>
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_check_latency ?>:
									</td>
									<td class='dataVal'>
										<?php echo $min_service_latency ?> <?php echo $label_sec ?>
									</td>
									<td class='dataVal'>
										<?php echo $min_service_latency ?> <?php echo $label_sec ?>
									</td>
									<td class='dataVal'>
										<?php echo $average_service_latency ?> <?php echo $label_sec ?>
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_percent_state_change ?>:
									</td>
									<td class='dataVal'>
										<?php echo $min_service_percent_change_a ?>%
									</td>
									<td class='dataVal'>
										<?php echo $max_service_percent_change_a ?>%
									</td>
									<td class='dataVal'>
										<?php echo $average_service_percent_change ?>%
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="center">
				<div class='perfTypeTitle'>
					<?php echo $label_svc_passively_checked ?>:
				</div>
			</td>
			<td valign="top">
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable1'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_time_frame ?>
									</th>
									<th class='data'>
										<?php echo $label_services_checked ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 1 <?php echo $label_minute ?>:
									</td>
									<td class='dataVal'>
										<?php echo $svc_passive_1min ?> (<?php echo $svc_passive_1min_perc ?>%)
									</td>

								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 5 <?php echo $label_minutes ?>:
									</td>
									<td class='dataVal'>
										<?php echo $svc_passive_5min ?> (<?php echo $svc_passive_5min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 15 <?php echo $label_minutes ?>:
									</td>
									<td class='dataVal'>
										<?php echo $svc_passive_15min ?> (<?php echo $svc_passive_15min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 1 <?php echo $label_hour ?>:
									</td>
									<td class='dataVal'>
										<?php echo $svc_passive_1hour ?> (<?php echo $svc_passive_1hour_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_since_program_start ?>:&nbsp;&nbsp;
									</td>
									<td class='dataVal'>
										<?php echo $svc_passive_start ?> (<?php echo $svc_passive_start_perc ?>%)
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
			<td valign="top">
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable2'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_metric ?>
									</th>
									<th class='data'>
										<?php echo $label_min ?>
									</th>
									<th class='data'>
										<?php echo $label_max ?>
									</th>
									<th class='data'>
										<?php echo $label_average ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_percent_state_change ?>:&nbsp;&nbsp;
									</td>
									<td class='dataVal'>
										<?php echo $min_service_percent_change_b ?>%
									</td>
									<td class='dataVal'>
										<?php echo $max_service_percent_change_b ?>%
									</td>
									<td class='dataVal'>
										<?php echo $average_service_percent_change ?>%
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="center">
				<div class='perfTypeTitle'>
					<?php echo $label_hosts_actively_checked ?>:
				</div>
			</td>
			<td valign="top">
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable1'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_time_frame ?>
									</th>
									<th class='data'>
										<?php echo $label_hosts_checked ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 1 <?php echo $label_minute ?>:
									</td>
									<td class='dataVal'>
										<?php echo $hst_active_1min ?> (<?php echo $hst_active_1min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 5 <?php echo $label_minutes ?>:
									</td>
									<td class='dataVal'>
										<?php echo $hst_active_5min ?> (<?php echo $hst_active_5min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 15 <?php echo $label_minutes ?>:
									</td>
									<td class='dataVal'>
										<?php echo $hst_active_15min ?> (<?php echo $hst_active_15min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 1 <?php echo $label_hour ?>:
									</td>
									<td class='dataVal'>
										<?php echo $hst_active_1hour ?> (<?php echo $hst_active_1hour_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_since_program_start ?>:&nbsp;&nbsp;
									</td>
									<td class='dataVal'>
										<?php echo $hst_active_start ?> (<?php echo $hst_active_start_perc ?>%)
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
			<td valign="top">
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable2'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_metric ?>
									</th>
									<th class='data'>
										<?php echo $label_min ?>
									</th>
									<th class='data'>
										<?php echo $label_max ?>
									</th>
									<th class='data'>
										<?php echo $label_average ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_check_execution_time ?>:&nbsp;&nbsp;
									</td>
									<td class='dataVal'>
										<?php echo $min_host_execution_time ?> <?php echo $label_sec ?>
									</td>
									<td class='dataVal'>
										<?php echo $max_host_execution_time ?> <?php echo $label_sec ?>
									</td>
									<td class='dataVal'>
										<?php echo $average_host_execution_time ?> <?php echo $label_sec ?>
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_check_latency ?>:
									</td>
									<td class='dataVal'>
										<?php echo $min_host_latency ?> <?php echo $label_sec ?>
									</td>
									<td class='dataVal'>
										<?php echo $max_host_latency ?> <?php echo $label_sec ?>
									</td>
									<td class='dataVal'>
										<?php echo $average_host_latency ?> <?php echo $label_sec ?>
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_percent_state_change ?>:
									</td>
									<td class='dataVal'>
										<?php echo $min_host_percent_change_a ?>%
									</td>
									<td class='dataVal'>
										<?php echo $max_host_percent_change_a ?>%
									</td>
									<td class='dataVal'>
										<?php echo $average_host_percent_change ?>%
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="center">
				<div class='perfTypeTitle'>
					<?php echo $label_hosts_passively_checked ?>:
				</div>
			</td>
			<td valign="top">
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable1'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_time_frame ?>
									</th>
									<th class='data'>
										<?php echo $label_hosts_checked ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 1 <?php echo $label_minute ?>:
									</td>
									<td class='dataVal'>
										<?php echo $hst_passive_1min ?> (<?php echo $hst_passive_1min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 5 <?php echo $label_minutes ?>:
									</td>
									<td class='dataVal'>
										<?php echo $hst_passive_5min ?> (<?php echo $hst_passive_5min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 15 <?php echo $label_minutes ?>:
									</td>
									<td class='dataVal'>
										<?php echo $hst_passive_15min ?> (<?php echo $hst_passive_15min_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										&lt;= 1 <?php echo $label_hour ?>:
									</td>
									<td class='dataVal'>
										<?php echo $hst_passive_1hour ?> (<?php echo $hst_passive_1hour_perc ?>%)
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_since_program_start ?>:&nbsp;&nbsp;
									</td>
									<td class='dataVal'>
										<?php echo $hst_passive_start ?> (<?php echo $hst_passive_start_perc ?>%)
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
			<td valign="top">
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable2'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_metric ?>
									</th>
									<th class='data'>
										<?php echo $label_min ?>
									</th>
									<th class='data'>
										<?php echo $label_max ?>
									</th>
									<th class='data'>
										<?php echo $label_average ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_percent_state_change ?>:&nbsp;&nbsp;
									</td>
									<td class='dataVal'>
										<?php echo $min_host_percent_change_b ?>%
									</td>
									<td class='dataVal'>
										<?php echo $max_host_percent_change_b ?>%
									</td>
									<td class='dataVal'>
										<?php echo $average_host_percent_change ?>%
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
<!--		<tr>
			<td valign="center">
				<div class='perfTypeTitle'>
					<?php echo $label_check_statistics ?>:
				</div>
			</td>
			<td valign="top" colspan='2'>
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable1'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_type ?>
									</th>
									<th class='data'>
										<?php echo $label_last_1_min ?>
									</th>
									<th class='data'>
										<?php echo $label_last_5_min ?>
									</th>
									<th class='data'>
										<?php echo $label_last_15_min ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_active_scheduled_host_check ?>
									</td>
									<td class='dataVal'>
										1
									</td>
									<td class='dataVal'>
										9
									</td>
									<td class='dataVal'>
										28
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_active_ondemand_host_check ?>
									</td>
									<td class='dataVal'>
										3
									</td>
									<td class='dataVal'>
										14
									</td>
									<td class='dataVal'>
										44
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_parallel_host_check ?>
									</td>
									<td class='dataVal'>
										1
									</td>
									<td class='dataVal'>
										9
									</td>
									<td class='dataVal'>
										28
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_serial_host_check ?>
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_cached_host_check ?>
									</td>
									<td class='dataVal'>
										3
									</td>
									<td class='dataVal'>
										14
									</td>
									<td class='dataVal'>
										44
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_passive_host_check ?>
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_active_scheduled_service_check ?>
									</td>
									<td class='dataVal'>
										10
									</td>
									<td class='dataVal'>
										42
									</td>
									<td class='dataVal'>
										130
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_active_ondemand_service_check ?>
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_cached_service_check ?>
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_passive_service_check ?>
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_external_commands ?>
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="center">
				<div class='perfTypeTitle'>
					<?php echo $label_buffer_usage ?>:
				</div>
			</td>
			<td valign="top" colspan='2'>
				<table border="1" cellspacing="0" cellpadding="0">
					<tr>
						<td class='stateInfoTable1'>
							<table border="0">
								<tr class='data'>
									<th class='data'>
										<?php echo $label_type ?>
									</th>
									<th class='data'>
										<?php echo $label_in_use ?>
									</th>
									<th class='data'>
										<?php echo $label_max_used ?>
									</th>
									<th class='data'>
										<?php echo $label_total_available ?>
									</th>
								</tr>
								<tr>
									<td class='dataVar'>
										<?php echo $label_external_commands ?>&nbsp;
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										0
									</td>
									<td class='dataVal'>
										4096
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>-->
	</table>
</div>
