<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
	<ul id="property_menu" class="contextMenu">
		<li><a href="#" data-cmd="schedule_downtime"><?php echo _('Schedule downtime') ?></a></li>
		<!-- @TODO id of downtime<li><a href="#" data-cmd="del_host_downtime"><?php echo _('Cancel Scheduled Downtime') ?></a></li>-->
		<li><a href="#" data-cmd="acknowledge_problem"><?php echo _('Acknowledge') ?></a></li>
		<li><a href="#" data-cmd="remove_acknowledgement"><?php echo _('Remove problem acknowledgement') ?></a></li>
		<li><a href="#" data-cmd="stop_notifications"><?php echo _('Disable host notifications') ?></a></li>
		<li><a href="#" data-cmd="start_notifications"><?php echo _('Enable host notifications') ?></a></li>
		<li><a href="#" data-cmd="disable_service_notifications"><?php echo _('Disable notifications for all services') ?></a></li>
		<li><a href="#" data-cmd="disable_check"><?php echo _('Disable active checks') ?></a></li>
		<li><a href="#" data-cmd="enable_check"><?php echo _('Enable active checks') ?></a></li>
		<li><a href="#" data-cmd="schedule_check"><?php echo _('Reschedule host checks') ?></a></li>
		<li><a href="#" data-cmd="schedule_service_checks"><?php echo _('Schedule a check of all services on this host') ?></a></li>
		<li><a href="#" data-cmd="add_comment"><?php echo _('Add host comment') ?></a></li>
	</ul>
	<ul id="svc_property_menu" class="contextMenu">
		<li><a href="#" data-cmd="schedule_downtime"><?php echo _('Schedule downtime') ?></a></li>
		<!-- @TODO id of downtime<li><a href="#" data-cmd="del_svc_downtime"><?php echo _('Cancel Scheduled Downtime') ?></a></li>-->
		<li><a href="#" data-cmd="acknowledge_problem"><?php echo _('Acknowledge') ?></a></li>
		<li><a href="#" data-cmd="remove_acknowledgement"><?php echo _('Remove problem acknowledgement') ?></a></li>
		<li><a href="#" data-cmd="stop_notifications"><?php echo _('Disable service notifications') ?></a></li>
		<li><a href="#" data-cmd="start_notifications"><?php echo _('Enable service notifications') ?></a></li>
		<li><a href="#" data-cmd="disable_check"><?php echo _('Disable active checks') ?></a></li>
		<li><a href="#" data-cmd="enable_check"><?php echo _('Enable active checks') ?></a></li>
		<li><a href="#" data-cmd="schedule_check"><?php echo _('Reschedule service checks') ?></a></li>
		<li><a href="#" data-cmd="add_comment"><?php echo _('Add service comment') ?></a></li>
	</ul>
