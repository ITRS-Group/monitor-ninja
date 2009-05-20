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
		$data = ORM::factory('servicegroup')->where($field, $value)->find();
		return $data->loaded ? $data : false;
	}

	/**
	 * Fetch info on all defined servicegroups
	 */
	public function get_all()
	{
		return ORM::factory('servicegroup')->find_all();
	}

	/**
	*	Fetch service info filtered on specific field and value
	*/
	public function get_where($field=false, $value=false, $limit=false)
	{
		if (empty($field) || empty($value)) {
			return false;
		}
		$auth = new Nagios_auth_Model();
		$auth_objects = $auth->get_authorized_servicegroups();
		$obj_ids = array_keys($auth_objects);
		$obj_info = $this->db
			->from('servicegroup')
			->like($field, $value)
			->in('id', $obj_ids)
			->limit($limit)
			->get();
		return $obj_info;
	}

	/**
	*	Search through several fields for a specific value
	*/
	public function search($value=false, $limit=false)
	{
		if (empty($value)) return false;

		$auth = new Nagios_auth_Model();
		$auth_objects = $auth->get_authorized_servicegroups();
		$obj_ids = array_keys($auth_objects);
		$obj_info = $this->db
			->select('DISTINCT *')
			->from('servicegroup')
			->orlike(
				array(
					'servicegroup_name' => $value,
					'alias' => $value
				)
			)
			->in('id', $obj_ids)
			->limit($limit)
			->get();
		return $obj_info;
	}
}
