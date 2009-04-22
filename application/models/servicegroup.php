<?php defined('SYSPATH') OR die('No direct access allowed.');

class Servicegroup_Model extends ORM {
	protected $table_names_plural = false;

	/**
	*	@name 	get_by_field_value
	*	@desc 	Fetch servicegroup where field matches value
	* 	@param	str $field
	* 	@param	mixed $value
	*
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
	*	@name	get_all
	*	@desc	Fetch info on all defined servicegroups
	*
	*/
	public function get_all()
	{
		return ORM::factory('servicegroup')->find_all();
	}
}