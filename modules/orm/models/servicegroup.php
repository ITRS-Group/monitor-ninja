<?php

require_once( dirname(__FILE__).'/base/baseservicegroup.php' );

class ServiceGroup_Model extends BaseServiceGroup_Model {
	static public $macros =  array(
		'$SERVICEGROUPNAME$' => 'name',
		'$SERVICEGROUPALIAS$' => 'alias'
	);
	static public $rewrite_columns = array(
		'service_stats' => array('name')
	);

	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'service_stats';
	}

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
}
