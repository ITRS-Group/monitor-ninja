<?php


class ServiceGroupSet_Model extends BaseServiceGroupSet_Model {
	public function validate_columns($columns) {

		if( in_array( 'service_stats', $columns ) ) {
			$columns = array_diff( $columns, array('service_stats') );
			if(!in_array('name',$columns)) $columns[] = 'name';
		}
		
		return parent::validate_columns($columns);
	}
}
