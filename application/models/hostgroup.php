<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate information regarding hostgroups
 */
class Hostgroup_Model extends Ninja_Model
{
	/**
	 * Fetch hostgroup by name
	 * @param $name The name of the object
	 * @return false on errors, array on success
	 */
	public function get($name=false)
	{
		$name = trim($name);
		$auth = Nagios_auth_Model::instance();
		if (!$auth->is_authorized_for_hostgroup($name))
			return false;
		$db = Database::instance();
		$sql = "SELECT * FROM hostgroup WHERE hostgroup_name=".$db->escape($name);
		$data = $db->query($sql);
		return $data;
	}

	/**
	 * Fetch info on all defined hostgroups
	 */
	public static function get_all($items_per_page = false, $offset=false)
	{
		$limit_str = "";
		if (!empty($items_per_page)) {
			$limit_str = " LIMIT ".$items_per_page." OFFSET ".$offset;
		}
		$auth = Nagios_auth_Model::instance();
		if ($auth->view_hosts_root) {
			$sql = "SELECT * FROM hostgroup ".$limit_str;
		} else {
			$auth_objects = $auth->get_authorized_hostgroups();
			if (!is_array($auth_objects))
				return false;
			$auth_ids = array_keys($auth_objects);
			if (empty($auth_ids))
				return false;
			$sql = "SELECT * FROM hostgroup WHERE id IN (".implode(',', $auth_ids).") ".$limit_str;
		}
		$db = Database::instance();
		$data = $db->query($sql);
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
		$auth = Nagios_auth_Model::instance();
		$auth_objects = $auth->get_authorized_hostgroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);
		$limit_str = sql::limit_parse($limit);
		$value = '%' . $value . '%';
		$sql = "SELECT * FROM hostgroup WHERE LCASE(".$field.") LIKE LCASE(".$this->db->escape($value).") ".
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

		$auth = Nagios_auth_Model::instance();
		$auth_objects = $auth->get_authorized_hostgroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);

		$obj_ids = implode(',', $obj_ids);
		$limit_str = sql::limit_parse($limit);
		if (is_array($value) && !empty($value)) {
			$query = false;
			$sql = false;
			foreach ($value as $val) {
				$val = '%'.$val.'%';
				$query[] = "SELECT DISTINCT id FROM hostgroup ".
				"WHERE (LCASE(hostgroup_name) LIKE LCASE(".$this->db->escape($val).") OR ".
				"LCASE(alias) LIKE LCASE(".$this->db->escape($val).")) AND ".
				"id IN (".$obj_ids.")  ";
			}
			if (!empty($query)) {
				$sql = 'SELECT * FROM hostgroup WHERE id IN ('.implode(' UNION ', $query).') ORDER BY hostgroup_name '.$limit_str;
			}
		} else {
			$value = '%'.$value.'%';
			$sql = "SELECT DISTINCT * FROM hostgroup ".
				"WHERE (LCASE(hostgroup_name) LIKE LCASE(".$this->db->escape($value).") OR ".
				"LCASE(alias) LIKE LCASE(".$this->db->escape($value).")) AND ".
				"id IN (".$obj_ids.") ORDER BY hostgroup_name ".$limit_str;
		}
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

	/**
	 * Fetch hosts that belong to a specific hostgroup
	 * @param $group The host group name(s)
	 * @return Array of host_id => host_name
	 */
	public function member_names($group = false)
	{
		$objs = $this->get_hosts_for_group($group);
		if ($objs === false) {
			return false;
		}
		$ret = array();
		foreach ($objs as $obj) {
			$ret[$obj->id] = $obj->host_name;
		}
		return $ret;
	}

	/**
	 * Fetch all information on all hosts that belongs to one or more specific hostgroup(s)
	 * @param $group Hostgroup name, or array of names
	 * @return database result set
	 */
	public function get_hosts_for_group($group=false)
	{
		if (empty($group)) {
			return false;
		}
		if (!is_array($group)) {
			$group = array($group);
		}
		$hg = array();
		foreach ($group as $g) {
			$hg[$g] = $this->db->escape($g);
		}
		$auth = Nagios_auth_Model::instance();
		$contact = $auth->id;

		$ca_access = '';
		if (!$auth->view_hosts_root) {
			$ca_access = "INNER JOIN contact_access ca ON h.id = ca.host AND ca.contact=$contact";
		}
		$sql = "SELECT h.* FROM host h
			INNER JOIN host_hostgroup hhg ON h.id = hhg.host
			INNER JOIN hostgroup hg ON hhg.hostgroup = hg.id
			$ca_access
			WHERE hg.hostgroup_name IN (".join(',',$hg).")
			ORDER BY h.host_name";
		$result = $this->db->query($sql);
		return $result;
	}

	/**
	*	Verify that user has access to a specific group
	*	by comparing nr of authorized hosts with nr of
	* 	hosts in a group.
	*
	* 	This will return true or false depending on if the
	* 	numbers are equal or not.
	*
	* 	@return bool true/false
	*/
	public function check_group_access($groupname=false)
	{
		$auth = Nagios_auth_Model::instance();
		return $auth->is_authorized_for_hostgroup($groupname);
	}

	/**
	 * Fetch all host data over livestatus.
	 * This includes some information about the host's services
	 */
	public static function get_group_hosts($group_name) {
/* TODO: check if this is really needed or can be included in default query */
		$ls = Livestatus::instance();
		return $ls->getHostsByGroup(array('filter' => array('hostgroup_name' => array('>=' => $group_name))));
	}
}
