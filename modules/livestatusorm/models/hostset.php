<?php


class HostSet_Model extends BaseHostSet_Model {
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
