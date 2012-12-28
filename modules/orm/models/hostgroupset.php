<?php

require_once( dirname(__FILE__).'/base/basehostgroupset.php' );

class HostGroupSet_Model extends BaseHostGroupSet_Model {
	public function validate_columns($columns) {

		if( in_array( 'host_stats', $columns ) ) {
			$columns = array_diff( $columns, array('host_stats') );
			if(!in_array('name',$columns)) $columns[] = 'name';
		}
		if( in_array( 'service_stats', $columns ) ) {
			$columns = array_diff( $columns, array('service_stats') );
			if(!in_array('name',$columns)) $columns[] = 'name';
		}
		
		return parent::validate_columns($columns);
	}
}
