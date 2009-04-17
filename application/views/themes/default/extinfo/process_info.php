<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<table>
	<tr>
		<td><?php echo $lable_program_version ?>:</td>
		<td><?php echo $program_version ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_program_start_time ?>:</td>
		<td><?php echo $program_start ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_total_run_time ?>:</td>
		<td><?php echo $run_time ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_last_external_cmd_check ?>:</td>
		<td><?php echo $last_command_check ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_last_logfile_rotation ?>:</td>
		<td><?php echo $last_log_rotation ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_pid ?></td>
		<td><?php echo $nagios_pid ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_notifications_enabled ?></td>
		<td class="<?php echo $notifications_class ?>"><?php echo $notifications_str ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_service_checks ?> </td>
		<td class="<?php echo $servicechecks_class ?>"><?php echo $servicechecks_str ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_service_checks_passive ?></td>
		<td class="<?php echo $passive_servicechecks_class ?>"><?php echo $passive_servicechecks_str ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_host_checks ?></td>
		<td class="<?php echo $hostchecks_class?>"><?php echo $hostchecks_str ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_host_checks_passive ?></td>
		<td class="<?php echo $passive_hostchecks_class ?>"><?php echo $passive_hostchecks_str ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_event_handlers ?></td>
		<td><?php echo $eventhandler_str ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_obsess_services ?></td>
		<td><?php echo $obsess_services_str ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_obsess_hosts ?></td>
		<td><?php echo $obsess_hosts_str ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_flap_enabled ?></td>
		<td><?php echo $flap_detection_str ?></td>
	</tr>
	<tr>
		<td><?php echo $lable_performance_data ?></td>
		<td><?php echo $performance_data_str ?></td>
	</tr>
</table>

<?php if (isset($commands))
	echo $commands;