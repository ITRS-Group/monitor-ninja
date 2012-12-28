<?php

require_once( dirname(__FILE__).'/base/basenotificationset.php' );

class NotificationSet_Model extends BaseNotificationSet_Model {

	public function validate_columns($columns) {

		if( in_array( 'state_text', $columns ) ) {
			$columns = array_diff( $columns, array('state_text') );
			if(!in_array('state',$columns)) $columns[] = 'state';
			if(!in_array('notification_type',$columns)) $columns[] = 'notification_type';
		}

		return parent::validate_columns($columns);
	}
}
