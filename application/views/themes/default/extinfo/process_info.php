<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<table border=1>
	<tr><td class='dataVar'><?php echo $lable_program_version ?>:</td><td class='dataVal'><?php echo $program_version ?></td></tr>
	<tr><td class='dataVar'><?php echo $lable_program_start_time ?>:</td><td class='dataVal'><?php echo $program_start ?></td></tr>
	<tr><td class='dataVar'><?php echo $lable_total_run_time ?>:</td><td class='dataVal'><?php echo $run_time ?></td></tr>

	<tr><td class='dataVar'><?php echo $lable_last_external_cmd_check ?>:</td><td class='dataVal'><?php echo $last_command_check ?></td></tr>
	<tr><td class='dataVar'><?php echo $lable_last_logfile_rotation ?>:</td><td class='dataVal'><?php echo $last_log_rotation ?></td></tr>
	<tr><td class='dataVar'><?php echo $lable_pid ?></td><td class='dataval'><?php echo $nagios_pid ?></td></tr>

	<tr><td class='dataVar'><?php echo $lable_notifications_enabled ?></td><td class='dataVal'><div class='<?php echo $notifications_class ?>'>&nbsp;&nbsp;<?php echo $notifications_str ?>&nbsp;&nbsp;</div></td></tr>
	<tr><td class='dataVar'><?php echo $lable_service_checks ?> </td><td class='dataVal'><div class='<?php echo $servicechecks_class ?>'>&nbsp;&nbsp;<?php echo $servicechecks_str ?>&nbsp;&nbsp;</div></td></tr>
	<tr><td class='dataVar'><?php echo $lable_service_checks_passive ?></td><td class='dataVal'><div class='<?php echo $passive_servicechecks_class ?>'>&nbsp;&nbsp;<?php echo $passive_servicechecks_str ?>&nbsp;&nbsp;</div></td></tr>

	<tr><td class='dataVar'><?php echo $lable_host_checks ?></td><td class='dataVal'><div class='<?php echo $hostchecks_class?>'>&nbsp;&nbsp;<?php echo $hostchecks_str ?>&nbsp;&nbsp;</div></td></tr>
	<tr><td class='dataVar'><?php echo $lable_host_checks_passive ?></td><td class='dataVal'><div class='<?php echo $passive_hostchecks_class ?>'>&nbsp;&nbsp;<?php echo $passive_hostchecks_str ?>&nbsp;&nbsp;</div></td></tr>
	<tr><td class='dataVar'><?php echo $lable_event_handlers ?></td><td class='dataVal'><?php echo $eventhandler_str ?></td></tr>
	<tr><td class='dataVar'><?php echo $lable_obsess_services ?></td><td class='dataVal'><?php echo $obsess_services_str ?></td></tr>
	<tr><td class='dataVar'><?php echo $lable_obsess_hosts ?></td><td class='dataVal'><?php echo $obsess_hosts_str ?></td></tr>
	<tr><td class='dataVar'><?php echo $lable_flap_enabled ?></td><td class='dataVal'><?php echo $flap_detection_str ?></td></tr>

	<tr><td class='dataVar'><?php echo $lable_performance_data ?></td><td class='dataVal'><?php echo $performance_data_str ?></td></tr>
</table>
