<?php


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
		return $this->list_custom_commands();
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
		if($auth->authorized_for('host_edit_contact') && in_array($auth->get_user()->get_username(), $cts)) {
			return true;
		}
		return false;
	}

	/**
	 * Get notes url
	 *
	 * @ninja orm_command name Notes
	 * @ninja orm_command category Links
	 * @ninja orm_command icon host-notes
	 * @ninja orm_command mayi_method read.extinfo
	 * @ninja orm_command description
	 *     Follow the notes links
	 * @ninja orm_command enabled_if notes_url
	 * @ninja orm_command redirect 1
	 */
	public function get_notes_url() {
		return parent::get_notes_url();
	}

	/**
	 * Get action url
	 *
	 * @ninja orm_command name Actions
	 * @ninja orm_command category Links
	 * @ninja orm_command icon host-actions
	 * @ninja orm_command mayi_method read.extinfo
	 * @ninja orm_command description
	 *     Follow the action link
	 * @ninja orm_command enabled_if action_url
	 * @ninja orm_command redirect 1
	 */
	public function get_action_url() {
		return parent::get_action_url();
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
	 * @return string Either local, peer or poller
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
	 * @ninja orm_command name Check now
	 * @ninja orm_command category Actions
	 * @ninja orm_command icon re-schedule
	 * @ninja orm_command mayi_method update.command.schedule
	 * @ninja orm_command description
	 *     Schedule the next check as soon as possible
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function check_now() {
		return $this->schedule_check(time());
	}

	/**
	 * @param comment
	 * @param persistent = true
	 * @param notify = true
	 * @param sticky = true
	 *
	 * @ninja orm_command name Acknowledge Problem
	 * @ninja orm_command category Actions
	 * @ninja orm_command icon acknowledged
	 *
	 * @ninja orm_command params.comment.id 0
	 * @ninja orm_command params.comment.type string
	 * @ninja orm_command params.comment.name Comment
	 *
	 * @ninja orm_command params.sticky.id 1
	 * @ninja orm_command params.sticky.type bool
	 * @ninja orm_command params.sticky.name Sticky
	 * @ninja orm_command params.sticky.default 1
	 * @ninja orm_command params.sticky.description
	 * 		If you want acknowledgement to disable notifications until the host recovers, check this checkbox.
	 *
	 * @ninja orm_command params.notify.id 2
	 * @ninja orm_command params.notify.type bool
	 * @ninja orm_command params.notify.name Notify
	 * @ninja orm_command params.notify.default 1
	 * @ninja orm_command params.notify.description
	 * 		If you want an acknowledgement notification sent out to the appropriate contacts, check this checkbox.
	 *
	 * @ninja orm_command params.persistent.id 3
	 * @ninja orm_command params.persistent.type bool
	 * @ninja orm_command params.persistent.name Persistent
	 * @ninja orm_command params.persistent.default 1
	 * @ninja orm_command params.persistent.description
	 * 		If you would like the host comment to remain once the acknowledgement is removed, check this checkbox.
	 *
	 * @ninja orm_command mayi_method update.command.acknowledge
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
	 * @ninja orm_command enabled_if unacknowledged_problem
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function acknowledge_problem($comment, $sticky=true, $notify=true, $persistent=true) {
		return $this->submit_naemon_command("ACKNOWLEDGE_HOST_PROBLEM", $sticky?2:0, $notify?1:0, $persistent?1:0, $this->get_current_user(), $comment);
	}

	/**
	 * Returns if the host has a problem which is unacknowledged
	 *
	 * @ninja orm export false
	 * @ninja orm depend[] state
	 * @ninja orm depend[] has_been_checked
	 * @ninja orm depend[] acknowledged
	 */
	public function get_unacknowledged_problem() {
		return ($this->get_state() > 0 && $this->get_has_been_checked()) && ! $this->get_acknowledged();
	}

	/**
	 * @ninja orm_command name Remove acknowledgement
	 * @ninja orm_command category Actions
	 * @ninja orm_command icon acknowledged-not
	 * @ninja orm_command mayi_method update.command.acknowledge
	 * @ninja orm_command description
	 *     This command is used to remove an acknowledgement for a host problem.
	 *     Once the acknowledgement is removed, notifications may start being
	 *     sent out about the host problem.
	 * @ninja orm_command enabled_if acknowledged_problem
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function remove_acknowledgement() {
		return $this->submit_naemon_command("REMOVE_HOST_ACKNOWLEDGEMENT");
	}

	/**
	 * Returns if the host has a problem which is acknowledged
	 *
	 * @ninja orm export false
	 * @ninja orm depend[] state
	 * @ninja orm depend[] has_been_checked
	 * @ninja orm depend[] acknowledged
	 */
	public function get_acknowledged_problem() {
		return ($this->get_state() > 0 && $this->get_has_been_checked()) && $this->get_acknowledged();
	}

	/**
	 * @param comment
	 *
	 * @ninja orm_command name Add a new comment
	 * @ninja orm_command category Actions
	 * @ninja orm_command icon add-comment
	 *
	 * @ninja orm_command params.comment.id 0
	 * @ninja orm_command params.comment.type string
	 * @ninja orm_command params.comment.name Comment
	 *
	 * @ninja orm_command mayi_method update.command.comment
	 * @ninja orm_command description
	 *     This command is used to add a comment for the specified host. If you
	 *     work with other administrators, you may find it useful to share
	 *     information about a host that is having problems if more than one of
	 *     you may be working on it.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function add_comment($comment) {
		return $this->submit_naemon_command("ADD_HOST_COMMENT",1,$this->get_current_user(),$comment);
	}

	/**
	 * @return bool
	 *
	 * @ninja orm_command name Enable active checks
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon enable
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command description
	 *     This command is used to enable active checks of this host.
	 * @ninja orm_command enabled_if checks_disabled
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function enable_check() {
		return $this->submit_naemon_command("ENABLE_HOST_CHECK");
	}

	/**
	 * @ninja orm_command name Disable active checks
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon disable-active-checks
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command description
	 *     This command is used to temporarily prevent Nagios from actively
	 *     checking the status of a host.  If Nagios needs to check the status
	 *     of this host, it will assume that it is in the same state that it was
	 *     in before checks were disabled.
	 * @ninja orm_command enabled_if checks_enabled
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function disable_check() {
		return $this->submit_naemon_command("DISABLE_HOST_CHECK");
	}

	/**
	 * @param plugin_output
	 * @param status_code
	 * @param perf_data
	 *
	 * @ninja orm_command name Submit passive check result
	 * @ninja orm_command category Actions
	 * @ninja orm_command icon checks-passive
	 * @ninja orm_command mayi_method update.command.passive
	 *
	 * @ninja orm_command params.plugin_output.id 0
	 * @ninja orm_command params.plugin_output.type string
	 * @ninja orm_command params.plugin_output.name Plugin output
	 * @ninja orm_command params.plugin_output.description The status string reported as plugin output
	 *
	 * @ninja orm_command params.status_code.id 1
	 * @ninja orm_command params.status_code.type select
	 * @ninja orm_command params.status_code.name Status code
	 * @ninja orm_command params.status_code.option[] Up
	 * @ninja orm_command params.status_code.option[] Down
	 * @ninja orm_command params.status_code.option[] Unreachable
	 *
	 * @ninja orm_command params.perf_data.id 2
	 * @ninja orm_command params.perf_data.type string
	 * @ninja orm_command params.perf_data.name Perf data
	 * @ninja orm_command params.perf_data.description Performance data, formatted as monitoring-plugins defines
	 *
	 * @ninja orm_command description
	 *     This command is used to submit a passive check result for a host.
	 * @ninja orm_command enabled_if accept_passive_checks
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function process_check_result($plugin_output, $status_code, $perf_data = null) {
		if($perf_data !== null)
			$plugin_output .= '|' . $perf_data;
		return $this->submit_naemon_command("PROCESS_HOST_CHECK_RESULT", $status_code, $plugin_output);
	}

	/**
	 * @param check_time
	 * @param forced = false
	 *
	 * @ninja orm_command name Re-schedule next host check
	 * @ninja orm_command category Actions
	 * @ninja orm_command icon re-schedule
	 * @ninja orm_command mayi_method update.command.schedule
	 *
	 * @ninja orm_command params.check_time.id 0
	 * @ninja orm_command params.check_time.type time
	 * @ninja orm_command params.check_time.name Check time
	 * @ninja orm_command params.check_time.default now
	 * @ninja orm_command params.check_time.description
	 * 		Enter a check time in the format: YYYY-MM-DD hh:mm::ss
	 *
	 * @ninja orm_command params.forced.id 1
	 * @ninja orm_command params.forced.type bool
	 * @ninja orm_command params.forced.name Force check
	 * @ninja orm_command params.forced.default 1
	 * @ninja orm_command params.forced.description
	 * 		If you want Naemon to force a check of the host regardless of both what time the scheduled check occurs and whether or not checks are enabled for the host, check this checkbox.
	 *
	 * @ninja orm_command description
	 *     This command is used to schedule the next check of a host. Naemon
	 *     will re-queue the host to be checked at the time you specify. If you
	 *     select the <i>force check</i> option, Naemon will force a check of
	 *     the host regardless of both what time the scheduled check occurs and
	 *     whether or not checks are enabled for the host.
	 * @ninja orm_command enabled_if checks_enabled
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function schedule_check($check_time, $forced = false) {

		$check_tstamp = date::timestamp_format(false, $check_time);
		if($check_tstamp === false)
			return array(
				'status' => 0,
				'output' => $check_time . " is not a valid date, please adjust it"
				);

		if($forced)
			return $this->submit_naemon_command("SCHEDULE_FORCED_HOST_CHECK", $check_tstamp);
		return $this->submit_naemon_command("SCHEDULE_HOST_CHECK", $check_tstamp);
	}

	/**
	 * @param start_time
	 * @param end_time
	 * @param flexible
	 * @param duration
	 * @param trigger_id
	 * @param propagation
	 * @param comment
	 *
	 * @ninja orm_command name Schedule downtime
	 * @ninja orm_command category Actions
	 * @ninja orm_command icon scheduled-downtime
	 * @ninja orm_command mayi_method update.command.downtime
	 *
	 * @ninja orm_command params.start_time.id 0
	 * @ninja orm_command params.start_time.type time
	 * @ninja orm_command params.start_time.name Start time
	 * @ninja orm_command params.start_time.default now
	 * @ninja orm_command params.start_time.description
	 *     Start time in the format: YYYY-MM-DD hh:mm:ss
	 *
	 * @ninja orm_command params.end_time.id 1
	 * @ninja orm_command params.end_time.type time
	 * @ninja orm_command params.end_time.name End time
	 * @ninja orm_command params.end_time.default now + 2hours
	 * @ninja orm_command params.end_time.description
	 *     End time in the format: YYYY-MM-DD hh:mm:ss
	 *
	 * @ninja orm_command params.flexible.id 2
	 * @ninja orm_command params.flexible.type bool
	 * @ninja orm_command params.flexible.name Flexible
	 * @ninja orm_command params.flexible.description
	 *     Flexible downtime starts when the host goes down or becomes
	 *     unreachable (sometime between the start and end times you specified)
	 *     and lasts as long as the duration of time you enter.
	 *
	 * @ninja orm_command params.duration.id 3
	 * @ninja orm_command params.duration.type duration
	 * @ninja orm_command params.duration.name Duration
	 * @ninja orm_command params.duration.default 2.0
	 * @ninja orm_command params.duration.description
	 *     Only for flexible downtimes. Number of hours from first problem the
	 *     scheduled downtime should progress
	 *
	 * @ninja orm_command params.trigger_id.id 4
	 * @ninja orm_command params.trigger_id.type object
	 * @ninja orm_command params.trigger_id.query [downtimes] all
	 * @ninja orm_command params.trigger_id.name Triggering downtime
	 * @ninja orm_command params.trigger_id.description
	 *     Only for flexible downtimes. Which downtime that should trigger this
	 *     downtime
	 *
	 * @ninja orm_command params.propagation.id 5
	 * @ninja orm_command params.propagation.type select
	 * @ninja orm_command params.propagation.name Propagate to children
	 * @ninja orm_command params.propagation.option[] No propagation
	 * @ninja orm_command params.propagation.option[] Propagate to child hosts
	 * @ninja orm_command params.propagation.option[] Propagate as triggered downtime to child hosts
	 * @ninja orm_command params.propagation.description
	 *     Also add this downtime to children hosts. If selecting propagation,
	 *     this downtime, with its parameters, will be added to all children
	 *     hosts automatically. If selected that propagation is triggered, the
	 *     downtime on children hosts is registered as flexible, triggered by
	 *     the selected host.
	 *
	 * @ninja orm_command params.comment.id 6
	 * @ninja orm_command params.comment.type string
	 * @ninja orm_command params.comment.name Comment
	 *
	 * @ninja orm_command description
	 *     This command is used to schedule downtime for a host. During the
	 *     specified downtime, Naemon will not send notifications out about the
	 *     host. When the scheduled downtime expires, Naemon will send out
	 *     notifications for this host as it normally would. Scheduled downtimes
	 *     are preserved across program shutdowns and restarts.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function schedule_downtime($start_time, $end_time, $flexible, $duration, $trigger_id, $propagation, $comment) {
		$duration_sec = intval(floatval($duration) * 3600);

		$trigger_id = intval($trigger_id);

		/* Handle propagation correctly */
		$propagation = (int)$propagation;
		$commands = array(
			"SCHEDULE_HOST_DOWNTIME",
			"SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME",
			"SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME"
		);
		$command = array_key_exists($propagation, $commands) ? $commands[$propagation] : $commands[0];

		$start_tstamp = date::timestamp_format(false, $start_time);
		if($start_tstamp === false) {
			return array(
				'status' => false,
				'output' => $start_time . " is not a valid date, please adjust it"
			);
		}

		$end_tstamp = date::timestamp_format(false, $end_time);
		if($end_tstamp === false) {
			return array(
				'status' => false,
				'output' => $end_time . " is not a valid date, please adjust it"
			);
		}

		if($start_tstamp >= $end_tstamp) {
			return array(
				'status' => false,
				'output' => "Downtime must start before it ends"
			);
		}

		$installation_time = installation::get_installation_time();

		$output = array();
		if($start_tstamp < $installation_time) {
			$output[] = sprintf(
				"Adjusting start of downtime to start when Monitor was installed (tried to submit %s and Monitor was installed %s)",
				$start_time,
				date(date::date_format(), $installation_time)
			);
			$start_tstamp = $installation_time;
		}

		if($end_tstamp < $installation_time) {
			return array(
				'status' => true,
				'output' => sprintf(
					"Not scheduling any downtime since it was supposed to end before Monitor was installed (tried to submit %s and Monitor was installed %s)",
					$end_time,
					date(date::date_format(), $installation_time)
				)
			);
		}

		/* TODO: handle propagation of downtimes retrospectively */
		$retrospective = $this->schedule_downtime_retrospectively($this->get_name(), false, $start_tstamp, $end_tstamp, $comment);
		$output[] = $retrospective['output'];
		if($end_tstamp < time()) {
			return array(
				'status' => $retrospective['status'],
				'output' => implode("<br>", array_filter($output))
			);
		}
		$downtime = $this->submit_naemon_command($command, $start_tstamp, $end_tstamp, $flexible ? 0 : 1, $trigger_id, $duration_sec, $this->get_current_user(), $comment);
		$output[] = $downtime['output'];
		return array(
			'status' => $downtime['status'] && $retrospective['status'],
			'output' => implode("<br>", array_filter($output))
		);
	}

	/**
	 * @ninja orm_command name Cancel all downtimes
	 * @ninja orm_command category Actions
	 * @ninja orm_command icon delete-downtime
	 * @ninja orm_command mayi_method delete.command.delete
	 * @ninja orm_command description
	 *     Cancel all scheduled downtime entries for the host.
	 * @ninja orm_command view monitoring/naemon_command
	 * @return array ['status' => boolean, 'output' => string]
	 */
	public function cancel_all_downtimes() {
		$downtime_set = ObjectPool_Model::get_by_query('[downtimes] host.name = "'.$this->get_name().'"');
		foreach($downtime_set->it(array(),array(), false, 0) as $downtime) {
			$output = $downtime->delete();
			if (!$output['status'])
				return $output;
		}
		return array(
			'status' => True,
			'output' => sprintf(_('Your command was successfully submitted to %s.'), Kohana::config('config.product_name'))
		);
	}

	/**
	 * @param comment
	 *
	 * @ninja orm_command name Send custom notification
	 * @ninja orm_command category Actions
	 * @ninja orm_command icon notify-send
	 * @ninja orm_command mayi_method update.command.send_notification
	 *
	 * @ninja orm_command params.comment.id 0
	 * @ninja orm_command params.comment.type string
	 * @ninja orm_command params.comment.name Comment
	 *
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
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function send_notification($comment) {
		$options = 4; // forced
		return $this->submit_naemon_command("SEND_CUSTOM_HOST_NOTIFICATION", $options, $this->get_current_user(), $comment);
	}

	/**
	 * @ninja orm_command name Enable notifications for all services
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon notify-send
	 * @ninja orm_command mayi_method update.command.notification
	 * @ninja orm_command description
	 *     This command is used to enable notifications for all services on the
	 *     specified host.  Notifications will only be sent out for the service
	 *     state types you defined in your service definition.  This <i>does
	 *     not</i> enable notifications for the host unless you check the
	 *     'Enable for host too' option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function enable_service_notifications() {
		return $this->submit_naemon_command("ENABLE_HOST_SVC_NOTIFICATIONS");
	}

	/**
	 * @ninja orm_command name Disable notifications for all services
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon notify-disabled
	 * @ninja orm_command mayi_method update.command.notification
	 * @ninja orm_command description
	 *     This command is used to prevent notifications from being sent out for
	 *     all services on the specified host.  You will have to re-enable
	 *     notifications for all services associated with this host before any
	 *     alerts can be sent out in the future.  This <i>does not</i> prevent
	 *     notifications from being sent out about the host unless you check the
	 *     'Disable for host too' option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function disable_service_notifications() {
		return $this->submit_naemon_command("DISABLE_HOST_SVC_NOTIFICATIONS");
	}

	/**
	 * @ninja orm_command name Enable checks of all services
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon enable
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command description
	 *     This command is used to enable active checks of all services
	 *     associated with the specified host.  This <i>does not</i> enable
	 *     checks of the host unless you check the 'Enable for host too' option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function enable_service_checks() {
		return $this->submit_naemon_command("ENABLE_HOST_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Disable checks of all services
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon disable-active-checks
	 * @ninja orm_command mayi_method update.command.enabled
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
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function disable_service_checks() {
		return $this->submit_naemon_command("DISABLE_HOST_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Locate host on map
	 * @ninja orm_command category Links
	 * @ninja orm_command icon locate-host-on-map
	 * @ninja orm_command mayi_method read.nagvis
	 * @ninja orm_command description
	 *     Find the host on a nagvis automap.
	 * @ninja orm_command redirect 1
	 */
	public function locate_on_nagvis_map() {
		if(!Kohana::config('nagvis.nagvis_path')) {
			return false;
		}
		return sprintf('nagvis/automap/host/%s', urlencode($this->get_name()));
	}

	/**
	 * @ninja orm_command name Notifications
	 * @ninja orm_command category Links
	 * @ninja orm_command icon notification
	 * @ninja orm_command mayi_method read
	 * @ninja orm_command description
	 *     Show host notifications.
	 * @ninja orm_command redirect 1
	 */
	public function notifications() {
		return LinkProvider::factory()->get_url('listview', null, array('q' => '[notifications] host_name = "' . $this->get_name() . '"'));
	}

	/**
	 * @ninja orm_command name Graphs
	 * @ninja orm_command category Links
	 * @ninja orm_command icon pnp
	 * @ninja orm_command mayi_method read
	 * @ninja orm_command description
	 *     Show host graphs.
	 * @ninja orm_command redirect 1
	 */
	public function graphs() {
		return LinkProvider::factory()->get_url('pnp', null, array('host' => $this->get_name(), 'srv' => '_HOST_'));
	}

	/**
	 * @param check_time
	 * @param forced = false
	 *
	 * @ninja orm_command name Schedule a check of all services
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon re-schedule
	 * @ninja orm_command mayi_method update.command.schedule
	 *
	 * @ninja orm_command params.check_time.id 0
	 * @ninja orm_command params.check_time.type time
	 * @ninja orm_command params.check_time.name Check time
	 * @ninja orm_command params.check_time.default now
	 * @ninja orm_command params.check_time.description
	 * 		Enter a check time in the format: YYYY-MM-DD hh:mm::ss
	 *
	 * @ninja orm_command params.forced.id 1
	 * @ninja orm_command params.forced.type bool
	 * @ninja orm_command params.forced.name Forced
	 * @ninja orm_command params.forced.default 1
	 * @ninja orm_command params.forced.description
	 * 		If you want Naemon to force a check of the services regardless of both what time the scheduled check occurs and whether or not checks are enabled for the services, check this checkbox.
	 *
	 * @ninja orm_command description
	 *     This command is used to scheduled the next check of all services on
	 *     the specified host. If you select the <i>force check</i> option,
	 *     Naemon will force a check of all services on the host regardless of
	 *     both what time the scheduled checks occur and whether or not checks
	 *     are enabled for those services.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function schedule_service_checks($check_time, $forced = false) {
		$check_time = date::timestamp_format(false, $check_time);
		if($forced)
			return $this->submit_naemon_command("SCHEDULE_FORCED_HOST_SVC_CHECKS", $check_time);
		return $this->submit_naemon_command("SCHEDULE_HOST_SVC_CHECKS", $check_time);
	}


	/**
	 * @ninja orm_command name Stop obsessing over this host
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon shield-disabled
	 * @ninja orm_command mayi_method update.command.obsess
	 * @ninja orm_command description
	 *     Disables processing of host checks via the OCHP command for the
	 *     specified host.
	 * @ninja orm_command enabled_if obsess
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function stop_obsessing() {
		return $this->submit_naemon_command("STOP_OBSESSING_OVER_HOST");
	}


	/**
	 * @ninja orm_command name Start obsessing over this host
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon shield-enabled
	 * @ninja orm_command mayi_method update.command.obsess
	 * @ninja orm_command description
	 *     Disables processing of host checks via the OCHP command for the
	 *     specified host.
	 * @ninja orm_command enabled_if !obsess
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function start_obsessing() {
		return $this->submit_naemon_command("START_OBSESSING_OVER_HOST");
	}


	/**
	 * @ninja orm_command name Stop accepting passive checks
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon shield-disabled
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command description
	 *     Stop accepting new passive host check results
	 * @ninja orm_command enabled_if accept_passive_checks
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function stop_accept_passive_checks() {
		return $this->submit_naemon_command("DISABLE_PASSIVE_HOST_CHECKS");
	}


	/**
	 * @ninja orm_command name Start accepting passive checks for
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon shield-enabled
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command description
	 *     Start accepting new passive host check results
	 * @ninja orm_command enabled_if !accept_passive_checks
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function start_accept_passive_checks() {
		return $this->submit_naemon_command("ENABLE_PASSIVE_HOST_CHECKS");
	}


	/**
	 * @ninja orm_command name Disable notifications
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon notify-disabled
	 * @ninja orm_command mayi_method update.command.notification
	 * @ninja orm_command description
	 *     Disable notifications from this host. No contacts will be contacted
	 *     if this host are having trouble.
	 * @ninja orm_command enabled_if notifications_enabled
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function stop_notifications() {
		return $this->submit_naemon_command("DISABLE_HOST_NOTIFICATIONS");
	}


	/**
	 * @ninja orm_command name Enable notifications
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon notify
	 * @ninja orm_command mayi_method update.command.notification
	 * @ninja orm_command description
	 *     Enable notifications from this host. Contacts for this host will be
	 *     contacted if this host are having trouble, if there are no other
	 *     reason for notifications to be prevented, like scheduled downtime or
	 *     reachabilitiy.
	 * @ninja orm_command enabled_if !notifications_enabled
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function start_notifications() {
		return $this->submit_naemon_command("ENABLE_HOST_NOTIFICATIONS");
	}


	/**
	 * @ninja orm_command name Disable event handler
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon shield-disabled
	 * @ninja orm_command mayi_method update.command.event_handler
	 * @ninja orm_command description
	 *     Disable execution of the custom event handler for this host.
	 * @ninja orm_command enabled_if event_handler_enabled
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function stop_event_handler() {
		return $this->submit_naemon_command("DISABLE_HOST_EVENT_HANDLER");
	}


	/**
	 * @ninja orm_command name Enable event handler
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon shield-enabled
	 * @ninja orm_command mayi_method update.command.event_handler
	 * @ninja orm_command description
	 *     Enable execution of the custom event handler for this host.
	 * @ninja orm_command enabled_if !event_handler_enabled
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function start_event_handler() {
		return $this->submit_naemon_command("ENABLE_HOST_EVENT_HANDLER");
	}

	/**
	 * @ninja orm_command name Disable flap detection
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon shield-disabled
	 * @ninja orm_command mayi_method update.command.flap_detection
	 * @ninja orm_command description
	 *     Disable analysis of this host is flapping. If no flap detection
	 *     analysis is enabled, the host will trigger a problem and recovery
	 *     notification every time the host goes up or down, not only just a
	 *     "flapping" notification when it starts flapping,
	 * @ninja orm_command enabled_if flap_detection_enabled
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function stop_flap_detection() {
		return $this->submit_naemon_command("DISABLE_HOST_FLAP_DETECTION");
	}


	/**
	 * @ninja orm_command name Enable flap detection
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon shield-enabled
	 * @ninja orm_command mayi_method update.command.flap_detection
	 * @ninja orm_command description
	 *     Enable analysis of this host is flapping. If flap detection
	 *     analysis is enabled, the host will trigger flapping notification
	 *     when the host starts to rapidly change between states, instead of
	 *     sending lot of notifications for every state change.
	 * @ninja orm_command enabled_if !flap_detection_enabled
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function start_flap_detection() {
		return $this->submit_naemon_command("ENABLE_HOST_FLAP_DETECTION");
	}

	/**
	 * Get configuration url
	 *
	 * @ninja orm depend[] name
	 *
	 * @ninja orm_command name Configure
	 * @ninja orm_command category Configuration
	 * @ninja orm_command icon nacoma
	 * @ninja orm_command mayi_method update.command.configure
	 * @ninja orm_command description
	 *     Configure this host
	 * @ninja orm_command enabled_if config_allowed
	 * @ninja orm_command redirect 1
	 */
	public function get_config_url() {
		return str_replace(array(
			'$HOSTNAME$'
		), array(
			urlencode($this->get_name())
		), Kohana::config('config.config_url.hosts'));
	}

	/**
	 * @ninja orm_command name Delete
	 * @ninja orm_command category Configuration
	 * @ninja orm_command icon delete
	 * @ninja orm_command mayi_method delete.command.delete
	 * @ninja orm_command description
	 *     Delete the host in Nacoma, and queue it in the changelog. The changes
	 *     still needs to be published in nacoma before the host is removed
	 *     from the running config
	 * @ninja orm_command view monitoring/nacoma_command
	 */
	public function delete() {
		if (Kohana::config('config.nacoma_path')===false) {
			return array(
				'result' => false,
				'name' => '',
				'output' => "Nacoma is not installed"
			);
		}


		exec('php /opt/monitor/op5/nacoma/api/monitor.php -u ' . op5Auth::instance()->get_user()->get_username() . ' -t host -n ' . escapeshellarg($this->get_key()) . ' -a delete', $out, $retval);
		if($retval === 0) {
			return array(
				'result' => true,
				'name' => $this->get_name(),
				'output' => "Submitted for deletion"
			);
		} else {
			return array(
				'result' => false,
				'name' => $this->get_name(),
				'output' => "Error deleting service"
			);
		}
	}

	/**
	 * Returns the public custom variables of an object
	 *
	 * @ninja orm depend[] custom_variables
	 * @return array $public_variables
	 */
	public function get_public_custom_variables () {

		$all_variables = $this->get_custom_variables();
		$public = array();

		foreach ($all_variables as $key => $value) {
			if (substr($key, 0, 6) !== 'OP5H_') {
				$public[$key] = $value;
			}
		}

		return $public;

	}

}
