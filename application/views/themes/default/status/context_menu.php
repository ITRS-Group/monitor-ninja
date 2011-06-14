<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
	<ul id="property_menu" class="contextMenu">
		<li class="schedule_downtime" id="_menu_schedule_host_downtime"><a href="#schedule_host_downtime"><?php echo $this->translate->_('Schedule Downtime') ?></a></li>
		<li class="acknowledge_problem" id="_menu_acknowledge_host_problem"><a href="#acknowledge_host_problem"><?php echo $this->translate->_('Acknowledge') ?></a></li>
		<li class="remove_acknowledgement" id="_menu_remove_host_acknowledgement"><a href="#remove_acknowledgement"><?php echo $this->translate->_('Remove Problem Acknowledgement') ?></a></li>
		<li class="disable_notifications" id="_menu_disable_host_notifications"><a href="#disable_notifications"><?php echo $this->translate->_('Disable Host Notifications') ?></a></li>
		<li class="enable_notifications" id="_menu_enable_host_notifications"><a href="#enable_notifications"><?php echo $this->translate->_('Enable Host Notifications') ?></a></li>
		<li class="disable_host_svc_notifications" id="_menu_disable_host_svc_notifications"><a href="#disable_host_svc_notifications"><?php echo $this->translate->_('Disable Notifications For All Services') ?></a></li>
		<li class="disable_check" id="_menu_disable_host_check"><a href="#disable_host_check"><?php echo $this->translate->_('Disable Active Checks') ?></a></li>
		<li class="enable_check" id="_menu_enable_host_check"><a href="#enable_host_check"><?php echo $this->translate->_('Enable Active Checks') ?></a></li>
		<li class="schedule_check" id="_menu_schedule_host_check"><a href="#schedule_host_check"><?php echo $this->translate->_('Reschedule Host Checks') ?></a></li>
		<li class="add_comment" id="_menu_add_host_comment"><a href="#add_host_comment"><?php echo $this->translate->_('Add Host Comment') ?></a></li>
	</ul>
	<ul id="svc_property_menu" class="contextMenu">
		<li class="schedule_downtime" id="_menu_schedule_svc_downtime"><a href="#schedule_svc_downtime"><?php echo $this->translate->_('Schedule Downtime') ?></a></li>
		<li class="acknowledge_problem" id="_menu_acknowledge_svc_problem"><a href="#acknowledge_svc_problem"><?php echo $this->translate->_('Acknowledge') ?></a></li>
		<li class="remove_acknowledgement" id="_menu_remove_svc_acknowledgement"><a href="#remove_acknowledgement"><?php echo $this->translate->_('Remove Problem Acknowledgement') ?></a></li>
		<li class="disable_notifications" id="_menu_disable_svc_notifications"><a href="#disable_notifications"><?php echo $this->translate->_('Disable Service Notifications') ?></a></li>
		<li class="enable_notifications" id="_menu_enable_svc_notifications"><a href="#enable_notifications"><?php echo $this->translate->_('Enable Service Notifications') ?></a></li>
		<li class="disable_check" id="_menu_disable_svc_check"><a href="#disable_svc_check"><?php echo $this->translate->_('Disable Active Checks') ?></a></li>
		<li class="enable_check" id="_menu_enable_svc_check"><a href="#enable_svc_check"><?php echo $this->translate->_('Enable Active Checks') ?></a></li>
		<li class="schedule_check" id="_menu_schedule_svc_check"><a href="#schedule_svc_check"><?php echo $this->translate->_('Reschedule Service Checks') ?></a></li>
		<li class="add_comment" id="_menu_add_svc_comment"><a href="#add_svc_comment"><?php echo $this->translate->_('Add Service Comment') ?></a></li>
	</ul>
