<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieves and manipulates current status of hosts (and services?)
 */
class Current_status_Model extends Model
{
	const HOST_UP =  0;
	const HOST_DOWN = 1;
	const HOST_UNREACHABLE = 2;
	const HOST_PENDING = 6;

	const SERVICE_OK = 0;
	const SERVICE_WARNING = 1;
	const SERVICE_CRITICAL = 2;
	const SERVICE_UNKNOWN =  3;
	const SERVICE_PENDING = 6;
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
	 * Fetch current host status from db for current user
	 * return bool
	 */
	public function host_status()
	{
		$hostlist = Host_Model::authorized_hosts();
		if (empty($hostlist)) {
			return false;
		}
		$auth = new Nagios_auth_Model();
		if ($auth->view_hosts_root) {
			$sql = "SELECT * FROM host";
		} else {
			$sql = "SELECT h.* ".
				"FROM host AS h, contact, contact_access ca ".
				"WHERE contact.contact_name=".$this->db->escape(Auth::instance()->get_user()->username).
				" AND ca.contact=contact.id ".
				"AND ca.service IS null ".
				"AND ca.host=h.id";
		}

		$result = $this->db->query($sql);

		$show_passive_as_active = config::get('checks.show_passive_as_active');
		/* check all hosts */
		foreach ($result as $host){
			$this->total_hosts++;

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
				$this->active_checks_disabled_hosts++;

			/* passive check acceptance */
			if(!$host->passive_checks_enabled)
				$this->passive_checks_disabled_hosts++;


			/********* CHECK STATUS ********/
			$this->problem = true;
			switch ($host->current_state) {
				case self::HOST_UP:
					if (!$host->active_checks_enabled && !$show_passive_as_active)
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
					if (!$host->active_checks_enabled && !$show_passive_as_active) {
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
					if (!$host->active_checks_enabled && !$show_passive_as_active) {
						$this->hosts_unreachable_disabled++;
						$this->problem = false;
					}
					if ($this->problem == true)
						$this->hosts_unreachable_unacknowledged++;
					$this->hosts_unreachable++;
					break;
				case self::HOST_PENDING:
					if(!$host->active_checks_enabled && !$show_passive_as_active)
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
		$svc = new Service_Model();
		$result = $svc->service_status();
		if (!$result)
			return false;

		$show_passive_as_active = config::get('checks.show_passive_as_active');
		/* check all services */
		foreach ($result as $service) {
			$this->total_services++;

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
					if(!$service->active_checks_enabled && !$show_passive_as_active)
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
					if (!$service->active_checks_enabled && !$show_passive_as_active) {
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
					if (!$service->active_checks_enabled && !$show_passive_as_active) {
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
					if (!$service->active_checks_enabled && !$show_passive_as_active) {
						$this->services_critical_disabled++;
						$this->problem = false;
					}
					if ($this->problem == true)
						$this->services_critical_unacknowledged++;
					$this->services_critical++;
					break;
				case self::SERVICE_PENDING:
					if(!$service->active_checks_enabled && !$show_passive_as_active)
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
		$hostlist = Host_Model::authorized_hosts();
		if (empty($hostlist)) {
			return false;
		}
		$str_hostlist = implode(', ', $hostlist);

		$sql = "
			SELECT
				*
			FROM
				host
			WHERE
				id IN (".$str_hostlist.") AND
				(current_state!=".self::HOST_UP." AND current_state!=".self::HOST_PENDING.")";

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
