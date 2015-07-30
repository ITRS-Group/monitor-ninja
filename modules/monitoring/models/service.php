<?php

require_once( dirname(__FILE__).'/base/baseservice.php' );

/**
 * Describes a single object from livestatus
 */
class Service_Model extends BaseService_Model {
	/**
	 * Return the state as text
	 *
	 * @ninja orm depend[] state
	 * @ninja orm depend[] has_been_checked
	 */
	public function get_state_text() {
		if( !$this->get_has_been_checked() )
			return 'pending';
		switch( $this->get_state() ) {
			case 0: return 'ok';
			case 1: return 'warning';
			case 2: return 'critical';
			case 3: return 'unknown';
		}
		return 'unknown'; // should never happen
	}

	/**
	 * Return the name of one of the services groups the object is member of
	 *
	 * @ninja orm depend[] groups
	 */
	public function get_first_group() {
		$groups = $this->get_groups();
		if(isset($groups[0])) return $groups[0];
		return '';
	}

	/**
	 * Returns true if checks are disabled
	 *
	 * @ninja orm depend[] active_checks_enabled
	 */
	public function get_checks_disabled() {
		//FIXME: passive as active
		return !$this->get_active_checks_enabled();
	}

	/**
	 * Returns the duration since last state change
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
	 * Get the long plugin output, which is second line and forward
	 *
	 * By some reason, nagios escapes this field.
	 */
	public function get_long_plugin_output() {
		$long_plugin_output = parent::get_long_plugin_output();
		return stripcslashes($long_plugin_output);
	}

	/**
	 * Returns the number of comments related to the service
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
	 * Get the check type as string (passive/active)
	 *
	 * @ninja orm depend[] check_type
	 */
	public function get_check_type_str() {
		return $this->get_check_type() ? 'passive' : 'active';
	}

	/**
	 * Get a list of custom commands for the service
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
		if($auth->authorized_for('service_edit_all')) {
			return true;
		}
		$cts = $this->get_contacts();
		if(!is_array($cts)) $cts = array();
		if($auth->authorized_for('service_edit_contact') && in_array($auth->get_user()->username, $cts)) {
			return true;
		}
		return false;
	}

	/**
	 * Get configuration url
	 *
	 * @ninja orm depend[] host.name
	 * @ninja orm depend[] description
	 */
	public function get_config_url() {
		return str_replace(array(
			'$HOSTNAME$',
			'$SERVICEDESC$'
		), array(
			urlencode($this->get_host()->get_name()),
			urlencode($this->get_description())
		), Kohana::config('config.config_url.services'));
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
	 * @param comment
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command name Add comment
	 * @ninja orm_command icon comment
	 * @ninja orm_command mayi_method update.command.add_comment
	 * @ninja orm_command param[] string comment
	 * @ninja orm_command description
	 *     This command is used to add a comment for the specified service. If
	 *     you work with other administrators, you may find it useful to share
	 *     information about a host or service that is having problems if more
	 *     than one of you may be working on it.
	 */
	public function add_comment($comment, &$error_string=NULL) {
		$error_string = null;

		// we're hardcoding persistance here, it seems like one of those
		// very weird parameters to expose
		$command = nagioscmd::build_command("ADD_SVC_COMMENT", array(
			'service' => $this->get_host()->get_name().";".$this->get_description(),
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
	 * @ninja orm_command name Disable active checks
	 * @ninja orm_command icon disable-active-checks
	 * @ninja orm_command mayi_method update.command.disable_check
	 * @ninja orm_command description
	 *     This command is used to disable active checks of a service.
	 */
	public function disable_check(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("DISABLE_SVC_CHECK",
			array(
				'service' => $this->get_host()->get_name().";".$this->get_description()
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
	 * @ninja orm_command name Submit passive check result
	 * @ninja orm_command icon checks-passive
	 * @ninja orm_command mayi_method update.command.process_check_result
	 * @ninja orm_command param[] string plugin_output
	 * @ninja orm_command param[] select status_code
	 * @ninja orm_command select.status_code[] Ok
	 * @ninja orm_command select.status_code[] Warning
	 * @ninja orm_command select.status_code[] Critical
	 * @ninja orm_command select.status_code[] Unknown
	 * @ninja orm_command description
	 *     This command is used to submit a passive check result for a service.
	 *     It is particularly useful for resetting security-related services to
	 *     OK states once they have been dealt with.
	 */
	public function process_check_result($plugin_output, $status_code, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("PROCESS_SERVICE_CHECK_RESULT",
		array(
		'service' => implode(';', array($this->get_host()->get_name(), $this->get_description())),
		'return_code' => $status_code,
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
	 * @param check_time
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command name Reschedule check
	 * @ninja orm_command icon re-schedule
	 * @ninja orm_command mayi_method update.command.schedule_check
	 * @ninja orm_command param[] time check_time
	 * @ninja orm_command description
	 *     This command is used to schedule the next check of a service. Naemon
	 *     will re-queue the service to be checked at the time you specify. If
	 *     you select the <i>force check</i> option, Nagios will force a check
	 *     of the service regardless of both what time the scheduled check
	 *     occurs and whether or not checks are enabled for the service.
	 */
	public function schedule_check($check_time, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("SCHEDULE_SVC_CHECK",
		array(
		'service' => implode(';', array($this->get_host()->get_name(), $this->get_description())),
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
	 * @ninja orm_command name Schedule downtime
	 * @ninja orm_command icon scheduled-downtime
	 * @ninja orm_command mayi_method update.command.schedule_downtime
	 * @ninja orm_command param[] duration duration
	 * @ninja orm_command param[] int trigger_id
	 * @ninja orm_command param[] time start_time
	 * @ninja orm_command param[] time end_time
	 * @ninja orm_command param[] string comment
	 * @ninja orm_command param[] bool fixed
	 * @ninja orm_command description
	 *     This command is used to schedule downtime for a service.  During the
	 *     specified downtime, Naemon will not send notifications out about the
	 *     service. When the scheduled downtime expires, Naemon will send out
	 *     notifications for this service as it normally would. Scheduled
	 *     downtimes are preserved across program shutdowns and restarts. Both
	 *     the start and end times should be specified in the following format:
	 *     <b>YYYY-MM-DD hh:mm:ss</b>. If fixed is disabled, Naemon will treat
	 *     this as "flexible" downtime. Flexible downtime starts when the
	 *     service enters a non-OK state (sometime between the start and end
	 *     times you specified) and lasts as long as the duration of time you
	 *     enter. The duration fields do not apply for fixed downtime.
	 */
	public function schedule_downtime($duration, $trigger_id, $start_time, $end_time, $comment, $fixed=true, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("SCHEDULE_SVC_DOWNTIME",
		array(
		'service' => implode(';', array($this->get_host()->get_name(), $this->get_description())),
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
	 * @param comment
	 * @param &error_string = NULL
	 * @return bool
	 *
	 * @ninja orm_command name Send custom notificatoin
	 * @ninja orm_command icon notify-send
	 * @ninja orm_command mayi_method update.command.send_custom_notification
	 * @ninja orm_command param[] string comment
	 * @ninja orm_command description
	 *     This command is used to send a custom notification about the
	 *     specified service. Useful in emergencies when you need to notify
	 *     admins of an issue regarding a monitored system or service. Custom
	 *     notifications normally follow the regular notification logic in
	 *     Naemon. Selecting the <i>Forced</i> option will force the
	 *     notification to be sent out, regardless of the time restrictions,
	 *     whether or not notifications are enabled, etc. Selecting the
	 *     <i>Broadcast</i> option causes the notification to be sent out to all
	 *     normal (non-escalated) and escalated contacts. These options allow
	 *     you to override the normal notification logic if you need to get an
	 *     important message out.
	 */
	public function send_custom_notification($comment, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("SEND_CUSTOM_SVC_NOTIFICATION",
		array(
		'service' => implode(';', array($this->get_host()->get_name(), $this->get_description())),
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
