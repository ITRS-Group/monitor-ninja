<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="right width-50" id="extinfo_info">

	<table class="ext">
		<tr>
			<th colspan="2"><?php echo $lable_command_title ?></th>
		</tr>
		<?php # only for hosts!
			$i =0;
			if ($type == 'host' && Kohana::config('nagvis.nagvis_real_path', false, false)) {
		?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/locate-host-on-map.png'), array('alt' => $lable_host_map, 'title' => $lable_host_map)); ?>
			</td>
			<td class="bt"><?php echo html::anchor('nagvis/automap/host/'.$host, $lable_host_map) ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/disabled.png'), array('alt' => $lable_active_checks, 'title' => $lable_active_checks)); ?>
			</td>
			<td><?php echo $link_active_checks ?></td>
		</tr>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/re-schedule.png'), array('alt' => $lable_reschedule_check, 'title' => $lable_reschedule_check)); ?>
			</td>
			<td><?php echo $link_reschedule_check ?></td>
		</tr>
		<?php if (isset($lable_submit_passive_checks)) { ?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/checks-passive.png'), array('alt' => $lable_submit_passive_checks, 'title' => $lable_submit_passive_checks)); ?>
			</td>
			<td><?php echo $link_submit_passive_check ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/disabled.png'), array('alt' => $lable_stop_start_passive_checks, 'title' => $lable_stop_start_passive_checks)); ?>
			</td>
			<td><?php echo $link_stop_start_passive_check ?></td>
		</tr>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/disabled.png'), array('alt' => $lable_obsessing, 'title' => $lable_obsessing)); ?>
			</td>
			<td><?php echo $link_obsessing ?></td>
		</tr>
		<?php if ($show_ackinfo) { ?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/acknowledged.png'), array('alt' => $lable_acknowledge_problem, 'title' => $lable_acknowledge_problem)); ?>
			</td>
			<td><?php echo $link_acknowledge_problem ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/notify-disabled.png'), array('alt' => $lable_notifications, 'title' => $lable_notifications)); ?>
			</td>
			<td><?php echo $link_notifications ?></td>
		</tr>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/notify-send.png'), array('alt' => $lable_custom_notifications, 'title' => $lable_custom_notifications)); ?>
			</td>
			<td><?php echo $link_custom_notifications ?></td>
		</tr>
		<?php if ($show_delay) {	?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/notify-delay.png'), array('alt' => $lable_delay_notification, 'title' => $lable_delay_notification)); ?>
			</td>
			<td><?php echo $link_delay_notifications ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/scheduled-downtime.png'), array('alt' => $lable_schedule_dt, 'title' => $lable_schedule_dt)); ?>
			</td>
			<td><?php echo $link_schedule_dt ?></td>
		</tr>
		<?php if ($type == 'host') {?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/notify-disabled.png'), array('alt' => $lable_disable_service_notifications_on_host, 'title' => $lable_disable_service_notifications_on_host)); ?>
			</td>
			<td><?php echo $link_disable_service_notifications_on_host ?></td>
		</tr>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/notify.png'), array('alt' => $lable_enable_service_notifications_on_host, 'title' => $lable_enable_service_notifications_on_host)); ?>
			</td>
			<td><?php echo $link_enable_service_notifications_on_host ?></td>
		</tr>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/schedule.png'), array('alt' => $lable_check_all_services, 'title' => $lable_check_all_services)); ?>
			</td>
			<td><?php echo $link_check_all_services ?></td>
		</tr>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/disabled.png'), array('alt' => $lable_disable_servicechecks, 'title' => $lable_disable_servicechecks)); ?>
			</td>
			<td><?php echo $link_disable_servicechecks ?></td>
		</tr>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/enabled.png'), array('alt' => $lable_enable_servicechecks, 'title' => $lable_enable_servicechecks)); ?>
			</td>
			<td><?php echo $link_enable_servicechecks ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/disabled.png'), array('alt' => $lable_enable_disable_event_handler, 'title' => $lable_enable_disable_event_handler)); ?>
			</td>
			<td><?php echo $link_enable_disable_event_handler ?></td>
		</tr>
		<tr>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/disabled.png'), array('alt' => $lable_enable_disable_flapdetection, 'title' => $lable_enable_disable_flapdetection)); ?>
			</td>
			<td><?php echo $link_enable_disable_flapdetection ?></td>
		</tr>
	</table>
</div>

