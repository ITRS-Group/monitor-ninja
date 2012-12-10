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
		
		$service_set = $this->convert_to_object('services', 'host');
		return $this->stats($stats) + $service_set->get_totals();
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
