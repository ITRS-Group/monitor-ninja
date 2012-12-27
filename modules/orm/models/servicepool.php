<?php


class ServicePool_Model extends BaseServicePool_Model {
	public function get_by_name( $name ) {
		$set = parent::get_by_name( $name );
		if( $set === false ) {
			$set = self::all()->reduceBy( 'groups', $name, '>=' );
		}
		return $set;
	}
}
