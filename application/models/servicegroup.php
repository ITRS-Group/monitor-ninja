<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate information regarding servicegroups
 */
class Servicegroup_Model extends Ninja_Model
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
		if (!$auth->is_authorized_for_servicegroup($name))
			return false;
		$db = Database::instance();
		$sql = "SELECT * FROM servicegroup WHERE servicegroup_name=".$db->escape($name);
		$data = $db->query($sql);
		return $data;
	}

	/**
	 * Fetch info on all defined servicegroups
	 */
	public static function get_all($items_per_page = false, $offset = false)
	{
		$limit_str = "";
		if (!empty($items_per_page)) {
			$limit_str = " LIMIT $items_per_page OFFSET $offset";
		}

		$auth = Nagios_auth_Model::instance();
		$auth_objects = $auth->get_authorized_servicegroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);

		$sql = "SELECT * FROM servicegroup WHERE id IN(".implode(',', $obj_ids).") ".$limit_str;
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
		$auth_objects = $auth->get_authorized_servicegroups();
		if (empty($auth_objects))
			return false;
		$obj_ids = array_keys($auth_objects);
		$limit_str = sql::limit_parse($limit);
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

		$auth = Nagios_auth_Model::instance();
		$auth_objects = $auth->get_authorized_servicegroups();
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
				$query[] = "SELECT DISTINCT id FROM servicegroup WHERE ".
			"(LCASE(servicegroup_name) LIKE LCASE(".$this->db->escape($val).") OR ".
			"LCASE(alias) LIKE LCASE(".$this->db->escape($val).")) ".
			"AND id IN (".$obj_ids.") ";
			}
			if (!empty($query)) {
				$sql = 'SELECT * FROM servicegroup WHERE id IN ('.implode(' UNION ', $query).') ORDER BY servicegroup_name '.$limit_str;
			}
		} else {
			$value = '%'.$value.'%';
			$sql = "SELECT DISTINCT * FROM servicegroup WHERE ".
			"(LCASE(servicegroup_name) LIKE LCASE(".$this->db->escape($value).") OR ".
			"LCASE(alias) LIKE LCASE(".$this->db->escape($value).")) ".
			"AND id IN (".$obj_ids.") ORDER BY servicegroup_name ".$limit_str;
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
	 * Fetch all information on all services that belong to one or more specific servicegroup(s)
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
		$auth = Nagios_auth_Model::instance();
		$contact = $auth->id;

		$ca_access = '';
		if (!$auth->view_hosts_root) {
			$ca_access = "INNER JOIN contact_access ca ON s.id = ca.service AND ca.contact=$contact";
		}
		$sql = "SELECT s.* FROM service s
			INNER JOIN service_servicegroup ssg ON s.id = ssg.service
			INNER JOIN servicegroup sg ON ssg.servicegroup = sg.id
			$ca_access
			WHERE sg.servicegroup_name IN (".join(',',$sg).")
			ORDER BY s.host_name, s.service_description";
		$result = $this->db->query($sql);
		return $result;
	}
	/**
	 * Create a query to find all the host and service
	 * state breakdown in one single query.
	 *
	 * @param $groups A named group, a group ID or 'all'.
	 * @param $items_per_page Items per page
	 * @param $offset Item to start with
	 * @param $hostprops A bitmask of host flags as defined in the nagstat helper
	 * @param $serviceprops A bitmask of service flags as defined in the nagstat helper
	 * @param $hoststatustypes A bitmask of interesting host states (1 << the_nagios_state)
	 * @param $servicestatustypes A bitmask of interesting service states (1 << the_nagios_state)
	 */
	public static function summary($groups='all', $items_per_page=false, $offset=false, $hostprops=false, $serviceprops=false, $hoststatustypes=false, $servicestatustypes=false)
	{
		$auth = Nagios_auth_Model::instance();
		$auth_objects = $auth->get_authorized_servicegroups();
		if (!is_array($auth_objects) || empty($auth_objects) || empty($groups))
			return false;

		$groups_to_find = false;
		if (is_numeric($groups)) {
			if (isset($auth_objects[$groups])) {
				$groups_to_find = array($auth_objects[$groups]);
			}
		} elseif (is_string($groups) && $groups !== 'all') {
			# we have a named group
			if (array_key_exists($groups, $auth->servicegroups_r)) {
				# groups_to_find should always be an array
				$groups_to_find = array($groups);
			}
		} else {
			if ($groups === 'all') {
				$groups_to_find = $auth_objects;
			}
		}
		if (empty($groups_to_find)) {
			# no access
			return false;
		}

		if (!empty($items_per_page)) {
			$groups_to_find = array_splice($groups_to_find, $offset, $items_per_page);
		}

		$host_match = array();
		$service_match = array();

		if (!empty($hostprops)) {
			$host_match .= Host_Model::build_host_livestatus_props($hostprops);
		}

		if (!empty($serviceprops)) {
			$service_match .= Host_Model::build_service_livestatus_props($serviceprops);
		}

		if (!empty($hoststatustypes)) {
			$bits = db::bitmask_to_string($hoststatustypes);
			$host_match[] = "Filter: state = ". implode("\nFilter: state = ", $bits)."\nOr: ".count($bits);
		}
		if (!empty($servicestatustypes)) {
			$bits = db::bitmask_to_string($servicestatustypes);
			$service_match[] = "Filter: state = ". implode("\nFilter: state = ", $bits)."\nOr: ".count($bits);
		}

		$res = array();
		$stats = new Stats_Model();
		// If there are fewer servicegroups to look for than we wanted, that means we want all of them
		// or:ing too much can be slow.
		if (!empty($items_per_page) && $items_per_page <= count($groups_to_find)) {
			foreach ($groups_to_find as $group) {
				$service_match[] = "Filter: servicegroup_name = $group";
			}
			$service_match[] = 'Or: '.count($groups_to_find);
		}

		$services = $stats->get_stats('servicesbygroup',
			array(
				'services_ok',
				'services_warning',
				'services_warning_unacknowledged',
				'services_warning_host_problem',
				'services_warning_scheduled',
				'services_warning_acknowledged',
				'services_warning_disabled',
				'services_unknown',
				'services_unknown_unacknowledged',
				'services_unknown_host_problem',
				'services_unknown_scheduled',
				'services_unknown_acknowledged',
				'services_unknown_disabled',
				'services_critical',
				'services_critical_unacknowledged',
				'services_critical_host_problem',
				'services_critical_scheduled',
				'services_critical_acknowledged',
				'services_critical_disabled',
				'services_pending'
			),
			$service_match,
			array('servicegroup_name', 'servicegroup_alias')
		);
		$ret = array();
		try {
			$ls = Livestatus::instance();
			foreach ($services as $service) {
				$group = $service['servicegroup_name'];
				$ret[$group] = $service;
				$host_names = $ls->query("GET servicesbygroup
Columns: host_name
Filter: servicegroup_name = $group");
				$this_match = $host_match;
				foreach ($host_names as $host) {
					$this_match[] = "Filter: host_name = {$host[0]}";
				}
				$this_match[] = 'Or: '.count($host_names);
				$host_stat = $stats->get_stats('hosts',
					array(
						'hosts_up',
						'hosts_down',
						'hosts_down_unacknowledged',
						'hosts_down_scheduled',
						'hosts_down_acknowledged',
						'hosts_down_disabled',
						'hosts_unreachable',
						'hosts_unreachable_unacknowledged',
						'hosts_unreachable_scheduled',
						'hosts_unreachable_acknowledged',
						'hosts_unreachable_disabled',
						'hosts_pending'
					),
					$this_match
				);
				$ret[$group] = array_merge($service, $host_stat[0]);
			}
		} catch (LivestatusException $ex) {
			return false;
		}

		return $ret;
	}

	/**
	*	Fetch info from regexp query
	*/
	public function regexp_where($field=false, $regexp=false, $limit=false)
	{
		if (empty($field) || empty($regexp)) {
			return false;
		}
		if (!isset($this->auth) || !is_object($this->auth)) {
			$auth = Nagios_auth_Model::instance();
			$auth_obj = $auth->get_authorized_servicegroups();
		} else {
			$auth_obj = $this->auth->get_authorized_servicegroups();
		}
		$obj_ids = array_keys($auth_obj);
		$limit_str = sql::limit_parse($limit);
		if (!isset($this->db) || !is_object($this->db)) {
			$db = Database::instance();
		} else {
			$db = $this->db;
		}

		$sql = "SELECT * FROM servicegroup WHERE ".$field." REGEXP ".$db->escape($regexp)." ".
		 "AND id IN(".implode(',', $obj_ids).") ".$limit_str;
		$obj_info = $db->query($sql);
		return count($obj_info)>0 ? $obj_info : false;
	}

/**
	*	Verify that user has access to a specific group
	*	by comparing nr of authorized services with nr of
	* 	services in a group.
	*
	* 	This will return true or false depending on if the
	* 	numbers are equal or not.
	*
	* 	@return bool true/false
	*/
	public function check_group_access($groupname=false)
	{
<<<<<<< HEAD
		$auth = Nagios_auth_Model::instance();
		return $auth->is_authorized_for_servicegroup($groupname);
	}
}
