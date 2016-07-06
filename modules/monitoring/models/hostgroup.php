<?php


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

		if (config::get('checks.show_passive_as_active')) {
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

		if (config::get('checks.show_passive_as_active')) {
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
	 * @ninja orm_command mayi_method update.command.enabled
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
	 * @ninja orm_command mayi_method update.command.notification
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
	 * @ninja orm_command mayi_method update.command.enabled
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
	 * @ninja orm_command mayi_method update.command.notification
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
	 * @ninja orm_command mayi_method update.command.enabled
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
	 * @ninja orm_command mayi_method update.command.notification
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
	 * @ninja orm_command mayi_method update.command.enabled
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
	 * @ninja orm_command mayi_method update.command.notification
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
	 * @param start_time
	 * @param end_time
	 * @param flexible
	 * @param duration
	 * @param trigger_id
	 * @param propagation
	 * @param comment
	 *
	 * @ninja orm_command name Schedule host downtime
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
	 *     This command is used to schedule downtime for all hosts in a
	 *     hostgroup. During the specified downtime, Naemon will not send
	 *     notifications out about the hosts. When the scheduled downtime
	 *     expires, Naemon will send out notifications for the hosts as it
	 *     normally would. Scheduled downtimes are preserved across program
	 *     shutdowns and restarts.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function schedule_host_downtime($start_time, $end_time, $flexible, $duration, $trigger_id, $propagation, $comment) {
		$hosts = HostPool_Model::all()->reduce_by('groups', $this->get_name(), '>=');
		$result = array(
				'status' => 0,
				'output' => 'No hosts in hostgroup.'
				);
		foreach($hosts as $host) {
			/* @var $host Host_Model */
			$cur_result = $host->schedule_downtime($start_time, $end_time, $flexible, $duration, $trigger_id, $propagation, $comment);

			/* Keep those with errors... */
			if($cur_result['status'] > $result['status'])
				$result = $cur_result;
		}
		return $result;
	}


	/**
	 * @param start_time
	 * @param end_time
	 * @param flexible
	 * @param duration
	 * @param trigger_id
	 * @param comment
	 *
	 * @ninja orm_command name Schedule service downtime
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
	 *     Flexible downtime starts when the service get a problem state
	 *     (sometime between the start and end times you specified)
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
	 * @ninja orm_command params.comment.id 5
	 * @ninja orm_command params.comment.type string
	 * @ninja orm_command params.comment.name Comment
	 *
	 * @ninja orm_command description
	 *     This command is used to schedule downtime for all services in a
	 *     hostgroup. During the specified downtime, Naemon will not send
	 *     notifications out about the services. When the scheduled downtime
	 *     expires, Naemon will send out notifications for the services as it
	 *     normally would. Scheduled downtimes are preserved across program
	 *     shutdowns and restarts.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function schedule_service_downtime($start_time, $end_time, $flexible, $duration, $trigger_id, $comment) {
		$services = HostPool_Model::all()->reduce_by('groups', $this->get_name(), '>=')->get_services();
		$result = array(
				'status' => 0,
				'output' => 'No hosts in hostgroup.'
				);
		foreach($services as $service) {
			/* @var $host Service_Model */
			$cur_result = $service->schedule_downtime($start_time, $end_time, $flexible, $duration, $trigger_id, $comment);

			/* Keep those with errors... */
			if($cur_result['status'] > $result['status'])
				$result = $cur_result;
		}
		return $result;
	}
}
