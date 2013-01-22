<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="right width-50" id="extinfo_info">

	<table class="ext">
		<tr>
			<th colspan="2"><?php echo ($type == 'host' ? _('Host Commands') : _('Service Commands')) ?></th>
		</tr>
		<?php # only for hosts!
			$i =0;
			if ($type == 'host' && Kohana::config('nagvis.nagvis_real_path', false, false)) {
		?>
		<tr>
			<td class="icon dark">
				<span class="icon-16 x16-locate-host-on-map" title="<?php echo  _('Locate host on map') ?>"></span>
			</td>
			<td class="bt"><?php echo html::anchor('nagvis/automap/host/'.$host, _('Locate host on map')) ?></td>
		</tr>
		<?php } ?>
		<tr>
			<?php
			if ($result->active_checks_enabled) {
				$img = 'disabled';
				$label = _("Disable active checks of this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_CHECK') : nagioscmd::command_id('DISABLE_SVC_CHECK');
			} else {
				$img = 'enabled';
				$label = _("Enable active checks of this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_CHECK') : nagioscmd::command_id('ENABLE_SVC_CHECK');
			} ?>
			<td class="icon dark">
				<span class="icon-16 x16-<?php echo $img ?>" title="<?php echo $label ?>"></span>
			</td>
			<td>
				<?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?>
			</td>
		</tr>
		<tr>
			<?php
			$label = _("Re-schedule next $type check");
			$cmd = $type == 'host' ? nagioscmd::command_id('SCHEDULE_HOST_CHECK') : nagioscmd::command_id('SCHEDULE_SVC_CHECK'); ?>
			<td class="icon dark">
				<?php echo html::image($this->add_path('icons/16x16/re-schedule.png'), array('alt' => $label, 'title' => $label)); ?>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
			<?php
			if ($result->accept_passive_checks) {
				$label = _("Submit passive check result for this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('PROCESS_HOST_CHECK_RESULT') : nagioscmd::command_id('PROCESS_SERVICE_CHECK_RESULT') ?>
			<tr>
				<td class="icon dark">
					<span class="icon-16 x16-checks-passive" title="<?php echo $label ?>"></span>
				</td>
				<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
			</tr>
			<?php } ?>
		<tr>
			<?php
			if ($result->accept_passive_checks) {
				$img = 'disabled';
				$label = _("Stop accepting passive checks for this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_PASSIVE_HOST_CHECKS') : nagioscmd::command_id('DISABLE_PASSIVE_SVC_CHECKS');
			} else {
				$img = 'enabled';
				$label= _("Start accepting passive checks for this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_PASSIVE_HOST_CHECKS') : nagioscmd::command_id('ENABLE_PASSIVE_SVC_CHECKS');
			} ?>
			<td class="icon dark">
				<span class="icon-16 x16-<?php echo $img ?>" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<tr>
			<?php
			if ($result->obsess) {
				$img = 'disabled';
				$label = _("Stop obsessing over this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('STOP_OBSESSING_OVER_HOST') : nagioscmd::command_id('STOP_OBSESSING_OVER_SVC');
			} else {
				$img = 'enabled';
				$label = _('Start obsessing over this host');
				$type == 'host' ? nagioscmd::command_id('START_OBSESSING_OVER_HOST') : nagioscmd::command_id('START_OBSESSING_OVER_SVC');
			} ?>
			<td class="icon dark">
				<span class="icon-16 x16-<?php echo $img ?>" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<?php if ($result->state) {
			if ($result->acknowledged) {
				$img = 'remove';
				$label = _('Remove problem acknowledgement');
				$cmd = $type == 'host' ? nagioscmd::command_id('REMOVE_HOST_ACKNOWLEDGEMENT') : nagioscmd::command_id('REMOVE_SVC_ACKNOWLEDGEMENT');
			} else {
				$img = 'acknowledged';
				$label = _("Acknowledge this $type problem");
				$cmd = $type == 'host' ? nagioscmd::command_id('ACKNOWLEDGE_HOST_PROBLEM') : nagioscmd::command_id('ACKNOWLEDGE_SVC_PROBLEM');
			} ?>
		<tr>
			<td class="icon dark">
				<span class="icon-16 x16-<?php echo $img ?>" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<?php
			$img = 'notify-disabled';
			if ($result->notifications_enabled) {
				$label = _("Disable notifications for this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_NOTIFICATIONS') : nagioscmd::command_id('DISABLE_SVC_NOTIFICATIONS');
			} else {
				$label = _("Enable notifications for this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_NOTIFICATIONS') : nagioscmd::command_id('ENABLE_SVC_NOTIFICATIONS');
			} ?>
			<td class="icon dark">
				<span class="icon-16 x16-<?php echo $img ?>" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<tr>
			<?php
			$label = _('Send custom notification');
			$cmd = $type == 'host' ? nagioscmd::command_id('SEND_CUSTOM_HOST_NOTIFICATION') : nagioscmd::command_id('SEND_CUSTOM_SVC_NOTIFICATION');
			?>
			<td class="icon dark">
				<span class="icon-16 x16-notify-send" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<?php if ($result->state && $result->notifications_enabled) {
			$label = _("Delay next $type notification");
			$cmd = $type == 'host' ? nagioscmd::command_id('DELAY_HOST_NOTIFICATION') : nagioscmd::command_id('DELAY_SVC_NOTIFICATION');
		?>
		<tr>
			<td class="icon dark">
				<span class="icon-16 x16-notify-delay" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<?php
			$label = _("Schedule downtime for this $type");
			$cmd = $type == 'host' ?  nagioscmd::command_id('SCHEDULE_HOST_DOWNTIME') : nagioscmd::command_id('SCHEDULE_SVC_DOWNTIME');
			?>
			<td class="icon dark">
				<span class="icon-16 x16-scheduled-downtime" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<?php if ($type == 'host') {?>
		<tr>
			<?php
			$label = _('Disable notifications for all services on this host');
			$cmd = nagioscmd::command_id('DISABLE_HOST_SVC_NOTIFICATIONS');
			?>
			<td class="icon dark">
				<span class="icon-16 x16-notify-disabled" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<tr>
			<?php
			$label = _('Enable notifications for all services on this host');
			$cmd = nagioscmd::command_id('ENABLE_HOST_SVC_NOTIFICATIONS');
			?>
			<td class="icon dark">
				<span class="icon-16 x16-notify" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<tr>
			<?php
			$label = _('Schedule a check of all services on this host');
			$cmd = nagioscmd::command_id('SCHEDULE_HOST_SVC_CHECKS');
			?>
			<td class="icon dark">
				<span class="icon-16 x16-schedule" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<tr>
			<?php
			$label = _('Disable checks of all services on this host');
			$cmd = nagioscmd::command_id('DISABLE_HOST_SVC_CHECKS');
			?>
			<td class="icon dark">
				<span class="icon-16 x16-disabled" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<tr>
			<?php
			$label = _('Enable checks of all services on this host');
			$cmd = nagioscmd::command_id('ENABLE_HOST_SVC_CHECKS');
			?>
			<td class="icon dark">
				<span class="icon-16 x16-enabled" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<?php
			if ($result->event_handler_enabled) {
				$img = 'disabled';
				$label = _("Disable event handler for this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_EVENT_HANDLER') : nagioscmd::command_id('DISABLE_SVC_EVENT_HANDLER');
			} else {
				$img = 'enabled';
				$label = _("Enable event handler for this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_EVENT_HANDLER') : nagioscmd::command_id('ENABLE_SVC_EVENT_HANDLER');
			}
			?>
			<td class="icon dark">
				<span class="icon-16 x16-<?php echo $img ?>" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<tr>
			<?php
			if ($result->flap_detection_enabled) {
				$img = 'disabled';
				$label = _("Disable flap detection for this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('DISABLE_HOST_FLAP_DETECTION') : nagioscmd::command_id('DISABLE_SVC_FLAP_DETECTION');
			} else {
				$img = 'enabled';
				$label = _("Enable flap detection for this $type");
				$cmd = $type == 'host' ? nagioscmd::command_id('ENABLE_HOST_FLAP_DETECTION') : nagioscmd::command_id('ENABLE_SVC_FLAP_DETECTION');
			}
			?>
			<td class="icon dark">
				<span class="icon-16 x16-<?php echo $img ?>" title="<?php echo $label ?>"></span>
			</td>
			<td><?php echo nagioscmd::command_link($cmd, $host, $service, $label); ?></td>
		</tr>
		<?php
		if (isset($custom_commands)) {
			foreach ($custom_commands as $command_name => $link) {
				?>
				<tr>
					<td class="icon dark">
						<span class="icon-16 x16-cli" title="<?php echo _("$command_name") ?>"></span>
					</td>
					<td class="custom_command"><?php echo $link ?></td>
				</tr>
			<?php
			}
		}?>
	</table>
</div>

