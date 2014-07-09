<?php

require_once( dirname(__FILE__).'/base/baseserviceset.php' );

/**
 * Describes a set of objects from livestatus
 */
class ServiceSet_Model extends BaseServiceSet_Model {

	/**
	 * Get statistics from the given set
	 */
	public function get_totals() {
		$pool = new ServicePool_Model();
		$stats = array(
				'service_state_ok'       => $pool->get_by_query('[services] state = 0 and has_been_checked=1'),
				'service_state_warning'  => $pool->get_by_query('[services] state = 1 and has_been_checked=1'),
				'service_state_critical' => $pool->get_by_query('[services] state = 2 and has_been_checked=1'),
				'service_state_unknown'  => $pool->get_by_query('[services] state = 3 and has_been_checked=1'),
				'service_pending'        => $pool->get_by_query('[services] has_been_checked=0'),
				'service_all'            => $pool->get_by_query('[services] all')
		);
		$stats_result = $this->stats($stats);
		$totals = array();
		foreach( $stats as $name => $set ) {
			$totals[$name] = array($this->intersect($set)->get_query(), $stats_result[$name]);
		}

		return $totals;
	}

	/**
	 * Get a set of comments for the services in the set
	 */
	public function get_comments() {
		$set = parent::get_comments();
		return $set->reduce_by('is_service', true, '=');
	}
}
