<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieves and manipulates current status of hosts (and services?)
 */
class Current_status_Model extends Model
{
	const HOST_UP =  0; /**< Nagios' host up code */
	const HOST_DOWN = 1; /**< Nagios' host down code */
	const HOST_UNREACHABLE = 2; /**< Nagios' host unreachable code */
	const HOST_PENDING = 6; /**< Our magical "host pending" code for unchecked hosts */

	const SERVICE_OK = 0; /**< Nagios' service ok code */
	const SERVICE_WARNING = 1; /**< Nagios' service warning code */
	const SERVICE_CRITICAL = 2; /**< Nagios' service critical code */
	const SERVICE_UNKNOWN =  3; /**< Nagios' service unknown code */
	const SERVICE_PENDING = 6; /**< Our magical "service pending" code for unchecked services */
	const HOST_CHECK_ACTIVE = 0;	/**< Nagios performed the host check */
	const HOST_CHECK_PASSIVE = 1;	/**< the host check result was submitted by an external source */
	const SERVICE_CHECK_ACTIVE = 0; /**< Nagios performed the service check */
	const SERVICE_CHECK_PASSIVE = 1; /**< the service check result was submitted by an external source */

	public $flapping_services = 0; /**< Number of flapping services */
	public $notification_disabled_services = 0; /**< Number of services with disabled notifications */
	public $event_handler_disabled_svcs = 0; /**< Number of services with disabled event handler */
	public $active_checks_disabled_svcs = 0; /**< Number of services with disabled active checks */
	public $passive_checks_disabled_svcs = 0; /**< Number of services with disabled passive checks */

	public $services_ok_disabled = 0; /**< Number of ok services with disabled active checks */
	public $services_ok_unacknowledged = 0; /**< FIXME: Number of ok services that are actively checked. The fuck? */
	public $services_ok = 0; /**< Number of ok services */

	public $services_warning_host_problem = 0; /**< Number of services in warning on problem hosts */
	public $services_warning_scheduled = 0; /**< Number of services in warning in scheduled downtime */
	public $services_warning_acknowledged = 0; /**< Number of services in warning that are acknowledged */
	public $services_warning_disabled = 0; /**< Number of services in warning with active checks disabled */
	public $svcs_warning_unacknowledged = 0; /**< Number of services in warning that are unacknowledged */
	public $services_warning = 0; /**< Number of services in warning */

	public $services_unknown_host_problem = 0; /**< Number of services in unknown on problem hosts */
	public $services_unknown_scheduled = 0; /**< Number of services in unknown in scheduled downtime */
	public $services_unknown_acknowledged = 0; /**< Number of services in unknown that are acknowledged */
	public $services_unknown_disabled = 0; /**< Number of services in unknown with active checks disabled */
	public $svcs_unknown_unacknowledged = 0; /**< Number of services in unknown that are unacknowledged */
	public $services_unknown = 0; /**< Number of services in unknown */

	public $services_critical_host_problem = 0; /**< Number of services in critical on problem hosts */
	public $services_critical_scheduled = 0; /**< Number of services in critical in scheduled downtime */
	public $services_critical_acknowledged = 0; /**< Number of services in critical that are acknowledged */
	public $services_critical_disabled = 0; /**< Number of services in critical with active checks disabled */
	public $svcs_critical_unacknowledged = 0; /**< Number of services in critical that are unacknowledged */
	public $services_critical = 0; /**< Number of services in critical */

	public $services_pending_disabled = 0; /**< Number of pending services with active checks disabled */
	public $services_pending = 0; /**< Number of pending services */

	public $total_service_health = 0; /**< Strange total health algorithm as copied from nagios */
	public $potential_service_health = 0; /**< Strange potential health algorithm as copied from nagios */

	public $total_active_service_checks = 0; /**< Number of services where last check was active */
	public $min_service_latency = -1.0; /**< Minimum service check latency */
	public $max_service_latency = -1.0; /**< Maximum service check latency */
	public $min_service_execution_time = -1.0; /**< Minimum service check execution time */
	public $max_service_execution_time = -1.0; /**< Maximum service check execution time */
	public $total_service_latency = 0; /**< Total service check latency */
	public $total_service_execution_time = 0; /**< Total service check execution time */
	public $total_passive_service_checks = 0; /**< Number of services where last check was passive */
	public $total_services = 0; /**< The total number of services */

	public $flap_disabled_hosts = 0; /**< Number of hosts with flap detection disabled */
	public $flap_disabled_services = 0; /**< Number of services with flap detection disabled */
	public $flapping_hosts = 0; /**< Number of flapping hosts */
	public $notification_disabled_hosts = 0; /**< Number of hosts with notification disabled */
	public $event_handler_disabled_hosts = 0; /**< Number of hosts with event handlers disabled */
	public $active_checks_disabled_hosts = 0; /**< Number of hosts with active checks disabled */
	public $passive_checks_disabled_hosts = 0; /**< Number of hosts with passive checks disabled */

	public $hosts_up_disabled = 0; /**< Number of hosts that are up with active checks disabled */
	public $hosts_up_unacknowledged = 0; /**< FIXME: Number of hosts that are up with active checks enabled. Makes no sense. */
	public $hosts_up = 0; /**< Number of hosts that are up */

	public $hosts_down_scheduled = 0; /**< Number of hosts that are down and in scheduled downtime */
	public $hosts_down_acknowledged = 0; /**< Number of hosts that are down and acknowledged */
	public $hosts_down_disabled = 0; /**< Number of hosts that are down and disabled */
	public $hosts_down_unacknowledged = 0; /**< Number of hosts that are down and unacknowledged */
	public $hosts_down = 0; /**< Number of hosts that are down */

	public $hosts_unreachable_scheduled = 0; /**< Number of hosts that are unreachable and in scheduled downtime */
	public $hosts_unreachable_acknowledged = 0; /**< Number of hosts that are unreachable and acknowledged */
	public $hosts_unreachable_disabled = 0; /**< Number of hosts that are unreachable and disabled */
	public $hosts_unreach_unacknowledged = 0; /**< Number of hosts that are unreachable and unacknowledged */
	public $hosts_unreachable = 0; /**< Number of hosts that are unreachable */

	public $hosts_pending_disabled = 0; /**< Number of pending hosts with active checks disabled */
	public $hosts_pending = 0; /**< Number of pending hosts */

	public $total_host_health = 0; /**< Strange total health algorithm as copied from nagios */
	public $potential_host_health = 0; /**< Strange potential health algorithm as copied from nagios */
	public $total_active_host_checks = 0; /**< Number of hosts where last check was active */

	public $min_host_latency = -1.0; /**< Minimum host check latency */
	public $max_host_latency = -1.0; /**< Maximum host check latency */
	public $min_host_execution_time = -1.0; /**< Minimum host check execution time */
	public $max_host_execution_time = -1.0; /**< Maximum host check execution time */

	public $total_host_latency = 0; /**< Total host check latency */
	public $total_host_execution_time = 0; /**< Total host check execution time */
	public $total_passive_host_checks = 0; /**< Number of hosts where last check was passive */

	public $total_hosts = 0; /**< Total number of hosts */

	# health
	public $percent_service_health = 0; /**< Percentage of total service health by potential service health */
	public $percent_host_health = 0; /**< Percentage of total host health by potential host health */

	public $average_service_latency = 0; /**< Average latency for service checks */
	public $average_host_latency = 0; /**< Average latency for host checks */
	public $average_service_execution_time = 0; /**< Average execution time for service checks */
	public $average_host_execution_time = 0; /**< Average execution time for host checks */

	public $hostoutage_list = array(); /**< List of host outages */
	public $total_blocking_outages = 0; /**< Number of blocking outages */
	public $total_nonblocking_outages = 0; /**< Number of nonblocking outages */
	public $affected_hosts = array(); /**< Number of hosts affected by outages */
	public $unreachable_hosts = array(); /**< hosts being unreachable because of network outages */
	public $children_services = array(); /**< nr of services belonging to host affected by an outage */

	public $host_data_present = false; /**< FIXME: implementation detail, make private */
	public $service_data_present = false; /**< FIXME: implementation detail, make private */
	public $outage_data_present = false; /**< FIXME: implementation detail, make private */

	private $base_path = '';
	private $auth = false;
	private static $instance = false;

	public function __construct()
	{
		parent::__construct();
		$this->base_path = Kohana::config('config.nagios_base_path');
		$this->auth = new Nagios_auth_Model();
	}

	/**
	 * Use this class as a singleton, as it is quite slow
	 *
	 * @return A Current_status_Model object
	 */
	public static function instance()
	{
		if (!self::$instance) {
			self::$instance = new Current_status_Model();
		}
		return self::$instance;
	}

	/**
	 * Check if we have current data in object
	 * Used to check if the host/service_data
	 * methods has been run. If not, all class
	 * variables will be in default state.
	 */
	public function data_present()
	{
		if (!$this->host_data_present || !$this->service_data_present) {
			return false;
		}
		return true;
	}

	/**
	 * Calculate host and service health
	 * Requires that host_status and service_status
	 * has been run before this.
	 * @return true on success, false on errors
	 */
	public function calculate_health()
	{
		if (!$this->data_present()) {
			return false;
		}

		/* calculate service health */
		if ($this->potential_service_health == 0)
			$this->percent_service_health = 0.0;
		else
			# weird calculation to match accuracy by Nagios
			$this->percent_service_health = $this->potential_service_health!=0
				? number_format((floor(($this->total_service_health/$this->potential_service_health)*1000)/10), 1)
				: 0;

			# $host_status = number_format(($up/$total)*100, 1);
		/* calculate host health */
		if ($this->potential_host_health == 0)
			$this->percent_host_health = 0.0;
		else
			$this->percent_host_health = $this->potential_host_health!=0
				? number_format(($this->total_host_health/$this->potential_host_health)*100, 1)
				: 0;

		/* calculate service latency */
		if ($this->total_service_latency == 0)
			$this->average_service_latency = 0.0;
		else
			$this->average_service_latency = $this->total_active_service_checks!=0
				? number_format($this->total_service_latency /$this->total_active_service_checks, 1)
				: 0;

		/* calculate host latency */
		if ($this->total_host_latency == 0)
			$this->average_host_latency = 0.0;
		else
			$this->average_host_latency = $this->total_active_host_checks!=0
				? number_format($this->total_host_latency/$this->total_active_host_checks, 1)
				: 0;

		/* calculate service execution time */
		if ($this->total_service_execution_time == 0.0)
			$this->average_service_execution_time = 0.0;
		else
			$this->average_service_execution_time = $this->total_active_service_checks!=0
				? number_format($this->total_service_execution_time/$this->total_active_service_checks, 1)
				: 0;

		/* calculate host execution time */
		if ($this->total_host_execution_time == 0.0)
			$this->average_host_execution_time = 0.0;
		else
			$this->average_host_execution_time = $this->total_active_host_checks!=0
			? number_format($this->total_host_execution_time/$this->total_active_host_checks, 1)
			: 0;

		return true;
	}

	/**
	 * Fetch current host status from db for current user
	 * return bool
	 */
	public function host_status()
	{
		$auth = new Nagios_auth_Model();
		$show_passive_as_active = config::get('checks.show_passive_as_active', '*');

		if ($show_passive_as_active) {
			$active_checks_condition = ' AND (active_checks_enabled=1 OR passive_checks_enabled=1) ';
			$disabled_checks_condition = ' AND (active_checks_enabled!=1 AND passive_checks_enabled!=1) ';
			
		} else {
			$active_checks_condition = ' AND active_checks_enabled=1 ';
			$disabled_checks_condition = ' AND active_checks_enabled!=1 ';
		}

		$access_check = '';
		$access_check_xtra = ' WHERE ';
		if (!$auth->view_hosts_root && $auth->id) {
			$access_check = "INNER JOIN contact_access ON host.id=contact_access.host ".
				"WHERE contact_access.service IS NULL ".
				"AND contact_access.contact=".$auth->id;
			$access_check_xtra = ' AND ';
		}

		$sql = "SELECT ".
			"(SELECT COUNT(*) FROM host ".$access_check.") AS total_hosts, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." flap_detection_enabled!=1) AS flap_disabled_hosts, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." is_flapping=1) AS flapping_hosts, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." notifications_enabled!=1) AS notification_disabled_hosts, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." event_handler_enabled!=1) AS event_handler_disabled_hosts, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." active_checks_enabled!=1) AS active_checks_disabled_hosts, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." passive_checks_enabled!=1) AS passive_checks_disabled_hosts, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_UP.$disabled_checks_condition.") AS hosts_up_disabled, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_UP." ".$active_checks_condition." ) AS hosts_up_unacknowledged, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_UP." ) AS hosts_up, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_DOWN." AND scheduled_downtime_depth>0 ) AS hosts_down_scheduled, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_DOWN." AND problem_has_been_acknowledged=1 ) AS hosts_down_acknowledged, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_DOWN.$disabled_checks_condition.") AS hosts_down_disabled, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_DOWN." AND scheduled_downtime_depth = 0 AND problem_has_been_acknowledged!=1 ".$active_checks_condition.") AS hosts_down_unacknowledged, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_DOWN.") AS hosts_down, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_UNREACHABLE." AND scheduled_downtime_depth>0 ) AS hosts_unreachable_scheduled, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_UNREACHABLE." AND problem_has_been_acknowledged=1 ) AS hosts_unreachable_acknowledged, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_UNREACHABLE.$disabled_checks_condition.") AS hosts_unreachable_disabled, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_UNREACHABLE." AND scheduled_downtime_depth = 0 AND problem_has_been_acknowledged!=1 ".$active_checks_condition.") AS hosts_unreach_unacknowledged, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_UNREACHABLE.") AS hosts_unreachable, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_PENDING ." ".$active_checks_condition.") AS hosts_pending_disabled, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." current_state=".self::HOST_PENDING .") AS hosts_pending, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." check_type=".self::HOST_CHECK_ACTIVE.") AS total_active_host_checks, \n".
			"(SELECT COUNT(*) FROM host ".$access_check.$access_check_xtra." check_type>".self::HOST_CHECK_ACTIVE.") AS total_passive_host_checks, \n".
			"(SELECT MIN(latency) FROM host ".$access_check.$access_check_xtra." last_check!=0) AS min_host_latency, \n".
			"(SELECT MAX(latency) FROM host ".$access_check.$access_check_xtra." last_check!=0) AS max_host_latency, \n".
			"(SELECT SUM(latency) FROM host ".$access_check.$access_check_xtra." last_check!=0) AS total_host_latency, \n".
			"(SELECT MIN(execution_time) FROM host ".$access_check.$access_check_xtra." last_check!=0) AS min_host_execution_time, \n".
			"(SELECT MAX(execution_time) FROM host ".$access_check.") AS max_host_execution_time, \n".
			"(SELECT SUM(execution_time) FROM host ".$access_check.") AS total_host_execution_time";
		if (!$auth->view_hosts_root && $auth->id) {
			$sql .= " FROM host ".
				"INNER JOIN contact_access ON host.id=contact_access.host ".
				"WHERE contact_access.service IS NULL ".
				"AND contact_access.contact=".$auth->id.
				" GROUP BY contact_access.contact";
		}

		$result = $this->db->query($sql);

		$host = $result->current();
		if ($result===false || !count($result)) {
			return false;
		}

		$this->total_hosts = $host->total_hosts;
		$this->flap_disabled_hosts = $host->flap_disabled_hosts;
		$this->flapping_hosts = $host->flapping_hosts;
		$this->notification_disabled_hosts = $host->notification_disabled_hosts;
		$this->event_handler_disabled_hosts = $host->event_handler_disabled_hosts;
		$this->active_checks_disabled_hosts = $host->active_checks_disabled_hosts;
		$this->passive_checks_disabled_hosts = $host->passive_checks_disabled_hosts;

		$this->hosts_up_disabled = $host->hosts_up_disabled;
		$this->hosts_up_unacknowledged = $host->hosts_up_unacknowledged;
		if ($show_passive_as_active) {
			$this->hosts_up_unacknowledged += $this->hosts_up_disabled;
			$this->hosts_up_disabled = 0;
		}

		$this->hosts_up = $host->hosts_up;
		$this->hosts_down_scheduled = $host->hosts_down_scheduled;
		$this->hosts_down_acknowledged = $host->hosts_down_acknowledged;

		$this->hosts_down_disabled = $show_passive_as_active ? 0 : $host->hosts_down_disabled;

		$this->hosts_down_unacknowledged = $host->hosts_down_unacknowledged;
		$this->hosts_down = $host->hosts_down;
		$this->hosts_down_acknowledged = $host->hosts_down_acknowledged;
		$this->hosts_down_disabled = $host->hosts_down_disabled;
		$this->hosts_down_unacknowledged = $host->hosts_down_unacknowledged;
		$this->hosts_unreachable_scheduled = $host->hosts_unreachable_scheduled;
		$this->hosts_unreachable_acknowledged = $host->hosts_unreachable_acknowledged;

		$this->hosts_unreachable_disabled = $show_passive_as_active ? 0 : $host->hosts_unreachable_disabled;

		$this->hosts_unreach_unacknowledged = $host->hosts_unreach_unacknowledged;
		$this->hosts_unreachable = $host->hosts_unreachable;

		$this->hosts_pending_disabled = $show_passive_as_active ? 0 : $host->hosts_pending_disabled;

		$this->hosts_pending = $host->hosts_pending;

		$this->total_host_health = $this->hosts_up;
		$this->potential_host_health = ($this->hosts_up + $this->hosts_down + $this->hosts_unreachable);
		$this->total_active_host_checks = $host->total_active_host_checks;
		$this->min_host_latency = number_format($host->min_host_latency, 3);
		$this->max_host_latency = number_format($host->max_host_latency, 3);
		$this->min_host_execution_time = number_format($host->min_host_execution_time, 3);
		$this->max_host_execution_time = number_format($host->max_host_execution_time, 3);
		$this->total_host_latency = number_format($host->total_host_latency, 3);
		$this->total_host_execution_time = number_format($host->total_host_execution_time, 3);
		$this->total_passive_host_checks = $host->total_passive_host_checks;

		$this->host_data_present = true;
		return true;
	}

	/**
	 * Fetch and calculate status for all services for current user
	 * @return bool
	 */
	public function service_status()
	{
		$auth = new Nagios_auth_Model();
		$show_passive_as_active = config::get('checks.show_passive_as_active', '*');

		if ($show_passive_as_active) {
			$active_checks_condition = ' AND (service.active_checks_enabled=1 OR service.passive_checks_enabled=1) ';
			$disabled_checks_condition = ' AND (service.active_checks_enabled!=1 AND service.passive_checks_enabled!=1) ';
		} else {
			$active_checks_condition = ' AND service.active_checks_enabled=1 ';
			$disabled_checks_condition = ' AND service.active_checks_enabled!=1 ';
		}

		$access_check = '';
		$access_check_xtra = ' WHERE ';
		if (!$auth->view_hosts_root && !$auth->view_services_root && $auth->id) {
			$access_check = "INNER JOIN contact_access ON service.id=contact_access.service ".
				"WHERE contact_access.service IS NOT NULL ".
				"AND contact_access.contact=".$auth->id;
			$access_check_xtra = ' AND ';
		}


		$sql = "SELECT ".
			"(SELECT COUNT(*) FROM service ".$access_check.") AS total_services, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." flap_detection_enabled!=1) AS flap_disabled_services, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." is_flapping=1) AS flapping_services, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." notifications_enabled!=1) AS notification_disabled_services, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." event_handler_enabled!=1) AS event_handler_disabled_svcs, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." active_checks_enabled!=1) AS active_checks_disabled_svcs, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." passive_checks_enabled!=1) AS passive_checks_disabled_svcs, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_OK .$disabled_checks_condition.") AS services_ok_disabled, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_OK .$active_checks_condition." ) AS services_ok_unacknowledged, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_OK ." ) AS services_ok, \n".
			"(SELECT COUNT(*) FROM service INNER JOIN host ON service.host_name=host.host_name ".$access_check.$access_check_xtra." service.current_state=".self::SERVICE_WARNING." AND (host.current_state=".self::HOST_DOWN." OR host.current_state=".self::HOST_UNREACHABLE." )) AS services_warning_host_problem, \n".
			"(SELECT COUNT(*) FROM service INNER JOIN host ON service.host_name = host.host_name ".$access_check.$access_check_xtra." service.current_state=".self::SERVICE_WARNING." AND (host.scheduled_downtime_depth + service.scheduled_downtime_depth) >0 ) AS services_warning_scheduled, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_WARNING." AND problem_has_been_acknowledged=1 ) AS services_warning_acknowledged, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_WARNING.$disabled_checks_condition.") AS services_warning_disabled, \n".
			"(SELECT COUNT(*) FROM service INNER JOIN host ON host.host_name=service.host_name ".$access_check.$access_check_xtra." host.current_state NOT IN (".self::HOST_DOWN.",".self::HOST_UNREACHABLE.") AND service.current_state=".self::SERVICE_WARNING." AND (service.scheduled_downtime_depth + host.scheduled_downtime_depth) = 0 AND service.problem_has_been_acknowledged!=1 ".$active_checks_condition.") AS svcs_warning_unacknowledged, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_WARNING.") AS services_warning, \n".
			"(SELECT COUNT(*) FROM service INNER JOIN host ON service.host_name=host.host_name ".$access_check.$access_check_xtra." service.current_state=".self::SERVICE_CRITICAL." AND (host.current_state=".self::HOST_DOWN." OR host.current_state=".self::HOST_UNREACHABLE." )) AS services_critical_host_problem, \n".
			"(SELECT COUNT(*) FROM service INNER JOIN host ON service.host_name=host.host_name ".$access_check.$access_check_xtra." service.current_state=".self::SERVICE_CRITICAL." AND (host.scheduled_downtime_depth + service.scheduled_downtime_depth)>0 ) AS services_critical_scheduled, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_CRITICAL." AND problem_has_been_acknowledged=1 ) AS services_critical_acknowledged, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_CRITICAL.$disabled_checks_condition.") AS services_critical_disabled, \n".
			"(SELECT COUNT(*) FROM service INNER JOIN host ON host.host_name=service.host_name ".$access_check.$access_check_xtra." host.current_state NOT IN (".self::HOST_DOWN.",".self::HOST_UNREACHABLE.") AND service.current_state=".self::SERVICE_CRITICAL." AND (service.scheduled_downtime_depth + host.scheduled_downtime_depth) = 0 AND service.problem_has_been_acknowledged!=1 ".$active_checks_condition.") AS svcs_critical_unacknowledged, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_CRITICAL.") AS services_critical, \n".
			"(SELECT COUNT(*) FROM service INNER JOIN host ON service.host_name=host.host_name ".$access_check.$access_check_xtra." service.current_state=".self::SERVICE_UNKNOWN." AND (host.current_state=".self::HOST_DOWN." OR host.current_state=".self::HOST_UNREACHABLE." )) AS services_unknown_host_problem, \n".
			"(SELECT COUNT(*) FROM service INNER JOIN host ON service.host_name=host.host_name ".$access_check.$access_check_xtra." service.current_state=".self::SERVICE_UNKNOWN." AND (host.scheduled_downtime_depth + service.scheduled_downtime_depth) >0 ) AS services_unknown_scheduled, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_UNKNOWN." AND problem_has_been_acknowledged=1 ) AS services_unknown_acknowledged, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_UNKNOWN.$disabled_checks_condition.") AS services_unknown_disabled, \n".
			"(SELECT COUNT(*) FROM service INNER JOIN host ON host.host_name=service.host_name ".$access_check.$access_check_xtra." host.current_state NOT IN (".self::HOST_DOWN.",".self::HOST_UNREACHABLE.") AND service.current_state=".self::SERVICE_UNKNOWN." AND (host.scheduled_downtime_depth + service.scheduled_downtime_depth) = 0 AND service.problem_has_been_acknowledged!=1 ".$active_checks_condition.") AS svcs_unknown_unacknowledged, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_UNKNOWN.") AS services_unknown, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_PENDING.$disabled_checks_condition.") AS services_pending_disabled, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." current_state=".self::SERVICE_PENDING.") AS services_pending, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." check_type=0) AS total_active_service_checks, \n".
			"(SELECT COUNT(*) FROM service ".$access_check.$access_check_xtra." check_type>0) AS total_passive_service_checks, \n".
			"(SELECT MIN(latency) FROM service ".$access_check.$access_check_xtra." last_check!=0) AS min_service_latency, \n".
			"(SELECT MAX(latency) FROM service ".$access_check.$access_check_xtra." last_check!=0) AS max_service_latency, \n".
			"(SELECT SUM(latency) FROM service ".$access_check.$access_check_xtra." last_check!=0) AS total_service_latency, \n".
			"(SELECT MIN(execution_time) FROM service ".$access_check.$access_check_xtra." last_check!=0) AS min_service_execution_time, \n".
			"(SELECT MAX(execution_time) FROM service ".$access_check.") AS max_service_execution_time, \n".
			"(SELECT SUM(execution_time) FROM service ".$access_check.") AS total_service_execution_time\n";
		if (!$auth->view_hosts_root && !$auth->view_services_root && $auth->id) {
			$sql .= " FROM service ".
				"INNER JOIN contact_access ON service.id=contact_access.service ".
				"WHERE contact_access.service IS NOT NULL ".
				"AND contact_access.contact=".$auth->id.
				" GROUP BY contact_access.contact";
		}

		$result = $this->db->query($sql);

		if ($result === false || !count($result)) {
			return false;
		}

		$svc = $result->current();

		$this->total_services = $svc->total_services;
		$this->flap_disabled_services = $svc->flap_disabled_services;
		$this->flapping_services = $svc->flapping_services;
		$this->notification_disabled_services = $svc->notification_disabled_services;
		$this->event_handler_disabled_svcs = $svc->event_handler_disabled_svcs;
		$this->active_checks_disabled_svcs  = $svc->active_checks_disabled_svcs;
		$this->passive_checks_disabled_svcs = $svc->passive_checks_disabled_svcs;

		$this->services_ok_disabled = $svc->services_ok_disabled;
		$this->services_ok_unacknowledged = $svc->services_ok_unacknowledged;
		if ($show_passive_as_active) {
			$this->services_ok_unacknowledged += $this->services_ok_disabled;
			$this->services_ok_disabled = 0;
		}

		$this->services_ok = $svc->services_ok;
		$this->services_warning_host_problem = $svc->services_warning_host_problem;
		$this->services_warning_scheduled = $svc->services_warning_scheduled;
		$this->services_warning_acknowledged = $svc->services_warning_acknowledged;

		$this->services_warning_disabled = $show_passive_as_active ? 0 : $svc->services_warning_disabled;

		$this->svcs_warning_unacknowledged = $svc->svcs_warning_unacknowledged;
		$this->services_warning = $svc->services_warning;
		$this->services_unknown_host_problem = $svc->services_unknown_host_problem;
		$this->services_unknown_scheduled = $svc->services_unknown_scheduled;
		$this->services_unknown_acknowledged = $svc->services_unknown_acknowledged;

		$this->services_unknown_disabled = $show_passive_as_active ? 0 : $svc->services_unknown_disabled;

		$this->svcs_unknown_unacknowledged = $svc->svcs_unknown_unacknowledged;
		$this->services_unknown = $svc->services_unknown;
		$this->services_critical_host_problem = $svc->services_critical_host_problem;
		$this->services_critical_scheduled = $svc->services_critical_scheduled;
		$this->services_critical_acknowledged = $svc->services_critical_acknowledged;
		$this->services_critical_disabled = $svc->services_critical_disabled;
		$this->svcs_critical_unacknowledged = $svc->svcs_critical_unacknowledged;
		$this->services_critical = $svc->services_critical;

		$this->services_pending_disabled = $show_passive_as_active ? 0 : $svc->services_pending_disabled;

		$this->services_pending = $svc->services_pending;
		$this->total_active_service_checks = $svc->total_active_service_checks;
		$this->min_service_latency = number_format($svc->min_service_latency, 3);
		$this->max_service_latency = number_format($svc->max_service_latency, 3);
		$this->min_service_execution_time = number_format($svc->min_service_execution_time, 3);
		$this->max_service_execution_time = number_format($svc->max_service_execution_time, 3);
		$this->total_service_latency = number_format($svc->total_service_latency, 2);
		$this->total_service_execution_time = number_format($svc->total_service_execution_time, 3);
		$this->total_passive_service_checks = $svc->total_passive_service_checks;

		$this->total_service_health = (2*$this->services_ok + $this->services_warning + $this->services_unknown);

		$this->potential_service_health = 2*($this->services_ok + $this->services_warning + $this->services_critical + $this->services_unknown);

		$this->service_data_present = true;
		return true;
	}

	/**
	 * Analyze all status data for hosts and services
	 * Calls
	 * - host_status()
	 * - service_status()
	 * - calculate_health()
	 * @return bool
	 */
	public function analyze_status_data()
	{
		$errors = false;
		if (!$this->host_status()) {
			$errors[] = 'Faled to fetch host_status';
		}

		if (!$this->service_status()) {
			$errors[] = 'Failed to fetch service_status';
		}

		if (!$this->calculate_health()) {
			$errors[] = 'Failed to calculate health';
		}

		return empty($errors) ? true : false;
	}

	/**
	 * 	determine what hosts are causing network outages
	 * 	and the severity for each one found
	 */
	public function find_hosts_causing_outages()
	{
		$result = $this->fetch_hosts_causing_outages();

		if (empty($result)) {
			return false;
		}
		$hosts = array();
		foreach ($result as $host){
			$hosts[] = $host;
			// Note: count() returns 1 for FALSE
			$svcs = Host_Model::get_services($host->host_name);
			$this->children_services[$host->id] = $svcs !== false ? count($svcs) : 0;
		}
		unset($result);

		/* check all hosts */
		$outages = false;
		foreach ($hosts as $host){
			$children = array(); # reset children
			$outages[] = $host->host_name;

			# check if each host has any affected child hosts
			if (!$this->get_child_hosts($host->id, $children)) {
				$this->total_nonblocking_outages++;
			} else {
				$this->total_blocking_outages++;
			}
			if (!array_key_exists($host->host_name, $this->affected_hosts)) {
				$this->affected_hosts[$host->host_name] = 0;
			}
			$this->affected_hosts[$host->host_name] += sizeof($children);
			if (array_key_exists($host->host_name, $this->unreachable_hosts)) {
				$this->unreachable_hosts[$host->host_name] = array_merge($this->unreachable_hosts[$host->host_name], $children);
			} else {
				$this->unreachable_hosts[$host->host_name] = $children;
			}
		}

		if (!empty($outages)) {
			$this->hostoutage_list = array_merge($this->hostoutage_list, $outages);
		}

		return true;
	}

	/**
	*	determine what hosts are causing network outages
	*/
	public function fetch_hosts_causing_outages()
	{
		/* user must be authorized for all hosts in order to see outages */
		if(!$this->auth->view_hosts_root)
			return false;

		$xtra_sql = "";
		if (!$this->auth->view_hosts_root) {
			$xtra_sql = "id IN (SELECT host FROM contact_access WHERE service IS NULL AND contact=".$this->auth->id.") AND ";
		}

		$sql = "SELECT * FROM host WHERE ".$xtra_sql.
				"host.current_state=".self::HOST_DOWN;

		$result = $this->db->query($sql);

		$this->outage_data_present = true;
		return $result;
	}

	/**
	 * Fetch child hosts for a host
	 * @param $host_id Id of the host to fetch children for
	 * @param $children Out variable
	 * @return True on success, false on errors
	 */
	public function get_child_hosts($host_id=false, &$children=false)
	{
		$host_id = trim($host_id);
		if (empty($host_id)) {
			return false;
		}
		$host_id = (int)$host_id;

		$user_hosts = $this->auth->get_authorized_hosts();
		if (!array_key_exists($host_id, $user_hosts)) {
			return false;
		}

		$query = "SELECT ".
				"h.id, ".
				"h.host_name, ".
				"count(s.id) as service_cnt ".
			"FROM host h ".
			"INNER JOIN host_parents hp ON h.id=hp.host ".
			"LEFT JOIN service s ON s.host_name=h.host_name ".
			"WHERE hp.parents=".$host_id.
			" GROUP BY h.id, h.host_name";

		$result = $this->db->query($query);

		$hosts = array();
		foreach ($result as $host) {
		$hosts[] = $host;
		}

		unset($result);
		foreach( $hosts as $host ) {
			$children[$host->id] = $host->host_name;
			$this->children_services[$host->id] = $host->service_cnt;
			$this->get_child_hosts($host->id, $children); # RECURSIVE
		}

		return sizeof($children);
	}

	/**
	 * Translates a given status from db to a readable string
	 */
	public static function status_text($db_status=false, $type='host')
	{
		$host_states = array(
			self::HOST_UP => 'UP',
			self::HOST_DOWN => 'DOWN',
			self::HOST_UNREACHABLE => 'UNREACHABLE',
			self::HOST_PENDING => 'PENDING'
		);

		$service_states = array(
			self::SERVICE_OK => 'OK',
			self::SERVICE_WARNING => 'WARNING',
			self::SERVICE_CRITICAL => 'CRITICAL',
			self::SERVICE_PENDING => 'PENDING',
			self::SERVICE_UNKNOWN => 'UNKNOWN'
		);

		$retval = false;
		switch ($type) {
			case 'host': case 'hostgroup':
				if (array_key_exists($db_status, $host_states)) {
					$retval = $host_states[$db_status];
				}
				break;
			case 'service': case 'servicegroup':
				if (array_key_exists($db_status, $service_states)) {
					$retval = $service_states[$db_status];
				}
				break;
		}
		return $retval;
	}

	/**
	 * List available states for host or service
	 *
	 * @param $what string 'host' (or 'service')
	 * @return array
	 */
	public function get_available_states($what='host')
	{
		switch($what) {
			case 'host':
				return array(
					self::HOST_UP => 'UP',
					self::HOST_DOWN => 'DOWN',
					self::HOST_UNREACHABLE => 'UNREACHABLE',
					self::HOST_PENDING => 'PENDING'
				);
			case 'service':
				return array(
					self::SERVICE_OK => 'OK',
					self::SERVICE_WARNING => 'WARNING',
					self::SERVICE_CRITICAL => 'CRITICAL',
					self::SERVICE_PENDING => 'PENDING',
					self::SERVICE_UNKNOWN => 'UNKNOWN'
				);
			default:
				return array();
		}
	}

	/**
	 * List available states for host or service
	 *
	 * @param $what = 'host' (or 'service')
	 * @return array
	 */
	public function available_states($what='host')
	{
		if(!in_array($what, array('host', 'service'))) {
			return array();
		}
		$host_states = array(
			self::HOST_UP,
			self::HOST_DOWN,
			self::HOST_UNREACHABLE,
			self::HOST_PENDING
		);

		$service_states = array(
			self::SERVICE_OK,
			self::SERVICE_WARNING,
			self::SERVICE_CRITICAL,
			self::SERVICE_PENDING,
			self::SERVICE_UNKNOWN
		);
		return ${$what.'_states'};
	}

	/**
	 * Fetch "status_totals" for host or service
	 */
	public function status_totals($what='host')
	{
		$hosts = false;
		$services = false;
		$sql = false;
		switch (strtolower($what)) {
			case 'host':
				$hosts = $this->auth->get_authorized_hosts();
				if (empty($hosts)) {
					return false;
				}
				$host_str = implode(', ', array_keys($hosts));
				$sql = "
					SELECT
						COUNT(current_state) AS cnt,
						current_state
					FROM
						host
					WHERE
						id IN(".$host_str.")
					GROUP BY
						current_state;";
				break;
			case 'service':
				$services = $this->auth->get_authorized_services();
				if (empty($services)) {
					return false;
				}
				$service_str = implode(', ', array_keys($services));
				$sql = "
					SELECT
						COUNT(current_state) AS cnt,
						current_state
					FROM
						service
					WHERE
						id IN(".$service_str.")
					GROUP BY
						current_state;";
				break;
		}
		if (!empty($sql)) {
			$result = $this->db->query($sql);
			return $result;
		}
		return false;
	}

	/**
	 * Fetch information regarding the various merlin nodes
	 * @param $host Unused
	 * @return Array with various info elements
	 */
	public function get_merlin_node_status($host=null)
	{
		$sql = false;
		$db = New Database();
		$cols = array('instance_name' => false, 'instance_id' => false,
				'is_running' => false, 'last_alive' => false);
		$sql = "SELECT " . implode(',', array_keys($cols)) . " FROM program_status ORDER BY instance_name";

		$result = $db->query($sql);
		$result_set = array();

		foreach ($result as $row) {
			$result_set[$row->instance_id]['instance_name'] = $row->instance_name;
			$result_set[$row->instance_id]['instance_id'] = $row->instance_id;
			$result_set[$row->instance_id]['is_running'] = $row->is_running;
			$result_set[$row->instance_id]['last_alive'] = $row->last_alive;
			$result_set[$row->instance_id]['host']['checks'] = Current_status_Model::get_merlin_num_checks("host", $row->instance_id);
			$result_set[$row->instance_id]['host']['latency'] = Current_status_Model::get_merlin_min_max_avg('host', 'latency' , $row->instance_id);
			$result_set[$row->instance_id]['host']['exectime'] = Current_status_Model::get_merlin_min_max_avg('host', 'execution_time' , $row->instance_id);
			$result_set[$row->instance_id]['service']['checks'] = Current_status_Model::get_merlin_num_checks("service", $row->instance_id);
			$result_set[$row->instance_id]['service']['latency'] = Current_status_Model::get_merlin_min_max_avg('service', 'latency' , $row->instance_id);
			$result_set[$row->instance_id]['service']['exectime'] = Current_status_Model::get_merlin_min_max_avg('service', 'execution_time' , $row->instance_id);

		}

		return $result_set;
	}

	/**
	 * Fetch the number of checks performed by a specific merlin node
	 *
	 * @param $table The table to use ('host' or 'service')
	 * @param $iid The instance id we want to check for
	 * @return Number of checks executed by the node with iid $iid
	 */
	public function get_merlin_num_checks($table, $iid=false)
	{
		$sql = false;
		$db = New Database();
		$sql = "SELECT COUNT(*) as total FROM $table";
		if ($iid !== false) {
			$sql.= " WHERE instance_id = $iid";
		}

		if (!empty($sql)){
			$result = $db->query($sql);
			foreach ($result as $row) {
				return (int)$row->total;
			}
		}
		return false;
	}

	/**
	 * Get min, average and max values from a random table
	 *
	 * @param $table Usually 'host' or 'service', though table will work
	 * @param $column The column to get values from. Must be numerical
	 * @param $iid instance_id of the Merlin node we're interested in
	 * @return A string in the format "min / avg / max"
	 */
	public function get_merlin_min_max_avg($table, $column, $iid=false)
	{
		$sql = false;
		$db = New Database();

		$sql = "SELECT min($column) as min, avg($column) as avg, max($column) as max FROM $table";
		if ($iid != false) {
			$sql.= " WHERE instance_id = $iid";
		}

		if (!empty($sql)) {
			$result = $db->query($sql);
			foreach ($result as $row) {
				return number_format($row->min, 3) . " / " . number_format($row->avg, 3) . " / " . number_format($row->max, 3);
			}
		}
		return false;


	}

}
