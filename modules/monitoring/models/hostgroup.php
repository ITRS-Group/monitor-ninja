<?php

require_once( dirname(__FILE__).'/base/basehostgroup.php' );

/**
 * Describes a single object from livestatus
 */
class HostGroup_Model extends BaseHostGroup_Model {
	/**
	 * Get statistics about the hosts in the group
	 *
	 * @ninja orm depend[] name
	 */
	public function get_host_stats() {
		$set = HostPool_Model::all()->reduce_by('groups', $this->get_name(), '>=');

		if (config::get('checks.show_passive_as_active', '*')) {
			$active_checks = ObjectPool_Model::get_by_query('[hosts] active_checks_enabled = 1 or accept_passive_checks = 1');
			$disabled_checks = ObjectPool_Model::get_by_query('[hosts] active_checks_enabled = 0 and accept_passive_checks = 0');
		} else {
			$active_checks = ObjectPool_Model::get_by_query('[hosts] active_checks_enabled = 1');
			$disabled_checks = ObjectPool_Model::get_by_query('[hosts] active_checks_enabled = 0');
		}

		$all              = ObjectPool_Model::get_by_query('[hosts] state!=999');
		$pending          = ObjectPool_Model::get_by_query('[hosts] has_been_checked=0');
		$up               = ObjectPool_Model::get_by_query('[hosts] state=0 and has_been_checked=1');
		$down             = ObjectPool_Model::get_by_query('[hosts] state=1 and has_been_checked=1');
		$unreachable      = ObjectPool_Model::get_by_query('[hosts] state=2 and has_been_checked=1');

		$acknowledged     = ObjectPool_Model::get_by_query('[hosts] acknowledged = 1');
		$disabled_active  = ObjectPool_Model::get_by_query('[hosts] check_type = 0')->intersect($disabled_checks);
		$scheduled        = ObjectPool_Model::get_by_query('[hosts] scheduled_downtime_depth > 0');
		$unscheduled      = ObjectPool_Model::get_by_query('[hosts] scheduled_downtime_depth = 0');
		$unhandled        = ObjectPool_Model::get_by_query('[hosts] acknowledged = 0 and scheduled_downtime_depth = 0')->intersect($active_checks);

		$flapping         = ObjectPool_Model::get_by_query('[hosts] is_flapping = 1' );

		$stats = array(
			'total'                             => $all,
			'pending'                           => $pending,
			'pending_and_disabled'              => $pending->intersect($disabled_checks),
			'pending_and_scheduled'             => $pending->intersect($scheduled),
			'up'                                => $up,
			'up_and_disabled_active'            => $up->intersect($disabled_active),
			'up_and_scheduled'                  => $up->intersect($scheduled),
			'down'                              => $down,
			'down_and_ack'                      => $down->intersect($acknowledged),
			'down_and_scheduled'                => $down->intersect($scheduled),
			'down_and_disabled_active'          => $down->intersect($disabled_active),
			'down_and_unhandled'                => $down->intersect($unscheduled),
			'unreachable'                       => $unreachable,
			'unreachable_and_ack'               => $unreachable->intersect($acknowledged),
			'unreachable_and_scheduled'         => $unreachable->intersect($scheduled),
			'unreachable_and_disabled_active'   => $unreachable->intersect($disabled_active),
			'unreachable_and_unhandled'         => $unreachable->intersect($unhandled)
		);

		$queries = array();
		foreach( $stats as $name => $stat ) {
			$queries[$name] = $set->intersect($stat)->get_query();
		}
		return array( 'stats' => $set->stats($stats), 'queries' => $queries );
	}

	/**
	 * Get statistics about the services in the group
	 *
	 * @ninja orm depend[] name
	 */
	public function get_service_stats() {
		$set = ServicePool_Model::all()->reduce_by('host.groups', $this->get_name(), '>=');

		if (config::get('checks.show_passive_as_active', '*')) {
			$active_checks = ObjectPool_Model::get_by_query('[services] active_checks_enabled = 1 or accept_passive_checks = 1');
			$disabled_checks = ObjectPool_Model::get_by_query('[services] active_checks_enabled = 0 and accept_passive_checks = 0');
		} else {
			$active_checks = ObjectPool_Model::get_by_query('[services] active_checks_enabled = 1');
			$disabled_checks = ObjectPool_Model::get_by_query('[services] active_checks_enabled = 0');
		}

		$all              = ObjectPool_Model::get_by_query('[services] state!=999');
		$pending          = ObjectPool_Model::get_by_query('[services] has_been_checked=0');
		$ok               = ObjectPool_Model::get_by_query('[services] state=0 and has_been_checked=1');
		$warn             = ObjectPool_Model::get_by_query('[services] state=1 and has_been_checked=1');
		$critical         = ObjectPool_Model::get_by_query('[services] state=2 and has_been_checked=1');
		$unknown          = ObjectPool_Model::get_by_query('[services] state=3 and has_been_checked=1');

		$acknowledged     = ObjectPool_Model::get_by_query('[services] acknowledged = 1');
		$disabled_active  = ObjectPool_Model::get_by_query('[services] check_type = 0')->intersect($disabled_checks);
		$scheduled        = ObjectPool_Model::get_by_query('[services] scheduled_downtime_depth > 0');
		$unscheduled      = ObjectPool_Model::get_by_query('[services] scheduled_downtime_depth = 0');
		$unhandled        = ObjectPool_Model::get_by_query('[services] acknowledged = 0 and scheduled_downtime_depth = 0')->intersect($active_checks);

		$down_host        = ObjectPool_Model::get_by_query('[services] host.state != 0');

		$stats = array(
			'ok'                           => $ok,
			'warning'                      => $warn,
			'warning_and_ack'              => $warn->intersect($acknowledged),
			'warning_and_disabled_active'  => $warn->intersect($disabled_active),
			'warning_and_scheduled'        => $warn->intersect($scheduled),
			'warning_and_unhandled'        => $warn->intersect($unhandled),
			'warning_on_down_host'         => $warn->intersect($down_host),
			'critical'                     => $critical,
			'critical_and_ack'             => $critical->intersect($acknowledged),
			'critical_and_disabled_active' => $critical->intersect($disabled_active),
			'critical_and_scheduled'       => $critical->intersect($scheduled),
			'critical_and_unhandled'       => $critical->intersect($unhandled),
			'critical_on_down_host'        => $critical->intersect($down_host),
			'unknown'                      => $unknown,
			'unknown_and_ack'              => $unknown->intersect($acknowledged),
			'unknown_and_disabled_active'  => $unknown->intersect($disabled_active),
			'unknown_and_scheduled'        => $unknown->intersect($scheduled),
			'unknown_and_unhandled'        => $unknown->intersect($unhandled),
			'unknown_on_down_host'         => $unknown->intersect($down_host),
			'pending'                      => $pending
		);


		$queries = array();
		foreach( $stats as $name => $stat ) {
			$queries[$name] = $set->intersect($stat)->get_query();
		}
		return array( 'stats' => $set->stats($stats), 'queries' => $queries );
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
	 *     Configure this hostgroup.
	 * @ninja orm_command redirect 1
	 */
	public function get_config_url() {
		if(nacoma::link() !== true) {
			return false;
		}
		return 'configuration/configure/hostgroup/'.$this->get_name();
	}

	/**
	 * @ninja orm_command name Disable active host checks
	 * @ninja orm_command category Host Operations
	 * @ninja orm_command icon disable-active-checks
	 * @ninja orm_command mayi_method update.command.disable_host_checks
	 * @ninja orm_command description
	 *      Disables active checks for all hosts in this hostgroup.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function disable_host_checks() {
		return $this->submit_naemon_command("DISABLE_HOSTGROUP_HOST_CHECKS");
	}

	/**
	 * @ninja orm_command name Disable host notifications
	 * @ninja orm_command category Host Operations
	 * @ninja orm_command icon notify-disabled
	 * @ninja orm_command mayi_method update.command.stop_notifications
	 * @ninja orm_command description
	 *     Disable notifications for all hosts in this hostgroup.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function disable_host_notifications() {
		return $this->submit_naemon_command("DISABLE_HOSTGROUP_HOST_NOTIFICATIONS");
	}

	/**
	 * @ninja orm_command name Disable active service checks
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon disable-active-checks
	 * @ninja orm_command mayi_method update.command.disable_service_checks
	 * @ninja orm_command description
	 *      Disables active checks for all services associated with hosts
	 *      in this hostgroup.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function disable_service_checks() {
		return $this->submit_naemon_command("DISABLE_HOSTGROUP_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Disable service notifications
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon notify-disabled
	 * @ninja orm_command mayi_method update.command.disable_service_notifications
	 * @ninja orm_command description
	 *     This command is used to prevent notifications from being sent out for
	 *     all services in the specified hostgroup. You will have to re-enable
	 *     notifications for all services in this hostgroup before any alerts
	 *     can be sent out in the future. This <i>does not</i> prevent
	 *     notifications from being sent out about the hosts in this hostgroup
	 *     unless you check the 'Disable for hosts too' option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function disable_service_notifications() {
		return $this->submit_naemon_command("DISABLE_HOSTGROUP_SVC_NOTIFICATIONS");
	}

	/**
	 * @ninja orm_command name Enable active host checks
	 * @ninja orm_command category Host Operations
	 * @ninja orm_command icon enable
	 * @ninja orm_command mayi_method update.command.enable_host_checks
	 * @ninja orm_command description
	 *      Enables active checks for all hosts in this hostgroup.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function enable_host_checks() {
		return $this->submit_naemon_command("ENABLE_HOSTGROUP_HOST_CHECKS");
	}

	/**
	 * @ninja orm_command name Enable host notifications
	 * @ninja orm_command category Host Operations
	 * @ninja orm_command icon notify
	 * @ninja orm_command mayi_method update.command.start_notifications
	 * @ninja orm_command description
	 *     Enable notifications for all hosts in this hostgroup.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function enable_host_notifications() {
		return $this->submit_naemon_command("ENABLE_HOSTGROUP_HOST_NOTIFICATIONS");
	}

	/**
	 * @ninja orm_command name Enable active service checks
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon enable
	 * @ninja orm_command mayi_method update.command.enable_service_checks
	 * @ninja orm_command description
	 *     This command is used to enable active checks of all services in the
	 *     specified hostgroup. This <i>does not</i> enable active checks of the
	 *     hosts in the hostgroup unless you check the 'Enable for hosts too'
	 *     option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function enable_service_checks() {
		return $this->submit_naemon_command("ENABLE_HOSTGROUP_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Enable service notifications
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon notify-send
	 * @ninja orm_command mayi_method update.command.enable_service_notifications
	 * @ninja orm_command description
	 *     This command is used to enable notifications for all services in the
	 *     specified hostgroup. Notifications will only be sent out for the
	 *     service state types you defined in your service definitions. This
	 *     <i>does not</i> enable notifications for the hosts in this hostgroup
	 *     unless you check the 'Enable for hosts too' option.
	 */
	public function enable_service_notifications() {
		return $this->submit_naemon_command("ENABLE_HOSTGROUP_SVC_NOTIFICATIONS");
	}

	/**
	 * @param duration
	 * @param trigger_id
	 * @param start_time
	 * @param end_time
	 * @param comment
	 * @param fixed = true
	 *
	 * @ninja orm_command name Schedule host downtime
	 * @ninja orm_command category Host Operations
	 * @ninja orm_command icon scheduled-downtime
	 * @ninja orm_command mayi_method update.command.schedule_host_downtime
	 *
	 * @ninja orm_command params.duration.id 0
	 * @ninja orm_command params.duration.type duration
	 * @ninja orm_command params.duration.name Duration
	 *
	 * @ninja orm_command params.trigger_id.id 1
	 * @ninja orm_command params.trigger_id.type int
	 * @ninja orm_command params.trigger_id.name Trigger id
	 *
	 * @ninja orm_command params.start_time.id 2
	 * @ninja orm_command params.start_time.type time
	 * @ninja orm_command params.start_time.name Start time
	 *
	 * @ninja orm_command params.end_time.id 3
	 * @ninja orm_command params.end_time.type time
	 * @ninja orm_command params.end_time.name End time
	 *
	 * @ninja orm_command params.comment.id 4
	 * @ninja orm_command params.comment.type string
	 * @ninja orm_command params.comment.name Comment
	 *
	 * @ninja orm_command params.fixed.id 5
	 * @ninja orm_command params.fixed.type bool
	 * @ninja orm_command params.fixed.name Fixed
	 *
	 * @ninja orm_command description
	 *     This command is used to schedule downtime for all hosts in a
	 *     hostgroup. During the specified downtime, Naemon will not send
	 *     notifications out about the hosts. When the scheduled downtime
	 *     expires, Naemon will send out notifications for the hosts as it
	 *     normally would. Scheduled downtimes are preserved across program
	 *     shutdowns and restarts. Both the start and end times should be
	 *     pecified in the following format:  <b>YYYY-MM-DD hh:mm:ss</b>. If you
	 *     select the <i>fixed</i> option, the downtime will be in effect
	 *     between the start and end times you specify. If you do not select the
	 *     <i>fixed</i> option, Naemon will treat this as "flexible" downtime.
	 *     Flexible downtime starts when a host goes down or becomes unreachable
	 *     (sometime between the start and end times you specified) and lasts as
	 *     long as the duration of time you enter. The duration fields do not
	 *     apply for fixed dowtime.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function schedule_host_downtime($duration, $trigger_id, $start_time, $end_time, $comment, $fixed=true) {
		$duration_sec = intval(floatval($duration) * 3600);
		$start_tstamp = nagstat::timestamp_format(false, $start_time);
		if($start_tstamp === false) {
			return array(
				'status' => 0,
				'output' => $start_time . " is not a valid date, please adjust it"
			);
		}
		$end_tstamp = nagstat::timestamp_format(false, $end_time);
		if($end_tstamp === false) {
			return array(
				'status' => 0,
				'output' => $end_time . " is not a valid date, please adjust it"
			);
		}
		$set = HostPool_Model::all()->reduce_by('groups', $this->get_name(), '>=');
		return $this->schedule_downtime_retrospectively($set, "SCHEDULE_HOSTGROUP_HOST_DOWNTIME", $start_tstamp, $end_tstamp, $fixed ? 1 : 0, $trigger_id, $duration_sec, $this->get_current_user(), $comment);
	}

	/**
	 * @param duration
	 * @param trigger_id
	 * @param start_time
	 * @param end_time
	 * @param comment
	 * @param fixed = true
	 *
	 * @ninja orm_command name Schedule service downtime
	 * @ninja orm_command category Service Operations
	 * @ninja orm_command icon scheduled-downtime
	 * @ninja orm_command mayi_method update.command.schedule_service_downtime
	 *
	 * @ninja orm_command params.duration.id 0
	 * @ninja orm_command params.duration.type duration
	 * @ninja orm_command params.duration.name Duration
	 *
	 * @ninja orm_command params.trigger_id.id 1
	 * @ninja orm_command params.trigger_id.type int
	 * @ninja orm_command params.trigger_id.name Trigger id
	 *
	 * @ninja orm_command params.start_time.id 2
	 * @ninja orm_command params.start_time.type time
	 * @ninja orm_command params.start_time.name Start time
	 *
	 * @ninja orm_command params.end_time.id 3
	 * @ninja orm_command params.end_time.type time
	 * @ninja orm_command params.end_time.name End time
	 *
	 * @ninja orm_command params.comment.id 4
	 * @ninja orm_command params.comment.type string
	 * @ninja orm_command params.comment.name Comment
	 *
	 * @ninja orm_command params.fixed.id 5
	 * @ninja orm_command params.fixed.type bool
	 * @ninja orm_command params.fixed.name Fixed
	 *
	 * @ninja orm_command description
	 *     This command is used to schedule downtime for all services in a
	 *     hostgroup. During the specified downtime, Naemon will not send
	 *     notifications out about the services. When the scheduled downtime
	 *     expires, Nagios will send out notifications for the services as it
	 *     normally would. Scheduled downtimes are preserved across program
	 *     shutdowns and restarts. Both the start and end times should be
	 *     specified in the following format: <b>YYYY-MM-DD hh:mm:ss</b>. If you
	 *     select the <i>fixed</i> option, the downtime will be in effect
	 *     between the start and end times you specify. If you do not select the
	 *     <i>fixed</i> option, Naemon will treat this as "flexible" downtime.
	 *     Flexible downtime starts when a service enters a non-OK state
	 *     (sometime between the start and end times you specified) and lasts as
	 *     long as the duration of time you enter. The duration fields do not
	 *     apply for fixed dowtime. Note that scheduling downtime for services
	 *     does not automatically schedule downtime for the hosts those services
	 *     are associated with. If you want to also schedule downtime for all
	 *     hosts in the hostgroup, check the 'Schedule downtime for hosts too'
	 *     option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function schedule_service_downtime($duration, $trigger_id, $start_time, $end_time, $comment, $fixed=true) {
		$duration_sec = intval(floatval($duration) * 3600);
		$start_tstamp = nagstat::timestamp_format(false, $start_time);
		if($start_tstamp === false) {
			return array(
				'status' => 0,
				'output' => $start_time . " is not a valid date, please adjust it"
			);
		}
		$end_tstamp = nagstat::timestamp_format(false, $end_time);
		if($end_tstamp === false) {
			return array(
				'status' => 0,
				'output' => $end_time . " is not a valid date, please adjust it"
			);
		}

		$set = ServicePool_Model::all()->reduce_by('host.groups', $this->get_name(), '>=');
		return $this->schedule_downtime_retrospectively($set, "SCHEDULE_HOSTGROUP_SVC_DOWNTIME", $start_tstamp, $end_tstamp, $fixed ? 1 : 0, $trigger_id, $duration_sec, $this->get_current_user(), $comment);
	}
}
