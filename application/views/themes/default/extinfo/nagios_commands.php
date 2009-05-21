<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w32" id="nagios_commands" style="width: 250px;">
	<div class='widget-header'><?php echo $title ?></div>
	<table class="extinfo">
		<tr>
			<td class="status icon">
					<?php echo html::image('application/views/themes/default/images/icons/16x16/stop.png', array('alt' => $label_shutdown_nagios, 'title' => $label_shutdown_nagios)); ?>
			</td>
			<td><?php echo $link_shutdown_nagios ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/restart.gif', array('alt' => $label_restart_nagios, 'title' => $label_restart_nagios)); ?>
			</td>
			<td><?php echo $link_shutdown_nagios ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/nofity-disabled.png', array('alt' => $label_notifications, 'title' => $label_notifications)); ?>
			</td>
			<td><?php echo $link_notifications ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/stop-execute.png', array('alt' => $label_execute_service_checks, 'title' => $label_execute_service_checks)); ?>
			</td>
			<td><?php echo $link_execute_service_checks ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/stop-execute.png', array('alt' => $label_passive_service_checks, 'title' => $label_passive_service_checks)); ?>
			</td>
			<td><?php echo $link_passive_service_checks ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/stop-execute.png', array('alt' => $label_execute_host_checks, 'title' => $label_execute_host_checks)); ?>
			</td>
			<td><?php echo $link_execute_host_checks ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/stop-execute.png', array('alt' => $label_accept_passive_host_checks, 'title' => $label_accept_passive_host_checks)); ?>
			</td>
			<td><?php echo $link_accept_passive_host_checks ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/disabled.png', array('alt' => $label_enable_event_handlers, 'title' => $label_enable_event_handlers)); ?>
			</td>
			<td><?php echo $link_enable_event_handlers ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/start.png', array('alt' => $label_obsess_over_services, 'title' => $label_obsess_over_services)); ?>
			</td>
			<td><?php echo $link_obsess_over_services ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/start.png', array('alt' => $label_obsess_over_hosts, 'title' => $label_obsess_over_hosts)); ?>
			</td>
			<td><?php echo $link_obsess_over_hosts ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/disabled.png', array('alt' => $label_flap_detection_enabled, 'title' => $label_flap_detection_enabled)); ?>
			</td>
			<td><?php echo $link_flap_detection_enabled ?></td>
		</tr>
		<tr>
			<td class="status icon">
				<?php echo html::image('application/views/themes/default/images/icons/16x16/enabled.png', array('alt' => $label_process_performance_data, 'title' => $label_process_performance_data)); ?>
			</td>
			<td><?php echo $link_process_performance_data ?></td>
		</tr>
	</table>
</div>