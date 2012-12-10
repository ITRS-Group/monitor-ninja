<?php


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
		return $this->stats($stats);
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

		return parent::validate_columns($columns);
	}
}
