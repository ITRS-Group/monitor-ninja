<?php

require_once( dirname(__FILE__).'/base/basehostpool.php' );

/**
 * The univese of a objects of a given type in livestatus
 */
class HostPool_Model extends BaseHostPool_Model {
	/**
	 * Get hosts by a named group or named saved query
	 */
	public function get_by_name( $name, $disabled_saved_queries = array() ) {
		$set = parent::get_by_name( $name, $disabled_saved_queries );
		if( $set === false ) {
			$set = self::all()->reduce_by( 'groups', $name, '>=' );
		}
		return $set;
	}
}
