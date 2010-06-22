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
	 * Called from get_servicegroup_hoststatus() and get_servicegroup_hoststatus()
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
			$filter_sql .= " AND 1 << h.current_state & $hoststatus ";
		}
		$service_filter = false;
		$servicestatus = trim($servicestatus);
		if ($servicestatus!==false && !empty($servicestatus)) {
			$filter_sql .= " AND 1 << s.current_state & $servicestatus ";
		}

		$db = new Database();
		$all_sql = $groupname != 'all' ? "sg.".$grouptype."group_name=".$db->escape($groupname)." AND" : '';

		# we need to match against different field depending on if host- or servicegroup
		$member_match = $grouptype == 'service' ? " s.id=ssg.".$grouptype." AND " : " h.id=ssg.".$grouptype." AND ";

		if ($auth->view_hosts_root) {
			$sql = "
				SELECT
					h.*,
					s.current_state AS service_state,
					COUNT(s.current_state) AS state_count
				FROM
					service s,
					host h,
					".$grouptype."group sg,
					".$grouptype."_".$grouptype."group ssg
				WHERE
					".$all_sql."
					ssg.".$grouptype."group = sg.id AND
					".$member_match."
					h.host_name=s.host_name ".$filter_sql."
				GROUP BY
					h.id, s.current_state
				ORDER BY
					h.host_name,
					s.current_state;";
		} elseif ($auth->view_services_root && $grouptype == 'service') {
			$sql = "
				SELECT
					h.*,
					s.current_state AS service_state,
					COUNT(s.current_state) AS state_count
				FROM
					service s,
					host h,
					".$grouptype."group sg,
					".$grouptype."_".$grouptype."group ssg
				WHERE
					".$all_sql."
					ssg.".$grouptype."group = sg.id AND
					".$member_match."
					h.host_name=s.host_name ".$filter_sql."
				GROUP BY
					h.id, s.current_state
				ORDER BY
					h.host_name,
					s.current_state;";
		} else {
			$hostlist_str = implode(',', $hostlist);

			$sql = "
				SELECT
					h.*,
					s.current_state AS service_state,
					COUNT(s.current_state) AS state_count
				FROM
					service s,
					host h,
					".$grouptype."group sg,
					".$grouptype."_".$grouptype."group ssg
				WHERE
					".$all_sql."
					ssg.".$grouptype."group = sg.id AND
					".$member_match."
					h.host_name=s.host_name AND
					h.id IN (".$hostlist_str.") ".$filter_sql."
				GROUP BY
					h.id, s.current_state
				ORDER BY
					h.host_name,
					s.current_state;";
		}
		$result = $db->query($sql);
		#echo $sql."<hr />";
		return $result;
	}

	/**
	 * Finds all members of a host- or servicegroup
	 * Will return all info on the hosts but only service_description
	 * and current_state for services
	 *
	 * @param $grouptype [host|service]
	 * @param $groupname Name of the group
	 * @return db result
	 */
	public static function get_group_info($grouptype='service', $groupname=false, $hoststatus=false, $servicestatus=false, $service_props=false, $host_props=false)
	{
		$groupname = trim($groupname);
		if (empty($groupname)) {
			return false;
		}
		$filter_sql = '';
		$state_filter = false;
		if (!empty($hoststatus)) {
			$filter_sql .= " AND 1 << h.current_state & $hoststatus ";
		}
		$service_filter = false;
		$servicestatus = trim($servicestatus);
		if ($servicestatus!==false && !empty($servicestatus)) {
			$filter_sql .= " AND 1 << s.current_state & $servicestatus ";
		}

		$hostlist = Host_Model::authorized_hosts();

		$hostlist_str = !empty($hostlist) ? implode(',', $hostlist) : false;

		$db = new Database();
		$all_sql = $groupname != 'all' ? "sg.".$grouptype."group_name=".$db->escape($groupname)." AND" : '';

		# we need to match against different field depending on if host- or servicegroup
		$member_match = $grouptype == 'service' ? " s.id=ssg.".$grouptype." AND " : " h.id=ssg.".$grouptype." AND ";

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
		$sql = "SELECT
				h.host_name,
				h.address,
				h.alias,
				h.current_state AS host_state,
				(UNIX_TIMESTAMP() - h.last_state_change) AS duration,
				UNIX_TIMESTAMP() AS cur_time,
				h.output,
				h.long_output,
				h.problem_has_been_acknowledged AS hostproblem_is_acknowledged,
				h.scheduled_downtime_depth AS hostscheduled_downtime_depth,
				h.notifications_enabled AS host_notifications_enabled,
				h.action_url AS host_action_url,
				h.icon_image AS host_icon_image,
				h.icon_image_alt AS host_icon_image_alt,
				h.is_flapping AS host_is_flapping,
				h.notes_url AS host_notes_url,
				s.id AS service_id,
				s.current_state AS service_state,
				(UNIX_TIMESTAMP() - s.last_state_change) AS service_duration,
				UNIX_TIMESTAMP() AS service_cur_time,
				s.active_checks_enabled,
				s.current_state,
				s.problem_has_been_acknowledged,
				s.scheduled_downtime_depth,
				s.last_check,
				s.output AS service_output,
				s.long_output AS service_long_output,
				s.notes_url,
				s.action_url,
				s.current_attempt,
				s.max_check_attempts,
				s.should_be_scheduled,
				s.next_check,
				s.notifications_enabled,
				s.service_description
			FROM
				service s,
				host h,
				".$grouptype."group sg,
				".$grouptype."_".$grouptype."group ssg
			WHERE
				".$all_sql."
				ssg.".$grouptype."group = sg.id AND
				".$member_match."
				h.host_name=s.host_name ".$auth_str." ".$filter_sql.$service_props_sql.$host_props_sql.
			" GROUP BY
				h.host_name, s.id
			ORDER BY
				h.host_name,
				s.service_description,
				s.current_state;";
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
				break;
			case 'service':
				$service_list = $auth->get_authorized_services();
				break;
			default:
				return false;
		}

		$db = new Database();
		$all_sql = $name != 'all' ? "sg.".$type."group_name=".$db->escape($name)." AND" : '';

		# we need to match against different field depending on if host- or servicegroup
		$member_match = $type == 'service' ? " s.id=ssg.".$type." AND " : " h.id=ssg.".$type." AND ";

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
					gr.id=g.".$type."group;";
		} elseif (!empty($name)) {
			if (!in_array($name, ${$type.'_list'})) {
				return false;
			}
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

		if ($grouptype == 'host') {
			if (!$auth->view_hosts_root) {
				$auth_control = "AND ".
				"ca.contact=".(int)$contact_id." AND ".
				"h.id=ca.host ";
			}

			switch ($what) {
				case 'host':
					$sql = "SELECT
						COUNT(DISTINCT h.id) AS cnt,h.current_state ".
						"FROM ".
						"host h, ".
						$grouptype."group sg, ".
						$grouptype."_".$grouptype."group ssg, ".
						"contact_access ca ".
						"WHERE ".$all_sql.
						"ssg.".$grouptype."group = sg.id AND ".
						"h.id=ssg.host ".$auth_control.
						"GROUP BY ".
						"h.current_state ".
						"ORDER BY ".
						"h.current_state;";
					break;
				case 'service':
					$sql = "SELECT
						COUNT(DISTINCT s.id) AS cnt, s.current_state ".
						"FROM ".
						"service s, ".
						"host h, ".
						$grouptype."group sg, ".
						$grouptype."_".$grouptype."group ssg, ".
						"contact_access ca ".
						"WHERE ".$all_sql.
						"ssg.".$grouptype."group = sg.id AND ".
						"h.id=ssg.host AND ".
						"h.host_name=s.host_name ".$auth_control.
						"GROUP BY ".
						"s.current_state ".
						"ORDER BY ".
						"s.current_state;";
					break;
			}
		} elseif ($grouptype == 'service') {
			if ($auth->view_hosts_root || $auth->view_services_root) {
				$auth_control = " AND ca.contact=".(int)$contact_id." AND ".
				"s.id=ca.service ";
			}

			switch ($what) {
				case 'host':
					$sql = "SELECT
						COUNT(DISTINCT h.id) AS cnt,h.current_state ".
						"FROM ".
						"host h, ".
						"service s, ".
						$grouptype."group sg, ".
						$grouptype."_".$grouptype."group ssg, ".
						"contact_access ca ".
						"WHERE ".$all_sql.
						"ssg.".$grouptype."group = sg.id AND ".
						"s.id=ssg.service AND ".
						"h.host_name=s.host_name ".$auth_control.
						"GROUP BY ".
						"h.current_state ".
						"ORDER BY ".
						"h.current_state;";
					break;
				case 'service':
					$sql = "SELECT
						COUNT(DISTINCT s.id) AS cnt, s.current_state ".
						"FROM ".
						"service s, ".
						"host h, ".
						$grouptype."group sg, ".
						$grouptype."_".$grouptype."group ssg, ".
						"contact_access ca ".
						"WHERE ".$all_sql.
						"ssg.".$grouptype."group = sg.id AND ".
						"s.id=ssg.service AND ".
						"h.host_name=s.host_name AND ".
						"s.id=ca.service ".$auth_control.
						"GROUP BY ".
						"s.current_state ".
						"ORDER BY ".
						"s.current_state;";
					break;
			}
		}
		#echo $sql."<br />";
		$result = $db->query($sql);
		return count($result)>0 ? $result : false;
	}
}