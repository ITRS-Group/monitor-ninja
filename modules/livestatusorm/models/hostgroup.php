<?php


class HostGroup_Model extends BaseHostGroup_Model {
	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'host_stats';
		$this->export[] = 'service_stats';
	}
	
	public function get_host_stats() {
		$set = HostPool_Model::all()->reduceBy('groups', $this->get_name(), '>=');

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
		
		return $set->stats($stats);
	}
	public function get_service_stats() {
		$set = ServicePool_Model::all()->reduceBy('host.groups', $this->get_name(), '>=');

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
		
		return $set->stats($stats);
	}
}
