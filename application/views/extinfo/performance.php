<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table>
	<tr>
		<td>
			<table class="padd-table">
				<caption><?php echo _('Services actively checked') ?></caption>
				<tr>
					<th style="width: 40%"><?php echo _('Time frame') ?></th>
					<th><?php echo _('Services checked') ?></th>
				</tr>
				<?php
					$loads = $performance->get_active_service_loads();
					foreach ($loads as $interval => $load) {
						?>
							<tr class="even">
								<td>&le; <?php echo $interval . "min"; ?></td>
								<td><?php echo $load ?> (<?php printf("%.1f", $performance->percentage_of($load, $performance->service->active_all_sum)) ?>%)</td>
							</tr>
						<?php
					}
				?>
			</table>
			<br />
			<table class="padd-table">
				<tr>
					<th style="width: 40%"><?php echo _('Metric') ?></th>
					<th style="width: 20%"><?php echo _('Min.') ?></th>
					<th style="width: 20%"><?php echo _('Max.') ?></th>
					<th style="width: 20%"><?php echo _('Average') ?></th>
				</tr>
				<tr class="even">
					<td><?php echo _('Check execution Time') ?></td>
					<td><?php printf("%.2f", $performance->service->execution_time_min) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.2f", $performance->service->execution_time_max) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.3f", $performance->service->execution_time_avg) ?> <?php echo _('sec') ?></td>
				</tr>
				<tr class="odd">
					<td><?php echo _('Check latency') ?></td>
					<td><?php printf("%.2f", $performance->service->latency_min) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.2f", $performance->service->latency_max) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.3f", $performance->service->latency_avg) ?> <?php echo _('sec') ?></td>
				</tr>
				<tr class="even">
					<td><?php echo _('Percent state change') ?></td>
					<td><?php printf("%.2f", $performance->service->active_state_change_min) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.2f", $performance->service->active_state_change_max) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.3f", $performance->service->active_state_change_avg) ?> <?php echo _('sec') ?></td>
				</tr>
			</table>
		</td>
		<td>
			<table class="padd-table">
				<caption><?php echo _('Services passively checked') ?></caption>
				<tr>
					<th style="width: 40%"><?php echo _('Time frame') ?></th>
					<th><?php echo _('Services checked') ?></th>
				</tr>
				<?php
					$loads = $performance->get_passive_service_loads();
					foreach ($loads as $interval => $load) {
						?>
							<tr class="even">
								<td>&le; <?php echo $interval . "min"; ?></td>
								<td><?php echo $load ?> (<?php printf("%.1f", $performance->percentage_of($load, $performance->service->passive_all_sum)) ?>%)</td>
							</tr>
						<?php
					}
				?>
			</table>
			<br />
			<table class="padd-table">
				<tr>
					<th style="width: 40%"><?php echo _('Metric') ?></th>
					<th style="width: 20%"><?php echo _('Min.') ?></th>
					<th style="width: 20%"><?php echo _('Max.') ?></th>
					<th style="width: 20%"><?php echo _('Average') ?></th>
				</tr>
				<tr class="even">
					<td><?php echo _('Percent state change') ?></td>
					<td><?php printf("%.2f", $performance->service->passive_state_change_min) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.2f", $performance->service->passive_state_change_max) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.3f", $performance->service->passive_state_change_avg) ?> <?php echo _('sec') ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<table>
	<tr>
		<td>
			<table class="padd-table">
				<caption><?php echo _('Hosts actively checked') ?></caption>
				<tr>
					<th style="width: 40%"><?php echo _('Time frame') ?></th>
					<th><?php echo _('Hosts checked') ?></th>
				</tr>
				<?php
					$loads = $performance->get_active_host_loads();
					foreach ($loads as $interval => $load) {
						?>
							<tr class="even">
								<td>&le; <?php echo $interval . "min"; ?></td>
								<td><?php echo $load ?> (<?php printf("%.1f", $performance->percentage_of($load, $performance->host->active_all_sum)) ?>%)</td>
							</tr>
						<?php
					}
				?>
			</table>
			<br />
			<table class="padd-table">
				<tr>
					<th style="width: 40%"><?php echo _('Metric') ?></th>
					<th style="width: 20%"><?php echo _('Min.') ?></th>
					<th style="width: 20%"><?php echo _('Max.') ?></th>
					<th style="width: 20%"><?php echo _('Average') ?></th>
				</tr>
				<tr class="even">
					<td><?php echo _('Check execution Time') ?></td>
					<td><?php printf("%.2f", $performance->host->execution_time_min) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.2f", $performance->host->execution_time_max) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.3f", $performance->host->execution_time_avg) ?> <?php echo _('sec') ?></td>
				</tr>
				<tr class="odd">
					<td><?php echo _('Check latency') ?></td>
					<td><?php printf("%.2f", $performance->host->latency_min) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.2f", $performance->host->latency_max) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.3f", $performance->host->latency_avg) ?> <?php echo _('sec') ?></td>
				</tr>
				<tr class="even">
					<td><?php echo _('Percent state change') ?></td>
					<td><?php printf("%.2f", $performance->host->active_state_change_min) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.2f", $performance->host->active_state_change_max) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.3f", $performance->host->active_state_change_avg) ?> <?php echo _('sec') ?></td>
				</tr>
			</table>
		</td>
		<td>
			<table class="padd-table">
				<caption><?php echo _('Hosts passively checked') ?></caption>
				<tr>
					<th style="width: 40%"><?php echo _('Time frame') ?></th>
					<th><?php echo _('Hosts checked') ?></th>
				</tr>
				<?php
					$loads = $performance->get_passive_host_loads();
					foreach ($loads as $interval => $load) {
						?>
							<tr class="even">
								<td>&le; <?php echo $interval . "min"; ?></td>
								<td><?php echo $load ?> (<?php printf("%.1f", $performance->percentage_of($load, $performance->host->passive_all_sum)) ?>%)</td>
							</tr>
						<?php
					}
				?>
			</table>
			<br />
			<table class="padd-table">
				<tr>
					<th style="width: 40%"><?php echo _('Metric') ?></th>
					<th style="width: 20%"><?php echo _('Min.') ?></th>
					<th style="width: 20%"><?php echo _('Max.') ?></th>
					<th style="width: 20%"><?php echo _('Average') ?></th>
				</tr>
				<tr class="even">
					<td><?php echo _('Percent state change') ?></td>
					<td><?php printf("%.2f", $performance->host->passive_state_change_min) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.2f", $performance->host->passive_state_change_max) ?> <?php echo _('sec') ?></td>
					<td><?php printf("%.3f", $performance->host->passive_state_change_avg) ?> <?php echo _('sec') ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<div>
	<table style="margin-bottom: 15px" class="padd-table">
		<caption><?php echo _('Check statistics') ?></caption>
		<tr>
			<th style="width: 50%"><?php echo _('Type') ?></th>
			<th style="width: 25%"><?php echo _('Total') ?></th>
			<th style="width: 25%"><?php echo _('Rate') ?></th>
		</tr>
		<tr class="even">
			<td><?php echo _('Servicechecks') ?></td>
			<td><?php echo $performance->program->get_service_checks() ?></td>
			<td><?php printf("%.2f", $performance->program->get_service_checks_rate()) ?>/s</td>
		</tr>
		<tr class="odd">
			<td><?php echo _('Hostchecks') ?></td>
			<td><?php echo $performance->program->get_host_checks() ?></td>
			<td><?php printf("%.2f", $performance->program->get_host_checks_rate()) ?>/s</td>
		</tr>
	</table>
</div>