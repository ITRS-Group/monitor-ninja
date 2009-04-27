<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class='commandTitle'><?php echo $title ?></div>

<table>
	<tr>
		<td>
			<img src="/monitor/images/stop.gif" alt="<?php echo $label_shutdown_nagios ?>" title="<?php echo $label_shutdown_nagios ?>" />
		</td>
		<td><?php echo $link_shutdown_nagios ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/restart.gif" alt="<?php echo $label_restart_nagios ?>" title="<?php echo $label_restart_nagios ?>" />
		</td>
		<td><?php echo $link_shutdown_nagios ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/disabled.gif" alt="<?php echo $label_notifications ?>" title="<?php echo $label_notifications ?>" />
		</td>
		<td><?php echo $link_notifications ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/disabled.gif" alt="<?php echo $label_execute_service_checks ?>" title="<?php echo $label_execute_service_checks ?>" />
		</td>
		<td><?php echo $link_execute_service_checks ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/disabled.gif" alt="<?php echo $label_passive_service_checks ?>" title="<?php echo $label_passive_service_checks ?>" />
		</td>
		<td><?php echo $link_passive_service_checks ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/disabled.gif" alt="<?php echo $label_execute_host_checks ?>" title="<?php echo $label_execute_host_checks ?>" />
		</td>
		<td><?php echo $link_execute_host_checks ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/disabled.gif" alt="<?php echo $label_accept_passive_host_checks ?>" title="<?php echo $label_accept_passive_host_checks ?>" />
		</td>
		<td><?php echo $link_accept_passive_host_checks ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/disabled.gif" alt="<?php echo $label_enable_event_handlers ?>" title="<?php echo $label_enable_event_handlers ?>" />
		</td>
		<td><?php echo $link_enable_event_handlers ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/enabled.gif" alt="<?php echo $label_obsess_over_services ?>" title="<?php echo $label_obsess_over_services ?>" />
		</td>
		<td><?php echo $link_obsess_over_services ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/enabled.gif" alt="<?php echo $label_obsess_over_hosts ?>" title="<?php echo $label_obsess_over_hosts ?>" />
		</td>
		<td><?php echo $link_obsess_over_hosts ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/disabled.gif" alt="<?php echo $label_flap_detection_enabled ?>" title="<?php echo $label_flap_detection_enabled ?>" />
		</td>
		<td><?php echo $link_flap_detection_enabled ?></td>
	</tr>
	<tr>
		<td>
			<img src="/monitor/images/disabled.gif" alt="<?php echo $label_process_performance_data ?>" title="<?php echo $label_process_performance_data ?>" />
		</td>
		<td><?php echo $link_process_performance_data ?></td>
	</tr>
</table>