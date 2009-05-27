<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w32" id="extinfo_info" style="margin-right: 1%; width: 320px">

	<table>
		<caption><?php echo $lable_command_title ?></caption>
		<?php # only for hosts!
			if ($type == 'host') { # @@@FIXME check if we are using statusmap? USE_STATUSMAP
		?>
		<tr class="odd">
			<td class="icon bt">
				<?php echo html::image('application/views/themes/default/icons/16x16/locate-host-on-map.png', array('alt' => $lable_host_map, 'title' => $lable_host_map, 'style' => 'height: 14px')); ?>
			</td>
			<td class="bt"><a href="statusmap/host/<?php echo $host ?>"><?php echo $lable_host_map ?></a></td>
		</tr>
		<?php } ?>
		<tr class="even">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/disabled.png', array('alt' => $lable_active_checks, 'title' => $lable_active_checks, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_active_checks ?></td>
		</tr>
		<tr class="odd">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/delay.png', array('alt' => $lable_reschedule_check, 'title' => $lable_reschedule_check, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_reschedule_check ?></td>
		</tr>
		<tr class="even">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/passiveonly.png', array('alt' => $lable_submit_passive_checks, 'title' => $lable_submit_passive_checks, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_submit_passive_check ?></td>
		</tr>
		<tr class="odd">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/disabled.png', array('alt' => $lable_stop_start_passive_checks, 'title' => $lable_stop_start_passive_checks, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_stop_start_passive_check ?></td>
		</tr>
		<tr class="even">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/disabled.png', array('alt' => $lable_obsessing, 'title' => $lable_obsessing, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_obsessing ?></td>
		</tr>
		<?php if ($show_ackinfo) { ?>
		<tr>
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/acknowledged.png', array('alt' => $lable_acknowledge_problem, 'title' => $lable_acknowledge_problem, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_acknowledge_problem ?></td>
		</tr>
		<?php } ?>
		<tr class="even">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/nofity-disabled.png', array('alt' => $lable_notifications, 'title' => $lable_notifications, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_notifications ?></td>
		</tr>
		<tr class="odd">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/notify.png', array('alt' => $lable_custom_notifications, 'title' => $lable_custom_notifications, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_custom_notifications ?></td>
		</tr>
		<?php if ($show_delay) {	?>
		<tr class="even">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/delay.png', array('alt' => $lable_delay_notification, 'title' => $lable_delay_notification, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_delay_notifications ?></td>
		</tr>
		<?php } ?>
		<tr class="odd">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/downtime.png', array('alt' => $lable_schedule_dt, 'title' => $lable_schedule_dt, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_schedule_dt ?></td>
		</tr>
		<?php if ($type == 'host') {?>
		<tr class="even">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/disabled.png', array('alt' => $lable_disable_service_notifications_on_host, 'title' => $lable_disable_service_notifications_on_host, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_disable_service_notifications_on_host ?></td>
		</tr>
		<tr class="odd">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/enabled.png', array('alt' => $lable_enable_service_notifications_on_host, 'title' => $lable_enable_service_notifications_on_host, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_enable_service_notifications_on_host ?></td>
		</tr>
		<tr class="even">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/delay.png', array('alt' => $lable_check_all_services, 'title' => $lable_check_all_services, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_check_all_services ?></td>
		</tr>
		<tr class="odd">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/disabled.png', array('alt' => $lable_disable_servicechecks, 'title' => $lable_disable_servicechecks, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_disable_servicechecks ?></td>
		</tr>
		<tr class="even">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/enabled.png', array('alt' => $lable_enable_servicechecks, 'title' => $lable_enable_servicechecks, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_enable_servicechecks ?></td>
		</tr>
		<?php } ?>
		<tr class="odd">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/disabled.png', array('alt' => $lable_enable_disable_event_handler, 'title' => $lable_enable_disable_event_handler, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_enable_disable_event_handler ?></td>
		</tr>
		<tr class="even">
			<td class="icon">
				<?php echo html::image('application/views/themes/default/icons/16x16/disabled.png', array('alt' => $lable_enable_disable_flapdetection, 'title' => $lable_enable_disable_flapdetection, 'style' => 'height: 14px')); ?>
			</td>
			<td><?php echo $link_enable_disable_flapdetection ?></td>
		</tr>
	</table>
</div>