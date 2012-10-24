
<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

	<h2><?php echo $title ?></h2>

	<hr />

	<div>
		<strong><?php echo _('Services actively checked') ?></strong>
		<table>
			<tr>
				<th style="width: 40%"><?php echo _('Time frame') ?></th>
				<th><?php echo _('Services checked') ?></th>
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
				<th style="width: 40%"><?php echo _('Metric') ?></th>
				<th style="width: 20%"><?php echo _('Min.') ?></th>
				<th style="width: 20%"><?php echo _('Max.') ?></th>
				<th style="width: 20%"><?php echo _('Average') ?></th>
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
	<br />
	<div>
		<strong><?php echo _('Services passively checked') ?></strong>
		<table>
			<tr>
				<th style="width: 40%"><?php echo _('Time frame') ?></th>
				<th><?php echo _('Services checked') ?></th>
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
				<th style="width: 40%"><?php echo _('Metric') ?></th>
				<th style="width: 20%"><?php echo _('Min.') ?></th>
				<th style="width: 20%"><?php echo _('Max.') ?></th>
				<th style="width: 20%"><?php echo _('Average') ?></th>
			</tr>
			<tr class="even">
				<td><?php echo _('Percent state change') ?>&nbsp;&nbsp;</td>
				<td><?php echo $min_service_percent_change_b ?> %</td>
				<td><?php echo $max_service_percent_change_b ?> %</td>
				<td><?php echo $average_service_percent_change ?> %</td>
			</tr>
		</table>
	</div>

	<br />

	<div>
		<strong><?php echo _('Hosts actively checked') ?></strong>
		<table>
			<tr>
				<th style="width: 40%"><?php echo _('Time frame') ?></th>
				<th><?php echo _('Hosts checked') ?></th>
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
				<th style="width: 40%"><?php echo _('Metric') ?></th>
				<th style="width: 20%"><?php echo _('Min.') ?></th>
				<th style="width: 20%"><?php echo _('Max.') ?></th>
				<th style="width: 20%"><?php echo _('Average') ?></th>
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
	<br />
	<div>
		<strong><?php echo _('Hosts passively checked') ?></strong>
		<table>
			<tr>
				<th style="width: 40%"><?php echo _('Time frame') ?></th>
				<th><?php echo _('Hosts checked') ?></th>
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
				<th style="width: 40%"><?php echo _('Metric') ?></th>
				<th style="width: 20%"><?php echo _('Min.') ?></th>
				<th style="width: 20%"><?php echo _('Max.') ?></th>
				<th style="width: 20%"><?php echo _('Average') ?></th>
			</tr>
			<tr class="even">
				<td><?php echo _('Percent state change') ?>&nbsp;&nbsp;</td>
				<td><?php echo $min_host_percent_change_b ?> %</td>
				<td><?php echo $max_host_percent_change_b ?> %</td>
				<td><?php echo $average_host_percent_change ?> %</td>
			</tr>
		</table>
	</div>

	<div>
		<br />
		<strong><?php echo _('Check statistics') ?></strong>
		<table style="margin-bottom: 15px">
			<tr>
				<th style="width: 50%"><?php echo _('Type') ?></th>
				<th style="width: 25%"><?php echo _('Total') ?></th>
				<th style="width: 25%"><?php echo _('Rate') ?></th>
			</tr>
			<tr class="even">
				<td><?php echo _('Servicechecks') ?></td>
				<td><?php echo $program_status->service_checks ?></td>
				<td><?php echo number_format($program_status->service_checks_rate, 2) ?>/s</td>
			</tr>
			<tr class="odd">
				<td><?php echo _('Hostchecks') ?></td>
				<td><?php echo $program_status->host_checks ?></td>
				<td><?php echo number_format($program_status->host_checks_rate, 2) ?>/s</td>
			</tr>
		</table>
	</div>

