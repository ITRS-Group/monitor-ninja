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
									h.host_name, h.alias, h.address, hh.host_name as parent, h.max_check_attempts, h.check_interval,
									h.retry_interval, h.check_command, h.check_period, h.notification_interval,
									h.notes, h.notes_url, h.action_url, h.icon_image, h.icon_image_alt,
									h.obsess_over_host, h.active_checks_enabled, h.passive_checks_enabled, h.check_freshness,
									h.freshness_threshold, cg.contactgroup_name, h.last_host_notification, h.next_host_notification,
									h.first_notification_delay, h.event_handler, h.notification_options, h.notification_period,
									h.event_handler_enabled, h.stalking_options, h.flap_detection_enabled, h.low_flap_threshold,
									h.high_flap_threshold, h.process_perf_data, h.failure_prediction_enabled
									FROM host as h, host_parents as hp, host_contactgroup as hc, host as hh, contactgroup as cg
									WHERE h.id = hp.host AND
									h.id = hc.host AND
									hp.parents = hh.id AND
									hc.contactgroup = cg.id
									ORDER BY h.host_name";
									/* Failure Prediction Options, Retention Options*/
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
									cg.contactgroup_name
									FROM service, service_contactgroup as sc, contactgroup as cg
									WHERE service.id = sc.contactgroup AND sc.contactgroup = cg.id
									ORDER BY host_name, service_description";
									/* Failure Prediction Options,Retention Options*/
				break;

				case 'contacts':
					$sql = "SELECT contact_name, alias, email, pager, service_notification_options,
									host_notification_options, service_notification_period, host_notification_period,
									service_notification_commands, host_notification_commands
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
					$sql = "SELECT host_name, first_notification, last_notification, notification_interval,
									escalation_period, escalation_options
									FROM hostescalation ORDER BY host_name"; //contacts/groups?
				break;

				case 'service_escalations':
					$sql = "SELECT service, first_notification, last_notification, notification_interval,
									escalation_period, escalation_options
									FROM serviceescalation ORDER BY service";
				break;

				case 'contact_groups':
					$sql = "SELECT contactgroup_name, alias FROM contactgroup ORDER BY contactgroup_name";
				break;

				case 'host_dependencies';
					$sql = "SELECT dependent_host_name, host_name, dependency_period, execution_failure_options
									FROM hostdependency ORDER BY host_name"; // dependency type?
				break;

				case 'service_dependencies';
					$sql = "SELECT dependent_service, service, dependency_period, execution_failure_options
									FROM servicedependency ORDER BY service"; // master host? dependent host? dep. type?
				break;
			}

			$result = $db->query($sql);

			if ($count !== false) {
				return $result ? count($result) : 0;
			}
			return $result->count() ? $result->result(): false;
		}
	}
}