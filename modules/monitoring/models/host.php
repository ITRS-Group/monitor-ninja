<?php

require_once( dirname(__FILE__).'/base/basehost.php' );

/**
 * Describes a single object from livestatus
 */
class Host_Model extends BaseHost_Model {
	/**
	 * Get state, as text
	 *
	 * @ninja orm depend[] state
	 * @ninja orm depend[] has_been_checked
	 */
	public function get_state_text() {
		if( !$this->get_has_been_checked() )
			return 'pending';
		switch( $this->get_state() ) {
			case 0: return 'up';
			case 1: return 'down';
			case 2: return 'unreachable';
		}
		return 'unknown'; // should never happen
	}

	/**
	 * get if checks are disabled
	 *
	 * @ninja orm depend[] active_checks_enabled
	 */
	public function get_checks_disabled() {
		//FIXME: passive as active
		return !$this->get_active_checks_enabled();
	}

	/**
	 * Get the first host group of the host group memberships
	 *
	 * @ninja orm depend[] groups
	 */
	public function get_first_group() {
		$groups = $this->get_groups();
		if(isset($groups[0])) return $groups[0];
		return '';
	}

	/**
	 * Get the long plugin output, which is second line and forward
	 *
	 * By some reason, nagios escapes this field.
	 */
	public function get_long_plugin_output() {
		$long_plugin_output = parent::get_long_plugin_output();
		return stripcslashes($long_plugin_output);
	}

	/**
	 * Get duration
	 *
	 * @ninja orm depend[] last_state_change
	 */
	public function get_duration() {
		$now = time();
		$last_state_change = $this->get_last_state_change();
		if( $last_state_change == 0 )
			return -1;
		return $now - $last_state_change;
	}

	/**
	 * get the number of comments associated to the host
	 *
	 * @ninja orm depend[] comments
	 */
	public function get_comments_count() {
		return count($this->get_comments());
	}

	/**
	 * Return the state type, as text in uppercase
	 *
	 * @ninja orm depend[] state_type
	 */
	public function get_state_type_text() {
		return $this->get_state_type()?'hard':'soft';
	}

	/**
	 * Get check type, as a string ("active" or "passive")
	 *
	 * @ninja orm depend[] check_type
	 */
	public function get_check_type_str() {
		return $this->get_check_type() ? 'passive' : 'active';
	}

	/**
	 * Get a list of custom commands for the host
	 *
	 * @ninja orm depend[] custom_variables
	 */
	public function get_custom_commands() {
		return Custom_command_Model::parse_custom_variables($this->get_custom_variables());
	}

	/**
	 * Get if having access to configure the host.
	 * @param $auth op5auth module to use, if not default
	 *
	 * @ninja orm depend[] contacts
	 */
	public function get_config_allowed($auth = false) {
		if( $auth === false ) {
			$auth = op5auth::instance();
		}
		if(!$auth->authorized_for('configuration_information')) {
			return false;
		}
		if($auth->authorized_for('host_edit_all')) {
			return true;
		}
		$cts = $this->get_contacts();
		if(!is_array($cts)) $cts = array();
		if($auth->authorized_for('host_edit_contact') && in_array($auth->get_user()->username, $cts)) {
			return true;
		}
		return false;
	}

	/**
	 * Get configuration url
	 *
	 * @ninja orm depend[] name
	 */
	public function get_config_url() {
		return str_replace(array(
			'$HOSTNAME$'
		), array(
			urlencode($this->get_name())
		), Kohana::config('config.config_url.hosts'));
	}


	/**
	 * Get both address and type of check source
	 *
	 * internal function for get_source_node and get_source_type
	 */
	private function get_source() {
		$check_source = $this->get_check_source();
		$node = 'N/A';
		$type = 'N/A';
		if(preg_match('/^Core Worker ([0-9]+)$/', $check_source, $matches)) {
			$node = gethostname();
			$type = 'local';
		}
		if(preg_match('/^Merlin (.*) (.*)$/', $check_source, $matches)) {
			$node = $matches[2];
			$type = $matches[1];
		}
		return array($node, $type);
	}

	/**
	 * Get which merlin node handling the check.
	 *
	 * This is determined by magic regexp parsing of the check_source field
	 *
	 * @ninja orm depend[] check_source
	 */
	public function get_source_node() {
		$source = $this->get_source();
		return $source[0];
	}

	/**
	 * Get which merlin node handling the check.
	 *
	 * This is determined by magic regexp parsing of the check_source field
	 *
	 * @ninja orm depend[] check_source
	 */
	public function get_source_type() {
		$source = $this->get_source();
		return $source[1];
	}

	/**
	 * Get the performance data for the object, expressed as an associative array
	 *
	 * @ninja orm depend[] perf_data_raw
	 */
	public function get_perf_data() {
		$perf_data_str = parent::get_perf_data_raw();
		return performance_data::process_performance_data($perf_data_str);
	}

	/**
	 * Trigger this host to be checked right now.
	 *
	 * @param &$error_string
	 * @return boolean
	 *
	 * @ninja orm_command mayi_method update.command.check_now
	 * @ninja orm_command description
	 *     Schedule the next check as soon as possible
	 */
	public function check_now(&$error_string = null) {
		return $this->schedule_check(time(), $error_string);
	}

	/**
	 * @param comment
	 * @param persistent = true
	 * @param notify = true
	 * @param sticky = true
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command param[] bool sticky
	 * @ninja orm_command param[] bool notify
	 * @ninja orm_command param[] bool persistent
	 * @ninja orm_command param[] string comment
	 * @ninja orm_command mayi_method update.command.acknowledge_problem
	 * @ninja orm_command description
	 *     This command is used to acknowledge a host problem.
	 *     When a host problem is acknowledged, future notifications about
	 *     problems are temporarily disabled until the host changes from its
	 *     current state.
	 *     If you want acknowledgement to disable notifications until the host
	 *     recovers, check the 'Sticky Acknowledgement' checkbox. Contacts for
	 *     this host will receive a notification about the acknowledgement, so
	 *     they are aware that someone is working on the problem.  Additionally,
	 *     a comment will also be added to the host.
	 *     Make sure to enter your name and fill in a brief description of what
	 *     you are doing in the comment field.
	 *     If you would like the host comment to remain once the acknowledgement
	 *     is removed, check the 'Persistent Comment' checkbox.  If you do not
	 *     want an acknowledgement notification sent out to the appropriate
	 *     contacts, uncheck the 'Notify' checkbox.
	 */
	public function acknowledge_problem($comment, $persistent=true, $notify=true, $sticky=true, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("ACKNOWLEDGE_HOST_PROBLEM",
		array(
		'host_name' => implode(';', array($this->get_name())),
		'sticky' => $sticky,
		'notify' => $notify,
		'persistent' => $persistent,
		'author' => $this->get_current_user(),
		'comment' => $comment
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param comment
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command param[] string comment
	 * @ninja orm_command mayi_method update.command.add_comment
	 * @ninja orm_command description
	 *     This command is used to add a comment for the specified host. If you
	 *     work with other administrators, you may find it useful to share
	 *     information about a host that is having problems if more than one of
	 *     you may be working on it.
	 */
	public function add_comment($comment, &$error_string=NULL) {
		$error_string = null;

		// we're hardcoding persistance here, it seems like one of those
		// very weird parameters to expose
		$command = nagioscmd::build_command("ADD_HOST_COMMENT", array(
			'host_name' => $this->get_name(),
			'persistent' => 1,
			'author' => $this->get_current_user(),
			'comment' => $comment
		));
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.disable_check
	 * @ninja orm_command description
	 *     This command is used to temporarily prevent Nagios from actively
	 *     checking the status of a host.  If Nagios needs to check the status
	 *     of this host, it will assume that it is in the same state that it was
	 *     in before checks were disabled.
	 */
	public function disable_check(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("DISABLE_HOST_CHECK",
		array(
		'host_name' => implode(';', array($this->get_name()))
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.disable_service_checks
	 * @ninja orm_command description
	 *     This command is used to disable active checks of all services
	 *     associated with the specified host.  When a service is disabled
	 *     Naemon will not monitor the service.  Doing this will prevent any
	 *     notifications being sent out for the specified service while it is
	 *     disabled.  In order to have Nagios check the service in the future
	 *     you will have to re-enable the service. Note that disabling service
	 *     checks may not necessarily prevent notifications from being sent out
	 *     about the host which those services are associated with.  This
	 *     <i>does not</i> disable checks of the host unless you check the
	 *     'Disable for host too' option.
	 */
	public function disable_service_checks(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("DISABLE_HOST_SVC_CHECKS",
		array(
		'host_name' => implode(';', array($this->get_name()))
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method
	 *     update.command.disable_service_notifications
	 * @ninja orm_command description
	 *     This command is used to prevent notifications from being sent out for
	 *     all services on the specified host.  You will have to re-enable
	 *     notifications for all services associated with this host before any
	 *     alerts can be sent out in the future.  This <i>does not</i> prevent
	 *     notifications from being sent out about the host unless you check the
	 *     'Disable for host too' option.
	 */
	public function disable_service_notifications(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("DISABLE_HOST_SVC_NOTIFICATIONS",
		array(
		'host_name' => implode(';', array($this->get_name()))
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.enable_check
	 * @ninja orm_command description
	 *     This command is used to enable active checks of this host.
	 */
	public function enable_check(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("ENABLE_HOST_CHECK",
		array(
		'host_name' => implode(';', array($this->get_name()))
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.enable_service_checks
	 * @ninja orm_command description
	 *     This command is used to enable active checks of all services
	 *     associated with the specified host.  This <i>does not</i> enable
	 *     checks of the host unless you check the 'Enable for host too' option.
	 */
	public function enable_service_checks(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("ENABLE_HOST_SVC_CHECKS",
		array(
		'host_name' => implode(';', array($this->get_name()))
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method
	 *     update.command.enable_service_notifications
	 * @ninja orm_command description
	 *     This command is used to enable notifications for all services on the
	 *     specified host.  Notifications will only be sent out for the service
	 *     state types you defined in your service definition.  This <i>does
	 *     not</i> enable notifications for the host unless you check the
	 *     'Enable for host too' option.
	 */
	public function enable_service_notifications(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("ENABLE_HOST_SVC_NOTIFICATIONS",
		array(
		'host_name' => implode(';', array($this->get_name()))
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param plugin_output
	 * @param status_code
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.process_check_result
	 * @ninja orm_command param[] string plugin_output
	 * @ninja orm_command param[] select status_code
	 * @ninja orm_command description
	 *     This command is used to submit a passive check result for a host.
	 */
	public function process_check_result($plugin_output, $status_code, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("PROCESS_HOST_CHECK_RESULT",
		array(
		'host_name' => implode(';', array($this->get_name())),
		'status_code' => $status_code,
		'plugin_output' => $plugin_output
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.remove_acknowledgement
	 * @ninja orm_command description
	 *     This command is used to remove an acknowledgement for a host problem.
	 *     Once the acknowledgement is removed, notifications may start being
	 *     sent out about the host problem.
	 */
	public function remove_acknowledgement(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("REMOVE_HOST_ACKNOWLEDGEMENT",
		array(
		'host_name' => implode(';', array($this->get_name()))
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param check_time
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.schedule_check
	 * @ninja orm_command param[] time check_time
	 * @ninja orm_command description
	 *     This command is used to schedule the next check of a host. Naemon
	 *     will re-queue the host to be checked at the time you specify. If you
	 *     select the <i>force check</i> option, Naemon will force a check of
	 *     the host regardless of both what time the scheduled check occurs and
	 *     whether or not checks are enabled for the host.
	 */
	public function schedule_check($check_time, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("SCHEDULE_HOST_CHECK",
		array(
		'host_name' => implode(';', array($this->get_name())),
		'check_time' => $check_time
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param duration
	 * @param trigger_id
	 * @param start_time
	 * @param end_time
	 * @param comment
	 * @param fixed = true
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.schedule_downtime
	 * @ninja orm_command param[] duration duration
	 * @ninja orm_command param[] select trigger_id
	 * @ninja orm_command param[] time start_time
	 * @ninja orm_command param[] time end_time
	 * @ninja orm_command param[] string comment
	 * @ninja orm_command param[] bool fixed
	 * @ninja orm_command description
	 *     This command is used to schedule downtime for a host. During the
	 *     specified downtime, Nagios will not send notifications out about the
	 *     host. When the scheduled downtime expires, Nagios will send out
	 *     notifications for this host as it normally would. Scheduled downtimes
	 *     are preserved across program shutdowns and restarts. Both the start
	 *     and end times should be specified in the following format:
	 *     <b>YYYY-MM-DD hh:mm:ss</b>. If you select the <i>fixed</i> option,
	 *     the downtime will be in effect between the start and end times you
	 *     specify. If you do not select the <i>fixed</i> option, Naemon will
	 *     treat this as "flexible" downtime.  Flexible downtime starts when the
	 *     host goes down or becomes unreachable (sometime between the start and
	 *     end times you specified) and lasts as long as the duration of time
	 *     you enter. The duration fields do not apply for fixed downtime.
	 */
	public function schedule_downtime($duration, $trigger_id, $start_time, $end_time, $comment, $fixed=true, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("SCHEDULE_HOST_DOWNTIME",
		array(
		'host_name' => implode(';', array($this->get_name())),
		'start_time' => $start_time,
		'end_time' => $end_time,
		'fixed' => $fixed,
		'trigger_id' => $trigger_id,
		'duration' => $duration,
		'author' => $this->get_current_user(),
		'comment' => $comment
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param check_time
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.schedule_service_checks
	 * @ninja orm_command param[] time check_time
	 * @ninja orm_command description
	 *     This command is used to scheduled the next check of all services on
	 *     the specified host. If you select the <i>force check</i> option,
	 *     Naemon will force a check of all services on the host regardless of
	 *     both what time the scheduled checks occur and whether or not checks
	 *     are enabled for those services.
	 */
	public function schedule_service_checks($check_time, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("SCHEDULE_HOST_SVC_CHECKS",
		array(
		'host_name' => implode(';', array($this->get_name())),
		'check_time' => $check_time
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * @param comment
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command mayi_method update.command.send_custom_notification
	 * @ninja orm_command param[] string comment
	 * @ninja orm_command description
	 *     This command is used to send a custom notification about the
	 *     specified host. Useful in emergencies when you need to notify admins
	 *     of an issue regarding a monitored system or service. Custom
	 *     notifications normally follow the regular notification logic in
	 *     Naemon. Selecting the <i>Forced</i> option will force the
	 *     notification to be sent out, regardless of the time restrictions,
	 *     whether or not notifications are enabled, etc.  Selecting the
	 *     <i>Broadcast</i> option causes the notification to be sent out to all
	 *     normal (non-escalated) and escalated contacts. These options allow
	 *     you to override the normal notification logic if you need to get an
	 *     important message out.
	 */
	public function send_custom_notification($comment, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("SEND_CUSTOM_HOST_NOTIFICATION",
		array(
		'host_name' => implode(';', array($this->get_name())),
		'author' => $this->get_current_user(),
		'comment' => $comment
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}
}
