<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieves and manipulates current status of hosts (and services?)
 */
class Current_status_Model extends Model
{
	const HOST_UP =  0;
	const HOST_DOWN = 1;
	const HOST_UNREACHABLE = 2;
	const HOST_PENDING = -1;

	const SERVICE_OK = 0;
	const SERVICE_WARNING = 1;
	const SERVICE_CRITICAL = 2;
	const SERVICE_UNKNOWN =  3;
	const SERVICE_PENDING = -1;
	const STATE_PENDING = -1;
	const HOST_CHECK_ACTIVE = 0;	/* Nagios performed the host check */
	const HOST_CHECK_PASSIVE = 1;	/* the host check result was submitted by an external source */
	const SERVICE_CHECK_ACTIVE = 0;
	const SERVICE_CHECK_PASSIVE = 1;


	private $auth_hosts = false;
	private $auth_services = false;

	public $flapping_services = 0;
	public $notification_disabled_services = 0;
	public $event_handler_disabled_services = 0;
	public $active_checks_disabled_services = 0;
	public $passive_checks_disabled_services = 0;

	public $services_ok_disabled = 0;
	public $services_ok_unacknowledged = 0;
	public $services_ok = 0;

	public $services_warning_host_problem = 0;
	public $services_warning_scheduled = 0;
	public $services_warning_acknowledged = 0;
	public $services_warning_disabled = 0;
	public $services_warning_unacknowledged = 0;
	public $services_warning = 0;

	public $services_unknown_host_problem = 0;
	public $services_unknown_scheduled = 0;
	public $services_unknown_acknowledged = 0;
	public $services_unknown_disabled = 0;
	public $services_unknown_unacknowledged = 0;
	public $services_unknown = 0;

	public $services_critical_host_problem = 0;
	public $services_critical_scheduled = 0;
	public $services_critical_acknowledged = 0;
	public $services_critical_disabled = 0;
	public $services_critical_unacknowledged = 0;
	public $services_critical = 0;

	public $services_pending_disabled = 0;
	public $services_pending = 0;

	public $total_service_health = 0;
	public $potential_service_health = 0;

	public $total_active_service_checks = 0;
	public $min_service_latency = -1.0;
	public $max_service_latency = -1.0;
	public $min_service_execution_time = -1.0;
	public $max_service_execution_time = -1.0;
	public $total_service_latency = 0;
	public $total_service_execution_time = 0;
	public $total_passive_service_checks = 0;
	public $total_services = 0;

	public $flap_disabled_hosts = 0;
	public $flap_disabled_services = 0;
	public $flapping_hosts = 0;
	public $notification_disabled_hosts = 0;
	public $event_handler_disabled_hosts = 0;
	public $active_checks_disabled_hosts = 0;
	public $passive_checks_disabled_hosts = 0;
	public $problem = false;

	public $hosts_up_disabled = 0;
	public $hosts_up_unacknowledged = 0;
	public $hosts_up = 0;

	public $hosts_down_scheduled = 0;
	public $hosts_down_acknowledged = 0;
	public $hosts_down_disabled = 0;
	public $hosts_down_unacknowledged = 0;
	public $hosts_down = 0;

	public $hosts_unreachable_scheduled = 0;
	public $hosts_unreachable_acknowledged = 0;
	public $hosts_unreachable_disabled = 0;
	public $hosts_unreachable_unacknowledged = 0;
	public $hosts_unreachable = 0;

	public $hosts_pending_disabled = 0;
	public $hosts_pending = 0;

	public $total_host_health = 0;
	public $potential_host_health = 0;
	public $total_active_host_checks = 0;

	public $min_host_latency = -1.0;
	public $max_host_latency = -1.0;
	public $min_host_execution_time = -1.0;
	public $max_host_execution_time = -1.0;

	public $total_host_latency = 0;
	public $total_host_execution_time = 0;
	public $total_passive_host_checks = 0;

	public $total_hosts = 0;

	# health
	public $percent_service_health = 0;
	public $percent_host_health = 0;

	public $average_service_latency = 0;
	public $average_host_latency = 0;
	public $average_service_execution_time = 0;
	public $average_host_execution_time = 0;

	public $hostoutage_list = array();
	public $total_blocking_outages = 0;
	public $total_nonblocking_outages = 0;
	public $affected_hosts = array();
	public $unreachable_hosts = array(); # hosts being unreachable because of network outages
	public $children_services = array(); # nr of services belonging to host affected by an outage

	public $host_data_present = false;
	public $service_data_present = false;

	public $base_path = '';
	private $auth = false;

	public function __construct()
	{
		parent::__construct();
		$this->base_path = Kohana::config('config.nagios_base_path');
		$this->auth = new Nagios_auth_Model();
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
			$this->percent_service_health = number_format((floor(($this->total_service_health/$this->potential_service_health)*1000)/10), 1);

			# $host_status = number_format(($up/$total)*100, 1);
		/* calculate host health */
		if ($this->potential_host_health == 0)
			$this->percent_host_health = 0.0;
		else
			$this->percent_host_health = number_format(($this->total_host_health/$this->potential_host_health)*100, 1);

		/* calculate service latency */
		if ($this->total_service_latency == 0)
			$this->average_service_latency = 0.0;
		else
			$this->average_service_latency = number_format($this->total_service_latency /$this->total_active_service_checks, 1);

		/* calculate host latency */
		if ($this->total_host_latency == 0)
			$this->average_host_latency = 0.0;
		else
			$this->average_host_latency = number_format($this->total_host_latency/$this->total_active_host_checks, 1);

		/* calculate service execution time */
		if ($this->total_service_execution_time == 0.0)
			$this->average_service_execution_time = 0.0;
		else
			$this->average_service_execution_time = number_format($this->total_service_execution_time/$this->total_active_service_checks, 1);

		/* calculate host execution time */
		if ($this->total_host_execution_time == 0.0)
			$this->average_host_execution_time = 0.0;
		else
			$this->average_host_execution_time = number_format($this->total_host_execution_time/$this->total_active_host_checks, 1);

		return true;
	}

	/**
	 * Fetch hosts for current user and return
	 * array of host IDs
	 * @return array host IDs or false
	 */
	public function get_hostlist()
	{
		# fetch hosts for current user
		$user_hosts = $this->auth->get_authorized_hosts();
		$hostlist = array_keys($user_hosts);
		if (!is_array($hostlist) || empty($hostlist)) {
			return false;
		}
		sort($hostlist);
		return $hostlist;
	}

	/**
	 * Fetch services for current user and return
	 * an array of service IDs
	 * @return array service IDs or false
	 */
	public function get_servicelist()
	{
		# fetch services for current user
		$user_services = $this->auth->get_authorized_services();
		$servicelist = array_keys($user_services);
		# servicelist is an hash array with serviceID => host_name;service_description
		# since we have the serviceID we might as well use it
		if (!is_array($servicelist) || empty($servicelist)) {
			return false;
		}
		sort($servicelist);
		return $servicelist;
	}

	/**
	 * Fetch current host status from db for current user
	 * return bool
	 */
	public function host_status()
	{
		$hostlist = $this->get_hostlist();
		if (empty($hostlist)) {
			return false;
		}
		$str_hostlist = implode(', ', $hostlist);

		$sql = "SELECT * FROM host WHERE id IN (".$str_hostlist.")";
		$result = $this->db->query($sql);

		/* check all hosts */
		foreach ($result as $host){

			/******** CHECK FEATURES *******/

			/* check flapping */
			if(!$host->flap_detection_enabled)
				$this->flap_disabled_hosts++;
			else if ($host->is_flapping)
				$this->flapping_hosts++;

			/* check notifications */
			if (!$host->notifications_enabled)
				$this->notification_disabled_hosts++;

			/* check event handler */
			if(!$host->event_handler_enabled)
				$this->event_handler_disabled_hosts++;

			/* active check execution */
			if(!$host->active_checks_enabled)
				$this->active_checks_disabled_hostss++;

			/* passive check acceptance */
			if(!$host->passive_checks_enabled)
				$this->passive_checks_disabled_hosts++;


			/********* CHECK STATUS ********/

			$this->problem = true;
			switch ($host->current_state) {
				case self::HOST_UP:
					if (!$host->active_checks_enabled)
						$this->hosts_up_disabled++;
					else
						$this->hosts_up_unacknowledged++;
					$this->hosts_up++;
					break;
				case self::HOST_DOWN:
					if ($host ->scheduled_downtime_depth > 0) {
						$this->hosts_down_scheduled++;
						$this->problem = false;
					}
					if ($host->problem_has_been_acknowledged) {
						$this->hosts_down_acknowledged++;
						$this->problem = false;
					}
					if (!$host->active_checks_enabled && !$host->passive_checks_enabled) {
						$this->hosts_down_disabled++;
						$this->problem = false;
					}
					if($this->problem == true)
						$this->hosts_down_unacknowledged++;
					$this->hosts_down++;
					break;
				case self::HOST_UNREACHABLE:
					if ($host->scheduled_downtime_depth > 0) {
						$this->hosts_unreachable_scheduled++;
						$this->problem = false;
					}
					if ($host->problem_has_been_acknowledged) {
						$this->hosts_unreachable_acknowledged++;
						$this->problem = false;
					}
					if (!$host->active_checks_enabled) {
						$this->hosts_unreachable_disabled++;
						$this->problem = false;
					}
					if ($this->problem == true)
						$this->hosts_unreachable_unacknowledged++;
					$this->hosts_unreachable++;
					break;
				case self::HOST_PENDING:
					if(!$host->active_checks_enabled)
						$this->hosts_pending_disabled++;
					$this->hosts_pending++;
					break;
			}

			/* get health stats */
			if($host->current_state == self::HOST_UP)
				$this->total_host_health++;

			if($host->current_state!=self::HOST_PENDING)
				$this->potential_host_health++;

			/* check type stats */
			if($host->check_type == self::HOST_CHECK_ACTIVE){

				$this->total_active_host_checks++;

				if ($this->min_host_latency == -1.0 || $host->latency < $this->min_host_latency)
					$this->min_host_latency = $host->latency;
				if ($this->max_host_latency == -1.0 || $host->latency > $this->max_host_latency)
					$this->max_host_latency = $host->latency;

				if ($this->min_host_execution_time == -1.0 || $host->execution_time < $this->min_host_execution_time)
					$this->min_host_execution_time = $host->execution_time;
				if ($this->max_host_execution_time == -1.0 || $host->execution_time > $this->max_host_execution_time)
					$this->max_host_execution_time = $host->execution_time;

				$this->total_host_latency += $host->latency;
				$this->total_host_execution_time += $host->execution_time;
			} else
				$this->total_passive_host_checks++;

			$this->total_hosts++;
		}
		$this->host_data_present = true;
		return true;
	}

	/**
	 * Fetch and calculate status for all services for current user
	 * @return bool
	 */
	public function service_status()
	{
		$servicelist = $this->get_servicelist();
		if (empty($servicelist)) {
			return false;
		}

		$str_servicelist = implode(', ', $servicelist);

		$sql = "
			SELECT
				s.*,
				h.current_state AS host_status
			FROM
				service s,
				host h
			WHERE
				s.host_name = h.host_name AND
				s.id IN (".$str_servicelist.")";
		$result = $this->db->query($sql);

		/* check all services */
		foreach ($result as $service) {

			/******** CHECK FEATURES *******/

			/* check flapping */
			if (!$service->flap_detection_enabled)
				$this->flap_disabled_services++;
			else if ($service->is_flapping)
				$this->flapping_services++;

			/* check notifications */
			if (!$service->notifications_enabled)
				$this->notification_disabled_services++;

			/* check event handler */
			if (!$service->event_handler_enabled)
				$this->event_handler_disabled_services++;

			/* active check execution */
			if (!$service->active_checks_enabled)
				$this->active_checks_disabled_services++;

			/* passive check acceptance */
			if (!$service->passive_checks_enabled)
				$this->passive_checks_disabled_services++;


			/********* CHECK STATUS ********/

			$this->problem = true;

			switch ($service->current_state) {
				case self::SERVICE_OK:
					if(!$service->active_checks_enabled)
						$this->services_ok_disabled++;
					else
						$this->services_ok_unacknowledged++;
					$this->services_ok++;
					break;
				case self::SERVICE_WARNING:
					if ($service->host_status == self::HOST_DOWN || $service->host_status == self::HOST_UNREACHABLE) {
						$this->services_warning_host_problem++;
						$this->problem = false;
					}
					if ($service->scheduled_downtime_depth > 0) {
						$this->services_warning_scheduled++;
						$this->problem = false;
					}
					if ($service->problem_has_been_acknowledged) {
						$this->services_warning_acknowledged++;
						$this->problem = false;
					}
					if (!$service->active_checks_enabled) {
						$this->services_warning_disabled++;
						$this->problem = false;
					}
					if ($this->problem)
						$this->services_warning_unacknowledged++;
					$this->services_warning++;
					break;
				case self::SERVICE_UNKNOWN:
					if ($service->host_status == self::HOST_DOWN || $service->host_status == self::HOST_UNREACHABLE) {
						$this->services_unknown_host_problem++;
						$this->problem = false;
					}
					if ($service->scheduled_downtime_depth > 0) {
						$this->services_unknown_scheduled++;
						$this->problem = false;
					}
					if ($service->problem_has_been_acknowledged) {
						$this->services_unknown_acknowledged++;
						$this->problem = false;
					}
					if (!$service->active_checks_enabled) {
						$this->services_unknown_disabled++;
						$this->problem = false;
					}
					if($this->problem == true)
						$this->services_unknown_unacknowledged++;
					$this->services_unknown++;
					break;
				case self::SERVICE_CRITICAL:
					if ($service->host_status == self::HOST_DOWN || $service->host_status == self::HOST_UNREACHABLE) {
						$this->services_critical_host_problem++;
						$this->problem = false;
					}
					if ($service->scheduled_downtime_depth > 0) {
						$this->services_critical_scheduled++;
						$this->problem = false;
					}
					if ($service->problem_has_been_acknowledged) {
						$this->services_critical_acknowledged++;
						$this->problem = false;
					}
					if (!$service->active_checks_enabled) {
						$this->services_critical_disabled++;
						$this->problem = false;
					}
					if ($this->problem == true)
						$this->services_critical_unacknowledged++;
					$this->services_critical++;
					break;
				case self::SERVICE_PENDING:
					if(!$service->active_checks_enabled)
						$this->services_pending_disabled++;
					$this->services_pending++;
					break;
			}

			/* get health stats */
			if ($service->current_state == self::SERVICE_OK)
				$this->total_service_health+=2;

			else if ($service->current_state == self::SERVICE_WARNING || $service->current_state == self::SERVICE_UNKNOWN)
				$this->total_service_health++;

			if ($service->current_state != self::SERVICE_PENDING)
				$this->potential_service_health+=2;


			/* calculate execution time and latency stats */
			if ($service->check_type == self::SERVICE_CHECK_ACTIVE) {
				$this->total_active_service_checks++;

				if ($this->min_service_latency == -1.0 || $service->latency < $this->min_service_latency)
					$this->min_service_latency = $service->latency;
				if ($this->max_service_latency == -1.0 || $service->latency > $this->max_service_latency)
					$this->max_service_latency = $service->latency;

				if ($this->min_service_execution_time == -1.0 || $service->execution_time < $this->min_service_execution_time)
					$this->min_service_execution_time = $service->execution_time;
				if ($this->max_service_execution_time == -1.0 || $service->execution_time > $this->max_service_execution_time)
					$this->max_service_execution_time = $service->execution_time;

				$this->total_service_latency += $service->latency;
				$this->total_service_execution_time += $service->execution_time;
			} else
				$this->total_passive_service_checks++;
			$this->total_services++;
		}
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
		/* check all hosts */
		$outages = false;
		foreach ($result as $host){
			$children = false; # reset children
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

		# fetch hosts for current user
		$hostlist = $this->get_hostlist();
		if (empty($hostlist)) {
			return false;
		}
		$str_hostlist = implode(', ', $hostlist);

		$sql = "
			SELECT
				hh.*
			FROM
				host h,
				host_parents hp,
				host hh
			WHERE
				hh.id IN (".$str_hostlist.") AND
				(hh.current_state!=".self::HOST_UP." AND hh.current_state!=".self::HOST_PENDING.") AND
				h.id=hp.parents AND
				hh.id=hp.host";

		$result = $this->db->query($sql);
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

		$query = "
			SELECT
				h.id,
				h.host_name,
				count(s.id) as service_cnt
			FROM
				host h,
				host_parents hp,
				service s
			WHERE
				hp.parents=".$host_id." AND
				h.id=hp.host AND
				s.host_name=h.host_name
			GROUP BY
				h.id";
		$result = $this->db->query($query);
		if ($result->count()==0) {
			return false;
		}
		foreach ($result as $host) {
			$children[$host->id] = $host->host_name;
			$this->children_services[$host->id] = $host->service_cnt;
			$this->get_child_hosts($host->id, $children); # RECURSIVE
		}
		return sizeof($children);
	}

	/**
	 * find all hosts that have services that
	 * are members of a specific servicegroup and that
	 * are in the specified state. Shortcut to get_group_hoststatus('service'...)
	 */
	public function get_servicegroup_hoststatus($servicegroup=false, $hoststatus=false, $servicestatus=false)
	{
		$grouptype = 'service';
		return $this->get_group_hoststatus($grouptype, $servicegroup, $hoststatus, $servicestatus);
	}

	/**
	 * find all hosts that have services that
	 * are members of a specific hostgroup and that
	 * are in the specified state. Shortcut to get_group_hoststatus('host'...)
	 */
	public function get_hostgroup_hoststatus($servicegroup=false, $hoststatus=false, $servicestatus=false)
	{
		$grouptype = 'host';
		return $this->get_group_hoststatus($grouptype, $servicegroup, $hoststatus, $servicestatus);
	}

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
	public function get_group_hoststatus($grouptype='service', $groupname=false, $hoststatus=false, $servicestatus=false)
	{
		$groupname = trim($groupname);
		if (empty($groupname)) {
			return false;
		}

		$hostlist = $this->get_hostlist();
		if (empty($hostlist)) {
			return;
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

		$hostlist_str = implode(',', $hostlist);

		$all_sql = $groupname != 'all' ? "sg.".$grouptype."group_name=".$this->db->escape($groupname)." AND" : '';

		# we need to match against different field depending on if host- or servicegroup
		$member_match = $grouptype == 'service' ? " s.id=ssg.".$grouptype." AND " : " h.id=ssg.".$grouptype." AND ";

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
		$result = $this->db->query($sql);
		return $result;
	}

	/**
	 * Verify input host ID(s) and redirect to get_host_status()
	 * @param $host_ids Array of host id's to search for
	 * @param $show_services Show services if true, otherwise ignore them.
	 * @param $state_filter Bitmask filter of host statuses
	 * @param $sort_field field to sort on
	 * @param $sort_order ASC/DESC
	 * @param $service_filter Bitmask filter of service statuses
	 */
	public function host_status_subgroup
		($host_ids = false, $show_services = false, $state_filter=false,
		 $sort_field='', $sort_order='DESC', $service_filter=false, $serviceprops=false, $hostprops=false)
	{
		if (!is_array($host_ids)) {
			$host_ids = trim($host_ids);
		}
		if (empty($host_ids)) {
			return false;
		}
		$hosts = $this->auth->get_authorized_hosts();
		$auth_host_ids = false;
		if (is_array($host_ids)) {
			foreach ($host_ids as $id) {
				if (array_key_exists($id, $hosts)) {
					$auth_host_ids[] = $id;
				}
			}
		} else {
			if (strtolower($host_ids ) === 'all') {
				# return all authenticated host IDs
				$auth_host_ids = array_keys($hosts);
			} elseif (array_key_exists($host_ids, $hosts)) {
				$auth_host_ids[] = $host_ids;
			}
		}
		return $this->get_host_status($auth_host_ids, $show_services, $state_filter, $sort_field, $sort_order, $service_filter, $serviceprops, $hostprops);
	}

	/**
	 * Verify input host_name(s) and redirect to get_host_status()
	 * @param $host_names (array/string) host_name(s) to check
	 *	Also accepts 'all' as input, which will return
	 *	all hosts the user has been granted access to.
	 * @param $show_services Show services if true, otherwise ignore them.
	 * @param $state_filter Bitmask filter of host statuses
	 * @param $sort_field field to sort on
	 * @param $sort_order ASC/DESC
	 * @param $service_filter Bitmask filter of service statuses
	 */
	public function host_status_subgroup_names
		($host_names=false, $show_services = false, $state_filter=false,
		 $sort_field='', $sort_order='DESC', $service_filter=false, $serviceprops=false, $hostprops=false)
	{
		if (!is_array($host_names)) {
			$host_names = trim($host_names);
		}
		if (empty($host_names)) {
			return false;
		}
		# fetch available hosts for user
		$hosts = $this->auth->get_authorized_hosts();
		$host_r = $this->auth->hosts_r; # flipped array => host_names are keys, ID is value
		$retval = false;
		if (is_array($host_names)) {
			foreach ($host_names as $temp_host) {
				if (array_key_exists($temp_host, $host_r)) {
					$retval[] = $host_r[$temp_host];
				}
			}
		} else {
			if (strtolower($host_names) === 'all') {
				# return all authenticated host IDs
				$retval = array_keys($hosts);
			} elseif (array_key_exists($host_names, $host_r)) {
				$retval[] = $host_r[$host_names];
			}
		}
		return $this->get_host_status($retval, $show_services, $state_filter, $sort_field, $sort_order, $service_filter, $serviceprops, $hostprops);
	}

	/**
	 * Fetch status data for a subset of hosts
	 * (and their related services if show_services is set to true).
	 * @param $host_list List of hosts to get status for
	 * @param $show_services Only show services
	 * for each host if this is set to true
	 * Accepts 'all' as input, which will return
	 * all hosts the user has been granted access to.
	 * @param $state_filter value of current_state to filter for
	 * @param $sort_field field to sort on
	 * @param $sort_order ASC/DESC
	 */
	private function get_host_status($host_list = false, $show_services = false, $state_filter=false,
		$sort_field='', $sort_order='ASC', $service_filter=false, $serviceprops=false, $hostprops=false)
	{
		if (empty($host_list)) {
			return false;
		}

		$host_str = implode(', ', $host_list);
		$sort_field = trim($sort_field);
		$state_filter = trim($state_filter);
		$filter_sql = '';
		$h = '';
		$s = !$show_services ? '' : 's.';
		if (!empty($state_filter)) {
			$h = $show_services ? 'h.' : '';
			$filter_sql .= 'AND 1 << ' . $h . "current_state & $state_filter ";
		}
		if ($service_filter!==false && !empty($service_filter)) {
			$filter_sql .= " AND 1 << s.current_state & $service_filter ";
		}
		$serviceprops_sql = $this->build_service_props_query($serviceprops, $s);
		$hostprops_sql = $this->build_host_props_query($hostprops, $h);
		if (!empty($hostprops_sql)) {
			$hostprops_sql = sprintf($hostprops_sql, $h);
		}
		if (!$show_services) {
			$sort_field = empty($sort_field) ? 'host_name' : $sort_field;
			# only host listing
			$sql = "
				SELECT
					id AS host_id,
					host_name,
					address,
					alias,
					current_state,
					last_check,
					notes_url,
					notifications_enabled,
					active_checks_enabled,
					icon_image,
					icon_image_alt,
					is_flapping,
					action_url,
					(UNIX_TIMESTAMP() - last_state_change) AS duration,
					current_attempt,
					problem_has_been_acknowledged,
					scheduled_downtime_depth,
					output AS plugin_output
				FROM host
				WHERE
					id IN (".$host_str.")
					".$filter_sql.$hostprops_sql.$serviceprops_sql."
				ORDER BY
					".$sort_field." ".$sort_order;

		} else {
			$auth_query_parts = $this->auth->authorized_service_query();
			$auth_from = '';
			$auth_where = '';
			$service_in_query = '';
			if ($auth_query_parts !== true) {
				$auth_from = ', '.$auth_query_parts['from'];

				# match authorized services against service.host_name
				$auth_where = sprintf($auth_query_parts['where'], 'tmp.host_name');
				$service_in_query = " s.id IN(SELECT tmp.id FROM service tmp ".$auth_from." WHERE ".$auth_where.") AND ";
			}

			$sort_field = empty($sort_field) ? 'h.host_name, s.service_description' : $sort_field;
			$sql = "
				SELECT
					h.id AS host_id,
					h.host_name,
					h.address,
					h.alias,
					h.current_state AS host_state,
					h.problem_has_been_acknowledged AS hostproblem_is_acknowledged,
					h.scheduled_downtime_depth AS hostscheduled_downtime_depth,
					h.notifications_enabled AS host_notifications_enabled,
					h.action_url AS host_action_url,
					h.icon_image AS host_icon_image,
					h.icon_image_alt AS host_icon_image_alt,
					h.is_flapping AS host_is_flapping,
					h.notes_url,
					s.id AS service_id,
					s.service_description,
					s.current_state,
					s.last_check,
					s.notifications_enabled,
					s.active_checks_enabled,
					s.action_url,
					s.icon_image,
					s.icon_image_alt,
					s.passive_checks_enabled,
					s.problem_has_been_acknowledged,
					s.scheduled_downtime_depth,
					s.is_flapping as service_is_flapping,
					(UNIX_TIMESTAMP() - s.last_state_change) AS duration,
					s.current_attempt,
					s.output AS plugin_output
				FROM
					host h,
					service s
				WHERE
					h.id IN (".$host_str.") AND
					".$service_in_query."
					s.host_name = h.host_name
					".$filter_sql.$hostprops_sql.$serviceprops_sql."
				ORDER BY
					".$sort_field." ".$sort_order;
		}
		$result = $this->db->query($sql);
		return $result;
	}

	/**
	*
	*
	*/
	public function build_service_props_query($serviceprops=false, $table_alias='')
	{
		if (empty($serviceprops))
			return false;
		$ret_str = false;
		if ($serviceprops & nagstat::SERVICE_SCHEDULED_DOWNTIME)
			$ret_str .= ' AND '.$table_alias.'scheduled_downtime_depth>0 ';
		if ($serviceprops & nagstat::SERVICE_NO_SCHEDULED_DOWNTIME)
			$ret_str .= ' AND '.$table_alias.'scheduled_downtime_depth<=0 ';
		if ($serviceprops & nagstat::SERVICE_STATE_ACKNOWLEDGED)
			$ret_str .= ' AND '.$table_alias.'problem_has_been_acknowledged!=0 ';
		if ($serviceprops & nagstat::SERVICE_STATE_UNACKNOWLEDGED)
			$ret_str .= ' AND '.$table_alias.'problem_has_been_acknowledged=0 ';
		if ($serviceprops & nagstat::SERVICE_CHECKS_DISABLED)
			$ret_str .= ' AND '.$table_alias.'active_checks_enabled=0 ';
		if ($serviceprops & nagstat::SERVICE_CHECKS_ENABLED)
			$ret_str .= ' AND '.$table_alias.'active_checks_enabled=1 ';
		if ($serviceprops & nagstat::SERVICE_EVENT_HANDLER_DISABLED)
			$ret_str .= ' AND '.$table_alias.'event_handler_enabled=0 ';
		if ($serviceprops & nagstat::SERVICE_EVENT_HANDLER_ENABLED)
			$ret_str .= ' AND '.$table_alias.'event_handler_enabled=1 ';
		if ($serviceprops & nagstat::SERVICE_FLAP_DETECTION_DISABLED)
			$ret_str .= ' AND '.$table_alias.'flap_detection_enabled=0 ';
		if ($serviceprops & nagstat::SERVICE_FLAP_DETECTION_ENABLED)
			$ret_str .= ' AND '.$table_alias.'flap_detection_enabled=1 ';
		if ($serviceprops & nagstat::SERVICE_IS_FLAPPING)
			$ret_str .= ' AND '.$table_alias.'is_flapping=1 ';
		if ($serviceprops & nagstat::SERVICE_IS_NOT_FLAPPING)
			$ret_str .= ' AND '.$table_alias.'is_flapping=0 ';
		if ($serviceprops & nagstat::SERVICE_NOTIFICATIONS_DISABLED)
			$ret_str .= ' AND '.$table_alias.'notifications_enabled=0 ';
		if ($serviceprops & nagstat::SERVICE_NOTIFICATIONS_ENABLED)
			$ret_str .= ' AND '.$table_alias.'notifications_enabled=1 ';
		if ($serviceprops & nagstat::SERVICE_PASSIVE_CHECKS_DISABLED)
			$ret_str .= ' AND '.$table_alias.'passive_checks_enabled=0 ';
		if ($serviceprops & nagstat::SERVICE_PASSIVE_CHECKS_ENABLED)
			$ret_str .= ' AND '.$table_alias.'passive_checks_enabled=1 ';
		if ($serviceprops & nagstat::SERVICE_PASSIVE_CHECK)
			$ret_str .= ' AND '.$table_alias.'check_type='.self::SERVICE_CHECK_PASSIVE.' ';
		if ($serviceprops & nagstat::SERVICE_ACTIVE_CHECK)
			$ret_str .= ' AND '.$table_alias.'check_type='.self::SERVICE_CHECK_ACTIVE.' ';
		if ($serviceprops & nagstat::SERVICE_HARD_STATE)
			$ret_str .= ' AND '.$table_alias.'check_type='.nagstat::HARD_STATE.' ';
		if ($serviceprops & nagstat::SERVICE_HARD_STATE)
			$ret_str .= ' AND '.$table_alias.'check_type='.nagstat::SOFT_STATE.' ';

		return $ret_str;
	}

	/**
	*
	*
	*/
	public function build_host_props_query($hostprops=false, $table_alias='')
	{
		if (empty($hostprops))
			return false;
		$ret_str = false;
		if ($hostprops & nagstat::HOST_SCHEDULED_DOWNTIME)
			$ret_str .= ' AND '.$table_alias.'scheduled_downtime_depth>0 ';
		if ($hostprops & nagstat::HOST_NO_SCHEDULED_DOWNTIME)
			$ret_str .= ' AND '.$table_alias.'scheduled_downtime_depth<=0 ';
		if ($hostprops & nagstat::HOST_STATE_ACKNOWLEDGED)
			$ret_str .= ' AND '.$table_alias.'problem_has_been_acknowledged=1 ';
		if ($hostprops & nagstat::HOST_STATE_UNACKNOWLEDGED)
			$ret_str .= ' AND '.$table_alias.'problem_has_been_acknowledged=0 ';
		if ($hostprops & nagstat::HOST_CHECKS_DISABLED)
			$ret_str .= ' AND '.$table_alias.'active_checks_enabled=0 ';
		if ($hostprops & nagstat::HOST_CHECKS_ENABLED)
			$ret_str .= ' AND '.$table_alias.'active_checks_enabled=1 ';
		if ($hostprops & nagstat::HOST_EVENT_HANDLER_DISABLED)
			$ret_str .= ' AND '.$table_alias.'event_handler_enabled=0 ';
		if ($hostprops & nagstat::HOST_EVENT_HANDLER_ENABLED)
			$ret_str .= ' AND '.$table_alias.'event_handler_enabled=1 ';
		if ($hostprops & nagstat::HOST_FLAP_DETECTION_DISABLED)
			$ret_str .= ' AND '.$table_alias.'flap_detection_enabled=0 ';
		if ($hostprops & nagstat::HOST_FLAP_DETECTION_ENABLED)
			$ret_str .= ' AND '.$table_alias.'flap_detection_enabled=1 ';
		if ($hostprops & nagstat::HOST_IS_FLAPPING)
			$ret_str .= ' AND '.$table_alias.'is_flapping=1 ';
		if ($hostprops & nagstat::HOST_IS_NOT_FLAPPING)
			$ret_str .= ' AND '.$table_alias.'is_flapping=0 ';
		if ($hostprops & nagstat::HOST_NOTIFICATIONS_DISABLED)
			$ret_str .= ' AND '.$table_alias.'notifications_enabled=0 ';
		if ($hostprops & nagstat::HOST_NOTIFICATIONS_ENABLED)
			$ret_str .= ' AND '.$table_alias.'notifications_enabled=1 ';
		if ($hostprops & nagstat::HOST_PASSIVE_CHECKS_DISABLED)
			$ret_str .= ' AND '.$table_alias.'passive_checks_enabled=0 ';
		if ($hostprops & nagstat::HOST_PASSIVE_CHECKS_ENABLED)
			$ret_str .= ' AND '.$table_alias.'passive_checks_enabled=1 ';
		if ($hostprops & nagstat::HOST_PASSIVE_CHECK)
			$ret_str .= ' AND '.$table_alias.'check_type='.nagstat::HOST_CHECK_PASSIVE.' ';
		if ($hostprops & nagstat::HOST_ACTIVE_CHECK)
			$ret_str .= ' AND '.$table_alias.'check_type='.nagstat::HOST_CHECK_ACTIVE.' ';
		if ($hostprops & nagstat::HOST_HARD_STATE)
			$ret_str .= ' AND '.$table_alias.'state_type='.nagstat::HARD_STATE.' ';
		if ($hostprops & nagstat::HOST_SOFT_STATE)
			$ret_str .= ' AND '.$table_alias.'state_type='.nagstat::SOFT_STATE.' ';

		return $ret_str;
	}

	/**
	 * Translates a given status from db to a readable string
	 */
	public function status_text($db_status=false, $type='host')
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
	 */
	public function available_states($what='host')
	{
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
	 * Fetch status for single object (host/service)
	 * Will fetch status for both host and service if
	 * both params are present.
	 */
	public function object_status($host_name=false, $service_description=false)
	{
		$host_name = trim($host_name);
		if (empty($host_name)) {
			return false;
		}

		$service_description = trim($service_description);
		# check credentials for host
		$host_list = $this->auth->get_authorized_hosts();
		if (!in_array($host_name, $host_list)) {
			return false;
		}

		if (empty($service_description)) {
			$sql = "SELECT *, (UNIX_TIMESTAMP() - last_state_change) AS duration FROM host WHERE host_name='".$host_name."'";
		} else {
			$service_list = $this->auth->get_authorized_services();
			if (!in_array($host_name.';'.$service_description, $service_list)) {
				return false;
			}

			$sql = "
				SELECT
					h.id AS host_id,
					h.host_name,
					h.address,
					h.alias,
					h.current_state AS host_state,
					h.problem_has_been_acknowledged AS host_problem_is_acknowledged,
					h.scheduled_downtime_depth AS host_scheduled_downtime_depth,
					h.notifications_enabled AS host_notifications_enabled,
					h.action_url AS host_action_url,
					h.last_host_notification,
					h.icon_image AS host_icon_image,
					h.icon_image_alt AS host_icon_image_alt,
					h.is_flapping AS host_is_flapping,
					h.state_type AS host_state_type,
					h.next_check AS host_next_check,
					h.last_update AS host_last_update,
					h.percent_state_change AS host_percent_state_change,
					h.perf_data AS host_perf_data,
					h.flap_detection_enabled AS host_flap_detection_enabled,
					h.current_notification_number AS host_current_notification_number,
					h.check_type AS host_check_type,
					h.latency AS host_latency,
					h.execution_time AS host_execution_time,
					h.last_state_change AS host_last_state_change,
					h.last_check AS host_last_check,
					h.obsess_over_host,
					h.event_handler_enabled AS host_event_handler_enabled,
					s.id AS service_id,
					s.service_description,
					s.current_state,
					s.obsess_over_service,
					s.last_check,
					s.notifications_enabled,
					s.active_checks_enabled,
					s.action_url,
					s.last_notification,
					s.flap_detection_enabled,
					s.percent_state_change,
					s.icon_image,
					s.perf_data,
					s.current_notification_number,
					s.icon_image_alt,
					s.passive_checks_enabled,
					s.problem_has_been_acknowledged,
					s.scheduled_downtime_depth,
					s.is_flapping,
					(UNIX_TIMESTAMP() - s.last_state_change) AS duration,
					s.last_state_change,
					s.current_attempt,
					s.state_type,
					s.check_type,
					s.max_attempts,
					s.last_update,
					s.latency,
					s.execution_time,
					s.next_check,
					s.event_handler_enabled,
					s.output
				FROM
					host h,
					service s
				WHERE
					h.host_name=".$this->db->escape($host_name)." AND
					s.host_name=h.host_name AND
					s.service_description=".$this->db->escape($service_description);
		}
		$result = $this->db->query($sql);
		#echo $sql;
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
		switch (strtolower($type)) {
			case 'host':
				$host_list = $this->auth->get_authorized_hosts();
				break;
			case 'service':
				$service_list = $this->auth->get_authorized_services();
				break;
			default:
				return false;
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
					gr.id=g.".$type."group;";
		} elseif (!empty($name)) {
			if (!in_array($name, ${$type.'_list'})) {
				return false;
			}
		} else {
			# abort if both id and name are empty
			return false;
		}

		$result = $this->db->query($sql);
		return $result;
	}

	/**
	 * Reads a configuration file in the format variable=value
	 * and returns it in an array.
	 * lines beginning with # are considered to be comments
	 * @param $config_file The configuration file to parse
	 * @return Array of key => value type on success, false on errors
	 */
	function parse_config_file($config_file) {
		$config_file = trim($config_file);
		if (empty($config_file)) {
			return false;
		}
		$config_file = $this->base_path.'/etc/'.$config_file;
		$buf = file_get_contents($config_file);
		if($buf === false) return(false);

		$lines = explode("\n", $buf);
		$buf = '';

		$tmp = false;
		foreach($lines as $line) {
			// skip empty lines and non-variables
			$line = trim($line);
			if(!strlen($line) || $line{0} === '#') continue;
			$str = explode('=', $line);
			if(!isset($str[1])) continue;

			// preserve all values if a variable can be specified multiple times
			if(isset($options[$str[0]]) && $options[$str[0]] !== $str[1]) {
				if(!is_array($options[$str[0]])) {
					$tmp = $options[$str[0]];
					$options[$str[0]] = array($tmp);
				}
				$options[$str[0]][] = $str[1];
				continue;
			}
			$options[$str[0]] = $str[1];
		}

		return($options);
	}

	/**
	 * Finds all hosts and services that are members of a specific host- or servicegroup
	 * Will return all info on the hosts but only service_description and current_state for services
	 *
	 * @param str $grouptype [host|service]
	 * @param str $groupname
	 * @return db result
	 */
	public function get_group_info($grouptype='service', $groupname=false)
	{
		$groupname = trim($groupname);
		if (empty($groupname)) {
			return false;
		}

		$hostlist = $this->get_hostlist();
		if (empty($hostlist)) {
			return;
		}

		$hostlist_str = implode(',', $hostlist);

		$all_sql = $groupname != 'all' ? "sg.".$grouptype."group_name=".$this->db->escape($groupname)." AND" : '';

		# we need to match against different field depending on if host- or servicegroup
		$member_match = $grouptype == 'service' ? " s.id=ssg.".$grouptype." AND " : " h.id=ssg.".$grouptype." AND ";

		$sql = "
			SELECT
				h.*,
				s.current_state AS service_state,
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
				h.host_name=s.host_name AND
				h.id IN (".$hostlist_str.")
			ORDER BY
				h.host_name,
				s.service_description,
				s.current_state;";
		$result = $this->db->query($sql);
		return $result;
	}
}
