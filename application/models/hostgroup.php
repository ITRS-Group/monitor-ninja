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
		$db = new Database();
		$sql = "SELECT * FROM hostgroup WHERE $field=".$db->escape($value).' AND '.
			'id IN('.implode(',', $obj_ids).')';
		$data = $db->query($sql);
		return count($data)>0 ? $data : false;
	}

	/**
	 * Fetch info on all defined hostgroups
	 */
	public function get_all($items_per_page = false, $offset=false)
	{
		$limit_str = "";
		if (!empty($items_per_page)) {
			$limit_str = " LIMIT ".$offset." OFFSET ".$items_per_page;
		}
		$auth = new Nagios_auth_Model();
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
		$db = new Database();
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

		$auth = new Nagios_auth_Model();
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
				$query[] = "SELECT DISTINCT * FROM `hostgroup` ".
				"WHERE (LCASE(`hostgroup_name`) LIKE LCASE(".$this->db->escape($val).") OR ".
				"LCASE(`alias`) LIKE LCASE(".$this->db->escape($val).")) AND ".
				"`id` IN (".$obj_ids.")  ";
			}
			if (!empty($query)) {
				$sql = implode(' UNION ', $query).' ORDER BY hostgroup_name '.$limit_str;
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
	 * Fetch hosts that belongs to a specific hostgroup
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
		$auth = new Nagios_auth_Model();
		$contact = $auth->id;

		$ca_access = '';
		if (!$auth->view_hosts_root) {
			$ca_access = "AND h.id IN(SELECT host from contact_access where contact=".(int)$contact." and service is null)";
		}
		$sql = "SELECT
			DISTINCT h.*
		FROM
			host h,
			hostgroup hg,
			host_hostgroup hhg
		WHERE
			hg.hostgroup_name IN (" . join(', ', $hg) . ") AND
			hhg.hostgroup = hg.id AND
			h.id=hhg.host ".$ca_access."
		ORDER BY
			h.host_name";
		if (!empty($sql)) {
			$result = $this->db->query($sql);
			return $result;
		}
		return false;
	}

	/**
	 * Create a query to find all the host and service
	 * state breakdown in one single query.
	 *
	 * @param $groups A named group, a group ID or 'all'.
	 * @param $items_per_page Items per page
	 * @param $offset Item to start with
	 */
	public function summary($groups='all', $items_per_page=false, $offset=false, $hostprops=false, $serviceprops=false, $hoststatustypes=false, $servicestatustypes=false)
	{
		$auth = new Nagios_auth_Model();
		$auth_objects = $auth->get_authorized_hostgroups();
		$auth_hosts = $auth->get_authorized_hosts();
		if (!is_array($auth_objects))
			return false;
		$auth_host_ids = array_keys($auth_hosts);
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
			if (array_key_exists($groups, $auth->hostgroups_r)) {
				# groups_to_find should always be an array
				$groups_to_find = array($auth->hostgroups_r[$groups]);
			}
		} else {
			if ($groups === 'all') {
				$groups_to_find = $auth_ids;
			}
		}
		if (empty($groups_to_find)) {
			# no access
			return false;
		}

		$limit_str = "";
		if (!empty($items_per_page)) {
			$limit_str = " LIMIT ".$offset." OFFSET ".$items_per_page;
		}

		$host_match = $auth->view_hosts_root ? '' : " AND host.id IN(".implode(',', $auth_host_ids).") ";

		if (!empty($hostprops)) {
			$host_match .= Host_Model::build_host_props_query($hostprops, 'host.');
		}

		$service_match = false;
		if (!empty($serviceprops)) {
			$service_match .= Host_Model::build_service_props_query($serviceprops, 'service.');
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


		$base_query = "SELECT COUNT(*) from host_hostgroup ".
				    "INNER JOIN host ON host.id = host_hostgroup.host ".
				    "WHERE host_hostgroup.hostgroup = hostgroup.id ".$host_match.$filter_host_sql;
		$base_svc_query = "SELECT COUNT(*) FROM host_hostgroup ".
				    "INNER JOIN host ON host.id = host_hostgroup.host ".
				    "INNER JOIN service ON service.host_name = host.host_name ".
				    "WHERE host_hostgroup.hostgroup = hostgroup.id ".$host_match.$service_match.$filter_service_sql;
		$sql = "SELECT id,hostgroup_name AS groupname,alias,".
				"(".$base_query.
				    "AND current_state = ".Current_status_Model::HOST_UP.
				") AS hosts_up,".
				"(".$base_query.
				    "AND current_state = ".Current_status_Model::HOST_DOWN.
				") AS hosts_down,".
				"(".$base_query.
					"AND current_state = ".Current_status_Model::HOST_PENDING.
				") AS hosts_pending,".
				"(".$base_query.
				   	"AND current_state = ".Current_status_Model::HOST_DOWN.
				   	" AND problem_has_been_acknowledged = 0 ".
				   	"AND scheduled_downtime_depth=0 ".
				    "AND active_checks_enabled=1 ".
				") AS hosts_down_unhandled,".
				"(".$base_query.
				   	"AND current_state = ".Current_status_Model::HOST_DOWN.
				   	" AND scheduled_downtime_depth=1 ".
				") AS hosts_down_scheduled,".
				"(".$base_query.
				    "AND current_state = ".Current_status_Model::HOST_DOWN.
				    " AND problem_has_been_acknowledged = 1 ".
				") AS hosts_down_acknowledged,".
				"(".$base_query.
					"AND current_state = ".Current_status_Model::HOST_DOWN.
					" AND active_checks_enabled=0 ".
				") AS hosts_down_disabled,".
				"(".$base_query.
				    "AND current_state = ".Current_status_Model::HOST_UNREACHABLE.
				") AS hosts_unreachable,".
				"(".$base_query.
				   	"AND current_state = ".Current_status_Model::HOST_UNREACHABLE.
				   	" AND problem_has_been_acknowledged = 0 ".
				   	"AND scheduled_downtime_depth=0 ".
				    "AND active_checks_enabled=1 ".
				") AS hosts_unreachable_unhandled,".
				"(".$base_query.
				   	"AND current_state = ".Current_status_Model::HOST_UNREACHABLE.
				   	" AND scheduled_downtime_depth=1 ".
				") AS hosts_unreachable_scheduled,".
				"(".$base_query.
				    "AND current_state = ".Current_status_Model::HOST_UNREACHABLE.
				    " AND problem_has_been_acknowledged = 1 ".
				") AS hosts_unreachable_acknowledged,".
				"(".$base_query.
					"AND current_state = ".Current_status_Model::HOST_UNREACHABLE.
					" AND active_checks_enabled=0 ".
				") AS hosts_unreachable_disabled,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_OK.
				    " GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_ok,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
				    " GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_warning,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
				    " AND (host.current_state!=".Current_status_Model::HOST_DOWN." AND host.current_state!=".Current_status_Model::HOST_UNREACHABLE.") ".
				    "AND service.scheduled_downtime_depth=0 ".
				    "AND service.problem_has_been_acknowledged=0 ".
				    "AND service.active_checks_enabled=1 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup".
				") AS services_warning_unhandled,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
				    " AND (host.current_state=".Current_status_Model::HOST_DOWN." OR host.current_state=".Current_status_Model::HOST_UNREACHABLE.") ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_warning_host_problem,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
				    " AND service.scheduled_downtime_depth>0 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_warning_scheduled,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
				    " AND service.problem_has_been_acknowledged=1 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_warning_acknowledged,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_WARNING.
				    " AND service.active_checks_enabled=0 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_warning_disabled,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
				    " GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_unknown,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
				    " AND (host.current_state!=".Current_status_Model::HOST_DOWN." AND host.current_state!=".Current_status_Model::HOST_UNREACHABLE.") ".
				    "AND service.scheduled_downtime_depth=0 ".
				    "AND service.problem_has_been_acknowledged=0 ".
				    "AND service.active_checks_enabled=1 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_unknown_unhandled,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
				    " AND (host.current_state=".Current_status_Model::HOST_DOWN." OR host.current_state=".Current_status_Model::HOST_UNREACHABLE.") ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_unknown_host_problem, ".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
				    " AND service.scheduled_downtime_depth>0 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_unknown_scheduled,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
				    " AND service.problem_has_been_acknowledged=1 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_unknown_acknowledged,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_UNKNOWN.
				    " AND service.active_checks_enabled=0 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_unknown_disabled,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
				    " GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_critical,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_PENDING.
				    " GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_pending,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
				    " AND (host.current_state!=".Current_status_Model::HOST_DOWN." AND host.current_state!=".Current_status_Model::HOST_UNREACHABLE.") ".
				    "AND service.scheduled_downtime_depth=0 ".
				    "AND service.problem_has_been_acknowledged=0 ".
				    "AND service.active_checks_enabled=1 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_critical_unhandled, ".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
				    " AND (host.current_state=".Current_status_Model::HOST_DOWN." OR host.current_state=".Current_status_Model::HOST_UNREACHABLE.") ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_critical_host_problem,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
				    " AND service.scheduled_downtime_depth>0 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_critical_scheduled,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
				    " AND service.problem_has_been_acknowledged=1 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_critical_acknowledged,".
				"(".$base_svc_query.
				    "AND service.current_state = ".Current_status_Model::SERVICE_CRITICAL.
				    " AND service.active_checks_enabled=0 ".
				    "GROUP BY service.current_state,host_hostgroup.hostgroup ".
				") AS services_critical_disabled ".
				"FROM hostgroup ";
		if ($groups === 'all' && !$auth->view_hosts_root) {
			$sql .= " WHERE hostgroup.id IN(".implode(',', $groups_to_find).") ";
		} elseif ($groups != 'all') {
			$sql .= " WHERE hostgroup.id IN(".implode(',', $groups_to_find).") ";
		}
		$sql .= $limit_str;
		#echo $sql."<br />";
		$db = new Database();
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
			$auth_obj = $auth->get_authorized_hostgroups();
		} else {
			$auth_obj = $this->auth->get_authorized_hostgroups();
		}
		$obj_ids = array_keys($auth_obj);
		$limit_str = sql::limit_parse($limit);
		if (!isset($this->db) || !is_object($this->db)) {
			$db = new Database();
		} else {
			$db = $this->db;
		}

		$sql = "SELECT * FROM hostgroup WHERE ".$field." REGEXP ".$db->escape($regexp)." ".
		 "AND id IN(".implode(',', $obj_ids).") ".$limit_str;
		$obj_info = $db->query($sql);
		return count($obj_info)>0 ? $obj_info : false;
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
		$auth = new Nagios_auth_Model();
		if ($auth->view_hosts_root) {
			return true;
		}

		if (empty($groupname)) {
			return false;
		}

		$db = new Database();
		$cnt_hosts = 0;
		$cnt_hosts_in_group = 0;

		$sql = "SELECT COUNT(hhg.host) AS cnt FROM hostgroup hg, host_hostgroup hhg ".
			"WHERE hg.hostgroup_name=".$db->escape($groupname)." AND hhg.hostgroup=hg.id";

		$res = $db->query($sql);
		if ($res && count($res)) {
			$row = $res->current();
			$cnt_hosts = $row->cnt;
		} else {
			return false;
		}

		$sql = "SELECT COUNT(hhg.host) AS cnt ".
			"FROM hostgroup hg, ".
				"host_hostgroup hhg, ".
				"contact_access ca, ".
				"contact c ".
			"WHERE c.contact_name=".$db->escape(Auth::instance()->get_user()->username)." ".
				"AND ca.contact=c.id ".
				"AND ca.host = hhg.host ".
				"AND ca.service IS null ".
				"AND hg.hostgroup_name=".$db->escape($groupname)." ".
				"AND hhg.hostgroup=hg.id";

		$res = $db->query($sql);
		if ($res && count($res)) {
			$row = $res->current();
			$cnt_hosts_in_group = $row->cnt;
		} else {
			return false;
		}

		if ($cnt_hosts!=0 && $cnt_hosts_in_group!=0 && $cnt_hosts == $cnt_hosts_in_group) {
			return true;
		}
		return false;
	}
}
