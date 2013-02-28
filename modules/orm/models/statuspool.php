<?php

require_once( dirname(__FILE__).'/base/basestatuspool.php' );

/**
 * The univese of a objects of a given type in livestatus
 */
class StatusPool_Model extends BaseStatusPool_Model {
	public static function status() {
		return self::all()->getIterator()->current();
	}
}
