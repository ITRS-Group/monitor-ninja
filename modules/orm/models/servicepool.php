<?php

require_once( dirname(__FILE__).'/base/baseservicepool.php' );

/**
 * The univese of a objects of a given type in livestatus
 */
class ServicePool_Model extends BaseServicePool_Model {
	/**
	 * Get services by servicesgroup name, or by search filter name
	 */
	public function get_by_name( $name, $disabled_saved_queries = array() ) {
		$set = parent::get_by_name( $name, $disabled_saved_queries );
		if( $set === false ) {
			$set = self::all()->reduce_by( 'groups', $name, '>=' );
		}
		return $set;
	}
}
