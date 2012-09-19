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
		$ls = Livestatus::instance();
		$data = $ls->getServicegroups(array('filter' => array('name' => $name)));
		return count($data)>0 ? $data[0] : false;
	}

	/**
	 * Fetch info on all defined servicegroups
	 */
	public static function get_all($items_per_page = false, $offset = false)
	{
		$ls   = Livestatus::instance();
		$data = $ls->getServicegroups(array('limit' => $items_per_page, 'offset' => $offset));
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
		$ls   = Livestatus::instance();
		$data = $ls->getServicegroups(array('filter' => array($field => array('~~' => $value)), 'limit' => $limit));
		return count($data)>0 ? $data : false;
	}

	/**
	*	Search through several fields for a specific value
	*/
	public function search($value=false, $limit=false)
	{
		if (empty($value)) return false;

		$ls   = Livestatus::instance();
		$data = $ls->getServicegroups(array('filter' => array('-or' => array(array('alias' => array('~~' => $value)),
										     array('name'  => array('~~' => $value))
									)),
						    'limit'  => $limit));
		return count($data)>0 ? $data : false;
	}

	/**
	 * find all hosts that have services that
	 * are members of a specific servicegroup and that
	 * are in the specified state. Shortcut to get_group_hoststatus('service'...)
	 */
	public function get_servicegroup_hoststatus($servicegroup=false, $hoststatus=false, $servicestatus=false)
	{
throw new Exception('implement');
/* TODO: implement */
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
throw new Exception('implement');
/* TODO: implement */
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
throw new Exception('implement');
/* TODO: implement */
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
		$auth = Nagios_auth_Model::instance();
		return $auth->is_authorized_for_servicegroup($groupname);
	}
}
