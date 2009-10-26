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
		$auth = new Nagios_auth_Model();
		$auth_objects = $auth->get_authorized_hostgroups();
		if (empty($value) || empty($field) || empty($auth_objects)) {
			return false;
		}
		$obj_ids = array_keys($auth_objects);
		$data = ORM::factory('hostgroup')
			->where($field, $value)
			->in('id', $obj_ids)
			->find();
		return $data->loaded ? $data : false;
	}

	/**
	 * Fetch info on all defined hostgroups
	 */
	public function get_all()
	{
		$auth = new Nagios_auth_Model();
		if ($auth->view_hosts_root) {
			$data = ORM::factory('hostgroup')->find_all();
		} else {
			$auth_objects = $auth->get_authorized_hostgroups();
			$auth_ids = array_keys($auth_objects);
			if (empty($auth_ids))
				return false;
			$data = ORM::factory('hostgroup')->in('id', $auth_ids)->find_all();
		}
		return count($data)>0 ? $data : false;
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
		$auth_objects = $auth->get_authorized_hostgroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);
		$obj_info = $this->db
			->from('hostgroup')
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
		$auth_objects = $auth->get_authorized_hostgroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);
		$value = '%'.$value.'%';
		$obj_ids = implode(',', $obj_ids);
		$sql = "SELECT DISTINCT * FROM `hostgroup` ".
			"WHERE (`hostgroup_name` LIKE ".$this->db->escape($value)." OR ".
			"`alias` LIKE ".$this->db->escape($value).") AND ".
			"`id` IN (".$obj_ids.") LIMIT ".$limit;
		$obj_info = $this->db->query($sql);
		return $obj_info;
	}

	/**
	 * find all hosts that have services that
	 * are members of a specific hostgroup and that
	 * are in the specified state. Shortcut to get_group_hoststatus('host'...)
	 */
	public function get_hostgroup_hoststatus($servicegroup=false, $hoststatus=false, $servicestatus=false)
	{
		$grouptype = 'host';
		return Group_Model::get_group_hoststatus($grouptype, $servicegroup, $hoststatus, $servicestatus);
	}

}
