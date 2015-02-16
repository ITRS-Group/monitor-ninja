<?php

require_once( dirname(__FILE__).'/base/basehostset.php' );

/**
 * Describes a set of objects from livestatus
 */
class HostSet_Model extends BaseHostSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitor.monitoring.hosts";
	}

	/**
	 * Get statistics of the hosts in the set
	 */
	public function get_totals() {
		$pool = new HostPool_Model();
		$stats = array(
				'host_state_up'          => $pool->get_by_query('[hosts] state = 0 and has_been_checked=1'),
				'host_state_down'        => $pool->get_by_query('[hosts] state = 1 and has_been_checked=1'),
				'host_state_unreachable' => $pool->get_by_query('[hosts] state = 2 and has_been_checked=1'),
				'host_pending'           => $pool->get_by_query('[hosts] has_been_checked=0'),
				'host_all'               => $pool->get_by_query('[hosts] all')
		);

		$stats_result = $this->stats($stats);
		$totals = array();
		foreach( $stats as $name => $set ) {
			$totals[$name] = array($this->intersect($set)->get_query(), $stats_result[$name]);
		}

		$service_set = $this->get_services();
		return $totals + $service_set->get_totals();
	}

	/**
	 * Get comments for the hosts in the set
	 */
	public function get_comments() {
		$set = parent::get_comments();
		return $set->reduce_by('is_service', false, '=');
	}
}
