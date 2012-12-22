<?php


class HostSet_Model extends BaseHostSet_Model {
	public function get_totals() {
		$pool = new HostPool_Model();
		$stats = array(
				'host_state_up'          => $pool->get_by_name('std host state up'),
				'host_state_down'        => $pool->get_by_name('std host state down'),
				'host_state_unreachable' => $pool->get_by_name('std host state unreachable'),
				'host_pending'           => $pool->get_by_name('std host pending'),
				'host_all'               => $pool->get_by_name('std host all')
		);
		
		$stats_result = $this->stats($stats);
		$totals = array();
		foreach( $stats as $name => $set ) {
			$totals[$name] = array($this->intersect($set)->get_query(), $stats_result[$name]);
		}
		
		$service_set = $this->get_services();
		return $totals + $service_set->get_totals();
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
