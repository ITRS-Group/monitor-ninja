<?php

require_once( dirname(__FILE__).'/base/basehostset.php' );

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
		$columns = parent::validate_columns($columns);

		$this->do_column_rewrite($columns, 'state_text_uc', array('state_text'));
		$this->do_column_rewrite($columns, 'state_type_text_uc', array('state_type'));
		$this->do_column_rewrite($columns, 'state_text', array('state','has_been_checked'));
		$this->do_column_rewrite($columns, 'first_group', array('groups'));
		$this->do_column_rewrite($columns, 'checks_disabled', array('active_checks_enabled'));
		$this->do_column_rewrite($columns, 'duration', array('last_state_change'));
		$this->do_column_rewrite($columns, 'comments_count', array('comments'));

		return $columns;
	}
}
