<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * display configurations
 */
class Config_Model extends Model {

	public $num_per_page = false;
	public $offset = false;
	public $count = false;
	const SERVICE_NOTIFICATION_COMMANDS =  'service_notification_commands';
	const HOST_NOTIFICATION_COMMANDS = 'host_notification_commands';

	/**
	 Workaround for PDO queries: runs $db->query($sql), copies
	 the resultset to an array, closes the resultset, and returns
	 the array.
	 */
	private function query($db,$sql)
	{
		$res = $db->query($sql);
		if (!$res)
			return NULL;

		$rc = array();
		foreach($res as $row) {
			$rc[] = $row;
		}
		unset($res);
		return $rc;
	}

	/**
	*	Fetch host info
	*
	*/
	public function list_config($type = 'hosts', $num_per_page=false, $offset=false, $count=false)
	{

		$db = new Database();
		$auth = new Nagios_auth_Model();

		if ($auth->view_hosts_root) {

			$num_per_page = (int)$num_per_page;

			# only use LIMIT when NOT counting
			if ($offset !== false)
				$offset_limit = $count!==false ? "" : " LIMIT " . $num_per_page." OFFSET ".$offset;
			else
				$offset_limit = '';
				//$offset_limit = $count!==false ? "" : " LIMIT ".$num_per_page;

			switch($type) {
				case 'hosts':
			        $sql = "SELECT ".
							 "h.host_name, h.alias, h.address,  h.max_check_attempts, h.check_interval,".
							 "h.retry_interval, h.check_command, h.check_period, h.notification_interval,".
							 "h.notes, h.notes_url, h.action_url, h.icon_image, h.icon_image_alt,".
							 "h.obsess_over_host, h.active_checks_enabled, h.passive_checks_enabled, h.check_freshness,".
							 "h.freshness_threshold, h.last_host_notification, h.next_host_notification,".
							 "h.first_notification_delay, h.event_handler, h.notification_options, h.notification_period,".
							 "h.event_handler_enabled, h.stalking_options, h.flap_detection_enabled, h.low_flap_threshold,".
							 "h.high_flap_threshold, h.process_perf_data, h.failure_prediction_enabled,".
							 "h.retain_status_information, h.retain_nonstatus_information ".
							 "FROM host h ORDER BY h.host_name".$offset_limit;
			                 /* Failure Prediction Options*/
				break;

				case 'services':
					$sql = "SELECT ".
									"host_name, service_description, max_check_attempts, check_interval,".
									"retry_interval, check_command, check_period, parallelize_check, is_volatile,".
									"obsess_over_service, active_checks_enabled, passive_checks_enabled,".
									"check_freshness, freshness_threshold, notifications_enabled,".
									"notes, notes_url, action_url, icon_image, icon_image_alt,".
									"notification_interval, notification_options, notification_period,".
									"event_handler, event_handler_enabled, stalking_options, flap_detection_enabled,".
									"low_flap_threshold, high_flap_threshold, process_perf_data, failure_prediction_enabled,".
									"retain_status_information, retain_nonstatus_information ".
									"FROM service ORDER BY host_name, service_description".$offset_limit;
									/* Failure Prediction Options*/
				break;

				case 'contacts':
					$sql = "SELECT c.contact_name, c.alias, c.email, c.pager, c.service_notification_options,".
									"c.host_notification_options, c.service_notification_period, c.host_notification_period,".
									"c.".self::SERVICE_NOTIFICATION_COMMANDS.", c.".self::HOST_NOTIFICATION_COMMANDS.",".
									"c.retain_status_information, c.retain_nonstatus_information,".
									"h_n.timeperiod_name as h_notification_period, s_n.timeperiod_name as s_notification_period ".
									"FROM contact c, timeperiod h_n, timeperiod s_n ".
									"WHERE h_n.id = c.host_notification_period AND s_n.id = c.service_notification_period ".
									"ORDER BY c.contact_name".$offset_limit;
				break;

				case 'commands':
					$sql = "SELECT command_name, command_line FROM command ORDER BY command_name".$offset_limit;
				break;

				case 'timeperiods':
					$sql = "SELECT timeperiod_name, alias, monday, tuesday, wednesday, thursday, friday,".
									"saturday, sunday ".
									"FROM timeperiod ORDER BY timeperiod_name".$offset_limit;
				break;

				case 'host_groups':
					$sql = "SELECT hostgroup_name, alias, notes, notes_url, action_url ".
									"FROM hostgroup ORDER BY hostgroup_name".$offset_limit;
				break;

				case 'service_groups':
					$sql = "SELECT servicegroup_name, alias, notes, notes_url, action_url ".
									"FROM servicegroup ORDER BY servicegroup_name".$offset_limit;
				break;

				case 'host_escalations':
					$sql = "SELECT h.host_name, he.first_notification, he.last_notification, he.notification_interval,".
									"he.escalation_period, he.escalation_options, he.id as he_id ".
									"FROM hostescalation he, host h ".
									"WHERE h.id = he.host_name ".
									"ORDER BY h.host_name".$offset_limit;
				break;

				case 'service_escalations':
					$sql = "SELECT s.host_name, s.service_description, se.first_notification, se.last_notification, se.notification_interval,".
									"se.escalation_period, se.escalation_options, se.id as se_id ".
									"FROM serviceescalation se, service s ".
									"WHERE s.id = se.service ".
									"ORDER BY s.service_description".$offset_limit;
				break;

				case 'contact_groups':
					$sql = "SELECT contactgroup_name, alias FROM contactgroup ORDER BY contactgroup_name".$offset_limit;
				break;

				case 'host_dependencies';
					$sql = "SELECT dh.host_name as dependent, mh.host_name as master, dependency_period, execution_failure_options,".
									"notification_failure_options ".
									"FROM hostdependency hd, host mh, host dh ".
									"WHERE hd.dependent_host_name = dh.id AND hd.host_name = mh.id ".
									"ORDER BY dh.host_name".$offset_limit;
				break;

				case 'service_dependencies';
					$sql = "SELECT ds.host_name as dependent_host, ds.service_description as dependent_service,".
									"ms.service_description as master_service, ms.host_name as master_host,".
									"dependency_period, execution_failure_options, notification_failure_options ".
									"FROM servicedependency sd, service ds, service ms ".
									"WHERE ds.id = sd.dependent_service AND ms.id = sd.service ".
									"ORDER BY ds.service_description".$offset_limit;
				break;
			}

			$result = $this->query($db,$sql);

			# We special case host/services since there are one to many relationships
			# parents, contacts + contactgroups need to fetched separatly so we do this here
		    if ($type === 'hosts' && $count == false) {
				$parent_child = $this->query($db, "select host.host_name, host2.host_name as parent from host left join host_parents on host.id=host_parents.host " .
										   "left join host host2 on host2.id=host_parents.parents ORDER BY host.host_name ".$offset_limit);
				$contactgroups = $this->query($db, "select host.host_name,contactgroup.contactgroup_name  from host left join host_contactgroup on host.id = host_contactgroup.host " .
											"left join contactgroup on host_contactgroup.contactgroup = contactgroup.id ORDER BY host.host_name ".$offset_limit);
				$contacts = $this->query($db, "select host.host_name,contact.contact_name from host left join host_contact on host.id = host_contact.host " .
									   "left join contact on host_contact.contact = contact.id ORDER BY host.host_name ".$offset_limit);
				foreach($parent_child as $row){
					if (isset($parent_array[$row->host_name] )){
						$parent_array[$row->host_name] = $parent_array[$row->host_name] . "," . $row->parent;
					} else {
						$parent_array[$row->host_name] = $row->parent;
					}
				}
				foreach($contactgroups as $row){
					if (isset($contactgroups_array[$row->host_name] )) {
						$contactgroups_array[$row->host_name] = $contactgroups_array[$row->host_name] . "," . $row->contactgroup_name;
					} else {
						$contactgroups_array[$row->host_name] = $row->contactgroup_name;
					}
				}
				foreach($contacts as $row){
					if (isset($contacts_array[$row->host_name] )) {
						$contacts_array[$row->host_name] = $contacts_array[$row->host_name] . "," . $row->contact_name;
					} else {
						$contacts_array[$row->host_name] = $row->contact_name;
					}
				}
				foreach($result as $row){
					if(isset($parent_array[$row->host_name])){
						$row->parent = $parent_array[$row->host_name];
					}
					if(isset($contactgroups_array[$row->host_name])){
						$row->contactgroup_name = $contactgroups_array[$row->host_name];
					}
					if(isset($contacts_array[$row->host_name])){
						$row->contact_name = $contacts_array[$row->host_name];
					}
					$result_mod[] = $row;
				}
                                unset($result);
				return $result_mod;
		    }
		    if ($type === 'services' && $count == false) {
                        $s_contactgroups = $this->query($db,"select service.host_name, service.service_description, contactgroup.contactgroup_name " .
											  "from service left join service_contactgroup on service.id = service_contactgroup.service " .
											  "left join contactgroup on service_contactgroup.contactgroup = contactgroup.id ORDER BY service.host_name, service.service_description ".$offset_limit);
				$s_contacts = $this->query($db, "select service.host_name, service.service_description, contact.contact_name " .
										 "from service left join service_contact on service.id = service_contact.service " .
										 "left join contact on service_contact.contact = contact.id ORDER BY service.host_name, service.service_description ".$offset_limit);
				foreach($s_contactgroups as $row){
					if(isset($s_contactgroups_array[$row->host_name][$row->service_description])) {
						$s_contactgroups_array[$row->host_name][$row->service_description] = $s_contactgroups_array[$row->host_name][$row->service_description] . "," . $row->contactgroup_name;
					} else {
						$s_contactgroups_array[$row->host_name][$row->service_description] = $row->contactgroup_name;
					}
				}
				foreach($s_contacts as $row){
					if(isset($s_contacts_array[$row->host_name][$row->service_description])) {
						$s_contacts_array[$row->host_name][$row->service_description] = $s_contacts_array[$row->host_name][$row->service_description] . "," . $row->contact_name;
					} else {
						$s_contacts_array[$row->host_name][$row->service_description] = $row->contact_name;
					}
				}
				foreach($result as $row){
					if(isset($s_contactgroups_array[$row->host_name][$row->service_description])){
						$row->contactgroup_name = $s_contactgroups_array[$row->host_name][$row->service_description];
					}
					if(isset($s_contacts_array[$row->host_name][$row->service_description])){
						$row->contact_name = $s_contacts_array[$row->host_name][$row->service_description];
					}
					$result_mod[] = $row;
				}
                                unset($result);
				return $result_mod;
		    }
			# End host/service special casing #

		    if ($count !== false) {
				return $result ? count($result) : 0;
		    }
		    return count($result) ? $result : false;
                        //$result->count() ? $result->result(): false;
		}
		else
			return false;
	}

	public function count_config($type)
	{
		return self::list_config($type, false, false, true);
	}
}
