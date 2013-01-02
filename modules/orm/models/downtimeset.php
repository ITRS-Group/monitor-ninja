<?php

require_once( dirname(__FILE__).'/base/basedowntimeset.php' );

class DowntimeSet_Model extends BaseDowntimeSet_Model {
	public function validate_columns($columns) {

		if( in_array( 'triggered_by_text', $columns ) ) {
			$columns = array_diff( $columns, array('triggered_by_text') );
			if(!in_array('triggered_by',$columns)) $columns[] = 'triggered_by';
		}
		
		return parent::validate_columns($columns);
	}
}
