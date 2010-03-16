<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * display configurations
 */
class Config_Model extends Model {

	public $num_per_page = false;
	public $offset = false;
	public $count = false;

	/**
	*	Fetch host info
	*
	*/
	public function list_config($type = 'hosts', $num_per_page=false, $offset=false, $count=false)
	{

		$db = new Database();
		$auth = new Nagios_auth_Model();

		$host_query = $auth->authorized_host_query();
		if ($host_query === true) {

			$num_per_page = (int)$num_per_page;

			# only use LIMIT when NOT counting
			if ($offset !== false)
				$offset_limit = $count!==false ? "" : " LIMIT " . $offset.", ".$num_per_page;
			else
				$offset_limit = '';
				//$offset_limit = $count!==false ? "" : " LIMIT ".$num_per_page;

			switch($type) {
				case 'hosts':
			        $sql = "SELECT
							 h.host_name, h.alias, h.address,  h.max_check_attempts, h.check_interval,
							 h.retry_interval, h.check_command, h.check_period, h.notification_interval,
							 h.notes, h.notes_url, h.action_url, h.icon_image, h.icon_image_alt,
							 h.obsess_over_host, h.active_checks_enabled, h.passive_checks_enabled, h.check_freshness,
							 h.freshness_threshold, h.last_host_notification, h.next_host_notification,
							 h.first_notification_delay, h.event_handler, h.notification_options, h.notification_period,
							 h.event_handler_enabled, h.stalking_options, h.flap_detection_enabled, h.low_flap_threshold,
							 h.high_flap_threshold, h.process_perf_data, h.failure_prediction_enabled,
							 h.retain_status_information, h.retain_nonstatus_information
							 FROM host as h ORDER BY h.host_name";
			                 /* Failure Prediction Options*/
				break;

				case 'services':
 					$sql = "SELECT
									host_name, service_description, max_check_attempts, check_interval,
									retry_interval, check_command, check_period, parallelize_check, is_volatile,
									obsess_over_service, active_checks_enabled, passive_checks_enabled,
									check_freshness, freshness_threshold, notifications_enabled,
									notes, notes_url, action_url, icon_image, icon_image_alt,
									notification_interval, notification_options, notification_period,
									event_handler, event_handler_enabled, stalking_options, flap_detection_enabled,
									low_flap_threshold, high_flap_threshold, process_perf_data, failure_prediction_enabled,
									retain_status_information, retain_nonstatus_information
									FROM service ORDER BY host_name, service_description";
									/* Failure Prediction Options*/
				break;

				case 'contacts':
					$sql = "SELECT contact_name, alias, email, pager, service_notification_options,
									host_notification_options, service_notification_period, host_notification_period,
									service_notification_commands, host_notification_commands,
									retain_status_information, retain_nonstatus_information
									FROM contact ORDER BY contact_name";
				break;

				case 'commands':
					$sql = "SELECT command_name, command_line FROM command ORDER BY command_name";
				break;

				case 'timeperiods':
					$sql = "SELECT timeperiod_name, alias, monday, tuesday, wednesday, thursday, friday,
									saturday, sunday
									FROM timeperiod ORDER BY timeperiod_name";
				break;

				case 'host_groups':
					$sql = "SELECT hostgroup_name, alias, notes, notes_url, action_url
									FROM hostgroup ORDER BY hostgroup_name";
				break;

				case 'service_groups':
					$sql = "SELECT servicegroup_name, alias, notes, notes_url, action_url
									FROM servicegroup ORDER BY servicegroup_name";
				break;

				case 'host_escalations':
					$sql = "SELECT h.host_name, cg.contactgroup_name, he.first_notification, he.last_notification, he.notification_interval,
									he.escalation_period, he.escalation_options
									FROM hostescalation as he, host as h, host_contactgroup as hc, contactgroup as cg
									WHERE h.id = he.host_name AND hc.host = h.id
									ORDER BY h.host_name";
				break;

				case 'service_escalations':
					$sql = "SELECT s.host_name, s.service_description, se.first_notification, se.last_notification, se.notification_interval,
									se.escalation_period, se.escalation_options, cg.contactgroup_name
									FROM serviceescalation as se, service as s, service_contactgroup as sc, contactgroup as cg
									WHERE s.id = se.service AND sc.service = s.id
									ORDER BY s.service_description";
				break;

				case 'contact_groups':
					$sql = "SELECT contactgroup_name, alias FROM contactgroup ORDER BY contactgroup_name";
				break;

				case 'host_dependencies';
					$sql = "SELECT dh.host_name as dependent, mh.host_name as master, dependency_period, execution_failure_options,
									notification_failure_options
									FROM hostdependency as hd, host as mh, host dh
									WHERE hd.dependent_host_name = dh.id AND hd.host_name = mh.id
									ORDER BY dh.host_name";
				break;

				case 'service_dependencies';
					$sql = "SELECT ds.host_name as dependent_host, ds.service_description as dependent_service,
									ms.service_description as master_service, ms.host_name as master_host,
									dependency_period, execution_failure_options, notification_failure_options
									FROM servicedependency as sd, service as ds, service as ms
									WHERE ds.id = sd.dependent_service AND ms.id = sd.service
									ORDER BY ds.service_description";
				break;
			}

			$result = $db->query($sql);

			# We special case host/services since there are one to many relationships
			# parents, contacts + contactgroups need to fetched separatly so we do this here
		    if ($type === 'hosts') {
				$parent_child = $db->query("select host.host_name, host2.host_name as parent from host left join host_parents on host.id=host_parents.host " .
										   "left join host as host2 on host2.id=host_parents.parents");
				$contactgroups = $db->query("select host.host_name,contactgroup.contactgroup_name  from host left join host_contactgroup on host.id = host_contactgroup.host " .
											"left join contactgroup on host_contactgroup.contactgroup = contactgroup.id");
				$contacts = $db->query("select host.host_name,contact.contact_name from host left join host_contact on host.id = host_contact.host " .
									   "left join contact on host_contact.contact = contact.id");
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
				return $result_mod;
		    }
		    if ($type === 'services') {
				$s_contactgroups = $db->query("select service.host_name, service.service_description, contactgroup.contactgroup_name " .
											  "from service left join service_contactgroup on service.id = service_contactgroup.service " .
											  "left join contactgroup on service_contactgroup.contactgroup = contactgroup.id");
				$s_contacts = $db->query("select service.host_name, service.service_description, contact.contact_name " .
										 "from service left join service_contact on service.id = service_contact.service " .
										 "left join contact on service_contact.contact = contact.id");
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
				return $result_mod;
		    }
			# End host/service special casing #

		    if ($count !== false) {
				return $result ? count($result) : 0;
		    }
		    return $result->count() ? $result->result(): false;
		}
	}
}
