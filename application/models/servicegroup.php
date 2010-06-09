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
		$auth = new Nagios_auth_Model();
		$auth_objects = $auth->get_authorized_servicegroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);
		$data = ORM::factory('servicegroup')
			->where($field, $value)
			->in('id', $obj_ids)
			->find();
		return $data->loaded ? $data : false;
	}

	/**
	 * Fetch info on all defined servicegroups
	 */
	public function get_all()
	{
		$auth = new Nagios_auth_Model();
		$auth_objects = $auth->get_authorized_servicegroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);

		$data = ORM::factory('servicegroup')
			->in('id', $obj_ids)
			->find_all();
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
		$auth_objects = $auth->get_authorized_servicegroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);
		$limit_str = !empty($limit) ? ' LIMIT '.$limit : '';
		$value = '%' . $value . '%';
		$sql = "SELECT * FROM servicegroup WHERE LCASE(".$field.") LIKE LCASE(".$this->db->escape($value).") ".
		"AND id IN(".implode(',', $obj_ids).") ".$limit_str;
		$obj_info = $this->db->query($sql);
		return count($obj_info) > 0 ? $obj_info : false;
	}

	/**
	*	Search through several fields for a specific value
	*/
	public function search($value=false, $limit=false)
	{
		if (empty($value)) return false;

		$auth = new Nagios_auth_Model();
		$auth_objects = $auth->get_authorized_servicegroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);

		$obj_ids = implode(',', $obj_ids);
		$limit_str = !empty($limit) ? ' LIMIT '.$limit : '';
		if (is_array($value) && !empty($value)) {
			$query = false;
			$sql = false;
			foreach ($value as $val) {
				$val = '%'.$val.'%';
				$query[] = "SELECT DISTINCT * FROM `servicegroup` WHERE ".
			"(LCASE(`servicegroup_name`) LIKE LCASE(".$this->db->escape($val).") OR ".
			"LCASE(`alias`) LIKE LCASE(".$this->db->escape($val).")) ".
			"AND `id` IN (".$obj_ids.") ";
			}
			if (!empty($query)) {
				$sql = implode(' UNION ', $query).' ORDER BY servicegroup_name '.$limit_str;
			}
		} else {
			$value = '%'.$value.'%';
			$sql = "SELECT DISTINCT * FROM `servicegroup` WHERE ".
			"(LCASE(`servicegroup_name`) LIKE LCASE(".$this->db->escape($value).") OR ".
			"LCASE(`alias`) LIKE LCASE(".$this->db->escape($value).")) ".
			"AND `id` IN (".$obj_ids.") ORDER BY servicegroup_name ".$limit_str;
		}
		$obj_info = $this->db->query($sql);
		return $obj_info;
	}

	/**
	 * find all hosts that have services that
	 * are members of a specific servicegroup and that
	 * are in the specified state. Shortcut to get_group_hoststatus('service'...)
	 */
	public function get_servicegroup_hoststatus($servicegroup=false, $hoststatus=false, $servicestatus=false)
	{
		$grouptype = 'service';
		return Group_Model::get_group_hoststatus($grouptype, $servicegroup, $hoststatus, $servicestatus);
	}

	/**
	 * Fetch services that belong to one or more specific servicegroup(s)
	 * @param $group Servicegroup name, or array of names
	 * @return Array of service_id => host_name;service_description
	 */
	public function member_names($group = false)
	{
		$objs = $this->get_services_for_group($group);
		if ($objs === false)
			return false;

		$ret = array();
		foreach ($objs as $obj) {
			$ret[$obj->id] = $obj->host_name . ';' . $obj->service_description;
		}
		return $ret;
	}

	/**
	 * Fetch services that belong to one or more specific servicegroup(s)
	 * @param $group Servicegroup name, or array of names
	 * @return database result set
	 */
	public function get_services_for_group($group=false)
	{
		if (empty($group)) {
			return false;
		}
		if (!is_array($group)) {
			$group = array($group);
		}
		$sg = array();
		foreach ($group as $g) {
			$sg[$g] = $this->db->escape($g);
		}
		$auth_services = Service_Model::authorized_services();
		$service_str = implode(', ', array_values($auth_services));
		$sql = "SELECT
			DISTINCT s.*
		FROM
			service s,
			servicegroup sg,
			service_servicegroup ssg
		WHERE
			sg.servicegroup_name IN(". join(', ', $sg) . ") AND
			ssg.servicegroup = sg.id AND
			s.id=ssg.service AND
			s.id IN(".$service_str.")
		ORDER BY
			s.host_name, s.service_description";

		if (!empty($sql)) {
			$result = $this->db->query($sql);
			return $result;
		}
		return false;
	}

}
