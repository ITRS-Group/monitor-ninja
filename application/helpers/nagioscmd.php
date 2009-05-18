<?php
/**
 * Nagios FIFO command helper
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class nagioscmd_Core
{
	/**
	 * Obtain information about a command
	 * "information" in this case is a template we can use to inject
	 * one such command into Nagios' FIFO, a description of the
	 * command, it's name and the number Nagios has assigned to it via
	 * a macro.
	 * @param $id The Nagios ID of the command (avoid if possible)
	 * @param $name The 'name' of the command (DEL_HOST_COMMENT, fe)
	 * @return array with command information if a command was found, or
	 *         false otherwise
	 */
	public function cmd_info($id = false, $name = false)
	{
		if (empty($name) && empty($id)) {
			return false;
		}

		# scripted in from Nagios' developer information portal
		$command_info = array
			('ACKNOWLEDGE_HOST_PROBLEM' => array
			 ("template" => "ACKNOWLEDGE_HOST_PROBLEM;host_name;sticky;notify;persistent;author;comment",
			  "description" => _("Allows you to acknowledge the current problem for the specified host.  By acknowledging the current problem, future notifications (for the same host state) are disabled.  If the &quot;sticky&quot; option is set to one (1), the acknowledgement will remain until the host returns to an UP state.  Otherwise the acknowledgement will automatically be removed when the host changes state.  If the &quot;notify&quot; option is set to one (1), a notification will be sent out to contacts indicating that the current host problem has been acknowledged.  If the &quot;persistent&quot; option is set to one (1), the comment associated with the acknowledgement will survive across restarts of the Nagios process.  If not, the comment will be deleted the next time Nagios restarts. "),
			  "nagios_id" => 33,
			  ),
			 'ACKNOWLEDGE_SVC_PROBLEM' => array
			 ("template" => "ACKNOWLEDGE_SVC_PROBLEM;host_name;service_description;sticky;notify;persistent;author;comment",
			  "description" => _("Allows you to acknowledge the current problem for the specified service.  By acknowledging the current problem, future notifications (for the same servicestate) are disabled.  If the &quot;sticky&quot; option is set to one (1), the acknowledgement will remain until the service returns to an OK state.  Otherwise the acknowledgement will automatically be removed when the service changes state.  If the &quot;notify&quot; option is set to one (1), a notification will be sent out to contacts indicating that the current service problem has been acknowledged.  If the &quot;persistent&quot; option is set to one (1), the comment associated with the acknowledgement will survive across restarts of the Nagios process.  If not, the comment will be deleted the next time Nagios restarts. "),
			  "nagios_id" => 34,
			  ),
			 'ADD_HOST_COMMENT' => array
			 ("template" => "ADD_HOST_COMMENT;host_name;persistent;author;comment",
			  "description" => _("Adds a comment to a particular host.  If the &quot;persistent&quot; field is set to zero (0), the comment will be deleted the next time Nagios is restarted.  Otherwise, the comment will persist across program restarts until it is deleted manually."),
			  "nagios_id" => 1,
			  ),
			 'ADD_SVC_COMMENT' => array
			 ("template" => "ADD_SVC_COMMENT;host_name;service_description;persistent;author;comment",
			  "description" => _("Adds a comment to a particular service.  If the &quot;persistent&quot; field is set to zero (0), the comment will be deleted the next time Nagios is restarted.  Otherwise, the comment will persist across program restarts until it is deleted manually."),
			  "nagios_id" => 3,
			  ),
			 'CHANGE_CONTACT_HOST_NOTIFICATION_TIMEPERIOD' => array
			 ("template" => "CHANGE_CONTACT_HOST_NOTIFICATION_TIMEPERIOD;contact_name;notification_timeperiod",
			  "description" => _("Changes the host notification timeperiod for a particular contact to what is specified by the &quot;notification_timeperiod&quot; option.  The &quot;notification_timeperiod&quot; option should be the short name of the timeperiod that is to be used as the contact's host notification timeperiod.  The timeperiod must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 163,
			  ),
			 'CHANGE_CONTACT_MODATTR' => array
			 ("template" => "CHANGE_CONTACT_MODATTR;contact_name;value",
			  "description" => _("This command changes the modified attributes value for the specified contact.  Modified attributes values are used by Nagios to determine which object properties should be retained across program restarts.  Thus, modifying the value of the attributes can affect data retention.  This is an advanced option and should only be used by people who are intimately familiar with the data retention logic in Nagios."),
			  "nagios_id" => 167,
			  ),
			 'CHANGE_CONTACT_MODHATTR' => array
			 ("template" => "CHANGE_CONTACT_MODHATTR;contact_name;value",
			  "description" => _("This command changes the modified host attributes value for the specified contact.  Modified attributes values are used by Nagios to determine which object properties should be retained across program restarts.  Thus, modifying the value of the attributes can affect data retention.  This is an advanced option and should only be used by people who are intimately familiar with the data retention logic in Nagios."),
			  "nagios_id" => 168,
			  ),
			 'CHANGE_CONTACT_MODSATTR' => array
			 ("template" => "CHANGE_CONTACT_MODSATTR;contact_name;value",
			  "description" => _("This command changes the modified service attributes value for the specified contact.  Modified attributes values are used by Nagios to determine which object properties should be retained across program restarts.  Thus, modifying the value of the attributes can affect data retention.  This is an advanced option and should only be used by people who are intimately familiar with the data retention logic in Nagios."),
			  "nagios_id" => 169,
			  ),
			 'CHANGE_CONTACT_SVC_NOTIFICATION_TIMEPERIOD' => array
			 ("template" => "CHANGE_CONTACT_SVC_NOTIFICATION_TIMEPERIOD;contact_name;notification_timeperiod",
			  "description" => _("Changes the service notification timeperiod for a particular contact to what is specified by the &quot;notification_timeperiod&quot; option.  The &quot;notification_timeperiod&quot; option should be the short name of the timeperiod that is to be used as the contact's service notification timeperiod.  The timeperiod must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 164,
			  ),
			 'CHANGE_CUSTOM_CONTACT_VAR' => array
			 ("template" => "CHANGE_CUSTOM_CONTACT_VAR;contact_name;varname;varvalue",
			  "description" => _("Changes the value of a custom contact variable."),
			  "nagios_id" => 149,
			  ),
			 'CHANGE_CUSTOM_HOST_VAR' => array
			 ("template" => "CHANGE_CUSTOM_HOST_VAR;host_name;varname;varvalue",
			  "description" => _("Changes the value of a custom host variable."),
			  "nagios_id" => 147,
			  ),
			 'CHANGE_CUSTOM_SVC_VAR' => array
			 ("template" => "CHANGE_CUSTOM_SVC_VAR;host_name;service_description;varname;varvalue",
			  "description" => _("Changes the value of a custom service variable."),
			  "nagios_id" => 148,
			  ),
			 'CHANGE_GLOBAL_HOST_EVENT_HANDLER' => array
			 ("template" => "CHANGE_GLOBAL_HOST_EVENT_HANDLER;event_handler_command",
			  "description" => _("Changes the global host event handler command to be that specified by the &quot;event_handler_command&quot; option.  The &quot;event_handler_command&quot; option specifies the short name of the command that should be used as the new host event handler.  The command must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 123,
			  ),
			 'CHANGE_GLOBAL_SVC_EVENT_HANDLER' => array
			 ("template" => "CHANGE_GLOBAL_SVC_EVENT_HANDLER;event_handler_command",
			  "description" => _("Changes the global service event handler command to be that specified by the &quot;event_handler_command&quot; option.  The &quot;event_handler_command&quot; option specifies the short name of the command that should be used as the new service event handler.  The command must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 124,
			  ),
			 'CHANGE_HOST_CHECK_COMMAND' => array
			 ("template" => "CHANGE_HOST_CHECK_COMMAND;host_name;check_command",
			  "description" => _("Changes the check command for a particular host to be that specified by the &quot;check_command&quot; option.  The &quot;check_command&quot; option specifies the short name of the command that should be used as the new host check command.  The command must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 127,
			  ),
			 'CHANGE_HOST_CHECK_TIMEPERIOD' => array
			 ("template" => "CHANGE_HOST_CHECK_TIMEPERIOD;host_name;timeperiod",
			  "description" => _("Changes the valid check period for the specified host."),
			  "nagios_id" => 144,
			  ),
			 'CHANGE_HOST_EVENT_HANDLER' => array
			 ("template" => "CHANGE_HOST_EVENT_HANDLER;host_name;event_handler_command",
			  "description" => _("Changes the event handler command for a particular host to be that specified by the &quot;event_handler_command&quot; option.  The &quot;event_handler_command&quot; option specifies the short name of the command that should be used as the new host event handler.  The command must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 125,
			  ),
			 'CHANGE_HOST_MODATTR' => array
			 ("template" => "CHANGE_HOST_MODATTR;host_name;value",
			  "description" => _("This command changes the modified attributes value for the specified host.  Modified attributes values are used by Nagios to determine which object properties should be retained across program restarts.  Thus, modifying the value of the attributes can affect data retention.  This is an advanced option and should only be used by people who are intimately familiar with the data retention logic in Nagios."),
			  "nagios_id" => 165,
			  ),
			 'CHANGE_HOST_NOTIFICATION_TIMEPERIOD' => array
			 ("template" => "CHANGE_SVC_NOTIFICATION_TIMEPERIOD;host_name;service_description;notification_timeperiod",
			  "description" => _("Changes the notification timeperiod for a particular service to what is specified by the &quot;notification_timeperiod&quot; option.  The &quot;notification_timeperiod&quot; option should be the short name of the timeperiod that is to be used as the service notification timeperiod.  The timeperiod must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 161,
			  ),
			 'CHANGE_MAX_HOST_CHECK_ATTEMPTS' => array
			 ("template" => "CHANGE_MAX_HOST_CHECK_ATTEMPTS;host_name;check_attempts",
			  "description" => _("Changes the maximum number of check attempts (retries) for a particular host."),
			  "nagios_id" => 132,
			  ),
			 'CHANGE_MAX_SVC_CHECK_ATTEMPTS' => array
			 ("template" => "CHANGE_MAX_SVC_CHECK_ATTEMPTS;host_name;service_description;check_attempts",
			  "description" => _("Changes the maximum number of check attempts (retries) for a particular service."),
			  "nagios_id" => 133,
			  ),
			 'CHANGE_NORMAL_HOST_CHECK_INTERVAL' => array
			 ("template" => "CHANGE_NORMAL_HOST_CHECK_INTERVAL;host_name;check_interval",
			  "description" => _("Changes the normal (regularly scheduled) check interval for a particular host."),
			  "nagios_id" => 129,
			  ),
			 'CHANGE_NORMAL_SVC_CHECK_INTERVAL' => array
			 ("template" => "CHANGE_NORMAL_SVC_CHECK_INTERVAL;host_name;service_description;check_interval",
			  "description" => _("Changes the normal (regularly scheduled) check interval for a particular service"),
			  "nagios_id" => 130,
			  ),
			 'CHANGE_RETRY_HOST_CHECK_INTERVAL' => array
			 ("template" => "CHANGE_RETRY_HOST_CHECK_INTERVAL;host_name;service_description;check_interval",
			  "description" => _("Changes the retry check interval for a particular host."),
			  "nagios_id" => 158,
			  ),
			 'CHANGE_RETRY_SVC_CHECK_INTERVAL' => array
			 ("template" => "CHANGE_RETRY_SVC_CHECK_INTERVAL;host_name;service_description;check_interval",
			  "description" => _("Changes the retry check interval for a particular service."),
			  "nagios_id" => 131,
			  ),
			 'CHANGE_SVC_CHECK_COMMAND' => array
			 ("template" => "CHANGE_SVC_CHECK_COMMAND;host_name;service_description;check_command",
			  "description" => _("Changes the check command for a particular service to be that specified by the &quot;check_command&quot; option.  The &quot;check_command&quot; option specifies the short name of the command that should be used as the new service check command.  The command must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 128,
			  ),
			 'CHANGE_SVC_CHECK_TIMEPERIOD' => array
			 ("template" => "CHANGE_SVC_CHECK_TIMEPERIOD;host_name;service_description;check_timeperiod",
			  "description" => _("Changes the check timeperiod for a particular service to what is specified by the &quot;check_timeperiod&quot; option.  The &quot;check_timeperiod&quot; option should be the short name of the timeperod that is to be used as the service check timeperiod.  The timeperiod must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 145,
			  ),
			 'CHANGE_SVC_EVENT_HANDLER' => array
			 ("template" => "CHANGE_SVC_EVENT_HANDLER;host_name;service_description;event_handler_command",
			  "description" => _("Changes the event handler command for a particular service to be that specified by the &quot;event_handler_command&quot; option.  The &quot;event_handler_command&quot; option specifies the short name of the command that should be used as the new service event handler.  The command must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 126,
			  ),
			 'CHANGE_SVC_MODATTR' => array
			 ("template" => "CHANGE_SVC_MODATTR;host_name;service_description;value",
			  "description" => _("This command changes the modified attributes value for the specified service.  Modified attributes values are used by Nagios to determine which object properties should be retained across program restarts.  Thus, modifying the value of the attributes can affect data retention.  This is an advanced option and should only be used by people who are intimately familiar with the data retention logic in Nagios."),
			  "nagios_id" => 166,
			  ),
			 'CHANGE_SVC_NOTIFICATION_TIMEPERIOD' => array
			 ("template" => "CHANGE_SVC_NOTIFICATION_TIMEPERIOD;host_name;service_description;notification_timeperiod",
			  "description" => _("Changes the notification timeperiod for a particular service to what is specified by the &quot;notification_timeperiod&quot; option.  The &quot;notification_timeperiod&quot; option should be the short name of the timeperiod that is to be used as the service notification timeperiod.  The timeperiod must have been configured in Nagios before it was last (re)started."),
			  "nagios_id" => 162,
			  ),
			 'DEL_ALL_HOST_COMMENTS' => array
			 ("template" => "DEL_ALL_HOST_COMMENTS;host_name",
			  "description" => _("Deletes all comments assocated with a particular host."),
			  "nagios_id" => 20,
			  ),
			 'DEL_ALL_SVC_COMMENTS' => array
			 ("template" => "DEL_ALL_SVC_COMMENTS;host_name;service_description",
			  "description" => _("Deletes all comments associated with a particular service."),
			  "nagios_id" => 21,
			  ),
			 'DELAY_HOST_NOTIFICATION' => array
			 ("template" => "DELAY_HOST_NOTIFICATION;host_name;notification_time",
			  "description" => _("Delays the next notification for a parciular host until &quot;notification_time&quot;.  Note that this will only have an affect if the host stays in the same problem state that it is currently in.  If the host changes to another state, a new notification may go out before the time you specify in the &quot;notification_time&quot; argument."),
			  "nagios_id" => 10,
			  ),
			 'DELAY_HOST_SVC_NOTIFICATIONS' => array
			 ("template" => "DELAY_HOST_NOTIFICATION;host_name;notification_time",
			  "description" => _("Delays the next notification for all services associated with the selected host until &quot;notification_time&quot;.  Note that this will only have an affect if the host stays in the same problem state that it is currently in.  If the host changes to another state, a new notification may go out before the time you specify in the &quot;notification_time&quot; argument."),
			  "nagios_id" => 19,
			  ),
			 'DELAY_SVC_NOTIFICATION' => array
			 ("template" => "DELAY_SVC_NOTIFICATION;host_name;service_description;notification_time",
			  "description" => _("Delays the next notification for a parciular service until &quot;notification_time&quot;.  Note that this will only have an affect if the service stays in the same problem state that it is currently in.  If the service changes to another state, a new notification may go out before the time you specify in the &quot;notification_time&quot; argument."),
			  "nagios_id" => 9,
			  ),
			 'DEL_HOST_COMMENT' => array
			 ("template" => "DEL_HOST_COMMENT;comment_id",
			  "description" => _("Deletes a host comment.  The id number of the comment that is to be deleted must be specified."),
			  "nagios_id" => 2,
			  ),
			 'DEL_HOST_DOWNTIME' => array
			 ("template" => "DEL_HOST_DOWNTIME;downtime_id",
			  "description" => _("Deletes the host downtime entry that has an ID number matching the &quot;downtime_id&quot; argument.  If the downtime is currently in effect, the host will come out of scheduled downtime (as long as there are no other overlapping active downtime entries)."),
			  "nagios_id" => 78,
			  ),
			 'DEL_SVC_COMMENT' => array
			 ("template" => "DEL_SVC_COMMENT;comment_id",
			  "description" => _("Deletes a service comment.  The id number of the comment that is to be deleted must be specified."),
			  "nagios_id" => 4,
			  ),
			 'DEL_SVC_DOWNTIME' => array
			 ("template" => "DEL_SVC_DOWNTIME;downtime_id",
			  "description" => _("Deletes the service downtime entry that has an ID number matching the &quot;downtime_id&quot; argument.  If the downtime is currently in effect, the service will come out of scheduled downtime (as long as there are no other overlapping active downtime entries)."),
			  "nagios_id" => 79,
			  ),
			 'DISABLE_ALL_NOTIFICATIONS_BEYOND_HOST' => array
			 ("template" => "DISABLE_ALL_NOTIFICATIONS_BEYOND_HOST;host_name",
			  "description" => _("Disables notifications for all hosts and services &quot;beyond&quot; (e.g. on all child hosts of) the specified host.  The current notification setting for the specified host is not affected."),
			  "nagios_id" => 27,
			  ),
			 'DISABLE_CONTACTGROUP_HOST_NOTIFICATIONS' => array
			 ("template" => "DISABLE_CONTACTGROUP_HOST_NOTIFICATIONS;contactgroup_name",
			  "description" => _("Disables host notifications for all contacts in a particular contactgroup."),
			  "nagios_id" => 155,
			  ),
			 'DISABLE_CONTACTGROUP_SVC_NOTIFICATIONS' => array
			 ("template" => "DISABLE_CONTACTGROUP_SVC_NOTIFICATIONS;contactgroup_name",
			  "description" => _("Disables service notifications for all contacts in a particular contactgroup."),
			  "nagios_id" => 157,
			  ),
			 'DISABLE_CONTACT_HOST_NOTIFICATIONS' => array
			 ("template" => "DISABLE_CONTACT_HOST_NOTIFICATIONS;contact_name",
			  "description" => _("Disables host notifications for a particular contact."),
			  "nagios_id" => 151,
			  ),
			 'DISABLE_CONTACT_SVC_NOTIFICATIONS' => array
			 ("template" => "DISABLE_CONTACT_SVC_NOTIFICATIONS;contact_name",
			  "description" => _("Disables service notifications for a particular contact."),
			  "nagios_id" => 153,
			  ),
			 'DISABLE_EVENT_HANDLERS' => array
			 ("template" => "DISABLE_EVENT_HANDLERS",
			  "description" => _("Disables host and service event handlers on a program-wide basis."),
			  "nagios_id" => 42,
			  ),
			 'DISABLE_FAILURE_PREDICTION' => array
			 ("template" => "DISABLE_FAILURE_PREDICTION",
			  "description" => _("Disables failure prediction on a program-wide basis.  This feature is not currently implemented in Nagios."),
			  "nagios_id" => 81,
			  ),
			 'DISABLE_FLAP_DETECTION' => array
			 ("template" => "DISABLE_FLAP_DETECTION",
			  "description" => _("Disables host and service flap detection on a program-wide basis."),
			  "nagios_id" => 62,
			  ),
			 'DISABLE_HOST_AND_CHILD_NOTIFICATIONS' => array
			 ("template" => "DISABLE_HOST_AND_CHILD_NOTIFICATIONS;host_name",
			  "description" => _("Disables notifications for the specified host, as well as all hosts &quot;beyond&quot; (e.g. on all child hosts of) the specified host."),
			  "nagios_id" => 136,
			  ),
			 'DISABLE_HOST_CHECK' => array
			 ("template" => "DISABLE_HOST_CHECK;host_name",
			  "description" => _("Disables (regularly scheduled and on-demand) active checks of the specified host."),
			  "nagios_id" => 48,
			  ),
			 'DISABLE_HOST_EVENT_HANDLER' => array
			 ("template" => "DISABLE_HOST_EVENT_HANDLER;host_name",
			  "description" => _("Disables the event handler for the specified host."),
			  "nagios_id" => 44,
			  ),
			 'DISABLE_HOST_FLAP_DETECTION' => array
			 ("template" => "DISABLE_HOST_FLAP_DETECTION;host_name",
			  "description" => _("Disables flap detection for the specified host."),
			  "nagios_id" => 58,
			  ),
			 'DISABLE_HOST_FRESHNESS_CHECKS' => array
			 ("template" => "DISABLE_HOST_FRESHNESS_CHECKS",
			  "description" => _("Disables freshness checks of all hosts on a program-wide basis."),
			  "nagios_id" => 141,
			  ),
			 'DISABLE_HOSTGROUP_HOST_CHECKS' => array
			 ("template" => "DISABLE_HOSTGROUP_HOST_CHECKS;hostgroup_name",
			  "description" => _("Disables active checks for all hosts in a particular hostgroup."),
			  "nagios_id" => 104,
			  ),
			 'DISABLE_HOSTGROUP_HOST_NOTIFICATIONS' => array
			 ("template" => "DISABLE_HOSTGROUP_HOST_NOTIFICATIONS;hostgroup_name",
			  "description" => _("Disables notifications for all hosts in a particular hostgroup.  This does not disable notifications for the services associated with the hosts in the hostgroup - see the DISABLE_HOSTGROUP_SVC_NOTIFICATIONS command for that."),
			  "nagios_id" => 66,
			  ),
			 'DISABLE_HOSTGROUP_PASSIVE_HOST_CHECKS' => array
			 ("template" => "DISABLE_HOSTGROUP_PASSIVE_HOST_CHECKS;hostgroup_name",
			  "description" => _("Disables passive checks for all hosts in a particular hostgroup."),
			  "nagios_id" => 108,
			  ),
			 'DISABLE_HOSTGROUP_PASSIVE_SVC_CHECKS' => array
			 ("template" => "DISABLE_HOSTGROUP_PASSIVE_SVC_CHECKS;hostgroup_name",
			  "description" => _("Disables passive checks for all services associated with hosts in a particular hostgroup."),
			  "nagios_id" => 106,
			  ),
			 'DISABLE_HOSTGROUP_SVC_CHECKS' => array
			 ("template" => "DISABLE_HOSTGROUP_SVC_CHECKS;hostgroup_name",
			  "description" => _("Disables active checks for all services associated with hosts in a particular hostgroup."),
			  "nagios_id" => 68,
			  ),
			 'DISABLE_HOSTGROUP_SVC_NOTIFICATIONS' => array
			 ("template" => "DISABLE_HOSTGROUP_SVC_NOTIFICATIONS;hostgroup_name",
			  "description" => _("Disables notifications for all services associated with hosts in a particular hostgroup.  This does not disable notifications for the hosts in the hostgroup - see the DISABLE_HOSTGROUP_HOST_NOTIFICATIONS command for that."),
			  "nagios_id" => 64,
			  ),
			 'DISABLE_HOST_NOTIFICATIONS' => array
			 ("template" => "DISABLE_HOST_NOTIFICATIONS;host_name",
			  "description" => _("Disables notifications for a particular host."),
			  "nagios_id" => 25,
			  ),
			 'DISABLE_HOST_SVC_CHECKS' => array
			 ("template" => "DISABLE_HOST_SVC_CHECKS;host_name",
			  "description" => _("Enables active checks of all services on the specified host."),
			  "nagios_id" => 16,
			  ),
			 'DISABLE_HOST_SVC_NOTIFICATIONS' => array
			 ("template" => "DISABLE_HOST_SVC_NOTIFICATIONS;host_name",
			  "description" => _("Disables notifications for all services on the specified host."),
			  "nagios_id" => 29,
			  ),
			 'DISABLE_NOTIFICATIONS' => array
			 ("template" => "DISABLE_NOTIFICATIONS",
			  "description" => _("Disables host and service notifications on a program-wide basis."),
			  "nagios_id" => 11,
			  ),
			 'DISABLE_PASSIVE_HOST_CHECKS' => array
			 ("template" => "DISABLE_PASSIVE_HOST_CHECKS;host_name",
			  "description" => _("Disables acceptance and processing of passive host checks for the specified host."),
			  "nagios_id" => 93,
			  ),
			 'DISABLE_PASSIVE_SVC_CHECKS' => array
			 ("template" => "DISABLE_PASSIVE_SVC_CHECKS;host_name;service_description",
			  "description" => _("Disables passive checks for the specified service."),
			  "nagios_id" => 40,
			  ),
			 'DISABLE_PERFORMANCE_DATA' => array
			 ("template" => "DISABLE_PERFORMANCE_DATA",
			  "description" => _("Disables the processing of host and service performance data on a program-wide basis."),
			  "nagios_id" => 83,
			  ),
			 'DISABLE_SERVICE_FRESHNESS_CHECKS' => array
			 ("template" => "DISABLE_SERVICE_FRESHNESS_CHECKS",
			  "description" => _("Disables freshness checks of all services on a program-wide basis."),
			  "nagios_id" => 139,
			  ),
			 'DISABLE_SERVICEGROUP_HOST_CHECKS' => array
			 ("template" => "DISABLE_SERVICEGROUP_HOST_CHECKS;servicegroup_name",
			  "description" => _("Disables active checks for all hosts that have services that are members of a particular hostgroup."),
			  "nagios_id" => 116,
			  ),
			 'DISABLE_SERVICEGROUP_HOST_NOTIFICATIONS' => array
			 ("template" => "DISABLE_SERVICEGROUP_HOST_NOTIFICATIONS;servicegroup_name",
			  "description" => _("Disables notifications for all hosts that have services that are members of a particular servicegroup."),
			  "nagios_id" => 112,
			  ),
			 'DISABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS' => array
			 ("template" => "DISABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS;servicegroup_name",
			  "description" => _("Disables the acceptance and processing of passive checks for all hosts that have services that are members of a particular service group."),
			  "nagios_id" => 120,
			  ),
			 'DISABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS' => array
			 ("template" => "DISABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS;servicegroup_name",
			  "description" => _("Disables the acceptance and processing of passive checks for all services in a particular servicegroup."),
			  "nagios_id" => 118,
			  ),
			 'DISABLE_SERVICEGROUP_SVC_CHECKS' => array
			 ("template" => "DISABLE_SERVICEGROUP_SVC_CHECKS;servicegroup_name",
			  "description" => _("Disables active checks for all services in a particular servicegroup."),
			  "nagios_id" => 114,
			  ),
			 'DISABLE_SERVICEGROUP_SVC_NOTIFICATIONS' => array
			 ("template" => "DISABLE_SERVICEGROUP_SVC_NOTIFICATIONS;servicegroup_name",
			  "description" => _("Disables notifications for all services that are members of a particular servicegroup."),
			  "nagios_id" => 110,
			  ),
			 'DISABLE_SVC_CHECK' => array
			 ("template" => "DISABLE_SVC_CHECK;host_name;service_description",
			  "description" => _("Disables active checks for a particular service."),
			  "nagios_id" => 6,
			  ),
			 'DISABLE_SVC_EVENT_HANDLER' => array
			 ("template" => "DISABLE_SVC_EVENT_HANDLER;host_name;service_description",
			  "description" => _("Disables the event handler for the specified service."),
			  "nagios_id" => 46,
			  ),
			 'DISABLE_SVC_FLAP_DETECTION' => array
			 ("template" => "DISABLE_SVC_FLAP_DETECTION;host_name;service_description",
			  "description" => _("Disables flap detection for the specified service."),
			  "nagios_id" => 60,
			  ),
			 'DISABLE_SVC_NOTIFICATIONS' => array
			 ("template" => "DISABLE_SVC_NOTIFICATIONS;host_name;service_description",
			  "description" => _("Disables notifications for a particular service."),
			  "nagios_id" => 23,
			  ),
			 'ENABLE_ALL_NOTIFICATIONS_BEYOND_HOST' => array
			 ("template" => "ENABLE_ALL_NOTIFICATIONS_BEYOND_HOST;host_name",
			  "description" => _("Enables notifications for all hosts and services &quot;beyond&quot; (e.g. on all child hosts of) the specified host.  The current notification setting for the specified host is not affected.  Notifications will only be sent out for these hosts and services if notifications are also enabled on a program-wide basis."),
			  "nagios_id" => 26,
			  ),
			 'ENABLE_CONTACTGROUP_HOST_NOTIFICATIONS' => array
			 ("template" => "ENABLE_CONTACTGROUP_HOST_NOTIFICATIONS;contactgroup_name",
			  "description" => _("Enables host notifications for all contacts in a particular contactgroup."),
			  "nagios_id" => 154,
			  ),
			 'ENABLE_CONTACTGROUP_SVC_NOTIFICATIONS' => array
			 ("template" => "ENABLE_CONTACTGROUP_SVC_NOTIFICATIONS;contactgroup_name",
			  "description" => _("Enables service notifications for all contacts in a particular contactgroup."),
			  "nagios_id" => 156,
			  ),
			 'ENABLE_CONTACT_HOST_NOTIFICATIONS' => array
			 ("template" => "ENABLE_CONTACT_HOST_NOTIFICATIONS;contact_name",
			  "description" => _("Enables host notifications for a particular contact."),
			  "nagios_id" => 150,
			  ),
			 'ENABLE_CONTACT_SVC_NOTIFICATIONS' => array
			 ("template" => "ENABLE_CONTACT_SVC_NOTIFICATIONS;contact_name",
			  "description" => _("Disables service notifications for a particular contact."),
			  "nagios_id" => 152,
			  ),
			 'ENABLE_EVENT_HANDLERS' => array
			 ("template" => "ENABLE_EVENT_HANDLERS",
			  "description" => _("Enables host and service event handlers on a program-wide basis."),
			  "nagios_id" => 41,
			  ),
			 'ENABLE_FAILURE_PREDICTION' => array
			 ("template" => "ENABLE_FAILURE_PREDICTION",
			  "description" => _("Enables failure prediction on a program-wide basis.  This feature is not currently implemented in Nagios."),
			  "nagios_id" => 80,
			  ),
			 'ENABLE_FLAP_DETECTION' => array
			 ("template" => "ENABLE_FLAP_DETECTION",
			  "description" => _("Enables host and service flap detection on a program-wide basis."),
			  "nagios_id" => 61,
			  ),
			 'ENABLE_HOST_AND_CHILD_NOTIFICATIONS' => array
			 ("template" => "ENABLE_HOST_AND_CHILD_NOTIFICATIONS;host_name",
			  "description" => _("Enables notifications for the specified host, as well as all hosts &quot;beyond&quot; (e.g. on all child hosts of) the specified host.  Notifications will only be sent out for these hosts if notifications are also enabled on a program-wide basis."),
			  "nagios_id" => 135,
			  ),
			 'ENABLE_HOST_CHECK' => array
			 ("template" => "ENABLE_HOST_CHECK;host_name",
			  "description" => _("Enables (regularly scheduled and on-demand) active checks of the specified host."),
			  "nagios_id" => 47,
			  ),
			 'ENABLE_HOST_EVENT_HANDLER' => array
			 ("template" => "ENABLE_HOST_EVENT_HANDLER;host_name",
			  "description" => _("Enables the event handler for the specified host."),
			  "nagios_id" => 43,
			  ),
			 'ENABLE_HOST_FLAP_DETECTION' => array
			 ("template" => "ENABLE_HOST_FLAP_DETECTION;host_name",
			  "description" => _("Enables flap detection for the specified host.  In order for the flap detection algorithms to be run for the host, flap detection must be enabled on a program-wide basis as well."),
			  "nagios_id" => 57,
			  ),
			 'ENABLE_HOST_FRESHNESS_CHECKS' => array
			 ("template" => "ENABLE_HOST_FRESHNESS_CHECKS",
			  "description" => _("Enables freshness checks of all hosts on a program-wide basis.  Individual hosts that have freshness checks disabled will not be checked for freshness."),
			  "nagios_id" => 140,
			  ),
			 'ENABLE_HOSTGROUP_HOST_CHECKS' => array
			 ("template" => "ENABLE_HOSTGROUP_HOST_CHECKS;hostgroup_name",
			  "description" => _("Enables active checks for all hosts in a particular hostgroup."),
			  "nagios_id" => 103,
			  ),
			 'ENABLE_HOSTGROUP_HOST_NOTIFICATIONS' => array
			 ("template" => "ENABLE_HOSTGROUP_HOST_NOTIFICATIONS;hostgroup_name",
			  "description" => _("Enables notifications for all hosts in a particular hostgroup.  This does not enable notifications for the services associated with the hosts in the hostgroup - see the ENABLE_HOSTGROUP_SVC_NOTIFICATIONS command for that.  In order for notifications to be sent out for these hosts, notifications must be enabled on a program-wide basis as well."),
			  "nagios_id" => 65,
			  ),
			 'ENABLE_HOSTGROUP_PASSIVE_HOST_CHECKS' => array
			 ("template" => "ENABLE_HOSTGROUP_PASSIVE_HOST_CHECKS;hostgroup_name",
			  "description" => _("Enables passive checks for all hosts in a particular hostgroup."),
			  "nagios_id" => 107,
			  ),
			 'ENABLE_HOSTGROUP_PASSIVE_SVC_CHECKS' => array
			 ("template" => "ENABLE_HOSTGROUP_PASSIVE_SVC_CHECKS;hostgroup_name",
			  "description" => _("Enables passive checks for all services associated with hosts in a particular hostgroup."),
			  "nagios_id" => 105,
			  ),
			 'ENABLE_HOSTGROUP_SVC_CHECKS' => array
			 ("template" => "ENABLE_HOSTGROUP_SVC_CHECKS;hostgroup_name",
			  "description" => _("Enables active checks for all services associated with hosts in a particular hostgroup."),
			  "nagios_id" => 67,
			  ),
			 'ENABLE_HOSTGROUP_SVC_NOTIFICATIONS' => array
			 ("template" => "ENABLE_HOSTGROUP_SVC_NOTIFICATIONS;hostgroup_name",
			  "description" => _("Enables notifications for all services that are associated with hosts in a particular hostgroup.  This does not enable notifications for the hosts in the hostgroup - see the ENABLE_HOSTGROUP_HOST_NOTIFICATIONS command for that.  In order for notifications to be sent out for these services, notifications must be enabled on a program-wide basis as well."),
			  "nagios_id" => 63,
			  ),
			 'ENABLE_HOST_NOTIFICATIONS' => array
			 ("template" => "ENABLE_HOST_NOTIFICATIONS;host_name",
			  "description" => _("Enables notifications for a particular host.  Notifications will be sent out for the host only if notifications are enabled on a program-wide basis as well."),
			  "nagios_id" => 24,
			  ),
			 'ENABLE_HOST_SVC_CHECKS' => array
			 ("template" => "ENABLE_HOST_SVC_CHECKS;host_name",
			  "description" => _("Enables active checks of all services on the specified host."),
			  "nagios_id" => 15,
			  ),
			 'ENABLE_HOST_SVC_NOTIFICATIONS' => array
			 ("template" => "ENABLE_HOST_SVC_NOTIFICATIONS;host_name",
			  "description" => _("Enables notifications for all services on the specified host.  Note that notifications will not be sent out if notifications are disabled on a program-wide basis."),
			  "nagios_id" => 28,
			  ),
			 'ENABLE_NOTIFICATIONS' => array
			 ("template" => "ENABLE_NOTIFICATIONS",
			  "description" => _("Enables host and service notifications on a program-wide basis."),
			  "nagios_id" => 12,
			  ),
			 'ENABLE_PASSIVE_HOST_CHECKS' => array
			 ("template" => "ENABLE_PASSIVE_HOST_CHECKS;host_name",
			  "description" => _("Enables acceptance and processing of passive host checks for the specified host."),
			  "nagios_id" => 92,
			  ),
			 'ENABLE_PASSIVE_SVC_CHECKS' => array
			 ("template" => "ENABLE_PASSIVE_SVC_CHECKS;host_name;service_description",
			  "description" => _("Enables passive checks for the specified service."),
			  "nagios_id" => 39,
			  ),
			 'ENABLE_PERFORMANCE_DATA' => array
			 ("template" => "ENABLE_PERFORMANCE_DATA",
			  "description" => _("Enables the processing of host and service performance data on a program-wide basis."),
			  "nagios_id" => 82,
			  ),
			 'ENABLE_SERVICE_FRESHNESS_CHECKS' => array
			 ("template" => "ENABLE_SERVICE_FRESHNESS_CHECKS",
			  "description" => _("Enables freshness checks of all services on a program-wide basis.  Individual services that have freshness checks disabled will not be checked for freshness."),
			  "nagios_id" => 138,
			  ),
			 'ENABLE_SERVICEGROUP_HOST_CHECKS' => array
			 ("template" => "ENABLE_SERVICEGROUP_HOST_CHECKS;servicegroup_name",
			  "description" => _("Enables active checks for all hosts that have services that are members of a particular hostgroup."),
			  "nagios_id" => 115,
			  ),
			 'ENABLE_SERVICEGROUP_HOST_NOTIFICATIONS' => array
			 ("template" => "ENABLE_SERVICEGROUP_HOST_NOTIFICATIONS;servicegroup_name",
			  "description" => _("Enables notifications for all hosts that have services that are members of a particular servicegroup.  In order for notifications to be sent out for these hosts, notifications must also be enabled on a program-wide basis."),
			  "nagios_id" => 111,
			  ),
			 'ENABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS' => array
			 ("template" => "ENABLE_SERVICEGROUP_PASSIVE_HOST_CHECKS;servicegroup_name",
			  "description" => _("Enables the acceptance and processing of passive checks for all hosts that have services that are members of a particular service group."),
			  "nagios_id" => 119,
			  ),
			 'ENABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS' => array
			 ("template" => "ENABLE_SERVICEGROUP_PASSIVE_SVC_CHECKS;servicegroup_name",
			  "description" => _("Enables the acceptance and processing of passive checks for all services in a particular servicegroup."),
			  "nagios_id" => 117,
			  ),
			 'ENABLE_SERVICEGROUP_SVC_CHECKS' => array
			 ("template" => "ENABLE_SERVICEGROUP_SVC_CHECKS;servicegroup_name",
			  "description" => _("Enables active checks for all services in a particular servicegroup."),
			  "nagios_id" => 113,
			  ),
			 'ENABLE_SERVICEGROUP_SVC_NOTIFICATIONS' => array
			 ("template" => "ENABLE_SERVICEGROUP_SVC_NOTIFICATIONS;servicegroup_name",
			  "description" => _("Enables notifications for all services that are members of a particular servicegroup.  In order for notifications to be sent out for these services, notifications must also be enabled on a program-wide basis."),
			  "nagios_id" => 109,
			  ),
			 'ENABLE_SVC_CHECK' => array
			 ("template" => "ENABLE_SVC_CHECK;host_name;service_description",
			  "description" => _("Enables active checks for a particular service."),
			  "nagios_id" => 5,
			  ),
			 'ENABLE_SVC_EVENT_HANDLER' => array
			 ("template" => "ENABLE_SVC_EVENT_HANDLER;host_name;service_description",
			  "description" => _("Enables the event handler for the specified service."),
			  "nagios_id" => 45,
			  ),
			 'ENABLE_SVC_FLAP_DETECTION' => array
			 ("template" => "ENABLE_SVC_FLAP_DETECTION;host_name;service_description",
			  "description" => _("Enables flap detection for the specified service.  In order for the flap detection algorithms to be run for the service, flap detection must be enabled on a program-wide basis as well."),
			  "nagios_id" => 59,
			  ),
			 'ENABLE_SVC_NOTIFICATIONS' => array
			 ("template" => "ENABLE_SVC_NOTIFICATIONS;host_name;service_description",
			  "description" => _("Enables notifications for a particular service.  Notifications will be sent out for the service only if notifications are enabled on a program-wide basis as well."),
			  "nagios_id" => 22,
			  ),
			 'PROCESS_FILE' => array
			 ("template" => "PROCESS_FILE;file_name;delete",
			  "description" => _("Directs Nagios to process all external commands that are found in the file specified by the file_name argument.  If the delete option is non-zero, the file will be deleted once it has been processes.  If the delete option is set to zero, the file is left untouched."),
			  "nagios_id" => 146,
			  ),
			 'PROCESS_HOST_CHECK_RESULT' => array
			 ("template" => "PROCESS_HOST_CHECK_RESULT;host_name;status_code;plugin_output",
			  "description" => _("This is used to submit a passive check result for a particular host.  The &quot;status_code&quot; indicates the state of the host check and should be one of the following: 0=UP, 1=DOWN, 2=UNREACHABLE.  The &quot;plugin_output&quot; argument contains the text returned from the host check, along with optional performance data."),
			  "nagios_id" => 87,
			  ),
			 'PROCESS_SERVICE_CHECK_RESULT' => array
			 ("template" => "PROCESS_SERVICE_CHECK_RESULT;host_name;service_description;return_code;plugin_output",
			  "description" => _("This is used to submit a passive check result for a particular service.  The &quot;return_code&quot; field should be one of the following: 0=OK, 1=WARNING, 2=CRITICAL, 3=UNKNOWN.  The &quot;plugin_output&quot; field contains text output from the service check, along with optional performance data."),
			  "nagios_id" => 30,
			  ),
			 'READ_STATE_INFORMATION' => array
			 ("template" => "READ_STATE_INFORMATION",
			  "description" => _("Causes Nagios to load all current monitoring status information from the state retention file.  Normally, state retention information is loaded when the Nagios process starts up and before it starts monitoring.  WARNING: This command will cause Nagios to discard all current monitoring status information and use the information stored in state retention file!  Use with care."),
			  "nagios_id" => 32,
			  ),
			 'REMOVE_HOST_ACKNOWLEDGEMENT' => array
			 ("template" => "REMOVE_HOST_ACKNOWLEDGEMENT;host_name",
			  "description" => _("This removes the problem acknowledgement for a particular host.  Once the acknowledgement has been removed, notifications can once again be sent out for the given host."),
			  "nagios_id" => 51,
			  ),
			 'REMOVE_SVC_ACKNOWLEDGEMENT' => array
			 ("template" => "REMOVE_SVC_ACKNOWLEDGEMENT;host_name;service_description",
			  "description" => _("This removes the problem acknowledgement for a particular service.  Once the acknowledgement has been removed, notifications can once again be sent out for the given service."),
			  "nagios_id" => 52,
			  ),
			 'RESTART_PROCESS' => array
			 ("template" => "RESTART_PROGRAM",
			  "description" => _("Restarts the Nagios process."),
			  "nagios_id" => 13,
			  ),
			 'SAVE_STATE_INFORMATION' => array
			 ("template" => "SAVE_STATE_INFORMATION",
			  "description" => _("Causes Nagios to save all current monitoring status information to the state retention file.  Normally, state retention information is saved before the Nagios process shuts down and (potentially) at regularly scheduled intervals.  This command allows you to force Nagios to save this information to the state retention file immediately.  This does not affect the current status information in the Nagios process."),
			  "nagios_id" => 31,
			  ),
			 'SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME' => array
			 ("template" => "SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME;host_name;start_time;end_time;fixed;trigger_id;duration;author;comment",
			  "description" => _("Schedules downtime for a specified host and all of its children (hosts).  If the &quot;fixed&quot; argument is set to one (1), downtime will start and end at the times specified by the &quot;start&quot; and &quot;end&quot; arguments.  Otherwise, downtime will begin between the &quot;start&quot; and &quot;end&quot; times and last for &quot;duration&quot; seconds.  The &quot;start&quot; and &quot;end&quot; arguments are specified in time_t format (seconds since the UNIX epoch).  The specified (parent) host downtime can be triggered by another downtime entry if the &quot;trigger_id&quot; is set to the ID of another scheduled downtime entry.  Set the &quot;trigger_id&quot; argument to zero (0) if the downtime for the specified (parent) host should not be triggered by another downtime entry."),
			  "nagios_id" => 137,
			  ),
			 'SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME' => array
			 ("template" => "SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME;host_name;start_time;end_time;fixed;trigger_id;duration;author;comment",
			  "description" => _("Schedules downtime for a specified host and all of its children (hosts).  If the &quot;fixed&quot; argument is set to one (1), downtime will start and end at the times specified by the &quot;start&quot; and &quot;end&quot; arguments.  Otherwise, downtime will begin between the &quot;start&quot; and &quot;end&quot; times and last for &quot;duration&quot; seconds.  The &quot;start&quot; and &quot;end&quot; arguments are specified in time_t format (seconds since the UNIX epoch).  Downtime for child hosts are all set to be triggered by the downtime for the specified (parent) host.  The specified (parent) host downtime can be triggered by another downtime entry if the &quot;trigger_id&quot; is set to the ID of another scheduled downtime entry.  Set the &quot;trigger_id&quot; argument to zero (0) if the downtime for the specified (parent) host should not be triggered by another downtime entry."),
			  "nagios_id" => 134,
			  ),
			 'SCHEDULE_FORCED_HOST_CHECK' => array
			 ("template" => "SCHEDULE_FORCED_HOST_CHECK;host_name;check_time",
			  "description" => _("Schedules a forced active check of a particular host at &quot;check_time&quot;.  Forced checks are performed regardless of what time it is (e.g. timeperiod restrictions are ignored) and whether or not active checks are enabled on a host-specific or program-wide basis."),
			  "nagios_id" => 98,
			  ),
			 'SCHEDULE_FORCED_HOST_SVC_CHECKS' => array
			 ("template" => "SCHEDULE_FORCED_HOST_SVC_CHECKS;host_name;check_time",
			  "description" => _("Schedules a forced active check of all services associated with a particular host at &quot;check_time&quot;.  Forced checks are performed regardless of what time it is (e.g. timeperiod restrictions are ignored) and whether or not active checks are enabled on a service-specific or program-wide basis."),
			  "nagios_id" => 53,
			  ),
			 'SCHEDULE_FORCED_SVC_CHECK' => array
			 ("template" => "SCHEDULE_FORCED_SVC_CHECK;host_name;service_description;check_time",
			  "description" => _("Schedules a forced active check of a particular service at &quot;check_time&quot;.  Forced checks are performed regardless of what time it is (e.g. timeperiod restrictions are ignored) and whether or not active checks are enabled on a service-specific or program-wide basis."),
			  "nagios_id" => 54,
			  ),
			 'SCHEDULE_HOST_CHECK' => array
			 ("template" => "SCHEDULE_HOST_CHECK;host_name;check_time",
			  "description" => _("Schedules the next active check of a particular host at &quot;check_time&quot;.  Note that the host may not actually be checked at the time you specify.  This could occur for a number of reasons: active checks are disabled on a program-wide or service-specific basis, the host is already scheduled to be checked at an earlier time, etc.  If you want to force the host check to occur at the time you specify, look at the SCHEDULE_FORCED_HOST_CHECK command."),
			  "nagios_id" => 96,
			  ),
			 'SCHEDULE_HOST_DOWNTIME' => array
			 ("template" => "SCHEDULE_HOST_DOWNTIME;host_name;start_time;end_time;fixed;trigger_id;duration;author;comment",
			  "description" => _("Schedules downtime for a specified host.  If the &quot;fixed&quot; argument is set to one (1), downtime will start and end at the times specified by the &quot;start&quot; and &quot;end&quot; arguments.  Otherwise, downtime will begin between the &quot;start&quot; and &quot;end&quot; times and last for &quot;duration&quot; seconds.  The &quot;start&quot; and &quot;end&quot; arguments are specified in time_t format (seconds since the UNIX epoch).  The specified host downtime can be triggered by another downtime entry if the &quot;trigger_id&quot; is set to the ID of another scheduled downtime entry.  Set the &quot;trigger_id&quot; argument to zero (0) if the downtime for the specified host should not be triggered by another downtime entry."),
			  "nagios_id" => 55,
			  ),
			 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME' => array
			 ("template" => "SCHEDULE_HOSTGROUP_HOST_DOWNTIME;hostgroup_name;start_time;end_time;fixed;trigger_id;duration;author;comment",
			  "description" => _("Schedules downtime for all hosts in a specified hostgroup.  If the &quot;fixed&quot; argument is set to one (1), downtime will start and end at the times specified by the &quot;start&quot; and &quot;end&quot; arguments.  Otherwise, downtime will begin between the &quot;start&quot; and &quot;end&quot; times and last for &quot;duration&quot; seconds.  The &quot;start&quot; and &quot;end&quot; arguments are specified in time_t format (seconds since the UNIX epoch).  The host downtime entries can be triggered by another downtime entry if the &quot;trigger_id&quot; is set to the ID of another scheduled downtime entry.  Set the &quot;trigger_id&quot; argument to zero (0) if the downtime for the hosts should not be triggered by another downtime entry."),
			  "nagios_id" => 84,
			  ),
			 'SCHEDULE_HOSTGROUP_SVC_DOWNTIME' => array
			 ("template" => "SCHEDULE_HOSTGROUP_SVC_DOWNTIME;hostgroup_name;start_time;end_time;fixed;trigger_id;duration;author;comment",
			  "description" => _("Schedules downtime for all services associated with hosts in a specified servicegroup.  If the &quot;fixed&quot; argument is set to one (1), downtime will start and end at the times specified by the &quot;start&quot; and &quot;end&quot; arguments.  Otherwise, downtime will begin between the &quot;start&quot; and &quot;end&quot; times and last for &quot;duration&quot; seconds.  The &quot;start&quot; and &quot;end&quot; arguments are specified in time_t format (seconds since the UNIX epoch).  The service downtime entries can be triggered by another downtime entry if the &quot;trigger_id&quot; is set to the ID of another scheduled downtime entry.  Set the &quot;trigger_id&quot; argument to zero (0) if the downtime for the services should not be triggered by another downtime entry."),
			  "nagios_id" => 85,
			  ),
			 'SCHEDULE_HOST_SVC_CHECKS' => array
			 ("template" => "SCHEDULE_HOST_SVC_CHECKS;host_name;check_time",
			  "description" => _("Schedules the next active check of all services on a particular host at &quot;check_time&quot;.  Note that the services may not actually be checked at the time you specify.  This could occur for a number of reasons: active checks are disabled on a program-wide or service-specific basis, the services are already scheduled to be checked at an earlier time, etc.  If you want to force the service checks to occur at the time you specify, look at the SCHEDULE_FORCED_HOST_SVC_CHECKS command."),
			  "nagios_id" => 17,
			  ),
			 'SCHEDULE_HOST_SVC_DOWNTIME' => array
			 ("template" => "SCHEDULE_HOST_SVC_DOWNTIME;host_name;start_time;end_time;fixed;trigger_id;duration;author;comment",
			  "description" => _("Schedules downtime for all services associated with a particular host.  If the &quot;fixed&quot; argument is set to one (1), downtime will start and end at the times specified by the &quot;start&quot; and &quot;end&quot; arguments.  Otherwise, downtime will begin between the &quot;start&quot; and &quot;end&quot; times and last for &quot;duration&quot; seconds.  The &quot;start&quot; and &quot;end&quot; arguments are specified in time_t format (seconds since the UNIX epoch).  The service downtime entries can be triggered by another downtime entry if the &quot;trigger_id&quot; is set to the ID of another scheduled downtime entry.  Set the &quot;trigger_id&quot; argument to zero (0) if the downtime for the services should not be triggered by another downtime entry."),
			  "nagios_id" => 86,
			  ),
			 'SCHEDULE_SERVICEGROUP_HOST_DOWNTIME' => array
			 ("template" => "SCHEDULE_SERVICEGROUP_HOST_DOWNTIME;servicegroup_name;start_time;end_time;fixed;trigger_id;duration;author;comment",
			  "description" => _("Schedules downtime for all hosts that have services in a specified servicegroup.  If the &quot;fixed&quot; argument is set to one (1), downtime will start and end at the times specified by the &quot;start&quot; and &quot;end&quot; arguments.  Otherwise, downtime will begin between the &quot;start&quot; and &quot;end&quot; times and last for &quot;duration&quot; seconds.  The &quot;start&quot; and &quot;end&quot; arguments are specified in time_t format (seconds since the UNIX epoch).  The host downtime entries can be triggered by another downtime entry if the &quot;trigger_id&quot; is set to the ID of another scheduled downtime entry.  Set the &quot;trigger_id&quot; argument to zero (0) if the downtime for the hosts should not be triggered by another downtime entry."),
			  "nagios_id" => 121,
			  ),
			 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME' => array
			 ("template" => "SCHEDULE_SERVICEGROUP_SVC_DOWNTIME;servicegroup_name;start_time;end_time;fixed;trigger_id;duration;author;comment",
			  "description" => _("Schedules downtime for all services in a specified servicegroup.  If the &quot;fixed&quot; argument is set to one (1), downtime will start and end at the times specified by the &quot;start&quot; and &quot;end&quot; arguments.  Otherwise, downtime will begin between the &quot;start&quot; and &quot;end&quot; times and last for &quot;duration&quot; seconds.  The &quot;start&quot; and &quot;end&quot; arguments are specified in time_t format (seconds since the UNIX epoch).  The service downtime entries can be triggered by another downtime entry if the &quot;trigger_id&quot; is set to the ID of another scheduled downtime entry.  Set the &quot;trigger_id&quot; argument to zero (0) if the downtime for the services should not be triggered by another downtime entry."),
			  "nagios_id" => 122,
			  ),
			 'SCHEDULE_SVC_CHECK' => array
			 ("template" => "SCHEDULE_SVC_CHECK;host_name;service_description;check_time",
			  "description" => _("Schedules the next active check of a specified service at &quot;check_time&quot;.  Note that the service may not actually be checked at the time you specify.  This could occur for a number of reasons: active checks are disabled on a program-wide or service-specific basis, the service is already scheduled to be checked at an earlier time, etc.  If you want to force the service check to occur at the time you specify, look at the SCHEDULE_FORCED_SVC_CHECK command."),
			  "nagios_id" => 7,
			  ),
			 'SCHEDULE_SVC_DOWNTIME' => array
			 ("template" => "SCHEDULE_SVC_DOWNTIME;host_name;service_desriptionstart_time;end_time;fixed;trigger_id;duration;author;comment",
			  "description" => _("Schedules downtime for a specified service.  If the &quot;fixed&quot; argument is set to one (1), downtime will start and end at the times specified by the &quot;start&quot; and &quot;end&quot; arguments.  Otherwise, downtime will begin between the &quot;start&quot; and &quot;end&quot; times and last for &quot;duration&quot; seconds.  The &quot;start&quot; and &quot;end&quot; arguments are specified in time_t format (seconds since the UNIX epoch).  The specified service downtime can be triggered by another downtime entry if the &quot;trigger_id&quot; is set to the ID of another scheduled downtime entry.  Set the &quot;trigger_id&quot; argument to zero (0) if the downtime for the specified service should not be triggered by another downtime entry."),
			  "nagios_id" => 56,
			  ),
			 'SEND_CUSTOM_HOST_NOTIFICATION' => array
			 ("template" => "SEND_CUSTOM_HOST_NOTIFICATION;host_name;options;author;comment",
			  "description" => _("Allows you to send a custom host notification.  Very useful in dire situations, emergencies or to communicate with all admins that are responsible for a particular host.  When the host notification is sent out, the \$NOTIFICATIONTYPE\$ macro will be set to &quot;CUSTOM&quot;.  The options field is a logical OR of the following integer values that affect aspects of the notification that are sent out: 0 = No option (default), 1 = Broadcast (send notification to all normal and all escalated contacts for the host), 2 = Forced (notification is sent out regardless of current time, whether or not notifications are enabled, etc.), 4 = Increment current notification # for the host (this is not done by default for custom notifications).  The comment field can be used with the \$NOTIFICATIONCOMMENT\$ macro in notification commands."),
			  "nagios_id" => 159,
			  ),
			 'SEND_CUSTOM_SVC_NOTIFICATION' => array
			 ("template" => "SEND_CUSTOM_SVC_NOTIFICATION;host_name;service_description;options;author;comment",
			  "description" => _("Allows you to send a custom service notification.  Very useful in dire situations, emergencies or to communicate with all admins that are responsible for a particular service.  When the service notification is sent out, the \$NOTIFICATIONTYPE\$ macro will be set to &quot;CUSTOM&quot;.  The options field is a logical OR of the following integer values that affect aspects of the notification that are sent out: 0 = No option (default), 1 = Broadcast (send notification to all normal and all escalated contacts for the service), 2 = Forced (notification is sent out regardless of current time, whether or not notifications are enabled, etc.), 4 = Increment current notification # for the service(this is not done by default for custom notifications).  The comment field can be used with the \$NOTIFICATIONCOMMENT\$ macro in notification commands."),
			  "nagios_id" => 160,
			  ),
			 'SET_HOST_NOTIFICATION_NUMBER' => array
			 ("template" => "SET_HOST_NOTIFICATION_NUMBER;host_name;notification_number",
			  "description" => _("Sets the current notification number for a particular host.  A value of 0 indicates that no notification has yet been sent for the current host problem.  Useful for forcing an escalation (based on notification number) or replicating notification information in redundant monitoring environments. Notification numbers greater than zero have no noticeable affect on the notification process if the host is currently in an UP state."),
			  "nagios_id" => 142,
			  ),
			 'SET_SVC_NOTIFICATION_NUMBER' => array
			 ("template" => "SET_SVC_NOTIFICATION_NUMBER;host_name;service_description;notification_number",
			  "description" => _("Sets the current notification number for a particular service.  A value of 0 indicates that no notification has yet been sent for the current service problem.  Useful for forcing an escalation (based on notification number) or replicating notification information in redundant monitoring environments. Notification numbers greater than zero have no noticeable affect on the notification process if the service is currently in an OK state."),
			  "nagios_id" => 143,
			  ),
			 'SHUTDOWN_PROCESS' => array
			 ("template" => "SHUTDOWN_PROGRAM",
			  "description" => _("Shuts down the Nagios process."),
			  "nagios_id" => 14,
			  ),
			 'START_ACCEPTING_PASSIVE_HOST_CHECKS' => array
			 ("template" => "START_ACCEPTING_PASSIVE_HOST_CHECKS",
			  "description" => _("Enables acceptance and processing of passive host checks on a program-wide basis."),
			  "nagios_id" => 90,
			  ),
			 'START_ACCEPTING_PASSIVE_SVC_CHECKS' => array
			 ("template" => "START_ACCEPTING_PASSIVE_SVC_CHECKS",
			  "description" => _("Enables passive service checks on a program-wide basis."),
			  "nagios_id" => 37,
			  ),
			 'START_EXECUTING_HOST_CHECKS' => array
			 ("template" => "START_EXECUTING_HOST_CHECKS",
			  "description" => _("Enables active host checks on a program-wide basis."),
			  "nagios_id" => 88,
			  ),
			 'START_EXECUTING_SVC_CHECKS' => array
			 ("template" => "START_EXECUTING_SVC_CHECKS",
			  "description" => _("Enables active checks of services on a program-wide basis."),
			  "nagios_id" => 35,
			  ),
			 'START_OBSESSING_OVER_HOST' => array
			 ("template" => "START_OBSESSING_OVER_HOST;host_name",
			  "description" => _("Enables processing of host checks via the OCHP command for the specified host."),
			  "nagios_id" => 101,
			  ),
			 'START_OBSESSING_OVER_HOST_CHECKS' => array
			 ("template" => "START_OBSESSING_OVER_HOST_CHECKS",
			  "description" => _("Enables processing of host checks via the OCHP command on a program-wide basis."),
			  "nagios_id" => 94,
			  ),
			 'START_OBSESSING_OVER_SVC' => array
			 ("template" => "START_OBSESSING_OVER_SVC;host_name;service_description",
			  "description" => _("Enables processing of service checks via the OCSP command for the specified service."),
			  "nagios_id" => 99,
			  ),
			 'START_OBSESSING_OVER_SVC_CHECKS' => array
			 ("template" => "START_OBSESSING_OVER_SVC_CHECKS",
			  "description" => _("Enables processing of service checks via the OCSP command on a program-wide basis."),
			  "nagios_id" => 49,
			  ),
			 'STOP_ACCEPTING_PASSIVE_HOST_CHECKS' => array
			 ("template" => "STOP_ACCEPTING_PASSIVE_HOST_CHECKS",
			  "description" => _("Disables acceptance and processing of passive host checks on a program-wide basis."),
			  "nagios_id" => 91,
			  ),
			 'STOP_ACCEPTING_PASSIVE_SVC_CHECKS' => array
			 ("template" => "STOP_ACCEPTING_PASSIVE_SVC_CHECKS",
			  "description" => _("Disables passive service checks on a program-wide basis."),
			  "nagios_id" => 38,
			  ),
			 'STOP_EXECUTING_HOST_CHECKS' => array
			 ("template" => "STOP_EXECUTING_HOST_CHECKS",
			  "description" => _("Disables active host checks on a program-wide basis."),
			  "nagios_id" => 89,
			  ),
			 'STOP_EXECUTING_SVC_CHECKS' => array
			 ("template" => "STOP_EXECUTING_SVC_CHECKS",
			  "description" => _("Disables active checks of services on a program-wide basis."),
			  "nagios_id" => 36,
			  ),
			 'STOP_OBSESSING_OVER_HOST' => array
			 ("template" => "STOP_OBSESSING_OVER_HOST;host_name",
			  "description" => _("Disables processing of host checks via the OCHP command for the specified host."),
			  "nagios_id" => 102,
			  ),
			 'STOP_OBSESSING_OVER_HOST_CHECKS' => array
			 ("template" => "STOP_OBSESSING_OVER_HOST_CHECKS",
			  "description" => _("Disables processing of host checks via the OCHP command on a program-wide basis."),
			  "nagios_id" => 95,
			  ),
			 'STOP_OBSESSING_OVER_SVC' => array
			 ("template" => "STOP_OBSESSING_OVER_SVC;host_name;service_description",
			  "description" => _("Disables processing of service checks via the OCSP command for the specified service."),
			  "nagios_id" => 100,
			  ),
			 'STOP_OBSESSING_OVER_SVC_CHECKS' => array
			 ("template" => "STOP_OBSESSING_OVER_SVC_CHECKS",
			  "description" => _("Disables processing of service checks via the OCSP command on a program-wide basis."),
			  "nagios_id" => 50,
			  ),
			 );

		if (!empty($name) && isset($command_info[$name])) {
			$command_info[$name]['name'] = $name;
			return $command_info[$name];
		}
		# we weren't given a name, so loop it and look for the id
		foreach ($command_info as $name => $info) {
			if ($info['nagios_id'] == $id) {
				$info['name'] = $name;
				return $info;
			}
		}

		return false;
	}

	function cmd_name($id = false, $name = false)
	{
		$info = self::cmd_info($id, $name);
		if (isset($info['name'])) {
			return $info['name'];
		}
		return false;
	}

	/**
	 * Obtain the command name for a command id
	 * @param $id The id of the command
	 * @return False on errors, the command name as a string on success
	 */
	public function command_name($id)
	{
		return self::cmd_name($id);
	}

	/**
	 * Obtain the id for a command
	 * @param $name The name of the command
	 * @return False on errors, the numeric id on success (may be 0)
	 */
	public function command_id($name)
	{
		if (empty($name))
			return false;

		$first = self::cmd_name(false, $name);
		if ($first !== false) {
			return $first;
		}

		# handle a CMD_ prefixed name too
		if (substr($name, 0, 4) === 'CMD_') {
			return self::cmd_name(false, substr($name, 4));
		}
		return false;
	}

	/**
	 * Obtain Nagios' macro name for the given command
	 * @param $id Numeric or string representation of command
	 * @return False on errors, Nagios' macro name as string on
	 */
	public function nagios_name($id)
	{
		if (is_numeric($id)) {
			$base_cmd = self::command_name($id);
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

	public function submit_to_nagios($cmd, $pipe_path)
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
