<?php defined('SYSPATH') OR die('No direct access allowed.');

class Current_status_Model extends Model {
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
	public $affected_hosts = 0;

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
	*	@name 	data_present
	*	@desc 	Check if we have current data in object
	* 			Used to check if the host/service_data
	* 			methods has been run. If not, all class
	* 			variables will be in default state.
	*
	*/
	public function data_present()
	{
		if (!$this->host_data_present || !$this->service_data_present) {
			return false;
		}
		return true;
	}

	/**
	*	@name	calculate_health
	*	@desc	Calculate host and service health
	* 			Requires that host_status and service_status
	* 			has been run before this.
	* 	@return bool
	*
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
	*	@name	get_hostlist
	*	@desc	Fetch hosts for current user and return
	* 			an array of host IDs
	*	@return array host IDs or false
	*
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
	*	@name	get_servicelist
	*	@desc	Fetch services for current user and return
	* 			an array of service IDs
	*	@return array service IDs or false
	*
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
	*	@name	host_status
	*	@desc	fetch current host status from db for current user
	* 	@return	bool
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
					# @@@FIXME assuming active_checks_enabled (was checks_enabled)
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
					if (!$host->checks_enabled) {
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
					# @@@FIXME assuming active_checks_enabled (was checks_enabled)
					if (!$host->active_checks_enabled) {
						$this->hosts_unreachable_disabled++;
						$this->problem = false;
					}
					if ($this->problem == true)
						$this->hosts_unreachable_unacknowledged++;
					$this->hosts_unreachable++;
					break;
				case self::HOST_PENDING:
					# @@@FIXME assuming active_checks_enabled (was checks_enabled)
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
	*	@name	service_status
	*	@desc	Fetch and calculate status for all services for current user
	* 	@return bool
	*
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
				s.host_name = h.id AND
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
					# @@@FIXME assuming active_checks_enabled (was checks_enabled)
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
					# @@@FIXME assuming active_checks_enabled
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
					# @@@FIXME assuming active_checks_enabled
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
					# @@@FIXME assuming active_checks_enabled
					if (!$service->active_checks_enabled) {
						$this->services_critical_disabled++;
						$this->problem = false;
					}
					if ($this->problem == true)
						$this->services_critical_unacknowledged++;
					$this->services_critical++;
					break;
				case self::SERVICE_PENDING:
					# @@@FIXME assuming active_checks_enabled
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
	*	@name	analyze_status_data
	*	@desc 	Analyze all status data for hosts and services
	* 			Calls
	* 				* host_status()
	* 				* service_status()
	* 				* calculate_health()
	*	@return bool
	*
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
	*	@name find_hosts_causing_outages
	*	@desc
	*
	* 	@@@FIXME This method is all but clear and should be checked
	* 			thouroughly before relying on it.
	* 			For one, the calling of the recursive get_child_hosts()
	* 			could be a problem and also the SQL statements.
	*/
	public function find_hosts_causing_outages()
	{
		/* determine what hosts are causing network outages */

		/* user must be authorized for all hosts in order to see outages */
		if(!$this->auth->view_hosts_root)
			return;

		# fetch hosts for current user
		$hostlist = $this->get_hostlist();
		sort($hostlist);
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
				(h.current_state!=".self::HOST_UP." AND h.current_state!=".self::HOST_PENDING.") AND
				(hh.current_state!=".self::HOST_UP." AND hh.current_state!=".self::HOST_PENDING.") AND
				h.id=hp.parents AND
				hh.id=hp.host";

		# @@@FIXME Check and verify the above SQL statement

		$result = $this->db->query($sql);

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
			$this->affected_hosts += sizeof($children);
		}

		if (!empty($outages)) {
			$this->hostoutage_list = array_merge($this->hostoutage_list, $outages);
		}

		return true;
	}

	/**
	*	@name 	get_child_hosts
	*	@desc 	Fetch child hosts for a host
	* 	@param 	int $host_id
	* 	@param 	array $children
	*
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
				h.host_name
			FROM
				host h,
				host_parents hp
			WHERE
				hp.parents=".$host_id." AND
				h.id=hp.host";
		$result = $this->db->query($query);
		if ($result->count()==0) {
			return false;
		}
		foreach ($result as $host) {
			$children[$host->id] = $host->host_name;
			$this->get_child_hosts($host->id, $children); # RECURSIVE
		}
		return sizeof($children);
	}

	/**
	*	@name 	host_status_subgroup
	*	@desc 	Verify input host ID(s) and redirect to
	* 			get_host_status()
	*	@param	mixed (array/int) host_id(s) to check
	*	@param	bool
	* 	@param 	str $sort_field field to sort on
	* 	@param 	str $sort_order ASC/DESC
	*/
	public function host_status_subgroup($host_ids = false, $show_services = false, $state_filter=false, $sort_field='', $sort_order='DESC', $service_filter=false)
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
		return $this->get_host_status($auth_host_ids, $show_services, $state_filter, $sort_field, $sort_order, $service_filter);
	}

	/**
	*	@name	host_status_subgroup_names
	*	@desc 	Verify input host_name(s) and redirect to
	* 			get_host_status()
	*	@param	mixed (array/string) host_name(s) to check
	* 			Accepts 'all' as input, which will return
	* 			all hosts the user has been granted access to.
	*	@param	bool
	* 	@param 	str $sort_field field to sort on
	* 	@param 	str $sort_order ASC/DESC
	*/
	public function host_status_subgroup_names($host_names=false, $show_services = false, $state_filter=false, $sort_field='', $sort_order='DESC', $service_filter=false)
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
		return $this->get_host_status($retval, $show_services, $state_filter, $sort_field, $sort_order, $service_filter);
	}

	/**
	*	@name 	get_host_status
	*	@desc 	Fetch status data for a subset of hosts
	* 			(and their related services if show_services is set to true).
	* 	@param	array host_list
	* 	@param	bool show_services, will only show services
	* 			for each host if this is set to true
	* 			Accepts 'all' as input, which will return
	* 			all hosts the user has been granted access to.
	* 	@param	int $state_filter value of current_state to filter for
	* 	@param 	str $sort_field field to sort on
	* 	@param 	str $sort_order ASC/DESC
	*/
	private function get_host_status($host_list = false, $show_services = false, $state_filter=false, $sort_field='', $sort_order='ASC', $service_filter=false)
	{
		if (empty($host_list)) {
			return false;
		}

		$host_str = implode(', ', $host_list);
		$sort_field = trim($sort_field);
		$state_filter = trim($state_filter);
		$filter_sql = '';
		if ($state_filter!='') {
			#$state_filter = $this->db->escape($state_filter);
			# all problems =>

			if ($state_filter>2) {
				$state_filter = '>0';
			} else {
				$state_filter = '='.$state_filter;
			}
			$filter_sql .= $show_services ? ' AND h.current_state'.$state_filter : ' AND current_state'.$state_filter;
			$filter_sql .= ' ';
		}
		if ($service_filter!==false) {
			#$service_filter = $this->db->escape($service_filter);

			if ($service_filter>3) {
				$service_filter = '>0';
			} else {
				$service_filter = '='.$service_filter;
			}
			$filter_sql .= ' AND s.current_state'.$service_filter;
			$filter_sql .= ' ';
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
					plugin_output
				FROM host
				WHERE
					id IN (".$host_str.")
					".$filter_sql."
				ORDER BY
					".$sort_field." ".$sort_order;

		} else {
			$service_list = $this->auth->get_authorized_services();
			ksort($service_list); # not required but could (possibly) speed up the query
			$service_str = implode(',', array_keys($service_list));
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
					s.plugin_output
				FROM
					host h,
					service s
				WHERE
					h.id IN (".$host_str.") AND
					s.id IN (".$service_str.") AND
					s.host_name = h.id
					".$filter_sql."
				ORDER BY
					".$sort_field." ".$sort_order;
		}
		$result = $this->db->query($sql);
		return $result;
	}

	/**
	 *	Translates a given status from db to a readable string
	 */
	public function translate_status($db_status=false, $type='host')
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
			case 'host':
				if (array_key_exists($db_status, $host_states)) {
					$retval = $host_states[$db_status];
				}
				break;
			case 'service':
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
}