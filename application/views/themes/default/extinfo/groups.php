<div class="widget left w98" style="margin-top: 0px">
<table style="border-spacing: 1px; background-color: #dcdccd">
	<caption><?php echo $label_grouptype ?> <?php echo $label_commands.' '.$this->translate->_('for').': '.$group_alias.' ('.$groupname.')'; ?></caption>
	<tr>
		<td class="status icon">
			<?php echo html::image('application/views/themes/default/icons/16x16/downtime.png', array('alt' => $label_schedule_downtime_hosts.' '.$label_grouptype, 'title' => $label_schedule_downtime_hosts.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_schedule_downtime_hosts.'&'.strtolower($label_grouptype).'='.$groupname, $label_schedule_downtime_hosts." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image('application/views/themes/default/icons/16x16/downtime.png', array('alt' => $label_schedule_downtime_services.' '.$label_grouptype, 'title' => $label_schedule_downtime_services.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_schedule_downtime_services.'&'.strtolower($label_grouptype).'='.$groupname, $label_schedule_downtime_services." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image('application/views/themes/default/icons/16x16/notify.png', array('alt' => $label_enable.' '.$label_notifications_hosts.' '.$label_grouptype, 'title' => $label_enable.' '.$label_notifications_hosts.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_notifications_hosts.'&'.strtolower($label_grouptype).'='.$groupname, $label_enable." ".$label_notifications_hosts." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image('application/views/themes/default/icons/16x16/nofity-disabled.png', array('alt' => $label_disable.' '.$label_notifications_hosts.' '.$label_grouptype, 'title' => $label_disable.' '.$label_notifications_hosts.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_notifications_hosts.'&'.strtolower($label_grouptype).'='.$groupname, $label_disable." ".$label_notifications_hosts." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image('application/views/themes/default/icons/16x16/notify.png', array('alt' => $label_enable.' '.$label_notifications_services.' '.$label_grouptype, 'title' => $label_enable.' '.$label_notifications_services.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_notifications_services.'&'.strtolower($label_grouptype).'='.$groupname, $label_enable." ".$label_notifications_services." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image('application/views/themes/default/icons/16x16/nofity-disabled.png', array('alt' => $label_disable.' '.$label_notifications_services.' '.$label_grouptype, 'title' => $label_disable.' '.$label_notifications_services.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_notifications_services.'&'.strtolower($label_grouptype).'='.$groupname, $label_notifications_services." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image('application/views/themes/default/icons/16x16/enabled.png', array('alt' => $label_enable.' '.$label_active_checks.' '.$label_grouptype, 'title' => $label_enable.' '.$label_active_checks.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_active_checks.'&'.strtolower($label_grouptype).'='.$groupname, $label_enable." ".$label_active_checks." ".$label_grouptype); ?>
	</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image('application/views/themes/default/icons/16x16/disabled.png', array('alt' => $label_disable.' '.$label_active_checks.' '.$label_grouptype, 'title' => $label_disable.' '.$label_active_checks.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_active_checks.'&'.strtolower($label_grouptype).'='.$groupname, $label_disable." ".$label_active_checks." ".$label_grouptype); ?>
		</td>
	</tr>
</table>
</div>
