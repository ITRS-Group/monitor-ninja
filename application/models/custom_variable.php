<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate service status data
 */
class Custom_variable_Model extends Model
{
	/**
 	 * @param $object_type
	 * @throws InvalidArgumentException
	 * @return array
	 */
	public static function get_for($object_type, $object_id = null) {
		switch($object_type) {
			case 'host':
			case 'service':
				break;
			default:
				throw new InvalidArgumentException("'$object_type' is not a valid object type in ".__METHOD__);
		}
		if($object_id) {
			return Database::instance()->query("SELECT * FROM custom_vars WHERE obj_type = ? AND obj_id = ?", array($object_type, $object_id))->as_array(false);
		}
		return Database::instance()->query("SELECT * FROM custom_vars WHERE obj_type = ?", array($object_type))->as_array(false);
	}
}
