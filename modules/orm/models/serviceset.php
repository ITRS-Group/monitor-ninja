<?php

require_once( dirname(__FILE__).'/base/baseserviceset.php' );

class ServiceSet_Model extends BaseServiceSet_Model {
	public function validate_columns( $columns ) {
		$columns[] = 'custom_variables';
		return parent::validate_columns($columns);
	}
	public function get_totals() {
		$pool = new ServicePool_Model();
		$stats = array(
				'service_state_ok'       => $pool->get_by_name('std service state ok'),
				'service_state_warning'  => $pool->get_by_name('std service state warning'),
				'service_state_critical' => $pool->get_by_name('std service state critical'),
				'service_state_unknown'  => $pool->get_by_name('std service state unknown'),
				'service_pending'        => $pool->get_by_name('std service pending'),
				'service_all'            => $pool->get_by_name('std service all')
		);
		$stats_result = $this->stats($stats);
		$totals = array();
		foreach( $stats as $name => $set ) {
			$totals[$name] = array($this->intersect($set)->get_query(), $stats_result[$name]);
		}
		
		return $totals;
	}
}
