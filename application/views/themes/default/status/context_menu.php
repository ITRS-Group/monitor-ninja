<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
	<ul id="property_menu" class="contextMenu">
		<li class="schedule_downtime" id="_menu_schedule_host_downtime"><a href="#schedule_host_downtime"><?php echo $this->translate->_('Schedule downtime') ?></a></li>
		<li class="removeschedule_downtime" id="_menu_removeschedule_host_downtime"><a href="#del_host_downtime"><?php echo $this->translate->_('Cancel Scheduled Downtime') ?></a></li>
		<li class="acknowledge_problem" id="_menu_acknowledge_host_problem"><a href="#acknowledge_host_problem"><?php echo $this->translate->_('Acknowledge') ?></a></li>
		<li class="remove_acknowledgement" id="_menu_remove_host_acknowledgement"><a href="#remove_acknowledgement"><?php echo $this->translate->_('Remove problem acknowledgement') ?></a></li>
		<li class="disable_notifications" id="_menu_disable_host_notifications"><a href="#disable_notifications"><?php echo $this->translate->_('Disable host notifications') ?></a></li>
		<li class="enable_notifications" id="_menu_enable_host_notifications"><a href="#enable_notifications"><?php echo $this->translate->_('Enable host notifications') ?></a></li>
		<li class="disable_host_svc_notifications" id="_menu_disable_host_svc_notifications"><a href="#disable_host_svc_notifications"><?php echo $this->translate->_('Disable notifications for all services') ?></a></li>
		<li class="disable_check" id="_menu_disable_host_check"><a href="#disable_host_check"><?php echo $this->translate->_('Disable active checks') ?></a></li>
		<li class="enable_check" id="_menu_enable_host_check"><a href="#enable_host_check"><?php echo $this->translate->_('Enable active checks') ?></a></li>
		<li class="schedule_check" id="_menu_schedule_host_check"><a href="#schedule_host_check"><?php echo $this->translate->_('Reschedule host checks') ?></a></li>
		<li class="schedule_check" id="_menu_schedule_host_svc_checks"><a href="#schedule_host_svc_checks"><?php echo $this->translate->_('Schedule a check of all services on this host') ?></a></li>
		<li class="add_comment" id="_menu_add_host_comment"><a href="#add_host_comment"><?php echo $this->translate->_('Add host comment') ?></a></li>
	</ul>
	<ul id="svc_property_menu" class="contextMenu">
		<li class="schedule_downtime" id="_menu_schedule_svc_downtime"><a href="#schedule_svc_downtime"><?php echo $this->translate->_('Schedule downtime') ?></a></li>
		<li class="removeschedule_downtime" id="_menu_removeschedule_svc_downtime"><a href="#del_svc_downtime"><?php echo $this->translate->_('Cancel Scheduled Downtime') ?></a></li>
		<li class="acknowledge_problem" id="_menu_acknowledge_svc_problem"><a href="#acknowledge_svc_problem"><?php echo $this->translate->_('Acknowledge') ?></a></li>
		<li class="remove_acknowledgement" id="_menu_remove_svc_acknowledgement"><a href="#remove_acknowledgement"><?php echo $this->translate->_('Remove problem acknowledgement') ?></a></li>
		<li class="disable_notifications" id="_menu_disable_svc_notifications"><a href="#disable_notifications"><?php echo $this->translate->_('Disable service notifications') ?></a></li>
		<li class="enable_notifications" id="_menu_enable_svc_notifications"><a href="#enable_notifications"><?php echo $this->translate->_('Enable service notifications') ?></a></li>
		<li class="disable_check" id="_menu_disable_svc_check"><a href="#disable_svc_check"><?php echo $this->translate->_('Disable active checks') ?></a></li>
		<li class="enable_check" id="_menu_enable_svc_check"><a href="#enable_svc_check"><?php echo $this->translate->_('Enable active checks') ?></a></li>
		<li class="schedule_check" id="_menu_schedule_svc_check"><a href="#schedule_svc_check"><?php echo $this->translate->_('Reschedule service checks') ?></a></li>
		<li class="add_comment" id="_menu_add_svc_comment"><a href="#add_svc_comment"><?php echo $this->translate->_('Add service comment') ?></a></li>
	</ul>
