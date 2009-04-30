<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate information regarding hostgroups
 */
class Hostgroup_Model extends ORM
{
	protected $table_names_plural = false;

	/**
	 * Fetch hostgroup where field matches value
	 * @param $field The field to fetch
	 * @param $value The value to search for
	 * @return false on errors, array on success
	 */
	public function get_by_field_value($field=false, $value=false)
	{
		$value = trim($value);
		$field = trim($field);
		if (empty($value) || empty($field)) {
			return false;
		}
		$data = ORM::factory('hostgroup')->where($field, $value)->find();
		return $data->loaded ? $data : false;
	}

	/**
	 * Fetch info on all defined hostgroups
	 */
	public function get_all()
	{
		return ORM::factory('hostgroup')->find_all();
	}
}
