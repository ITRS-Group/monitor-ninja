<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w66" id="extinfo_current" style="width: 350px">
<table class="ext">
	<tr><th class="headerNone" colspan="2" style="border-left:0px"><?php echo _('Process Information'); ?></th></tr>
	<tr>
		<td class="dark"><?php echo _('Program version') ?></td>
		<td><?php echo $program_version ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Program start time') ?></td>
		<td><?php echo $program_start ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Total running time') ?></td>
		<td><?php echo $run_time ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Last external command check') ?></td>
		<td><?php echo $last_command_check != 0 ? date(cal::get_calendar_format(true).' H:i:s', $last_command_check) : _('N/A') ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Last log file rotation') ?></td>
		<td><?php echo $last_log_rotation ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_pid ?></td>
		<td><?php echo $nagios_pid ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Notifications enabled?') ?></td>
		<td>
			<span class="<?php echo strtolower($notifications_str); ?>"><?php echo ucfirst(strtolower($notifications_str)) ?></span>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Service checks being executed?') ?> </td>
		<td>
			<span class="<?php echo strtolower($servicechecks_str); ?>"><?php echo ucfirst(strtolower($servicechecks_str)) ?></span>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Passive service checks being accepted?') ?></td>
		<td>
			<span class="<?php echo strtolower($passive_servicechecks_str); ?>"><?php echo ucfirst(strtolower($passive_servicechecks_str)) ?></span>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Host checks being executed?') ?></td>
		<td>
			<span class="<?php echo strtolower($hostchecks_str); ?>"><?php echo ucfirst(strtolower($hostchecks_str)) ?></span>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Passive host checks being accepted?') ?></td>
		<td>
			<span class="<?php echo strtolower($passive_hostchecks_str); ?>"><?php echo ucfirst(strtolower($passive_hostchecks_str)) ?></span>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Event handlers enabled?') ?></td>
		<td>
			<span class="<?php echo strtolower($eventhandler_str); ?>"><?php echo ucfirst(strtolower($eventhandler_str)) ?></span>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Obsessing over services?') ?></td>
		<td>
			<span class="<?php echo strtolower($obsess_services_str); ?>"><?php echo ucfirst(strtolower($obsess_services_str)) ?></span>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Obsessing over hosts?') ?></td>
		<td>
			<span class="<?php echo strtolower($obsess_hosts_str); ?>"><?php echo ucfirst(strtolower($obsess_hosts_str)) ?></span>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Flap detection enabled?') ?></td>
		<td>
			<span class="<?php echo strtolower($flap_detection_str); ?>"><?php echo ucfirst(strtolower($flap_detection_str)) ?></span>
		</td>
	</tr>
	<tr>
		<td class="dark"><?php echo _('Performance data being processed?') ?></td>
		<td>
			<span class="<?php echo strtolower($performance_data_str); ?>"><?php echo ucfirst(strtolower($performance_data_str)) ?></span>
		</td>
	</tr>
</table>
</div>
<?php if (isset($commands))
	echo $commands;
