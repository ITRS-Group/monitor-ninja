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
		$columns = parent::validate_columns($columns);

		$this->do_column_rewrite($columns, 'state_text_uc',array('state_text'));
		$this->do_column_rewrite($columns, 'state_text',array('state','has_been_checked'));
		$this->do_column_rewrite($columns, 'first_group', array('groups'));
		$this->do_column_rewrite($columns, 'checks_disabled',array('active_checks_enabled'));
		$this->do_column_rewrite($columns, 'duration',array('last_state_change'));

		return $columns;
	}
}
