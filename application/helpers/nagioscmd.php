<?php
/**
 * Nagios FIFO command helper
 */
class nagioscmd_Core
{
	/**
	 * Obtain information about a command.
	 * "information" in this case is a template we can use to inject
	 * one such command into Nagios' FIFO, a description of the
	 * command, it's name and the number Nagios has assigned to it via
	 * a macro.
	 * @param $name The 'name' of the command (DEL_HOST_COMMENT, fe)
	 * @return array with command information if a command was found, or
	 *         false otherwise
	 */
	public static function cmd_info($name = false)
	{
		if (empty($name)) {
			return false;
		}

		$t = zend::instance('Registry')->get('Zend_Translate');
		$prod_name = Kohana::config('config.product_name');
		$command_info = array
			('NONE' => array
			 ('nagios_id' => 0,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			 ),
			 'ADD_HOST_COMMENT' => array
			 ('nagios_id' => 1,
			  'description' => sprintf($t->_('This command is used to add a comment for the specified host.  If you work with other administrators, you may find it useful to share information about a host that is having problems if more than one of you may be working on it.  If you do not check the \'persistent\' option, the comment will be automatically be deleted the next time %s is restarted. '), $prod_name),
			  'brief' => $t->_('You are trying to add a host comment'),
			  'template' => 'ADD_HOST_COMMENT;host_name;persistent;author;comment',
			 ),
			 'DEL_HOST_COMMENT' => array
			 ('nagios_id' => 2,
			  'description' => $t->_('This command is used to delete a specific host comment. '),
			  'brief' => $t->_('You are trying to delete a host comment'),
			  'template' => 'DEL_HOST_COMMENT;comment_id',
			 ),
			 'ADD_SVC_COMMENT' => array
			 ('nagios_id' => 3,
			  'description' => sprintf($t->_('This command is used to add a comment for the specified service.  If you work with other administrators, you may find it useful to share information about a host or service that is having problems if more than one of you may be working on it.  If you do not check the \'persistent\' option, the comment will automatically be deleted the next time %s is restarted. '), $prod_name),
			  'brief' => $t->_('You are trying to add a service comment'),
			  'template' => 'ADD_SVC_COMMENT;service;persistent;author;comment',
			 ),
			 'DEL_SVC_COMMENT' => array
			 ('nagios_id' => 4,
			  'description' => $t->_('This command is used to delete a specific service comment. '),
			  'brief' => $t->_('You are trying to delete a service comment'),
			  'template' => 'DEL_SVC_COMMENT;comment_id',
			 ),
			 'ENABLE_SVC_CHECK' => array
			 ('nagios_id' => 5,
			  'description' => $t->_('This command is used to enable active checks of a service. '),
			  'brief' => $t->_('You are trying to enable active checks of a service'),
			  'template' => 'ENABLE_SVC_CHECK;service',
			 ),
			 'DISABLE_SVC_CHECK' => array
			 ('nagios_id' => 6,
			  'description' => $t->_('This command is used to disable active checks of a service. '),
			  'brief' => $t->_('You are trying to disable active checks of a service'),
			  'template' => 'DISABLE_SVC_CHECK;service',
			 ),
			 'SCHEDULE_SVC_CHECK' => array
			 ('nagios_id' => 7,
			  'description' => sprintf($t->_('This command is used to schedule the next check of a service.  %s will re-queue the service to be checked at the time you specify. If you select the <i>force check</i> option, %s will force a check of the service regardless of both what time the scheduled check occurs and whether or not checks are enabled for the service. '), $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to schedule a service check'),
			  'template' => 'SCHEDULE_SVC_CHECK;service;check_time',
			 ),
			 'DELAY_SVC_NOTIFICATION' => array
			 ('nagios_id' => 9,
			  'description' => $t->_('This command is used to delay the next problem notification that is sent out for the specified service.  The notification delay will be disregarded if the service changes state before the next notification is scheduled to be sent out.  This command has no effect if the service is currently in an OK state. '),
			  'brief' => $t->_('You are trying to delay a service notification'),
			  'template' => 'DELAY_SVC_NOTIFICATION;service;notification_delay',
			 ),
			 'DELAY_HOST_NOTIFICATION' => array
			 ('nagios_id' => 10,
			  'description' => $t->_('This command is used to delay the next problem notification that is sent out for the specified host.  The notification delay will be disregarded if the host changes state before the next notification is scheduled to be sent out.  This command has no effect if the host is currently UP. '),
			  'brief' => $t->_('You are trying to delay a host notification'),
			  'template' => 'DELAY_HOST_NOTIFICATION;host_name;notification_delay',
			 ),
			 'DISABLE_NOTIFICATIONS' => array
			 ('nagios_id' => 11,
			  'description' => $t->_('This command is used to disable host and service notifications on a program-wide basis. '),
			  'brief' => $t->_('You are trying to disable notifications'),
			  'template' => 'DISABLE_NOTIFICATIONS',
			 ),
			 'ENABLE_NOTIFICATIONS' => array
			 ('nagios_id' => 12,
			  'description' => $t->_('This command is used to enable host and service notifications on a program-wide basis. '),
			  'brief' => $t->_('You are trying to enable notifications'),
			  'template' => 'ENABLE_NOTIFICATIONS',
			 ),
			 'RESTART_PROCESS' => array
			 ('nagios_id' => 13,
			  'description' => sprintf($t->_('This command is used to restart the %s process. Executing a restart command is equivalent to sending the process a HUP signal. All information will be flushed from memory, the configuration files will be re-read, and %s will start monitoring with the new configuration information. '), $prod_name, $prod_name),
			  'brief' => sprintf($t->_('You are trying to restart the %s process'), $prod_name),
			  'template' => 'RESTART_PROCESS',
			 ),
			 'SHUTDOWN_PROCESS' => array
			 ('nagios_id' => 14,
			  'description' => sprintf($t->_('This command is used to shutdown the %s process. Note: Once the %s has been shutdown, it cannot be restarted via the web interface! '), $prod_name, $prod_name),
			  'brief' => sprintf($t->_('You are trying to shutdown the %s process'), $prod_name),
			  'template' => 'SHUTDOWN_PROCESS',
			 ),
			 'ENABLE_HOST_SVC_CHECKS' => array
			 ('nagios_id' => 15,
			  'description' => $t->_('This command is used to enable active checks of all services associated with the specified host.  This <i>does not</i> enable checks of the host unless you check the \'Enable for host too\' option. '),
			  'brief' => $t->_('You are trying to enable active checks of all services on a host'),
			  'template' => 'ENABLE_HOST_SVC_CHECKS;host_name',
			 ),
			 'DISABLE_HOST_SVC_CHECKS' => array
			 ('nagios_id' => 16,
			  'description' => sprintf($t->_('This command is used to disable active checks of all services associated with the specified host.  When a service is disabled %s will not monitor the service.  Doing this will prevent any notifications being sent out for the specified service while it is disabled.  In order to have %s check the service in the future you will have to re-enable the service. Note that disabling service checks may not necessarily prevent notifications from being sent out about the host which those services are associated with.  This <i>does not</i> disable checks of the host unless you check the \'Disable for host too\' option. '), $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to disable active checks of all services on a host'),
			  'template' => 'DISABLE_HOST_SVC_CHECKS;host_name',
			 ),
			 'SCHEDULE_HOST_SVC_CHECKS' => array
			 ('nagios_id' => 17,
			  'description' => sprintf($t->_('This command is used to scheduled the next check of all services on the specified host.  If you select the <i>force check</i> option, %s will force a check of all services on the host regardless of both what time the scheduled checks occur and whether or not checks are enabled for those services. '), $prod_name),
			  'brief' => $t->_('You are trying to schedule a check of all services for a host'),
			  'template' => 'SCHEDULE_HOST_SVC_CHECKS;host_name;check_time',
			 ),
			 'DELAY_HOST_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 19,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DELAY_HOST_SVC_NOTIFICATIONS;host_name;notification_time',
			 ),
			 'DEL_ALL_HOST_COMMENTS' => array
			 ('nagios_id' => 20,
			  'description' => $t->_('This command is used to delete all comments associated with the specified host. '),
			  'brief' => $t->_('You are trying to delete all comments for a host'),
			  'template' => 'DEL_ALL_HOST_COMMENTS;host_name',
			 ),
			 'DEL_ALL_SVC_COMMENTS' => array
			 ('nagios_id' => 21,
			  'description' => $t->_('This command is used to delete all comments associated with the specified service. '),
			  'brief' => $t->_('You are trying to delete all comments for a service'),
			  'template' => 'DEL_ALL_SVC_COMMENTS;service',
			 ),
			 'ENABLE_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 22,
			  'description' => $t->_('This command is used to enable notifications for the specified service.  Notifications will only be sent out for the service state types you defined in your service definition. '),
			  'brief' => $t->_('You are trying to enable notifications for a service'),
			  'template' => 'ENABLE_SVC_NOTIFICATIONS;service',
			 ),
			 'DISABLE_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 23,
			  'description' => $t->_('This command is used to prevent notifications from being sent out for the specified service.  You will have to re-enable notifications for this service before any alerts can be sent out in the future. '),
			  'brief' => $t->_('You are trying to disable notifications for a service'),
			  'template' => 'DISABLE_SVC_NOTIFICATIONS;service',
			 ),
			 'ENABLE_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 24,
			  'description' => $t->_('This command is used to enable notifications for the specified host.  Notifications will only be sent out for the host state types you defined in your host definition.  Note that this command <i>does not</i> enable notifications for services associated with this host. '),
			  'brief' => $t->_('You are trying to enable notifications for a host'),
			  'template' => 'ENABLE_HOST_NOTIFICATIONS;host_name',
			 ),
			 'DISABLE_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 25,
			  'description' => $t->_('This command is used to prevent notifications from being sent out for the specified host.  You will have to re-enable notifications for this host before any alerts can be sent out in the future.  Note that this command <i>does not</i> disable notifications for services associated with this host. '),
			  'brief' => $t->_('You are trying to disable notifications for a host'),
			  'template' => 'DISABLE_HOST_NOTIFICATIONS;host_name',
			 ),
			 'ENABLE_ALL_NOTIFICATIONS_BEYOND_HOST' => array
			 ('nagios_id' => 26,
			  'description' => sprintf($t->_('This command is used to enable notifications for all hosts and services that lie "beyond" the specified host (from the view of %s). '), $prod_name),
			  'brief' => $t->_('You are trying to enable notifications for all hosts and services beyond a host'),
			  'template' => 'ENABLE_ALL_NOTIFICATIONS_BEYOND_HOST;host_name',
			 ),
			 'DISABLE_ALL_NOTIFICATIONS_BEYOND_HOST' => array
			 ('nagios_id' => 27,
			  'description' => sprintf($t->_('This command is used to temporarily prevent notifications from being sent out for all hosts and services that lie "beyone" the specified host (from the view of %s). '), $prod_name),
			  'brief' => $t->_('You are trying to disable notifications for all hosts and services beyond a host'),
			  'template' => 'DISABLE_ALL_NOTIFICATIONS_BEYOND_HOST;host_name',
			 ),
			 'ENABLE_HOST_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 28,
			  'description' => $t->_('This command is used to enable notifications for all services on the specified host.  Notifications will only be sent out for the service state types you defined in your service definition.  This <i>does not</i> enable notifications for the host unless you check the \'Enable for host too\' option. '),
			  'brief' => $t->_('You are trying to enable notifications for all services on a host'),
			  'template' => 'ENABLE_HOST_SVC_NOTIFICATIONS;host_name',
			 ),
			 'DISABLE_HOST_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 29,
			  'description' => $t->_('This command is used to prevent notifications from being sent out for all services on the specified host.  You will have to re-enable notifications for all services associated with this host before any alerts can be sent out in the future.  This <i>does not</i> prevent notifications from being sent out about the host unless you check the \'Disable for host too\' option. '),
			  'brief' => $t->_('You are trying to disable notifications for all services on a host'),
			  'template' => 'DISABLE_HOST_SVC_NOTIFICATIONS;host_name',
			 ),
			 'PROCESS_SERVICE_CHECK_RESULT' => array
			 ('nagios_id' => 30,
			  'description' => $t->_('This command is used to submit a passive check result for a service.  It is particularly useful for resetting security-related services to OK states once they have been dealt with. '),
			  'brief' => $t->_('You are trying to submit a passive check result for a service'),
			  'template' => 'PROCESS_SERVICE_CHECK_RESULT;service;return_code;plugin_output',
			 ),
			 'SAVE_STATE_INFORMATION' => array
			 ('nagios_id' => 31,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'SAVE_STATE_INFORMATION',
			 ),
			 'READ_STATE_INFORMATION' => array
			 ('nagios_id' => 32,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'READ_STATE_INFORMATION',
			 ),
			 'ACKNOWLEDGE_HOST_PROBLEM' => array
			 ('nagios_id' => 33,
			  'description' => $t->_('This command is used to acknowledge a host problem.  When a host problem is acknowledged, future notifications about problems are temporarily disabled until the host changes from its current state. If you want acknowledgement to disable notifications until the host recovers, check the \'Sticky Acknowledgement\' checkbox. Contacts for this host will receive a notification about the acknowledgement, so they are aware that someone is working on the problem.  Additionally, a comment will also be added to the host. Make sure to enter your name and fill in a brief description of what you are doing in the comment field.  If you would like the host comment to remain once the acknowledgement is removed, check the \'Persistent Comment\' checkbox.  If you do not want an acknowledgement notification sent out to the appropriate contacts, uncheck the \'Send Notification\' checkbox. '),
			  'brief' => $t->_('You are trying to acknowledge a host problem'),
			  'template' => 'ACKNOWLEDGE_HOST_PROBLEM;host_name;sticky;notify;persistent;author;comment',
			 ),
			 'ACKNOWLEDGE_SVC_PROBLEM' => array
			 ('nagios_id' => 34,
			  'description' => $t->_('This command is used to acknowledge a service problem.  When a service problem is acknowledged, future notifications about problems are temporarily disabled until the service changes from its current state. If you want acknowledgement to disable notifications until the service recovers, check the \'Sticky Acknowledgement\' checkbox. Contacts for this service will receive a notification about the acknowledgement, so they are aware that someone is working on the problem.  Additionally, a comment will also be added to the service. Make sure to enter your name and fill in a brief description of what you are doing in the comment field.  If you would like the service comment to remain once the acknowledgement is removed, check the \'Persistent Comment\' checkbox.  If you do not want an acknowledgement notification sent out to the appropriate contacts, uncheck the \'Send Notification\' checkbox. '),
			  'brief' => $t->_('You are trying to acknowledge a service problem'),
			  'template' => 'ACKNOWLEDGE_SVC_PROBLEM;service;sticky;notify;persistent;author;comment',
			 ),
			 'START_EXECUTING_SVC_CHECKS' => array
			 ('nagios_id' => 35,
			  'description' => $t->_('This command is used to resume execution of active service checks on a program-wide basis.  Individual services which are disabled will still not be checked. '),
			  'brief' => $t->_('You are trying to start executing active service checks'),
			  'template' => 'START_EXECUTING_SVC_CHECKS',
			 ),
			 'STOP_EXECUTING_SVC_CHECKS' => array
			 ('nagios_id' => 36,
			  'description' => sprintf($t->_('This command is used to temporarily stop %s from actively executing any service checks.  This will have the side effect of preventing any notifications from being sent out (for any and all services and hosts). Service checks will not be executed again until you issue a command to resume service check execution. '), $prod_name),
			  'brief' => $t->_('You are trying to stop executing active service checks'),
			  'template' => 'STOP_EXECUTING_SVC_CHECKS',
			 ),
			 'START_ACCEPTING_PASSIVE_SVC_CHECKS' => array
			 ('nagios_id' => 37,
			  'description' => sprintf($t->_('This command is used to make %s start accepting passive service check results that it finds in the external command file '), $prod_name),
			  'brief' => $t->_('You are trying to start accepting passive service checks'),
			  'template' => 'START_ACCEPTING_PASSIVE_SVC_CHECKS',
			 ),
			 'STOP_ACCEPTING_PASSIVE_SVC_CHECKS' => array
			 ('nagios_id' => 38,
			  'description' => sprintf($t->_('This command is use to make %s stop accepting passive service check results that it finds in the external command file.  All passive check results that are found will be ignored. '), $prod_name),
			  'brief' => $t->_('You are trying to stop accepting passive service checks'),
			  'template' => 'STOP_ACCEPTING_PASSIVE_SVC_CHECKS',
			 ),
			 'ENABLE_PASSIVE_SVC_CHECKS' => array
			 ('nagios_id' => 39,
			  'description' => sprintf($t->_('This command is used to allow %s to accept passive service check results that it finds in the external command file for this particular service. '), $prod_name),
			  'brief' => $t->_('You are trying to start accepting passive service checks for a service'),
			  'template' => 'ENABLE_PASSIVE_SVC_CHECKS;service',
			 ),
			 'DISABLE_PASSIVE_SVC_CHECKS' => array
			 ('nagios_id' => 40,
			  'description' => sprintf($t->_('This command is used to stop %s accepting passive service check results that it finds in the external command file for this particular service.  All passive check results that are found for this service will be ignored. '), $prod_name),
			  'brief' => $t->_('You are trying to stop accepting passive service checks for a service'),
			  'template' => 'DISABLE_PASSIVE_SVC_CHECKS;service',
			 ),
			 'ENABLE_EVENT_HANDLERS' => array
			 ('nagios_id' => 41,
			  'description' => sprintf($t->_('This command is used to allow %s to run host and service event handlers. '), $prod_name),
			  'brief' => $t->_('You are trying to enable event handlers'),
			  'template' => 'ENABLE_EVENT_HANDLERS',
			 ),
			 'DISABLE_EVENT_HANDLERS' => array
			 ('nagios_id' => 42,
			  'description' => sprintf($t->_('This command is used to temporarily prevent %s from running any host or service event handlers. '), $prod_name),
			  'brief' => $t->_('You are trying to disable event handlers'),
			  'template' => 'DISABLE_EVENT_HANDLERS',
			 ),
			 'ENABLE_HOST_EVENT_HANDLER' => array
			 ('nagios_id' => 43,
			  'description' => sprintf($t->_('This command is used to allow %s to run the host event handler for a service when necessary (if one is defined). '), $prod_name),
			  'brief' => $t->_('You are trying to enable the event handler for a host'),
			  'template' => 'ENABLE_HOST_EVENT_HANDLER;host_name',
			 ),
			 'DISABLE_HOST_EVENT_HANDLER' => array
			 ('nagios_id' => 44,
			  'description' => sprintf($t->_('This command is used to temporarily prevent %s from running the host event handler for a host. '), $prod_name),
			  'brief' => $t->_('You are trying to disable the event handler for a host'),
			  'template' => 'DISABLE_HOST_EVENT_HANDLER;host_name',
			 ),
			 'ENABLE_SVC_EVENT_HANDLER' => array
			 ('nagios_id' => 45,
			  'description' => sprintf($t->_('This command is used to allow %s to run the service event handler for a service when necessary (if one is defined). '), $prod_name),
			  'brief' => $t->_('You are trying to enable the event handler for a service'),
			  'template' => 'ENABLE_SVC_EVENT_HANDLER;service',
			 ),
			 'DISABLE_SVC_EVENT_HANDLER' => array
			 ('nagios_id' => 46,
			  'description' => sprintf($t->_('This command is used to temporarily prevent %s from running the service event handler for a service. '), $prod_name),
			  'brief' => $t->_('You are trying to disable the event handler for a service'),
			  'template' => 'DISABLE_SVC_EVENT_HANDLER;service',
			 ),
			 'ENABLE_HOST_CHECK' => array
			 ('nagios_id' => 47,
			  'description' => $t->_('This command is used to enable active checks of this host. '),
			  'brief' => $t->_('You are trying to enable active checks of a host'),
			  'template' => 'ENABLE_HOST_CHECK;host_name',
			 ),
			 'DISABLE_HOST_CHECK' => array
			 ('nagios_id' => 48,
			  'description' => sprintf($t->_('This command is used to temporarily prevent %s from actively checking the status of a host.  If %s needs to check the status of this host, it will assume that it is in the same state that it was in before checks were disabled. '), $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to disable active checks of a host'),
			  'template' => 'DISABLE_HOST_CHECK;host_name',
			 ),
			 'START_OBSESSING_OVER_SVC_CHECKS' => array
			 ('nagios_id' => 49,
			  'description' => sprintf($t->_('This command is used to have %s start obsessing over service checks.  Read the documentation on distributed monitoring for more information on this. '), $prod_name),
			  'brief' => $t->_('You are trying to start obsessing over service checks'),
			  'template' => 'START_OBSESSING_OVER_SVC_CHECKS',
			 ),
			 'STOP_OBSESSING_OVER_SVC_CHECKS' => array
			 ('nagios_id' => 50,
			  'description' => sprintf($t->_('This command is used stop %s from obsessing over service checks. '), $prod_name),
			  'brief' => $t->_('You are trying to stop obsessing over service checks'),
			  'template' => 'STOP_OBSESSING_OVER_SVC_CHECKS',
			 ),
			 'REMOVE_HOST_ACKNOWLEDGEMENT' => array
			 ('nagios_id' => 51,
			  'description' => $t->_('This command is used to remove an acknowledgement for a host problem.  Once the acknowledgement is removed, notifications may start being sent out about the host problem.  '),
			  'brief' => $t->_('You are trying to remove a host acknowledgement'),
			  'template' => 'REMOVE_HOST_ACKNOWLEDGEMENT;host_name',
			 ),
			 'REMOVE_SVC_ACKNOWLEDGEMENT' => array
			 ('nagios_id' => 52,
			  'description' => $t->_('This command is used to remove an acknowledgement for a service problem.  Once the acknowledgement is removed, notifications may start being sent out about the service problem. '),
			  'brief' => $t->_('You are trying to remove a service acknowledgement'),
			  'template' => 'REMOVE_SVC_ACKNOWLEDGEMENT;service',
			 ),
			 'SCHEDULE_FORCED_HOST_SVC_CHECKS' => array
			 ('nagios_id' => 53,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'SCHEDULE_FORCED_HOST_SVC_CHECKS;host_name;check_time',
			 ),
			 'SCHEDULE_FORCED_SVC_CHECK' => array
			 ('nagios_id' => 54,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'SCHEDULE_FORCED_SVC_CHECK;service;check_time',
			 ),
			 'SCHEDULE_HOST_DOWNTIME' => array
			 ('nagios_id' => 55,
			  'description' => sprintf($t->_('This command is used to schedule downtime for a host. During the specified downtime, %s will not send notifications out about the host. When the scheduled downtime expires, %s will send out notifications for this host as it normally would.  Scheduled downtimes are preserved across program shutdowns and restarts.  Both the start and end times should be specified in the following format:  <b>'.nagstat::date_format().'</b> (<a href="http://php.net/manual/en/function.date.php">see explanation of date-letters</a>). If you select the <i>fixed</i> option, the downtime will be in effect between the start and end times you specify.  If you do not select the <i>fixed</i> option, %s will treat this as "flexible" downtime.  Flexible downtime starts when the host goes down or becomes unreachable (sometime between the start and end times you specified) and lasts as long as the duration of time you enter.  The duration fields do not apply for fixed downtime. '), $prod_name, $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to schedule downtime for a host'),
			  'template' => 'SCHEDULE_HOST_DOWNTIME;host_name;start_time;end_time;fixed;trigger_id;duration;author;comment',
			 ),
			 'SCHEDULE_SVC_DOWNTIME' => array
			 ('nagios_id' => 56,
			  'description' => sprintf($t->_('This command is used to schedule downtime for a service.  During the specified downtime, %s will not send notifications out about the service. When the scheduled downtime expires, %s will send out notifications for this service as it normally would.  Scheduled downtimes are preserved across program shutdowns and restarts.  Both the start and end times should be specified in the following format:  <b>'.nagstat::date_format().'</b> (<a href="http://php.net/manual/en/function.date.php">see explanation of date-letters</a>). option, %s will treat this as "flexible" downtime.  Flexible downtime starts when the service enters a non-OK state (sometime between the start and end times you specified) and lasts as long as the duration of time you enter.  The duration fields do not apply for fixed downtime. '), $prod_name, $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to schedule downtime for a service'),
			  'template' => 'SCHEDULE_SVC_DOWNTIME;service;start_time;end_time;fixed;trigger_id;duration;author;comment',
			 ),
			 'ENABLE_HOST_FLAP_DETECTION' => array
			 ('nagios_id' => 57,
			  'description' => $t->_('This command is used to enable flap detection for a specific host.  If flap detection is disabled on a program-wide basis, this will have no effect, '),
			  'brief' => $t->_('You are trying to enable flap detection for a host'),
			  'template' => 'ENABLE_HOST_FLAP_DETECTION;host_name',
			 ),
			 'DISABLE_HOST_FLAP_DETECTION' => array
			 ('nagios_id' => 58,
			  'description' => $t->_('This command is used to disable flap detection for a specific host. '),
			  'brief' => $t->_('You are trying to disable flap detection for a host'),
			  'template' => 'DISABLE_HOST_FLAP_DETECTION;host_name',
			 ),
			 'ENABLE_SVC_FLAP_DETECTION' => array
			 ('nagios_id' => 59,
			  'description' => $t->_('This command is used to enable flap detection for a specific service.  If flap detection is disabled on a program-wide basis, this will have no effect, '),
			  'brief' => $t->_('You are trying to enable flap detection for a service'),
			  'template' => 'ENABLE_SVC_FLAP_DETECTION;service',
			 ),
			 'DISABLE_SVC_FLAP_DETECTION' => array
			 ('nagios_id' => 60,
			  'description' => $t->_('This command is used to disable flap detection for a specific service. '),
			  'brief' => $t->_('You are trying to disable flap detection for a service'),
			  'template' => 'DISABLE_SVC_FLAP_DETECTION;service',
			 ),
			 'ENABLE_FLAP_DETECTION' => array
			 ('nagios_id' => 61,
			  'description' => $t->_('This command is used to enable flap detection for hosts and services on a program-wide basis.  Individual hosts and services may have flap detection disabled. '),
			  'brief' => $t->_('You are trying to enable flap detection for hosts and services'),
			  'template' => 'ENABLE_FLAP_DETECTION',
			 ),
			 'DISABLE_FLAP_DETECTION' => array
			 ('nagios_id' => 62,
			  'description' => $t->_('This command is used to disable flap detection for hosts and services on a program-wide basis. '),
			  'brief' => $t->_('You are trying to disable flap detection for hosts and services'),
			  'template' => 'DISABLE_FLAP_DETECTION',
			 ),
			 'ENABLE_HOSTGROUP_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 63,
			  'description' => $t->_('This command is used to enable notifications for all services in the specified hostgroup.  Notifications will only be sent out for the service state types you defined in your service definitions.  This <i>does not</i> enable notifications for the hosts in this hostgroup unless you check the \'Enable for hosts too\' option. '),
			  'brief' => $t->_('You are trying to enable notifications for all services in a hostgroup'),
			  'template' => 'ENABLE_HOSTGROUP_SVC_NOTIFICATIONS;hostgroup_name',
			 ),
			 'DISABLE_HOSTGROUP_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 64,
			  'description' => $t->_('This command is used to prevent notifications from being sent out for all services in the specified hostgroup.  You will have to re-enable notifications for all services in this hostgroup before any alerts can be sent out in the future.  This <i>does not</i> prevent notifications from being sent out about the hosts in this hostgroup unless you check the \'Disable for hosts too\' option. '),
			  'brief' => $t->_('You are trying to disable notifications for all services in a hostgroup'),
			  'template' => 'DISABLE_HOSTGROUP_SVC_NOTIFICATIONS;hostgroup_name',
			 ),
			 'ENABLE_HOSTGROUP_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 65,
			  'description' => $t->_('This command is used to enable notifications for all hosts in the specified hostgroup.  Notifications will only be sent out for the host state types you defined in your host definitions. '),
			  'brief' => $t->_('You are trying to enable notifications for all hosts in a hostgroup'),
			  'template' => 'ENABLE_HOSTGROUP_HOST_NOTIFICATIONS;hostgroup_name',
			 ),
			 'DISABLE_HOSTGROUP_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 66,
			  'description' => $t->_('This command is used to prevent notifications from being sent out for all hosts in the specified hostgroup.  You will have to re-enable notifications for all hosts in this hostgroup before any alerts can be sent out in the future. '),
			  'brief' => $t->_('You are trying to disable notifications for all hosts in a hostgroup'),
			  'template' => 'DISABLE_HOSTGROUP_HOST_NOTIFICATIONS;hostgroup_name',
			 ),
			 'ENABLE_HOSTGROUP_SVC_CHECKS' => array
			 ('nagios_id' => 67,
			  'description' => $t->_('This command is used to enable active checks of all services in the specified hostgroup.  This <i>does not</i> enable active checks of the hosts in the hostgroup unless you check the \'Enable for hosts too\' option. '),
			  'brief' => $t->_('You are trying to enable active checks of all services in a hostgroup'),
			  'template' => 'ENABLE_HOSTGROUP_SVC_CHECKS;hostgroup_name',
			 ),
			 'DISABLE_HOSTGROUP_SVC_CHECKS' => array
			 ('nagios_id' => 68,
			  'description' => $t->_('This command is used to disable active checks of all services in the specified hostgroup.  This <i>does not</i> disable checks of the hosts in the hostgroup unless you check the \'Disable for hosts too\' option. '),
			  'brief' => $t->_('You are trying to disable active checks of all services in a hostgroup'),
			  'template' => 'DISABLE_HOSTGROUP_SVC_CHECKS;hostgroup_name',
			 ),
			 'CANCEL_HOST_DOWNTIME' => array
			 ('nagios_id' => 69,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'not_implemented' => true,
			 ),
			 'CANCEL_SVC_DOWNTIME' => array
			 ('nagios_id' => 70,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'not_implemented' => true,
			 ),
			 'CANCEL_ACTIVE_HOST_DOWNTIME' => array
			 ('nagios_id' => 71,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'not_implemented' => true,
			 ),
			 'CANCEL_PENDING_HOST_DOWNTIME' => array
			 ('nagios_id' => 72,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'not_implemented' => true,
			 ),
			 'CANCEL_ACTIVE_SVC_DOWNTIME' => array
			 ('nagios_id' => 73,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'not_implemented' => true,
			 ),
			 'CANCEL_PENDING_SVC_DOWNTIME' => array
			 ('nagios_id' => 74,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'not_implemented' => true,
			 ),
			 'CANCEL_ACTIVE_HOST_SVC_DOWNTIME' => array
			 ('nagios_id' => 75,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'not_implemented' => true,
			 ),
			 'CANCEL_PENDING_HOST_SVC_DOWNTIME' => array
			 ('nagios_id' => 76,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'not_implemented' => true,
			 ),
			 'FLUSH_PENDING_COMMANDS' => array
			 ('nagios_id' => 77,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'not_implemented' => true,
			 ),
			 'DEL_HOST_DOWNTIME' => array
			 ('nagios_id' => 78,
			  'description' => $t->_('This command is used to cancel active or pending scheduled downtime for the specified host. '),
			  'brief' => $t->_('You are trying to cancel scheduled downtime for a host'),
			  'template' => 'DEL_HOST_DOWNTIME;downtime_id',
			 ),
			 'DEL_SVC_DOWNTIME' => array
			 ('nagios_id' => 79,
			  'description' => $t->_('This command is used to cancel active or pending scheduled downtime for the specified service. '),
			  'brief' => $t->_('You are trying to cancel scheduled downtime for a service'),
			  'template' => 'DEL_SVC_DOWNTIME;downtime_id',
			 ),
			 'ENABLE_FAILURE_PREDICTION' => array
			 ('nagios_id' => 80,
			  'description' => $t->_('This command is used to enable failure prediction for hosts and services on a program-wide basis.  Individual hosts and services may have failure prediction disabled. '),
			  'brief' => $t->_('You are trying to enable failure prediction for hosts and service'),
			  'template' => 'ENABLE_FAILURE_PREDICTION',
			 ),
			 'DISABLE_FAILURE_PREDICTION' => array
			 ('nagios_id' => 81,
			  'description' => $t->_('This command is used to disable failure prediction for hosts and services on a program-wide basis. '),
			  'brief' => $t->_('You are trying to disable failure prediction for hosts and service'),
			  'template' => 'DISABLE_FAILURE_PREDICTION',
			 ),
			 'ENABLE_PERFORMANCE_DATA' => array
			 ('nagios_id' => 82,
			  'description' => $t->_('This command is used to enable the processing of performance data for hosts and services on a program-wide basis.  Individual hosts and services may have performance data processing disabled. '),
			  'brief' => $t->_('You are trying to enable performance data processing for hosts and services'),
			  'template' => 'ENABLE_PERFORMANCE_DATA',
			 ),
			 'DISABLE_PERFORMANCE_DATA' => array
			 ('nagios_id' => 83,
			  'description' => $t->_('This command is used to disable the processing of performance data for hosts and services on a program-wide basis. '),
			  'brief' => $t->_('You are trying to disable performance data processing for hosts and services'),
			  'template' => 'DISABLE_PERFORMANCE_DATA',
			 ),
			 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME' => array
			 ('nagios_id' => 84,
			  'description' => sprintf($t->_('This command is used to schedule downtime for all hosts in a hostgroup.  During the specified downtime, %s will not send notifications out about the hosts. When the scheduled downtime expires, %s will send out notifications for the hosts as it normally would.  Scheduled downtimes are preserved across program shutdowns and restarts.  Both the start and end times should be specified in the following format:  <b>'.nagstat::date_format().'</b> (<a href="http://php.net/manual/en/function.date.php">see explanation of date-letters</a>). If you select the <i>fixed</i> option, the downtime will be in effect between the start and end times you specify.  If you do not select the <i>fixed</i> option, %s will treat this as "flexible" downtime.  Flexible downtime starts when a host goes down or becomes unreachable (sometime between the start and end times you specified) and lasts as long as the duration of time you enter.  The duration fields do not apply for fixed dowtime. '), $prod_name, $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to schedule downtime for all hosts in a hostgroup'),
			  'template' => 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME;hostgroup_name;start_time;end_time;fixed;trigger_id;duration;author;comment',
			 ),
			 'SCHEDULE_HOSTGROUP_SVC_DOWNTIME' => array
			 ('nagios_id' => 85,
			  'description' => sprintf($t->_('This command is used to schedule downtime for all services in a hostgroup.  During the specified downtime, %s will not send notifications out about the services. When the scheduled downtime expires, %s will send out notifications for the services as it normally would.  Scheduled downtimes are preserved across program shutdowns and restarts.  Both the start and end times should be specified in the following format:  <b>'.nagstat::date_format().'</b> (<a href="http://php.net/manual/en/function.date.php">see explanation of date-letters</a>). If you select the <i>fixed</i> option, the downtime will be in effect between the start and end times you specify.  If you do not select the <i>fixed</i> option, %s will treat this as "flexible" downtime.  Flexible downtime starts when a service enters a non-OK state (sometime between the start and end times you specified) and lasts as long as the duration of time you enter.  The duration fields do not apply for fixed dowtime. Note that scheduling downtime for services does not automatically schedule downtime for the hosts those services are associated with.  If you want to also schedule downtime for all hosts in the hostgroup, check the \'Schedule downtime for hosts too\' option. '), $prod_name, $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to schedule downtime for all services in a hostgroup'),
			  'template' => 'SCHEDULE_HOSTGROUP_SVC_DOWNTIME;hostgroup_name;start_time;end_time;fixed;trigger_id;duration;author;comment',
			 ),
			 'SCHEDULE_HOST_SVC_DOWNTIME' => array
			 ('nagios_id' => 86,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'SCHEDULE_HOST_SVC_DOWNTIME;host_name;start_time;end_time;fixed;trigger_id;duration;author;comment',
			 ),
			 'PROCESS_HOST_CHECK_RESULT' => array
			 ('nagios_id' => 87,
			  'description' => $t->_('This command is used to submit a passive check result for a host. '),
			  'brief' => $t->_('You are trying to submit a passive check result for a host'),
			  'template' => 'PROCESS_HOST_CHECK_RESULT;host_name;status_code;plugin_output',
			 ),
			 'START_EXECUTING_HOST_CHECKS' => array
			 ('nagios_id' => 88,
			  'description' => $t->_('This command is used to enable active host checks on a program-wide basis. '),
			  'brief' => $t->_('You are trying to start executing host checks'),
			  'template' => 'START_EXECUTING_HOST_CHECKS',
			 ),
			 'STOP_EXECUTING_HOST_CHECKS' => array
			 ('nagios_id' => 89,
			  'description' => $t->_('This command is used to disable active host checks on a program-wide basis. '),
			  'brief' => $t->_('You are trying to stop executing host checks'),
			  'template' => 'STOP_EXECUTING_HOST_CHECKS',
			 ),
			 'START_ACCEPTING_PASSIVE_HOST_CHECKS' => array
			 ('nagios_id' => 90,
			  'description' => sprintf($t->_('This command is used to have %s start obsessing over host checks.  Read the documentation on distributed monitoring for more information on this. '), $prod_name),
			  'brief' => $t->_('You are trying to start accepting passive host checks'),
			  'template' => 'START_ACCEPTING_PASSIVE_HOST_CHECKS',
			 ),
			 'STOP_ACCEPTING_PASSIVE_HOST_CHECKS' => array
			 ('nagios_id' => 91,
			  'description' => sprintf($t->_('This command is used to stop %s from obsessing over host checks. '), $prod_name),
			  'brief' => $t->_('You are trying to stop accepting passive host checks'),
			  'template' => 'STOP_ACCEPTING_PASSIVE_HOST_CHECKS',
			 ),
			 'ENABLE_PASSIVE_HOST_CHECKS' => array
			 ('nagios_id' => 92,
			  'description' => sprintf($t->_('This command is used to allow %s to accept passive host check results that it finds in the external command file for a host. '), $prod_name),
			  'brief' => $t->_('You are trying to start accepting passive checks for a host'),
			  'template' => 'ENABLE_PASSIVE_HOST_CHECKS;host_name',
			 ),
			 'DISABLE_PASSIVE_HOST_CHECKS' => array
			 ('nagios_id' => 93,
			  'description' => sprintf($t->_('This command is used to stop %s from accepting passive host check results that it finds in the external command file for a host.  All passive check results that are found for this host will be ignored. '), $prod_name),
			  'brief' => $t->_('You are trying to stop accepting passive checks for a host'),
			  'template' => 'DISABLE_PASSIVE_HOST_CHECKS;host_name',
			 ),
			 'START_OBSESSING_OVER_HOST_CHECKS' => array
			 ('nagios_id' => 94,
			  'description' => sprintf($t->_('This command is used to have %s start obsessing over host checks.  Read the documentation on distributed monitoring for more information on this. '), $prod_name),
			  'brief' => $t->_('You are trying to start obsessing over host checks'),
			  'template' => 'START_OBSESSING_OVER_HOST_CHECKS',
			 ),
			 'STOP_OBSESSING_OVER_HOST_CHECKS' => array
			 ('nagios_id' => 95,
			  'description' => sprintf($t->_('This command is used to stop %s from obsessing over host checks. '), $prod_name),
			  'brief' => $t->_('You are trying to stop obsessing over host checks'),
			  'template' => 'STOP_OBSESSING_OVER_HOST_CHECKS',
			 ),
			 'SCHEDULE_HOST_CHECK' => array
			 ('nagios_id' => 96,
			  'description' => sprintf($t->_('This command is used to schedule the next check of a host. %s will re-queue the host to be checked at the time you specify. If you select the <i>force check</i> option, %s will force a check of the host regardless of both what time the scheduled check occurs and whether or not checks are enabled for the host.'), $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to schedule a host check'),
			  'template' => 'SCHEDULE_HOST_CHECK;host_name;check_time',
			 ),
			 'SCHEDULE_FORCED_HOST_CHECK' => array
			 ('nagios_id' => 98,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'SCHEDULE_FORCED_HOST_CHECK;host_name;check_time',
			 ),
			 'START_OBSESSING_OVER_SVC' => array
			 ('nagios_id' => 99,
			  'description' => sprintf($t->_('This command is used to have %s start obsessing over a service. '), $prod_name),
			  'brief' => $t->_('You are trying to start obsessing over a service'),
			  'template' => 'START_OBSESSING_OVER_SVC;service',
			 ),
			 'STOP_OBSESSING_OVER_SVC' => array
			 ('nagios_id' => 100,
			  'description' => sprintf($t->_('This command is used to stop %s from obsessing over a service. '), $prod_name),
			  'brief' => $t->_('You are trying to stop obsessing over a service'),
			  'template' => 'STOP_OBSESSING_OVER_SVC;service',
			 ),
			 'START_OBSESSING_OVER_HOST' => array
			 ('nagios_id' => 101,
			  'description' => sprintf($t->_('This command is used to have %s start obsessing over a host. '), $prod_name),
			  'brief' => $t->_('You are trying to start obsessing over a host'),
			  'template' => 'START_OBSESSING_OVER_HOST;host_name',
			 ),
			 'STOP_OBSESSING_OVER_HOST' => array
			 ('nagios_id' => 102,
			  'description' => sprintf($t->_('This command is used to stop %s from obsessing over a host. '), $prod_name),
			  'brief' => $t->_('You are trying to stop obsessing over a host'),
			  'template' => 'STOP_OBSESSING_OVER_HOST;host_name',
			 ),
			 'ENABLE_HOSTGROUP_HOST_CHECKS' => array
			 ('nagios_id' => 103,
			  'description' => $t->_('This command is used to enable active checks of all hosts in the specified hostgroup. This <i>does not</i> enable active checks of the services in the hostgroup. '),
			  'brief' => $t->_('You are trying to enable active checks of all hosts in a hostgroup'),
			  'template' => 'ENABLE_HOSTGROUP_HOST_CHECKS;hostgroup_name',
			 ),
			 'DISABLE_HOSTGROUP_HOST_CHECKS' => array
			 ('nagios_id' => 104,
			  'description' => $t->_('This command is used to disable active checks of all hosts in the specified hostgroup. This <i>does not</i> disable  active checks of the services in the hostgroup. '),
			  'brief' => $t->_('You are trying to  enable active checks of all hosts in a hostgroup'),
			  'template' => 'DISABLE_HOSTGROUP_HOST_CHECKS;hostgroup_name',
			 ),
			 'ENABLE_HOSTGROUP_PASSIVE_SVC_CHECKS' => array
			 ('nagios_id' => 105,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_HOSTGROUP_PASSIVE_SVC_CHECKS;hostgroup_name',
			 ),
			 'DISABLE_HOSTGROUP_PASSIVE_SVC_CHECKS' => array
			 ('nagios_id' => 106,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_HOSTGROUP_PASSIVE_SVC_CHECKS;hostgroup_name',
			 ),
			 'ENABLE_HOSTGROUP_PASSIVE_HOST_CHECKS' => array
			 ('nagios_id' => 107,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_HOSTGROUP_PASSIVE_HOST_CHECKS;hostgroup_name',
			 ),
			 'DISABLE_HOSTGROUP_PASSIVE_HOST_CHECKS' => array
			 ('nagios_id' => 108,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_HOSTGROUP_PASSIVE_HOST_CHECKS;hostgroup_name',
			 ),
			 'ENABLE_SERVICEGROUP_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 109,
			  'description' => $t->_('This command is used to enable notifications for all services in the specified servicegroup.  Notifications will only be sent out for the service state types you defined in your service definitions.  This <i>does not</i> enable notifications for the hosts in this servicegroup unless you check the \'Enable for hosts too\' option. '),
			  'brief' => $t->_('You are trying to enable notifications for all services in a servicegroup'),
			  'template' => 'ENABLE_SERVICEGROUP_SVC_NOTIFICATIONS;servicegroup_name',
			 ),
			 'DISABLE_SERVICEGROUP_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 110,
			  'description' => $t->_('This command is used to prevent notifications from being sent out for all services in the specified servicegroup.  You will have to re-enable notifications for all services in this servicegroup before any alerts can be sent out in the future.  This <i>does not</i> prevent notifications from being sent out about the hosts in this servicegroup unless you check the \'Disable for hosts too\' option. '),
			  'brief' => $t->_('You are trying to disable notifications for all services in a servicegroup'),
			  'template' => 'DISABLE_SERVICEGROUP_SVC_NOTIFICATIONS;servicegroup_name',
			 ),
			 'ENABLE_SERVICEGROUP_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 111,
			  'description' => $t->_('This command is used to enable notifications for all hosts in the specified servicegroup.  Notifications will only be sent out for the host state types you defined in your host definitions. '),
			  'brief' => $t->_('You are trying to enable notifications for all hosts in a servicegroup'),
			  'template' => 'ENABLE_SERVICEGROUP_HOST_NOTIFICATIONS;servicegroup_name',
			 ),
			 'DISABLE_SERVICEGROUP_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 112,
			  'description' => $t->_('This command is used to prevent notifications from being sent out for all hosts in the specified servicegroup.  You will have to re-enable notifications for all hosts in this servicegroup before any alerts can be sent out in the future. '),
			  'brief' => $t->_('You are trying to disable notifications for all hosts in a servicegroup'),
			  'template' => 'DISABLE_SERVICEGROUP_HOST_NOTIFICATIONS;servicegroup_name',
			 ),
			 'ENABLE_SERVICEGROUP_SVC_CHECKS' => array
			 ('nagios_id' => 113,
			  'description' => $t->_('This command is used to enable active checks of all services in the specified servicegroup.  This <i>does not</i> enable active checks of the hosts in the servicegroup unless you check the \'Enable for hosts too\' option. '),
			  'brief' => $t->_('You are trying to enable active checks of all services in a servicegroup'),
			  'template' => 'ENABLE_SERVICEGROUP_SVC_CHECKS;servicegroup_name',
			 ),
			 'DISABLE_SERVICEGROUP_SVC_CHECKS' => array
			 ('nagios_id' => 114,
			  'description' => $t->_('This command is used to disable active checks of all services in the specified servicegroup.  This <i>does not</i> disable checks of the hosts in the servicegroup unless you check the \'Disable for hosts too\' option. '),
			  'brief' => $t->_('You are trying to disable active checks of all services in a servicegroup'),
			  'template' => 'DISABLE_SERVICEGROUP_SVC_CHECKS;servicegroup_name',
			 ),
			 'ENABLE_SERVICEGROUP_HOST_CHECKS' => array
			 ('nagios_id' => 115,
			  'description' => $t->_('This command is used to enable active checks of all hosts in the specified servicegroup.  This <i>does not</i> enable active checks of the services in the servicegroup. '),
			  'brief' => $t->_('You are trying to enable active checks of all services in a servicegroup'),
			  'template' => 'ENABLE_SERVICEGROUP_HOST_CHECKS;servicegroup_name',
			 ),
			 'DISABLE_SERVICEGROUP_HOST_CHECKS' => array
			 ('nagios_id' => 116,
			  'description' => $t->_('This command is used to disable active checks of all hosts in the specified servicegroup.  This <i>does not</i> disable checks of the services in the servicegroup.'),
			  'brief' => $t->_('You are trying to disable active checks of all hosts in a servicegroup'),
			  'template' => 'DISABLE_SERVICEGROUP_HOST_CHECKS;servicegroup_name',
			 ),
			 'ENABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS' => array
			 ('nagios_id' => 117,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS;servicegroup_name',
			 ),
			 'DISABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS' => array
			 ('nagios_id' => 118,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS;servicegroup_name',
			 ),
			 'ENABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS' => array
			 ('nagios_id' => 119,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS;servicegroup_name',
			 ),
			 'DISABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS' => array
			 ('nagios_id' => 120,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS;servicegroup_name',
			 ),
			 'SCHEDULE_SERVICEGROUP_HOST_DOWNTIME' => array
			 ('nagios_id' => 121,
			  'description' => sprintf($t->_('This command is used to schedule downtime for all hosts in a servicegroup.  During the specified downtime, %s will not send notifications out about the hosts. When the scheduled downtime expires, %s will send out notifications for the hosts as it normally would.  Scheduled downtimes are preserved across program shutdowns and restarts.  Both the start and end times should be specified in the following format:  <b>'.nagstat::date_format().'</b> (<a href="http://php.net/manual/en/function.date.php">see explanation of date-letters</a>). If you select the <i>fixed</i> option, the downtime will be in effect between the start and end times you specify.  If you do not select the <i>fixed</i> option, %s will treat this as "flexible" downtime.  Flexible downtime starts when a host goes down or becomes unreachable (sometime between the start and end times you specified) and lasts as long as the duration of time you enter.  The duration fields do not apply for fixed dowtime. '), $prod_name, $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to schedule downtime for all hosts in a servicegroup'),
			  'template' => 'SCHEDULE_SERVICEGROUP_HOST_DOWNTIME;servicegroup_name;start_time;end_time;fixed;trigger_id;duration;author;comment',
			 ),
			 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME' => array
			 ('nagios_id' => 122,
			  'description' => sprintf($t->_('This command is used to schedule downtime for all services in a servicegroup.  During the specified downtime, %s will not send notifications out about the services. When the scheduled downtime expires, %s will send out notifications for the services as it normally would.  Scheduled downtimes are preserved across program shutdowns and restarts.  Both the start and end times should be specified in the following format:  <b>'.nagstat::date_format().'</b> (<a href="http://php.net/manual/en/function.date.php">see explanation of date-letters</a>). If you select the <i>fixed</i> option, the downtime will be in effect between the start and end times you specify.  If you do not select the <i>fixed</i> option, %s will treat this as "flexible" downtime.  Flexible downtime starts when a service enters a non-OK state (sometime between the start and end times you specified) and lasts as long as the duration of time you enter.  The duration fields do not apply for fixed dowtime. Note that scheduling downtime for services does not automatically schedule downtime for the hosts those services are associated with.  If you want to also schedule downtime for all hosts in the servicegroup, check the \'Schedule downtime for hosts too\' option. '), $prod_name, $prod_name, $prod_name),
			  'brief' => $t->_('You are trying to schedule downtime for all services in a servicegroup'),
			  'template' => 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME;servicegroup_name;start_time;end_time;fixed;trigger_id;duration;author;comment',
			 ),
			 'CHANGE_GLOBAL_HOST_EVENT_HANDLER' => array
			 ('nagios_id' => 123,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_GLOBAL_HOST_EVENT_HANDLER;event_handler_command',
			 ),
			 'CHANGE_GLOBAL_SVC_EVENT_HANDLER' => array
			 ('nagios_id' => 124,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_GLOBAL_SVC_EVENT_HANDLER;event_handler_command',
			 ),
			 'CHANGE_HOST_EVENT_HANDLER' => array
			 ('nagios_id' => 125,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_HOST_EVENT_HANDLER;host_name;event_handler_command',
			 ),
			 'CHANGE_SVC_EVENT_HANDLER' => array
			 ('nagios_id' => 126,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_SVC_EVENT_HANDLER;service;event_handler_command',
			 ),
			 'CHANGE_HOST_CHECK_COMMAND' => array
			 ('nagios_id' => 127,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_HOST_CHECK_COMMAND;host_name;check_command',
			 ),
			 'CHANGE_SVC_CHECK_COMMAND' => array
			 ('nagios_id' => 128,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_SVC_CHECK_COMMAND;service;check_command',
			 ),
			 'CHANGE_NORMAL_HOST_CHECK_INTERVAL' => array
			 ('nagios_id' => 129,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_NORMAL_HOST_CHECK_INTERVAL;host_name;check_interval',
			 ),
			 'CHANGE_NORMAL_SVC_CHECK_INTERVAL' => array
			 ('nagios_id' => 130,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_NORMAL_SVC_CHECK_INTERVAL;service;check_interval',
			 ),
			 'CHANGE_RETRY_SVC_CHECK_INTERVAL' => array
			 ('nagios_id' => 131,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_RETRY_SVC_CHECK_INTERVAL;service;check_interval',
			 ),
			 'CHANGE_MAX_HOST_CHECK_ATTEMPTS' => array
			 ('nagios_id' => 132,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_MAX_HOST_CHECK_ATTEMPTS;host_name;check_attempts',
			 ),
			 'CHANGE_MAX_SVC_CHECK_ATTEMPTS' => array
			 ('nagios_id' => 133,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_MAX_SVC_CHECK_ATTEMPTS;service;check_attempts',
			 ),
			 'SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME' => array
			 ('nagios_id' => 134,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME;host_name;start_time;end_time;fixed;trigger_id;duration;author;comment',
			 ),
			 'ENABLE_HOST_AND_CHILD_NOTIFICATIONS' => array
			 ('nagios_id' => 135,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_HOST_AND_CHILD_NOTIFICATIONS;host_name',
			 ),
			 'DISABLE_HOST_AND_CHILD_NOTIFICATIONS' => array
			 ('nagios_id' => 136,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_HOST_AND_CHILD_NOTIFICATIONS;host_name',
			 ),
			 'SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME' => array
			 ('nagios_id' => 137,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME;host_name;start_time;end_time;fixed;trigger_id;duration;author;comment',
			 ),
			 'ENABLE_SERVICE_FRESHNESS_CHECKS' => array
			 ('nagios_id' => 138,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_SERVICE_FRESHNESS_CHECKS',
			 ),
			 'DISABLE_SERVICE_FRESHNESS_CHECKS' => array
			 ('nagios_id' => 139,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_SERVICE_FRESHNESS_CHECKS',
			 ),
			 'ENABLE_HOST_FRESHNESS_CHECKS' => array
			 ('nagios_id' => 140,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_HOST_FRESHNESS_CHECKS',
			 ),
			 'DISABLE_HOST_FRESHNESS_CHECKS' => array
			 ('nagios_id' => 141,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_HOST_FRESHNESS_CHECKS',
			 ),
			 'SET_HOST_NOTIFICATION_NUMBER' => array
			 ('nagios_id' => 142,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'SET_HOST_NOTIFICATION_NUMBER;host_name;notification_number',
			 ),
			 'SET_SVC_NOTIFICATION_NUMBER' => array
			 ('nagios_id' => 143,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'SET_SVC_NOTIFICATION_NUMBER;service;notification_number',
			 ),
			 'CHANGE_HOST_CHECK_TIMEPERIOD' => array
			 ('nagios_id' => 144,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_HOST_CHECK_TIMEPERIOD;host_name;timeperiod',
			 ),
			 'CHANGE_SVC_CHECK_TIMEPERIOD' => array
			 ('nagios_id' => 145,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_SVC_CHECK_TIMEPERIOD;service;check_timeperiod',
			 ),
			 'PROCESS_FILE' => array
			 ('nagios_id' => 146,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'PROCESS_FILE;file_name;delete',
			 ),
			 'CHANGE_CUSTOM_HOST_VAR' => array
			 ('nagios_id' => 147,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_CUSTOM_HOST_VAR;host_name;varname;varvalue',
			 ),
			 'CHANGE_CUSTOM_SVC_VAR' => array
			 ('nagios_id' => 148,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_CUSTOM_SVC_VAR;service;varname;varvalue',
			 ),
			 'CHANGE_CUSTOM_CONTACT_VAR' => array
			 ('nagios_id' => 149,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_CUSTOM_CONTACT_VAR;contact_name;varname;varvalue',
			 ),
			 'ENABLE_CONTACT_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 150,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_CONTACT_HOST_NOTIFICATIONS;contact_name',
			 ),
			 'DISABLE_CONTACT_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 151,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_CONTACT_HOST_NOTIFICATIONS;contact_name',
			 ),
			 'ENABLE_CONTACT_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 152,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_CONTACT_SVC_NOTIFICATIONS;contact_name',
			 ),
			 'DISABLE_CONTACT_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 153,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_CONTACT_SVC_NOTIFICATIONS;contact_name',
			 ),
			 'ENABLE_CONTACTGROUP_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 154,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_CONTACTGROUP_HOST_NOTIFICATIONS;contactgroup_name',
			 ),
			 'DISABLE_CONTACTGROUP_HOST_NOTIFICATIONS' => array
			 ('nagios_id' => 155,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_CONTACTGROUP_HOST_NOTIFICATIONS;contactgroup_name',
			 ),
			 'ENABLE_CONTACTGROUP_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 156,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'ENABLE_CONTACTGROUP_SVC_NOTIFICATIONS;contactgroup_name',
			 ),
			 'DISABLE_CONTACTGROUP_SVC_NOTIFICATIONS' => array
			 ('nagios_id' => 157,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'DISABLE_CONTACTGROUP_SVC_NOTIFICATIONS;contactgroup_name',
			 ),
			 'CHANGE_RETRY_HOST_CHECK_INTERVAL' => array
			 ('nagios_id' => 158,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_RETRY_HOST_CHECK_INTERVAL;service;check_interval',
			 ),
			 'SEND_CUSTOM_HOST_NOTIFICATION' => array
			 ('nagios_id' => 159,
			  'description' => sprintf($t->_('This command is used to send a custom notification about the specified host.  Useful in emergencies when you need to notify admins of an issue regarding a monitored system or service. Custom notifications normally follow the regular notification logic in %s.  Selecting the <i>Forced</i> option will force the notification to be sent out, regardless of the time restrictions, whether or not notifications are enabled, etc.  Selecting the <i>Broadcast</i> option causes the notification to be sent out to all normal (non-escalated) and escalated contacts.  These options allow you to override the normal notification logic if you need to get an important message out. '), $prod_name),
			  'brief' => $t->_('You are trying to send a custom host notification'),
			  'template' => 'SEND_CUSTOM_HOST_NOTIFICATION;host_name;options;author;comment',
			 ),
			 'SEND_CUSTOM_SVC_NOTIFICATION' => array
			 ('nagios_id' => 160,
			  'description' => sprintf($t->_('This command is used to send a custom notification about the specified service.  Useful in emergencies when you need to notify admins of an issue regarding a monitored system or service. Custom notifications normally follow the regular notification logic in %s.  Selecting the <i>Forced</i> option will force the notification to be sent out, regardless of the time restrictions, whether or not notifications are enabled, etc.  Selecting the <i>Broadcast</i> option causes the notification to be sent out to all normal (non-escalated) and escalated contacts.  These options allow you to override the normal notification logic if you need to get an important message out. '), $prod_name),
			  'brief' => $t->_('You are trying to send a custom service notification'),
			  'template' => 'SEND_CUSTOM_SVC_NOTIFICATION;service;options;author;comment',
			 ),
			 'CHANGE_HOST_NOTIFICATION_TIMEPERIOD' => array
			 ('nagios_id' => 161,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_HOST_NOTIFICATION_TIMEPERIOD;host_name;notification_timeperiod',
			 ),
			 'CHANGE_SVC_NOTIFICATION_TIMEPERIOD' => array
			 ('nagios_id' => 162,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_SVC_NOTIFICATION_TIMEPERIOD;service;notification_timeperiod',
			 ),
			 'CHANGE_CONTACT_HOST_NOTIFICATION_TIMEPERIOD' => array
			 ('nagios_id' => 163,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_CONTACT_HOST_NOTIFICATION_TIMEPERIOD;contact_name;notification_timeperiod',
			 ),
			 'CHANGE_CONTACT_SVC_NOTIFICATION_TIMEPERIOD' => array
			 ('nagios_id' => 164,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_CONTACT_SVC_NOTIFICATION_TIMEPERIOD;contact_name;notification_timeperiod',
			 ),
			 'CHANGE_HOST_MODATTR' => array
			 ('nagios_id' => 165,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_HOST_MODATTR;host_name;value',
			 ),
			 'CHANGE_SVC_MODATTR' => array
			 ('nagios_id' => 166,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_SVC_MODATTR;service;value',
			 ),
			 'CHANGE_CONTACT_MODATTR' => array
			 ('nagios_id' => 167,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_CONTACT_MODATTR;contact_name;value',
			 ),
			 'CHANGE_CONTACT_MODHATTR' => array
			 ('nagios_id' => 168,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_CONTACT_MODHATTR;contact_name;value',
			 ),
			 'CHANGE_CONTACT_MODSATTR' => array
			 ('nagios_id' => 169,
			  'description' => sprintf($t->_('This command is not implemented in %s.'), $prod_name),
			  'brief' => $t->_('You are trying to execute an unsupported command.'),
			  'template' => 'CHANGE_CONTACT_MODSATTR;contact_name;value',
			 ),
		);

		if (isset($command_info[$name])) {
			$command_info[$name]['name'] = $name;
			return $command_info[$name];
		}

		if (!is_numeric($name)) {
			return false;
		}

		# we weren't given a name, but $name is numeric so we loop
		# loop the command_info array and look for a matching id
		foreach ($command_info as $cmd_name => $info) {
			if ($info['nagios_id'] == $name) {
				$info['name'] = $cmd_name;
				return $info;
			}
		}

		return false;
	}

	/**
	 * If the provided name is a valid command, return it, otherwise return false
	 */
	public static function cmd_name($name = false)
	{
		$info = self::cmd_info($name);
		if (empty($info) || isset($info['name'])) {
			return $info['name'];
		}
		return false;
	}

	/**
	 * Obtain the id for a command
	 * @param $name The name of the command
	 * @return False on errors, the numeric id on success (may be 0)
	 */
	public static function command_id($name)
	{
		if (empty($name))
			return false;

		$first = self::cmd_name($name);
		if ($first !== false) {
			return $first;
		}

		# handle a CMD_ prefixed name too
		if (substr($name, 0, 4) === 'CMD_') {
			return self::cmd_name(substr($name, 4));
		}
		return false;
	}

	/**
	 * Massage a parameter value to make it suitable for
	 * consumption by Nagios.
	 *
	 * @param $name The parameter name
	 * @value The parameter value
	 * @return The massaged parameter value
	 */
	private function massage_param($name, $value)
	{
		# We only massage *_time fields for now
		if (strpos($name, '_time') !== false) {
			return nagstat::timestamp_format(nagstat::date_format(), $value);
		}

		if ($name === 'duration') {
			return floatval($value) * 3600;
		}

		# notification_delay is given in minutes,
		# but nagios wants a unix timestamp
		if ($name === 'notification_delay') {
			return ($value * 60) + time();
		}

		return $value;
	}

	/**
	 * Construct a command suitable for passing to Nagios
	 *
	 * @param $cmd string or command-info array
	 * @param $param Parameters to use as macros for the template
	 * @return A command string on success, false on errors
	 */
	public function build_command($cmd, $param)
	{
		if (is_array($cmd))
			$info = $cmd;
		else
			$info = self::cmd_info($cmd);

		if (!$info || !$info['template'])
			return false;

		$template = explode(';', $info['template']);
		for ($i = 1; $i < count($template); $i++) {
			$k = $template[$i];
			if (isset($param[$k])) {
				if('trigger_id' == $k && is_array($param[$k])) {
					$v = current($param[$k]);
				} else {
					$v = $param[$k];
				}
			} else {
				# boolean variables that have gone missing mean "0"
				switch ($k) {
				 case 'persistent': case 'delete':
				 case 'fixed': case 'notify': case 'sticky':
					$v = 0;
					break;
				 default:
					$v = false;
					break;
				}
			}
			if ($v === false)
				continue;
			$template[$i] = nagioscmd::massage_param($k, $v);
		}

		return join(';', $template);
	}

	/**
	 * Obtain Nagios' macro name for the given command
	 * @param $id Numeric or string representation of command
	 * @return False on errors, Nagios' macro name as string on
	 */
	public function nagios_name($id)
	{
		if (is_numeric($id)) {
			$base_cmd = self::cmd_name($id);
			if (!$base_cmd) {
				return false;
			}
			return "CMD_" . $base_cmd;
		}
		if (self::command_id($name)) {
			return "CMD_" . $id;
		}
		return false;
	}

	/**
	 * Actually submit command to nagios
	 * @param $cmd The complete command
	 * @param $pipe_path Path to the nagios path
	 * @return false on error, else true
	 */
	public static function submit_to_nagios($cmd, $pipe_path)
	{
		$fh = fopen($pipe_path, "w");
		if ($fh === false)
			return false;

		$len = fprintf($fh, "[%d] %s\n", time(), $cmd);
		fclose($fh);
		if (!$len)
			return false;

		return true;
	}
}
