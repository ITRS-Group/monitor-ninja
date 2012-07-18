<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * display configurations
 */
class Config_Model extends Model {

	const SERVICE_NOTIFICATION_COMMANDS =  'service_notification_commands'; /**< DB column name for service notification commands */
	const HOST_NOTIFICATION_COMMANDS = 'host_notification_commands'; /**< DB column name for host notification commands */

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
	 * Fetch config info for a specific type
	 * @param $type The object type
	 * @param $num_per_page The number of rows to get
	 * @param $offset The number of rows to skip
	 * @param $count Skip fetching config info, fetch the number of matching database rows
	 * @return If count is false, database object or false on error or empty. If count is true, number
	 */
	public function list_config($type = 'hosts', $num_per_page=false, $offset=false, $count=false)
	{

		$db = Database::instance();
		$result_mod = array();

		if (Auth::instance()->authorized_for('all_hosts')) {

			$num_per_page = (int)$num_per_page;

			# only use LIMIT when NOT counting
			if ($offset !== false)
				$offset_limit = $count!==false ? "" : " LIMIT " . $num_per_page." OFFSET ".$offset;
			else
				$offset_limit = '';

			switch($type) {
				case 'hosts':
					if (!$count) {
						$sql = "SELECT ".
							 "h.id, h.host_name, h.alias, h.address,  h.max_check_attempts, h.check_interval,".
							 "h.retry_interval, h.check_command, h.check_period, h.notification_interval,".
							 "h.notes, h.notes_url, h.action_url, h.icon_image, h.icon_image_alt,".
							 "h.obsess_over_host, h.active_checks_enabled, h.passive_checks_enabled, h.check_freshness,".
							 "h.freshness_threshold, h.last_host_notification, h.next_host_notification,".
							 "h.first_notification_delay, h.event_handler, h.notification_options, h.notification_period,".
							 "h.event_handler_enabled, h.stalking_options, h.flap_detection_enabled, h.low_flap_threshold,".
							 "h.high_flap_threshold, h.process_perf_data, h.failure_prediction_enabled,".
							 "h.retain_status_information, h.retain_nonstatus_information ".
							 "FROM host h ORDER BY h.host_name".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM host';
					}
				break;

				case 'services':
					if (!$count) {
						$sql = "SELECT ".
									"id, host_name, service_description, max_check_attempts, check_interval,".
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
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM service';
					}
				break;

				case 'contacts':
					if (!$count) {
						$sql = "SELECT c.contact_name, c.alias, c.email, c.pager, c.service_notification_options,".
									"c.host_notification_options, c.service_notification_period, c.host_notification_period,".
									"c.".self::SERVICE_NOTIFICATION_COMMANDS.", c.".self::HOST_NOTIFICATION_COMMANDS.",".
									"c.retain_status_information, c.retain_nonstatus_information,".
									"h_n.timeperiod_name as h_notification_period, s_n.timeperiod_name as s_notification_period ".
									"FROM contact c, timeperiod h_n, timeperiod s_n ".
									"WHERE h_n.id = c.host_notification_period AND s_n.id = c.service_notification_period ".
									"ORDER BY c.contact_name".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM contact';
					}
				break;

				case 'commands':
					if (!$count) {
						$sql = "SELECT command_name, command_line FROM command ORDER BY command_name".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM command';
					}
				break;

				case 'timeperiods':
					if (!$count) {
						$sql = "SELECT timeperiod_name, alias, monday, tuesday, wednesday, thursday, friday,".
									"saturday, sunday ".
									"FROM timeperiod ORDER BY timeperiod_name".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM timeperiod';
					}
				break;

				case 'host_groups':
					if (!$count) {
						$sql = "SELECT hostgroup_name, alias, notes, notes_url, action_url ".
									"FROM hostgroup ORDER BY hostgroup_name".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM hostgroup';
					}
				break;

				case 'service_groups':
					if (!$count) {
						$sql = "SELECT servicegroup_name, alias, notes, notes_url, action_url ".
									"FROM servicegroup ORDER BY servicegroup_name".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM servicegroup';
					}
				break;

				case 'host_escalations':
					if (!$count) {
						$sql = "SELECT h.host_name, he.first_notification, he.last_notification, he.notification_interval,".
									"he.escalation_period, he.escalation_options, he.id as he_id ".
									"FROM hostescalation he, host h ".
									"WHERE h.id = he.host_name ".
									"ORDER BY h.host_name".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM hostescalation';
					}
				break;

				case 'service_escalations':
					if (!$count) {
						$sql = "SELECT s.host_name, s.service_description, se.first_notification, se.last_notification, se.notification_interval,".
									"se.escalation_period, se.escalation_options, se.id as se_id ".
									"FROM serviceescalation se, service s ".
									"WHERE s.id = se.service ".
									"ORDER BY s.service_description".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM serviceescalation';
					}
				break;

				case 'contact_groups':
					if (!$count) {
						$sql = "SELECT contactgroup_name, alias FROM contactgroup ORDER BY contactgroup_name".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM contactgroup';
					}
				break;

				case 'host_dependencies';
					if (!$count) {
						$sql = "SELECT dh.host_name as dependent, mh.host_name as master, dependency_period, execution_failure_options,".
									"notification_failure_options ".
									"FROM hostdependency hd, host mh, host dh ".
									"WHERE hd.dependent_host_name = dh.id AND hd.host_name = mh.id ".
									"ORDER BY dh.host_name".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM hostdependency';
					}
				break;

				case 'service_dependencies';
					if (!$count) {
						$sql = "SELECT ds.host_name as dependent_host, ds.service_description as dependent_service,".
									"ms.service_description as master_service, ms.host_name as master_host,".
									"dependency_period, execution_failure_options, notification_failure_options ".
									"FROM servicedependency sd, service ds, service ms ".
									"WHERE ds.id = sd.dependent_service AND ms.id = sd.service ".
									"ORDER BY ds.service_description".$offset_limit;
					} else {
						$sql = 'SELECT COUNT(1) AS count FROM servicedependency';
					}
				break;
			}

			$result = $this->query($db,$sql);
			$result_mod = array();

		    if ($count) {
				return $result[0]->count;
		    }

			# We special case host/services since there are one to many relationships
			# parents, contacts + contactgroups need to fetched separatly so we do this here
			if ($type === 'hosts') {
				foreach ($result as &$row) {
					$parents = $this->query($db, "select host.host_name as parent from host_parents " .
						"inner join host on host.id=host_parents.parents WHERE host_parents.host=".$row->id);
					$contactgroups = $this->query($db, "select contactgroup.contactgroup_name from host_contactgroup " .
						"inner join contactgroup on host_contactgroup.contactgroup = contactgroup.id WHERE host_contactgroup.host=".$row->id);
					$contacts = $this->query($db, "select contact.contact_name from host_contact " .
						"inner join contact on host_contact.contact = contact.id WHERE host_contact.host=".$row->id);
					$row->parent = array();
					foreach ($parents as $parent) {
						if ($parent->parent)
							$row->parent[] = $parent->parent;
					}
					$row->contactgroup_name = array();
					foreach ($contactgroups as $cg) {
						if ($cg->contactgroup_name)
							$row->contactgroup_name[] = $cg->contactgroup_name;
					}
					$row->contact_name = array();
					foreach ($contacts as $ctct) {
						if ($ctct->contact_name)
							$row->contact_name[] = $ctct->contact_name;
					}

					unset($row->id);
				}
			}
			if ($type === 'services') {
				foreach ($result as &$row) {
					$contactgroups = $this->query($db,"select contactgroup.contactgroup_name from service_contactgroup " .
						"inner join contactgroup on service_contactgroup.contactgroup = contactgroup.id WHERE service_contactgroup.service=".$row->id);
					$contacts = $this->query($db, "select contact.contact_name FROM service_contact " .
						"inner join contact on service_contact.contact = contact.id WHERE service_contact.service=".$row->id);
					$row->contactgroup_name = array();
					foreach($contactgroups as $cg){
						if ($cg->contactgroup_name)
							$row->contactgroup_name[] = $cg->contactgroup_name;
					}
					$row->contact_name = array();
					foreach($contacts as $ctct){
						if ($ctct->contact_name)
						$row->contact_name[] = $ctct->contact_name;
					}
				}
			}

		    return count($result) ? $result : false;
		}
		else
			return false;
	}

	/**
	 * Wrapper around list_config to only return the number of $type objects
	 * @param $type The object type
	 */
	public function count_config($type)
	{
		return self::list_config($type, false, false, true);
	}
}
