<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate information regarding servicegroups
 */
class Servicegroup_Model extends ORM
{
	protected $table_names_plural = false;

	/**
	 * Fetch servicegroup where field matches value
	 * @param $field The field to fetch
	 * @param $value The value to search for
	 * @return false on errors, array(?) on success
	 */
	public function get_by_field_value($field=false, $value=false)
	{
		$value = trim($value);
		$field = trim($field);
		if (empty($value) || empty($field)) {
			return false;
		}
		return ORM::factory('servicegroup')->where($field, $value)->find();
	}

	/**
	 * Fetch info on all defined servicegroups
	 */
	public function get_all()
	{
		return ORM::factory('servicegroup')->find_all();
	}
}
