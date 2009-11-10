<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w66" id="extinfo_current" style="width: 350px">
<table class="ext">
	<tr><th class="headerNone" colspan="2" style="border-left:0px"><?php echo $this->translate->_('Process Information'); ?></th></tr>
	<tr>
		<td class="dark"><?php echo $lable_program_version ?></td>
		<td><?php echo $program_version ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_program_start_time ?></td>
		<td><?php echo $program_start ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_total_run_time ?></td>
		<td><?php echo $run_time ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_last_external_cmd_check ?></td>
		<td><?php echo $last_command_check != 0 ? date('Y-m-d H:m:i',$last_command_check) : $this->translate->_('N/A') ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_last_logfile_rotation ?></td>
		<td><?php echo $last_log_rotation ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_pid ?></td>
		<td><?php echo $nagios_pid ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_notifications_enabled ?></td>
		<td>
			<?php echo html::image($this->add_path('icons/12x12/'.strtolower($notifications_str).'.png'),$notifications_str);?>
			<?php echo ucfirst(strtolower($notifications_str)) ?>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_service_checks ?> </td>
		<td>
			<?php echo html::image($this->add_path('icons/12x12/'.strtolower($servicechecks_str).'.png'),$servicechecks_str);?>
			<?php echo ucfirst(strtolower($servicechecks_str)) ?>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_service_checks_passive ?></td>
		<td>
			<?php echo html::image($this->add_path('icons/12x12/'.strtolower($passive_servicechecks_str).'.png'),$passive_servicechecks_str);?>
			<?php echo ucfirst(strtolower($passive_servicechecks_str)) ?>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_host_checks ?></td>
		<td>
			<?php echo html::image($this->add_path('icons/12x12/'.strtolower($hostchecks_str).'.png'),$hostchecks_str);?>
			<?php echo ucfirst(strtolower($hostchecks_str)) ?>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_host_checks_passive ?></td>
		<td>
			<?php echo html::image($this->add_path('icons/12x12/'.strtolower($passive_hostchecks_str).'.png'),$passive_hostchecks_str);?>
			<?php echo ucfirst(strtolower($passive_hostchecks_str)) ?>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_event_handlers ?></td>
		<td><?php echo $eventhandler_str ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_obsess_services ?></td>
		<td><?php echo $obsess_services_str ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_obsess_hosts ?></td>
		<td><?php echo $obsess_hosts_str ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_flap_enabled ?></td>
		<td><?php echo $flap_detection_str ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_performance_data ?></td>
		<td><?php echo $performance_data_str ?></td>
	</tr>
</table>
</div>
<?php if (isset($commands))
	echo $commands;