<?php

require_once( dirname(__FILE__).'/base/baseservicegroup.php' );

/**
 * Describes a single object from livestatus
 */
class ServiceGroup_Model extends BaseServiceGroup_Model {
	/**
	 * Get statistics about services in the group
	 *
	 * @ninja orm depend[] name
	 */
	public function get_service_stats() {
		$set = ServicePool_Model::all()->reduce_by('groups', $this->get_name(), '>=');

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
	 * @ninja orm_command name Disable active service checks
	 * @ninja orm_command icon disable-active-checks
	 * @ninja orm_command mayi_method update.command.disable_service_checks
	 * @ninja orm_command description
	 *     This command is used to disable active checks of all services in the
	 *     specified servicegroup. This <i>does not</i> disable checks of the
	 *     hosts in the servicegroup unless you check the 'Disable for hosts
	 *     too' option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function disable_service_checks() {
		return $this->submit_naemon_command("DISABLE_SERVICEGROUP_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Disable service notifications
	 * @ninja orm_command icon notify-disabled
	 * @ninja orm_command mayi_method update.command.disable_service_notifications
	 * @ninja orm_command description
	 *     This command is used to prevent notifications from being sent out for
	 *     all services in the specified servicegroup. You will have to
	 *     re-enable notifications for all services in this servicegroup before
	 *     any alerts can be sent out in the future. This <i>does not</i>
	 *     prevent notifications from being sent out about the hosts in this
	 *     servicegroup unless you check the 'Disable for hosts too' option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function disable_service_notifications() {
		return $this->submit_naemon_command("DISABLE_SERVICEGROUP_SVC_NOTIFICATIONS");
	}

	/**
	 * @ninja orm_command name Enable active service checks
	 * @ninja orm_command icon enable
	 * @ninja orm_command mayi_method update.command.enable_service_checks
	 * @ninja orm_command description
	 *     This command is used to enable active checks of all services in the
	 *     specified servicegroup. This <i>does not</i> enable active checks of
	 *     the hosts in the servicegroup unless you check the 'Enable for hosts
	 *     too' option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function enable_service_checks() {
		return $this->submit_naemon_command("ENABLE_SERVICEGROUP_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Enable service notifications
	 * @ninja orm_command icon notify-send
	 * @ninja orm_command mayi_method update.command.enable_service_notifications
	 * @ninja orm_command description
	 *     This command is used to enable notifications for all services in the
	 *     specified servicegroup. Notifications will only be sent out for the
	 *     service state types you defined in your service definitions. This
	 *     <i>does not</i> enable notifications for the hosts in this
	 *     servicegroup unless you check the 'Enable for hosts too' option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function enable_service_notifications() {
		return $this->submit_naemon_command("ENABLE_SERVICEGROUP_SVC_NOTIFICATIONS");
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
	 *     servicegroup. During the specified downtime, Naemon will not send
	 *     notifications out about the hosts. When the scheduled downtime
	 *     expires, Naemon will send out notifications for the hosts as it
	 *     normally would. Scheduled downtimes are preserved across program
	 *     shutdowns and restarts. Both the start and end times should be
	 *     specified in the following format: <b>YYYY-MM-DD hh:mm:ss</b>. If you
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
		return $this->submit_naemon_command( "SCHEDULE_SERVICEGROUP_HOST_DOWNTIME", $start_time, $end_time, $fixed ? 1 : 0, $trigger_id, $duration_sec, $this->get_current_user(), $comment );
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
	 *     servicegroup. During the specified downtime, Naemon will not send
	 *     notifications out about the services. When the scheduled downtime
	 *     expires, Naemon will send out notifications for the services as it
	 *     normally would.  Scheduled downtimes are preserved across program
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
	 *     hosts in the servicegroup, check the 'Schedule downtime for hosts
	 *     too' option.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function schedule_service_downtime($duration, $trigger_id, $start_time, $end_time, $comment, $fixed=true) {
		$duration_sec = intval(floatval($duration) * 3600);
		return $this->submit_naemon_command( "SCHEDULE_SERVICEGROUP_SVC_DOWNTIME", $start_time, $end_time, $fixed ? 1 : 0, $trigger_id, $duration_sec, $this->get_current_user(), $comment );
	}
}
