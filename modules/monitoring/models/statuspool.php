<?php


/**
 * The univese of a objects of a given type in livestatus
 */
class StatusPool_Model extends BaseStatusPool_Model {
	/**
	 * Get the status object, if avalible (depending on access rights)
	 */
	public static function status() {
		return self::all()->getIterator()->current();
	}
}
