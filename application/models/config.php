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
									host_name, alias, address, host_parents.parents, max_check_attempts, check_interval,
									retry_interval, check_command, check_period, notification_interval,
									notes, notes_url, action_url, icon_image, icon_image_alt,
									obsess_over_host, active_checks_enabled, passive_checks_enabled, check_freshness,
									freshness_threshold, host_contactgroup.contactgroup, last_host_notification, next_host_notification,
									first_notification_delay, event_handler, notification_options, notification_period,
									event_handler_enabled, stalking_options, flap_detection_enabled, low_flap_threshold,
									high_flap_threshold, process_perf_data, failure_prediction_enabled
									FROM host, host_parents, host_contactgroup
									WHERE host.id = host_parents.host AND
									host.id = host_contactgroup.host
									ORDER BY host_name";
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
									low_flap_threshold, high_flap_threshold, process_perf_data, failure_prediction_enabled
									FROM service ORDER BY host_name, service_description";
									/*'Default Contact Groups, Failure Prediction Options,Retention Options*/
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
									FROM hostgroup ORDER BY hostgroup_name"; // members
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