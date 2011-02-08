<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle both host- and servicegroup
 * 	A "helper model" for hostgroup and servicegroup
 */
class Group_Model extends Model
{

	/**
	 * Finds all hosts that have services that are members of a specific host- or servicegroup
	 * and that are in the specified state.
	 * Called from get_servicegroup_hoststatus() and get_hostgroup_hoststatus()
	 *
	 * @param $grouptype [host|service]
	 * @param $groupname
	 * @param $hoststatus
	 * @param $servicestatus
	 * @return db result
	 */
	public static function get_group_hoststatus($grouptype='service', $groupname=false, $hoststatus=false, $servicestatus=false)
	{
		$groupname = trim($groupname);
		if (empty($groupname)) {
			return false;
		}

		$auth = new Nagios_auth_Model();
		$hostlist = Host_Model::authorized_hosts();
		if (!$auth->view_hosts_root && !$auth->view_services_root && empty($hostlist)) {
			return false;
		}
		$filter_sql = '';
		$state_filter = false;
		if (!empty($hoststatus)) {
			$bits = db::bitmask_to_string($hoststatus);
			$filter_sql .= " AND h.current_state IN ($bits) ";
		}
		$service_filter = false;
		$servicestatus = trim($servicestatus);
		#$svc_field = '';
		#$svc_groupby = ' GROUP BY myhost';
		#$svc_where = '';
		if ($servicestatus!==false && !empty($servicestatus)) {
			$bits = db::bitmask_to_string($servicestatus);
			$filter_sql .= " AND s.current_state IN ($bits) ";
			#$svc_groupby = " GROUP BY ".$grouptype."group_name, host.host_name";
			#$svc_where = " AND service.host_name=host.host_name ";
		}

		$db = new Database();
		$all_sql = $groupname != 'all' ? "sg.".$grouptype."group_name=".$db->escape($groupname)." " : '1=1 ';

		# we need to match against different field depending on if host- or servicegroup
		$member_match = $grouptype == 'service' ? "s.id=ssg.".$grouptype : "h.id=ssg.".$grouptype;

		if (!$auth->view_hosts_root && !($auth->view_services_root && $grouptype == 'service')) {
			$hostlist_str = implode(',', $hostlist);
			$filter_sql = "AND h.id IN (".$hostlist_str.") ".$filter_sql;
		}

		$fields = 'h.host_name, h.current_state, h.address, h.action_url, h.notes_url, h.icon_image, h.icon_image_alt,';
		if ($auth->view_hosts_root) {
			$sql = "
				SELECT $fields
					s.current_state AS service_state,
					s.state_count AS state_count
				FROM
					host h,
				(SELECT MAX(current_state) AS current_state, COUNT(current_state) AS state_count, MAX(id) AS id FROM service WHERE service.host_name = h.host_name GROUP BY host_name) s
				INNER JOIN {$grouptype}_{$grouptype}group ssg ON {$member_match}
				INNER JOIN {$grouptype}group sg ON ssg.{$grouptype}group = sg.id
				WHERE
					{$all_sql} {$filter_sql}
				ORDER BY
					h.host_name";
		} elseif (!$auth->view_services_root && $grouptype == 'service') {
			$sql = "
				SELECT $fields
					s.current_state AS service_state,
					s.state_count AS state_count
				FROM
					host h,
				(SELECT MAX(current_state) AS current_state, COUNT(current_state) AS state_count, MAX(id) AS id FROM service WHERE service.host_name = h.host_name GROUP BY host_name) s
				INNER JOIN service s ON s.host_name = h.host_name
				INNER JOIN {$grouptype}_{$grouptype}group ssg ON {$member_match}
				INNER JOIN {$grouptype}group sg ON ssg.{$grouptype}group = sg.id
				WHERE
					{$all_sql}
					{$filter_sql}
				ORDER BY
					h.host_name";
		} else {
			$hostlist_str = implode(',', $hostlist);

			$sql = "
				SELECT $fields
					s.current_state AS service_state,
					s.state_count AS state_count
				FROM
					host h,
				(SELECT MAX(current_state) AS current_state, COUNT(current_state) AS state_count, MAX(id) AS id FROM service WHERE service.host_name = h.host_name GROUP BY host_name) s
				INNER JOIN {$grouptype}_{$grouptype}group ssg ON {$member_match}
				INNER JOIN {$grouptype}group sg ON sg.id = ssg.".$grouptype."group
				WHERE
					".$all_sql."
					".$filter_sql."
				ORDER BY
					h.host_name";
		}
		$result = $db->query($sql);
		#echo $sql."<hr />";
		return $result;
	}


	/**
	 * Finds all members of a host- or servicegroup, optionally filtering
	 * on status.
	 * Will return all info on the hosts but only service_description
	 * and current_state for services
	 *
	 * @param $grouptype [host|service]
	 * @param $groupname Name of the group
	 * @param $hoststatus Host status filter
	 * @param $servicestatus Service status filter
	 * @param $service_props Service properties filter
	 * @param $host_props Host properties filter
	 * @return db result
	 */
	public static function get_group_info($grouptype='service', $groupname=false, $hoststatus=false, $servicestatus=false, $service_props=false, $host_props=false, $limit=false, $sort_field=false, $sort_order='DESC')
	{
		$groupname = trim($groupname);
		if (empty($groupname)) {
			return false;
		}
		$filter_sql = '';
		$state_filter = false;
		if (!empty($hoststatus)) {
			$bits = db::bitmask_to_string($hoststatus);
			$filter_sql .= " AND h.current_state IN ($bits) ";
		}
		$service_filter = false;
		$servicestatus = trim($servicestatus);
		if ($servicestatus!==false && !empty($servicestatus)) {
			$bits = db::bitmask_to_string($servicestatus);
			$filter_sql .= " AND s.current_state IN ($bits) ";
		}

		$limit_str = !empty($limit) ? trim($limit) : '';
		$hostlist = Host_Model::authorized_hosts();

		$hostlist_str = !empty($hostlist) ? implode(',', $hostlist) : false;

		$db = new Database();
		$all_sql = $groupname != 'all' ? "AND sg.".$grouptype."group_name=".$db->escape($groupname)." " : '';

		# we need to match against different field depending on if host- or servicegroup
		$member_match = $grouptype == 'service' ? "s.id=ssg.".$grouptype : "h.id=ssg.".$grouptype;

		$sort_string = "";
		if (empty($sort_field)) {
			$sort_string = "h.host_name,s.current_state, s.service_description ".$sort_order;
		} else {
			$sort_string = $sort_field.' '.$sort_order;
		}

		$service_props_sql = Host_Model::build_service_props_query($service_props, 's.');
		$host_props_sql = Host_Model::build_host_props_query($host_props, 'h.');

		$auth = new Nagios_auth_Model();
		$auth_str = '';
		if ($auth->view_hosts_root || ($auth->view_services_root && $grouptype == 'service')) {
			$auth_str = "";
		} else {
			if (empty($hostlist_str))
				return false;
			$auth_str = " AND h.id IN (".$hostlist_str.")";
		}
		$sql = "SELECT ".
				"h.host_name,".
				"h.address,".
				"h.alias,".
				"h.current_state AS host_state,".
				"(UNIX_TIMESTAMP() - h.last_state_change) AS duration,".
				"UNIX_TIMESTAMP() AS cur_time,".
				"h.output,".
				"h.long_output,".
				"h.problem_has_been_acknowledged AS hostproblem_is_acknowledged,".
				"h.scheduled_downtime_depth AS hostscheduled_downtime_depth,".
				"h.notifications_enabled AS host_notifications_enabled,".
				"h.active_checks_enabled AS host_active_checks_enabled,".
				"h.action_url AS host_action_url,".
				"h.icon_image AS host_icon_image,".
				"h.icon_image_alt AS host_icon_image_alt,".
				"h.is_flapping AS host_is_flapping,".
				"h.notes_url AS host_notes_url,".
				"s.id AS service_id,".
				"s.current_state AS service_state,".
				"(UNIX_TIMESTAMP() - s.last_state_change) AS service_duration,".
				"UNIX_TIMESTAMP() AS service_cur_time,".
				"s.active_checks_enabled,".
				"s.current_state,".
				"s.problem_has_been_acknowledged,".
				"s.scheduled_downtime_depth,".
				"s.last_check,".
				"s.output AS service_output,".
				"s.long_output AS service_long_output,".
				"s.notes_url,".
				"s.action_url,".
				"s.current_attempt,".
				"s.max_check_attempts,".
				"s.should_be_scheduled,".
				"s.next_check,".
				"s.notifications_enabled,".
				"s.service_description ".
			"FROM host h ".
			"INNER JOIN service s ON h.host_name=s.host_name ".
			"INNER JOIN {$grouptype}_{$grouptype}group ssg ON {$member_match} ".
			"INNER JOIN {$grouptype}group sg ON sg.id = ssg.{$grouptype}group ".
			"WHERE 1 = 1 ".
				"{$all_sql} {$auth_str} {$filter_sql} {$service_props_sql} ".
				"{$host_props_sql} ".
			"ORDER BY ".$sort_string." ".$limit_str;
#echo $sql;
		$result = $db->query($sql);
		return $result;
	}

	/**
	 * Fetch host/service groups for host/service
	 * Accepts either object ID or object name.
	 * @param $type Host or service
	 * @param $id The id of the object
	 * @param $name The name of the object (host;service for services)
	 * @return Array of group objects the requested object is a member of
	 */
	public function get_groups_for_object($type='host', $id=false, $name=false)
	{
		$name = trim($name);
		$auth = new Nagios_auth_Model();
		switch (strtolower($type)) {
			case 'host':
				$host_list = $auth->get_authorized_hosts();
				$host_list_r = $auth->get_authorized_hosts_r();
				break;
			case 'service':
				$service_list = $auth->get_authorized_services();
				$service_list_r = $auth->get_authorized_services_r();
				break;
			default:
				return false;
		}

		$db = new Database();
		$all_sql = $name != 'all' ? "sg.".$type."group_name=".$db->escape($name)." AND" : '';

		# we need to match against different field depending on if host- or servicegroup
		$member_match = $type == 'service' ? "s.id=ssg.".$type : "h.id=ssg.".$type;

		if ($id === false && !empty($name) && array_key_exists($name, ${$type.'_list_r'})) {
			$id = ${$type.'_list_r'}[$name];
		}

		# check for authentication
		if ($id !== false) {
			# we have an ID
			# check that user is allowed to see this
			if (!array_key_exists($id, ${$type.'_list'})) {
				return false;
			}
			$sql = "
				SELECT
					gr.*
				FROM
					".$type."_".$type."group g,
					".$type."group gr
				WHERE
					g.".$type."=".$id." AND
					gr.id=g.".$type."group";
		} else {
			# abort if both id and name are empty
			return false;
		}

		$db = new Database();
		$result = $db->query($sql);
		return $result;
	}

	/**
	* Fetch state breakdown for host- or servicegroup
	* Using contact_access to solve access rights
	* @param $grouptype str host|service
	* @param $what str host|service
	* @param $name str 'all'|group name
	*/
	public function state_breakdown($grouptype='host', $what='host', $name=false)
	{
		$name = trim($name);
		$auth = new Nagios_auth_Model();

		$name = empty($name) ? 'all' : $name;
		$db = new Database();
		$contact_id = $auth->get_contact_id();
		$all_sql = $name != 'all' ? "sg.".$grouptype."group_name=".$db->escape($name)." AND " : '';

		$auth_control = '';
		$ca_table = '';

		if ($grouptype == 'host') {
			if (!$auth->view_hosts_root) {
				$auth_control = "AND ".
				"ca.contact=".(int)$contact_id." AND ".
				"h.id=ca.host ";
				$ca_table = ",contact_access ca ";
			}

			switch ($what) {
				case 'host':
					$sql = "SELECT
						COUNT(DISTINCT h.id) AS cnt,h.current_state ".
						"FROM ".
						"host h, ".
						$grouptype."group sg, ".
						$grouptype."_".$grouptype."group ssg ".$ca_table.
						"WHERE ".$all_sql.
						"ssg.".$grouptype."group = sg.id AND ".
						"h.id=ssg.host ".$auth_control.
						"GROUP BY ".
						"h.current_state ".
						"ORDER BY ".
						"h.current_state";
					break;
				case 'service':
					$sql = "SELECT
						COUNT(DISTINCT s.id) AS cnt, s.current_state ".
						"FROM ".
						"service s, ".
						"host h, ".
						$grouptype."group sg, ".
						$grouptype."_".$grouptype."group ssg ".$ca_table.
						"WHERE ".$all_sql.
						"ssg.".$grouptype."group = sg.id AND ".
						"h.id=ssg.host AND ".
						"h.host_name=s.host_name ".$auth_control.
						"GROUP BY ".
						"s.current_state ".
						"ORDER BY ".
						"s.current_state";
					break;
			}
		} elseif ($grouptype == 'service') {
			if (!$auth->view_hosts_root && !$auth->view_services_root) {
				$auth_control = " AND ca.contact=".(int)$contact_id." AND ".
				"s.id=ca.service ";
				$ca_table = ",contact_access ca ";
			}

			switch ($what) {
				case 'host':
					$sql = "SELECT
						COUNT(DISTINCT h.id) AS cnt,h.current_state ".
						"FROM ".
						"host h, ".
						"service s, ".
						$grouptype."group sg, ".
						$grouptype."_".$grouptype."group ssg ".$ca_table.
						"WHERE ".$all_sql.
						"ssg.".$grouptype."group = sg.id AND ".
						"s.id=ssg.service AND ".
						"h.host_name=s.host_name ".$auth_control.
						"GROUP BY ".
						"h.current_state ".
						"ORDER BY ".
						"h.current_state";
					break;
				case 'service':
					$sql = "SELECT
						COUNT(DISTINCT s.id) AS cnt, s.current_state ".
						"FROM ".
						"service s, ".
						"host h, ".
						$grouptype."group sg, ".
						$grouptype."_".$grouptype."group ssg ".$ca_table.
						"WHERE ".$all_sql.
						"ssg.".$grouptype."group = sg.id AND ".
						"s.id=ssg.service AND ".
						"h.host_name=s.host_name ".$auth_control.
						" GROUP BY ".
						"s.current_state ".
						"ORDER BY ".
						"s.current_state";
					break;
			}
		}
		#echo $sql."<br />";
		$result = $db->query($sql);
		return count($result)>0 ? $result : false;
	}

	/**
	*	Fetch group overview data
	* 	Expects group type (host/service) and group name
	*/
	public function group_overview($type='service', $group=false, $hostprops=false, $serviceprops=false, $hoststatustypes=false, $servicestatustypes=false)
	{
		$auth = new Nagios_auth_Model();
		$auth_objects = array();
		if ($type == 'service') {
			$auth_objects = $auth->get_authorized_servicegroups();
		} elseif ($type == 'host') {
			$auth_objects = $auth->get_authorized_hostgroups();
		}

		$contact = $auth->id;
		$auth_ids = array_keys($auth_objects);
		if (empty($auth_ids) || empty($group)) {
			return false;
		}

		$db = new Database();

		if (empty($group)) {
			return false;
		}

		# make sure we don't show info on services that
		# an authorized contact shouldn't see
		$service_match = $auth->view_hosts_root || $auth->view_services_root ? ''
			: "  AND service.id IN (SELECT service FROM contact_access WHERE contact=".(int)$contact." AND service IS NOT NULL) ";

		$host_match = false;
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
			$filter_host_sql = " AND h.current_state IN ($bits) ";
		}
		if (!empty($servicestatustypes)) {
			$bits = db::bitmask_to_string($servicestatustypes);
			$filter_service_sql = " AND service.current_state IN ($bits) ";
		}

		switch ($type) {
			case 'host':
				# restrict host access for authorized contacts
				if (!$auth->view_hosts_root) {
					$hostgroups = $auth->hostgroups_r;
					if (!is_array($hostgroups) || !array_key_exists($group, $hostgroups)) {
						# user doesn't have access
						return false;
					}
				}
				$host_match = $auth->view_hosts_root ? ''
					: "  AND h.id IN (SELECT host FROM contact_access WHERE contact=".(int)$contact." AND service IS NULL) ";

				$svc_query = "SELECT COUNT(*) FROM service WHERE service.host_name = h.host_name ".
					"AND current_state = %s ".$service_match.$filter_service_sql;

				$sql = "SELECT h.host_name, h.current_state, h.address, h.action_url, h.notes_url, h.icon_image,h.icon_image_alt,".
					"h.display_name, h.current_attempt, h.max_check_attempts, (".
					sprintf($svc_query, Current_status_Model::SERVICE_OK).") AS services_ok,(".
					sprintf($svc_query, Current_status_Model::SERVICE_WARNING).") AS services_warning,(".
					sprintf($svc_query, Current_status_Model::SERVICE_CRITICAL).") AS services_critical,(".
					sprintf($svc_query, Current_status_Model::SERVICE_UNKNOWN).") AS services_unknown,(".
					sprintf($svc_query, Current_status_Model::SERVICE_PENDING).") AS services_pending ".
					"FROM hostgroup hg, host h, host_hostgroup hhg ".
					"WHERE hhg.hostgroup=hg.id AND h.id=hhg.host ".
					"AND hg.hostgroup_name=".$db->escape($group).$filter_host_sql.Host_Model::build_host_props_query($hostprops, 'h.');
				break;
			case 'service':
				if (!$auth->view_hosts_root && !$auth->view_services_root) {
					$servicegroups = $auth->servicegroups_r;
					if (!is_array($servicegroups) || !array_key_exists($group, $servicegroups)) {
						# user doesn't have access
						return false;
					}
				}

				$host_join = '';
				$host_where = '';
				if (!$auth->view_hosts_root) {
					$host_join = ' INNER JOIN contact_access ON contact_access.host = host.id ';
					$host_where = ' AND contact_access.contact='.$contact.' AND contact_access.service IS NULL ';
				}
				$host_match = $auth->view_hosts_root ? ''
					: "  AND host.id IN (SELECT host FROM contact_access WHERE contact=".(int)$contact." AND service IS NULL) ";

				if (!empty($hostprops)) {
					$host_match .= Host_Model::build_host_props_query($hostprops, 'host.');
				}

				if (!empty($hoststatustypes)) {
					$bits = db::bitmask_to_string($hoststatustypes);
					$filter_host_sql = " AND host.current_state IN ($bits) ";
				}

				$svc_query = "SELECT COUNT(*) FROM service WHERE service.host_name = host.host_name AND current_state = %s ".$service_match;
				$sql = "SELECT host.host_name, host.current_state, host.address, host.action_url, ".
					"host.notes_url, host.icon_image, host.icon_image_alt, host.display_name, ".
					"host.current_attempt, host.max_check_attempts, (".
					sprintf($svc_query, Current_status_Model::SERVICE_OK).") AS services_ok,(".
					sprintf($svc_query, Current_status_Model::SERVICE_WARNING).") AS services_warning,(".
					sprintf($svc_query, Current_status_Model::SERVICE_CRITICAL).") AS services_critical,(".
					sprintf($svc_query, Current_status_Model::SERVICE_UNKNOWN).") AS services_unknown,(".
					sprintf($svc_query, Current_status_Model::SERVICE_PENDING).") AS services_pending ".
					"FROM host ".
					"INNER JOIN (SELECT MAX(id) AS id, host_name FROM service GROUP BY host_name) s ON host.host_name = s.host_name ".
					"INNER JOIN service_servicegroup ON service_servicegroup.service = s.id ".
					"INNER JOIN servicegroup ON servicegroup.id = service_servicegroup.servicegroup ".
					"WHERE servicegroup.servicegroup_name = ".$db->escape($group)." ".$host_match.$filter_host_sql;
					break;
			default:
				return false;
		}

		$result = $db->query($sql);
		return count($result)>0 ? $result : false;
	}
}
