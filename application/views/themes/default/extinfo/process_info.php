<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w66" id="extinfo_current" style="width: 350px">
<table>
<caption><?php echo $this->translate->_('Process Information'); ?></caption>
	<tr class="odd">
		<td class="bt" style="width: 220px"><?php echo $lable_program_version ?>:</td>
		<td class="bt"><?php echo $program_version ?></td>
	</tr>
	<tr class="even">
		<td><?php echo $lable_program_start_time ?>:</td>
		<td><?php echo $program_start ?></td>
	</tr>
	<tr class="odd">
		<td><?php echo $lable_total_run_time ?>:</td>
		<td><?php echo $run_time ?></td>
	</tr>
	<tr class="even">
		<td><?php echo $lable_last_external_cmd_check ?>:</td>
		<td><?php echo date('Y-m-d H:m:i',$last_command_check) ?></td>
	</tr>
	<tr class="odd">
		<td><?php echo $lable_last_logfile_rotation ?>:</td>
		<td><?php echo $last_log_rotation ?></td>
	</tr>
	<tr class="even">
		<td><?php echo $lable_pid ?></td>
		<td><?php echo $nagios_pid ?></td>
	</tr>
	<tr class="odd">
		<td><?php echo $lable_notifications_enabled ?></td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/'.strtolower($notifications_str).'.png',$notifications_str);?>
			<?php echo ucfirst(strtolower($notifications_str)) ?>
		</td>
	</tr>
	<tr class="even">
		<td><?php echo $lable_service_checks ?> </td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/'.strtolower($servicechecks_str).'.png',$servicechecks_str);?>
			<?php echo ucfirst(strtolower($servicechecks_str)) ?>
		</td>
	</tr>
	<tr class="odd">
		<td><?php echo $lable_service_checks_passive ?></td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/'.strtolower($passive_servicechecks_str).'.png',$passive_servicechecks_str);?>
			<?php echo ucfirst(strtolower($passive_servicechecks_str)) ?>
		</td>
	</tr>
	<tr class="even">
		<td><?php echo $lable_host_checks ?></td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/'.strtolower($hostchecks_str).'.png',$hostchecks_str);?>
			<?php echo ucfirst(strtolower($hostchecks_str)) ?>
		</td>
	</tr>
	<tr class="odd">
		<td><?php echo $lable_host_checks_passive ?></td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/'.strtolower($passive_hostchecks_str).'.png',$passive_hostchecks_str);?>
			<?php echo ucfirst(strtolower($passive_hostchecks_str)) ?>
		</td>
	</tr>
	<tr class="even">
		<td><?php echo $lable_event_handlers ?></td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/'.strtolower($eventhandler_str).'.png',$eventhandler_str);?>
			<?php echo $eventhandler_str ?>
		</td>
	</tr>
	<tr class="odd">
		<td><?php echo $lable_obsess_services ?></td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/invert-'.strtolower($obsess_services_str).'.png',$obsess_services_str);?>
			<?php echo $obsess_services_str ?></td>
	</tr>
	<tr class="even">
		<td><?php echo $lable_obsess_hosts ?></td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/invert-'.strtolower($obsess_services_str).'.png',$obsess_services_str);?>
			<?php echo $obsess_hosts_str ?>
		</td>
	</tr>
	<tr class="odd">
		<td><?php echo $lable_flap_enabled ?></td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/'.strtolower($flap_detection_str).'.png',$flap_detection_str);?>
			<?php echo $flap_detection_str ?>
		</td>
	</tr>
	<tr class="even">
		<td><?php echo $lable_performance_data ?></td>
		<td>
			<?php echo html::image('/application/views/themes/default/icons/12x12/invert-'.strtolower($performance_data_str).'.png',$performance_data_str);?>
			<?php echo $performance_data_str ?>
		</td>
	</tr>
</table>
</div>
<?php if (isset($commands))
	echo $commands;