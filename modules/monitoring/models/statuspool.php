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

	/**
	 * The status object-type always only has one object
	 * and that object should always be accessible (given
	 * livestatus is on)
	 *
	 * @param $key Ignored
	 * @return StatusSet_Model
	 */
	public static function set_by_key ($key) {
		return self::all();
	}
}
