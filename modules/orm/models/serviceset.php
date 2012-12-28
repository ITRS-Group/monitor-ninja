<?php

require_once( dirname(__FILE__).'/base/baseserviceset.php' );

class ServiceSet_Model extends BaseServiceSet_Model {
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

	public function validate_columns($columns) {

		if( in_array( 'state_text', $columns ) ) {
			$columns = array_diff( $columns, array('state_text') );
			if(!in_array('state',$columns)) $columns[] = 'state';
			if(!in_array('has_been_checked',$columns)) $columns[] = 'has_been_checked';
		}
		if( in_array( 'checks_disabled', $columns ) ) {
			$columns = array_diff( $columns, array('checks_disabled') );
			if(!in_array('active_checks_enabled',$columns)) $columns[] = 'active_checks_enabled';
		}
		if( in_array( 'duration', $columns ) ) {
			$columns = array_diff( $columns, array('duration') );
			if(!in_array('last_state_change',$columns)) $columns[] = 'last_state_change';
		}

		return parent::validate_columns($columns);
	}
}
