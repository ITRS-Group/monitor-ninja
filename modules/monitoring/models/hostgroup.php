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
	 * All methods in this class that corresponds 1:1 to a command, such
	 * as a Naemon command. Note that developers might have implemented
	 * other methods that in turn call these; those methods are *not*
	 * returned from this method (unless the method is overwritten). As
	 * such, this method can be used for listing e.g. mayi resources.
	 *
	 *
	 * @return array
	 */
	public function list_commands() {
		return array (
			'disable_service_checks' =>
			array (
				'parameters' =>
				array (
				),
				'description' => 'This command is used to disable active checks of all services in the specified hostgroup.  This <i>does not</i> disable checks of the hosts in the hostgroup unless you check the \'Disable for hosts too\' option. ',
				'mayi_resource' => '',
			),
			'disable_service_notifications' =>
			array (
				'parameters' =>
				array (
				),
				'description' => 'This command is used to prevent notifications from being sent out for all services in the specified hostgroup.  You will have to re-enable notifications for all services in this hostgroup before any alerts can be sent out in the future.  This <i>does not</i> prevent notifications from being sent out about the hosts in this hostgroup unless you check the \'Disable for hosts too\' option. ',
				'mayi_resource' => '',
			),
			'enable_service_checks' =>
			array (
				'parameters' =>
				array (
				),
				'description' => 'This command is used to enable active checks of all services in the specified hostgroup.  This <i>does not</i> enable active checks of the hosts in the hostgroup unless you check the \'Enable for hosts too\' option. ',
				'mayi_resource' => '',
			),
			'enable_service_notifications' =>
			array (
				'parameters' =>
				array (
				),
				'description' => 'This command is used to enable notifications for all services in the specified hostgroup.  Notifications will only be sent out for the service state types you defined in your service definitions.  This <i>does not</i> enable notifications for the hosts in this hostgroup unless you check the \'Enable for hosts too\' option. ',
				'mayi_resource' => '',
			),
			'schedule_host_downtime' =>
			array (
				'parameters' =>
				array (
					'start_time' => 'time',
					'end_time' => 'time',
					'fixed' => 'bool',
					'trigger_id' => 'select',
					'duration' => 'duration',
					'comment' => 'string',
				),
				'description' => 'This command is used to schedule downtime for all hosts in a hostgroup.  During the specified downtime, Nagios will not send notifications out about the hosts. When the scheduled downtime expires, Nagios will send out notifications for the hosts as it normally would.  Scheduled downtimes are preserved across program shutdowns and restarts.  Both the start and end times should be specified in the following format:  <b>Y-m-d H:i:s</b> (<a href="http://php.net/manual/en/function.date.php">see explanation of date-letters</a>). If you select the <i>fixed</i> option, the downtime will be in effect between the start and end times you specify.  If you do not select the <i>fixed</i> option, Nagios will treat this as "flexible" downtime.  Flexible downtime starts when a host goes down or becomes unreachable (sometime between the start and end times you specified) and lasts as long as the duration of time you enter.  The duration fields do not apply for fixed dowtime. ',
				'mayi_resource' => '',
			),
			'schedule_service_downtime' =>
			array (
				'parameters' =>
				array (
					'start_time' => 'time',
					'end_time' => 'time',
					'fixed' => 'bool',
					'trigger_id' => 'select',
					'duration' => 'duration',
					'comment' => 'string',
				),
				'description' => 'This command is used to schedule downtime for all services in a hostgroup.  During the specified downtime, Nagios will not send notifications out about the services. When the scheduled downtime expires, Nagios will send out notifications for the services as it normally would.  Scheduled downtimes are preserved across program shutdowns and restarts.  Both the start and end times should be specified in the following format:  <b>Y-m-d H:i:s</b> (<a href="http://php.net/manual/en/function.date.php">see explanation of date-letters</a>). If you select the <i>fixed</i> option, the downtime will be in effect between the start and end times you specify.  If you do not select the <i>fixed</i> option, Nagios will treat this as "flexible" downtime.  Flexible downtime starts when a service enters a non-OK state (sometime between the start and end times you specified) and lasts as long as the duration of time you enter.  The duration fields do not apply for fixed dowtime. Note that scheduling downtime for services does not automatically schedule downtime for the hosts those services are associated with.  If you want to also schedule downtime for all hosts in the hostgroup, check the \'Schedule downtime for hosts too\' option. ',
				'mayi_resource' => '',
			),
		);
	}

	/**
	 * @param &error_string = NULL
	 * @return bool
	 */
	public function disable_service_checks(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("DISABLE_HOSTGROUP_SVC_CHECKS",
		array(
		'hostgroup_name' => implode(';', array($this->get_name()))
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
	 */
	public function disable_service_notifications(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("DISABLE_HOSTGROUP_SVC_NOTIFICATIONS",
		array(
		'hostgroup_name' => implode(';', array($this->get_name()))
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
	 */
	public function enable_service_checks(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("ENABLE_HOSTGROUP_SVC_CHECKS",
		array(
		'hostgroup_name' => implode(';', array($this->get_name()))
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
	 */
	public function enable_service_notifications(&$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("ENABLE_HOSTGROUP_SVC_NOTIFICATIONS",
		array(
		'hostgroup_name' => implode(';', array($this->get_name()))
		)
		);
		$result = nagioscmd::submit_to_nagios($command, "", $output);
		if(!$result && $output !== false) {
			$error_string = $output;
		}
		return $result;
	}

	/**
	 * Autogenerated method
	 *
	 * @param duration
	 * @param trigger_id
	 * @param start_time
	 * @param end_time
	 * @param comment
	 * @param fixed = true
	 * @param &error_string = NULL
	 * @return bool
	 */
	public function schedule_host_downtime($duration, $trigger_id, $start_time, $end_time, $comment, $fixed=true, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("SCHEDULE_HOSTGROUP_HOST_DOWNTIME",
		array(
		'hostgroup_name' => implode(';', array($this->get_name())),
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
	 * Autogenerated method
	 *
	 * @param duration
	 * @param trigger_id
	 * @param start_time
	 * @param end_time
	 * @param comment
	 * @param fixed = true
	 * @param &error_string = NULL
	 * @return bool
	 */
	public function schedule_service_downtime($duration, $trigger_id, $start_time, $end_time, $comment, $fixed=true, &$error_string=NULL) {
		$error_string = null;
		$command = nagioscmd::build_command("SCHEDULE_HOSTGROUP_SVC_DOWNTIME",
		array(
		'hostgroup_name' => implode(';', array($this->get_name())),
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
}
