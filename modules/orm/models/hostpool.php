<?php

require_once( dirname(__FILE__).'/base/basehostpool.php' );

class HostPool_Model extends BaseHostPool_Model {
	public function get_by_name( $name ) {
		$set = parent::get_by_name( $name );
		if( $set === false ) {
			$set = self::all()->reduce_by( 'groups', $name, '>=' );
		}
		return $set;
	}
}
