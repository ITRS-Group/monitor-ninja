<?php

require_once( dirname(__FILE__).'/base/basestatuspool.php' );

class StatusPool_Model extends BaseStatusPool_Model {
	public static function status() {
		return self::all()->getIterator()->current();
	}
}
