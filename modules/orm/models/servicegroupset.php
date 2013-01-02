<?php

require_once( dirname(__FILE__).'/base/baseservicegroupset.php' );

class ServiceGroupSet_Model extends BaseServiceGroupSet_Model {
	public function validate_columns($columns) {
		$columns = parent::validate_columns($columns);

		if( in_array( 'service_stats', $columns ) ) {
			$columns = array_diff( $columns, array('service_stats') );
			$columns[] = 'name';
		}
		
		return $columns;
	}
}
