<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate information regarding servicegroups
 */
class Servicegroup_Model extends Ninja_Model
{
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
		$db = Database::instance();
		$sql = "SELECT * FROM servicegroup WHERE $field=".$db->escape($value).' AND '.
			'id IN('.implode(',', $obj_ids).')';
		$data = $db->query($sql);
		return count($data)>0 ? $data : false;
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

		$auth = new Nagios_auth_Model();
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
		$auth = new Nagios_auth_Model();
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

		$auth = new Nagios_auth_Model();
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
		$auth = new Nagios_auth_Model();
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
		$auth = new Nagios_auth_Model();
		$auth_objects = $auth->get_authorized_servicegroups();
		$auth_services = $auth->get_authorized_services();
		if (!is_array($auth_objects))
			return false;
		$auth_service_ids = array_keys($auth_services);
		$auth_ids = array_keys($auth_objects);
		if (empty($auth_ids) || empty($groups))
			return false;

		$groups_to_find = false;
		if (is_numeric($groups)) {
			if (in_array($groups, $auth_ids)) {
				$groups_to_find = array((int)$groups);
			}
		} elseif (is_string($groups) && $groups !== 'all') {
			# we have a named group
			if (array_key_exists($groups, $auth->servicegroups_r)) {
				# groups_to_find should always be an array
				$groups_to_find = array($auth->servicegroups_r[$groups]);
			}
		} else {
			if ($groups === 'all') {
				$groups_to_find = $auth_ids;
			}
		}
		if (empty($groups_to_find) || empty($auth_service_ids)) {
			# no access
			return false;
		}

		$limit_str = "";
		if (!empty($items_per_page)) {
			$limit_str = " LIMIT $items_per_page OFFSET $offset";
		}

		$service_match = $auth->view_hosts_root || $auth->view_services_root ? '' : " AND service.id IN(".implode(',', $auth_service_ids).") ";

		$host_match = false;
		if (!empty($hostprops)) {
			$host_match .= Host_Model::build_host_props_query($hostprops, 'host.');
		}

		if (!empty($serviceprops)) {
			$service_match .= Host_Model::build_service_props_query($serviceprops, 'service.', 'host.');
		}

		$filter_host_sql = false;
		$filter_service_sql = false;
		if (!empty($hoststatustypes)) {
			$bits = db::bitmask_to_string($hoststatustypes);
			$filter_host_sql = " AND host.current_state IN ($bits) ";
		}
		if (!empty($servicestatustypes)) {
			$bits = db::bitmask_to_string($servicestatustypes);
			$filter_service_sql = " AND service.current_state IN ($bits) ";
		}

		if (config::get('checks.show_passive_as_active', '*')) {
			$host_check_enabled = ' AND (host.active_checks_enabled=1 OR host.passive_checks_enabled=1) ';
			$host_check_disabled = ' AND (host.active_checks_enabled=0 AND host.passive_checks_enabled=0) ';
			$service_check_enabled = ' AND (service.active_checks_enabled=1 OR service.passive_checks_enabled=1) ';
			$service_check_disabled = ' AND (service.active_checks_enabled=0 AND service.passive_checks_enabled=0) ';
		} else {
			$host_check_enabled = ' AND host.active_checks_enabled=1 ';
			$host_check_disabled = ' AND host.active_checks_enabled=0 ';
			$service_check_enabled = ' AND service.active_checks_enabled=1 ';
			$service_check_disabled = ' AND service.active_checks_enabled=0 ';
		}

		$base_query = "SELECT COUNT(DISTINCT host.id) ".
			    "FROM service_servicegroup ".
			    "INNER JOIN service ON service.id = service_servicegroup.service ".
			    "INNER JOIN host ON host.host_name = service.host_name ".
			    "WHERE servicegroup.id = service_servicegroup.servicegroup ".$service_match.$host_match.$filter_host_sql;
		$base_svc_query = "SELECT COUNT(*) from service_servicegroup ".
			    "INNER JOIN service ON service.id = service_servicegroup.service ".
			    "INNER JOIN host ON host.host_name = service.host_name ".
			    "WHERE service_servicegroup.servicegroup = servicegroup.id ".$service_match.$filter_service_sql;

		$sql = "SELECT servicegroup.id,servicegroup_name AS groupname, servicegroup.alias,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_UP.
			") AS hosts_up,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_DOWN.
			") AS hosts_down,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_DOWN.
			    " AND host.problem_has_been_acknowledged = 0 ".
			    "AND host.scheduled_downtime_depth=0 ".
			    $host_check_enabled.
			") AS hosts_down_unhandled,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_DOWN.
			    " AND host.scheduled_downtime_depth=1".
			") AS hosts_down_scheduled,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_DOWN.
			    " AND host.problem_has_been_acknowledged=1".
			") AS hosts_down_acknowledged,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_DOWN.
			    $host_check_disabled.
			") AS hosts_down_disabled,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_UNREACHABLE.
			") AS hosts_unreachable,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_UNREACHABLE.
			    " AND host.problem_has_been_acknowledged = 0 ".
			    "AND host.scheduled_downtime_depth=0 ".
			    $host_check_enabled.
			") AS hosts_unreachable_unhandled,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_UNREACHABLE.
			    " AND host.scheduled_downtime_depth=1".
			") AS hosts_unreachable_scheduled,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_UNREACHABLE.
			    " AND host.problem_has_been_acknowledged = 1".
			") AS hosts_unreachable_acknowledged,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_UNREACHABLE.
			    $host_check_disabled.
			") AS hosts_unreachable_disabled,".
			"(".$base_query.
			    "AND host.current_state = ".Current_status_Model::HOST_PENDING.
			") AS hosts_pending,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_OK.
			") AS services_ok,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
			") AS services_warning,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
			    " AND (host.current_state!=".Current_status_Model::HOST_DOWN." AND host.current_state!=".Current_status_Model::HOST_UNREACHABLE.") ".
			    "AND (service.scheduled_downtime_depth + host.scheduled_downtime_depth)=0 ".
			    "AND service.problem_has_been_acknowledged=0 ".
			    $service_check_enabled.
			") AS services_warning_unhandled,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
			    " AND (host.current_state=".Current_status_Model::HOST_DOWN." OR host.current_state=".Current_status_Model::HOST_UNREACHABLE.") ".
			") AS services_warning_host_problem,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
			    " AND (service.scheduled_downtime_depth + host.scheduled_downtime_depth)>0".
			") AS services_warning_scheduled,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
			    " AND service.problem_has_been_acknowledged=1".
			") AS services_warning_acknowledged,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
			    $service_check_disabled.
			") AS services_warning_disabled,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
			") AS services_unknown,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
			    " AND (host.current_state!=".Current_status_Model::HOST_DOWN." AND host.current_state!=".Current_status_Model::HOST_UNREACHABLE.") ".
			    "AND (service.scheduled_downtime_depth + host.scheduled_downtime_depth)=0 ".
			    "AND service.problem_has_been_acknowledged=0 ".
			    $service_check_enabled.
			") AS services_unknown_unhandled,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
			    " AND (host.current_state=".Current_status_Model::HOST_DOWN ." OR host.current_state=".Current_status_Model::HOST_UNREACHABLE.")".
			") AS services_unknown_host_problem,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
			    " AND (service.scheduled_downtime_depth + host.scheduled_downtime_depth)>0".
			") AS services_unknown_scheduled,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
			    " AND service.problem_has_been_acknowledged=1".
			") AS services_unknown_acknowledged,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
			    $service_check_disabled.
			") AS services_unknown_disabled,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
			") AS services_critical,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
			    " AND (host.current_state!=".Current_status_Model::HOST_DOWN." AND host.current_state!=".Current_status_Model::HOST_UNREACHABLE.") ".
			    " AND (service.scheduled_downtime_depth + host.scheduled_downtime_depth)=0 ".
			    "AND service.problem_has_been_acknowledged=0 ".
			    $service_check_enabled.
			") AS services_critical_unhandled,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
			    " AND (host.current_state=".Current_status_Model::HOST_DOWN." OR host.current_state=".Current_status_Model::HOST_UNREACHABLE.") ".
			") AS services_critical_host_problem,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
			    " AND (service.scheduled_downtime_depth + host.scheduled_downtime_depth)>0".
			") AS services_critical_scheduled,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
			    " AND service.problem_has_been_acknowledged=1".
			") AS services_critical_acknowledged,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
			    $service_check_disabled.
			") AS services_critical_disabled,".
			"(".$base_svc_query.
			    "AND service.current_state = ".Current_status_Model::SERVICE_PENDING.
			") AS services_pending ".
			"FROM servicegroup ".
			"WHERE servicegroup.id IN(".implode(',', $groups_to_find).") ".$limit_str;
		#echo $sql."<br />";
		$db = Database::instance();
		$obj_info = $db->query($sql);
		return count($obj_info) > 0 ? $obj_info : false;
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
			$auth = new Nagios_auth_Model();
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
		$auth = new Nagios_auth_Model();

		if ($auth->view_hosts_root || $auth->view_services_root) {
			return true;
		}

		if (empty($groupname)) {
			return false;
		}

		$db = Database::instance();
		$cnt_services = 0;
		$cnt_services_in_group = 0;

		$sql = "SELECT COUNT(ssg.service) AS cnt FROM servicegroup sg, service_servicegroup ssg ".
			"WHERE sg.servicegroup_name=".$db->escape($groupname)." AND ssg.servicegroup=sg.id;";

		$res = $db->query($sql);
		if ($res && count($res)) {
			$row = $res->current();
			$cnt_services = $row->cnt;
		} else {
			return false;
		}

		$sql = "SELECT  COUNT(ssg.service) AS cnt ".
			"FROM servicegroup sg, service_servicegroup ssg, ".
			"contact_access ca, contact c ".
			"WHERE c.contact_name=".$db->escape(Auth::instance()->get_user()->username)." ".
			"AND ca.contact=c.id AND ca.service=ssg.service ".
			"AND sg.servicegroup_name=".$db->escape($groupname)." ".
			"AND ssg.servicegroup=sg.id";

		$res = $db->query($sql);
		if ($res && count($res)) {
			$row = $res->current();
			$cnt_services_in_group = $row->cnt;
		} else {
			return false;
		}

		if ($cnt_services!=0 && $cnt_services_in_group!=0 && $cnt_services == $cnt_services_in_group) {
			return true;
		}

		return false;
	}
}
