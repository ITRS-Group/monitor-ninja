<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate service status data
 */
class Service_Model extends Model
{
	private $auth = false;
	private $table = "service";

	/***** ACTIVE SERVICE CHECKS *****/
	public $total_active_service_checks = 0; /**< The total number of active service checks */
	public $active_service_checks_1min = 0; /**< The number of active service checks the last minute */
	public $active_service_checks_5min = 0; /**< The number of active service checks the last 5 minutes */
	public $active_service_checks_15min = 0; /**< The number of active service checks the last 15 minutes */
	public $active_service_checks_1hour = 0; /**< The number of active service checks in the last hour */
	public $active_service_checks_start = 0; /**< The number of active service checks since program start */
	public $active_service_checks_ever = 0; /**< The total number of active service checks ever FIXME: other than being retrieved separately through an extra slow query, this appears to be the same as total_active_service_checks */
	public $total_service_execution_time = 0; /**< The total active service check execution time */
	public $min_service_execution_time = 0; /**< The minimum active service check execution time */
	public $max_service_execution_time = 0; /**< The maximum active service check execution time */
	public $total_service_percent_change_a = 0; /**< The total percentage of service state changes for active checks */
	public $min_service_percent_change_a = 0; /**< The minimum percentage of service state changes for active checks */
	public $max_service_percent_change_a = 0; /**< The maximum percentage of service state changes for active checks */
	public $min_service_latency = 0; /**< The minimum active service check latency */
	public $max_service_latency = 0; /**< The maximum active service check latency */
	public $total_service_latency = 0; /**< The total active service check latency */

	/***** PASSIVE SERVICE CHECKS *****/
	public $passive_service_checks_1min = 0; /**< The number of passive service checks the last minute */
	public $total_passive_service_checks = 0; /**< The total number of passive service checks */
	public $passive_service_checks_5min = 0; /**< The number of passive service checks the last 5 minutes */
	public $passive_service_checks_15min = 0; /**< The number of passive service checks the last 15 minutes */
	public $passive_service_checks_1hour = 0; /**< The number of passive service checks the last hour */
	public $passive_service_checks_start = 0; /**< The number of passive service checks since program start */
	public $min_service_percent_change_b = 0; /**< The minium percentage of service state changes for passive checks */
	public $max_service_percent_change_b = 0; /**< The max percentage of service state changes for passive checks */
	public $total_service_percent_change_b = 0; /**< The total percentage of service state changes for passive checks */
	public $passive_service_checks_ever = 0; /**< The total number of passive service checks FIXME: other than being retrieved separately through an extra slow query, this appears to be the same as total_passive_service_checks */

	/***** CHECK STATS *****/
	public $passive_host_checks_start = 0; /**< Number of passive host checks since program start */

	public function __construct()
	{
		parent::__construct();
		$this->auth = Nagios_auth_Model::instance();
	}

	/**
	 * Useless indirection
	 */
	private static function query($db,$sql)
	{
		return $db->query($sql)->result_array();
	}

	/**
	 * Return the current service state
	 * @param $host_name The name of the host the service is on
	 * @param $service_description The service description
	 * @return Database result object on success, false on errors
	 */
	public function get($host_name=false, $service_description=false)
	{
		return Host_Model::object_status($host_name, $service_description);
	}

	/**
	*	Fetch services that belongs to a specific service- or hostgroup
	*
	*	There's an identically named method in servicegroup that does the
	*	exact same thing, except without supporting the type argument.
	*/
	public function get_services_for_group($group=false, $type='service')
	{
		$type = trim($type);
		if (strpos($type, 'group'))
			$type = substr($type, 0, -5);
		if (empty($group) || empty($type)) {
			return false;
		}
		$auth = Nagios_auth_Model::instance();
		$auth_str = '';
		if (!$auth->view_hosts_root && !$auth->view_services_root) {
			$auth_str = " INNER JOIN contact_access ca ON ca.service = s.id AND ca.contact = ".$auth->id;
		}

		switch ($type) {
			case 'service':
				$sql = "SELECT
					s.*
				FROM
					service s ".$auth_str.",
					servicegroup sg,
					service_servicegroup ssg
				WHERE
					sg.servicegroup_name=".$this->db->escape($group)." AND
					ssg.servicegroup = sg.id AND
					s.id=ssg.service
				ORDER BY
					s.service_description";
					break;
			case 'host':
				$sql = "SELECT
					s.*
				FROM
					service s ".$auth_str.",
					host h,
					hostgroup hg,
					host_hostgroup hhg
				WHERE
					hg.hostgroup_name=".$this->db->escape($group)." AND
					hhg.hostgroup = hg.id AND
					s.host_name=h.host_name AND
					hhg.host = h.id
				ORDER BY
					s.service_description";
				break;
		}
		if (!empty($sql)) {
			$result = self::query($this->db,$sql);
			return $result;
		}
		return false;
	}

	/**
	*	Fetch hosts that belongs to a specific servicegroup
	*/
	public function get_hosts_for_group($group=false, $type='servicegroup')
	{
		$type = trim($type);
		if (empty($group) || empty($type)) {
			return false;
		}
		$auth = Nagios_auth_Model::instance();
		$auth_str = '';
		if (!$auth->view_hosts_root && !$auth->view_services_root)
			$auth_str = " INNER JOIN contact_access ca ON ca.host = h.id AND ca.contact = ".$auth->id;
		$db = Database::instance();
		switch ($type) {
			case 'servicegroup':
				$sql = "SELECT
					DISTINCT h.*
				FROM
					service s,
					host h ".$auth_str.",
					servicegroup sg,
					service_servicegroup ssg
				WHERE
					sg.servicegroup_name=".$db->escape($group)." AND
					ssg.servicegroup = sg.id AND
					s.id=ssg.service AND
					h.host_name=s.host_name
				ORDER BY
					h.host_name";
				break;
			case 'hostgroup':
				$sql = "SELECT
					DISTINCT h.*
				FROM
					host h ".$auth_str.",
					hostgroup sg,
					host_hostgroup ssg
				WHERE
					sg.hostgroup_name=".$db->escape($group)." AND
					ssg.hostgroup = sg.id AND
					h.id=ssg.host
				ORDER BY
					h.host_name";
				break;
		}
		if (!empty($sql)) {
			$result = self::query($db,$sql);
			return $result;
		}
		return false;
	}

	/**
	*
	*	Fetch service info filtered on specific field and value
	*/
	public function get_where($field=false, $value=false, $limit=false, $exact=false)
	{
		if (empty($field) || empty($value)) {
			return false;
		}
		$auth = Nagios_auth_Model::instance();
		$sql_join = false;
		$sql_where = false;
		if (!$auth->view_hosts_root && !$auth->view_services_root) {
			$sql_join = ' INNER JOIN contact_access ON contact_access.service=service.id';
			$sql_where = ' AND contact_access.contact= '.(int)$auth->id;
		}

		$db = Database::instance();
		$limit_str = sql::limit_parse($limit);
		if (!$exact) {
			$value = '%' . $value . '%';
			$sql = "SELECT * FROM service ".$sql_join." WHERE LCASE(service.".$field.") LIKE LCASE(".$db->escape($value).") ".$sql_where;
		} else {
			$sql = "SELECT * FROM service ".$sql_join." WHERE service.".$field." = ".$db->escape($value)." ".$sql_where;
		}
		$sql .= $limit_str;
		$obj_info = $db->query($sql);
		return count($obj_info) > 0 ? $obj_info : false;
	}

	/**
	 * FIXME: I have no fucking clue really what this does.
	 *
	 * According to the search controller, this is used for AND searches.
	 * @param $host_name This appears to be an array of host name candidates
	 * @param $service This appears to be an array of service description candidates
	 * @param $xtra_query Extra filters to apply
	 * @param $limit If non-false, specifies maximum number of rows
	 */
	public function multi_search($host_name=array(), $service=array(), $xtra_query=false, $limit=false)
	{
		if (empty($host_name) || empty($service)) {
			return false;
		}

		$auth = Nagios_auth_Model::instance();
		$auth_str = '';
		if (!$auth->view_hosts_root && !$auth->view_services_root)
			$auth_str = " INNER JOIN contact_access ca ON ca.service = s.id AND ca.contact = ".$auth->id;

		$db = Database::instance();

		$sql = "SELECT s.*, h.current_state AS host_state FROM service s INNER JOIN host h ON s.host_name = h.host_name ".$auth_str." WHERE s.id IN (SELECT DISTINCT s.id ".
		"FROM service s WHERE ";
		$limit_str = sql::limit_parse($limit);
		$query_parts = false;
		foreach ($host_name as $host) {
			$host = '%' . $host . '%';
			$query = "LCASE(s.host_name) LIKE LCASE(".$db->escape($host).") AND (";
			$svc_query = array();
			foreach ($service as $s) {
				$s = '%' . $s . '%';
				$svc_query[] = " LCASE(s.service_description) LIKE LCASE(".$db->escape($s).") ";
			}
			if (!empty($svc_query)) {
				$query .= implode(' OR ', $svc_query);
			}
			$query .= ') ';
			$query_parts[] = $query;
		}
		if (!empty($query_parts)) {
			$sql_xtra = false;
			$sql .= '( ( '.implode(') OR (', $query_parts) . ') ) ';
			if (!empty($xtra_query) && is_array($xtra_query)) {
				foreach ($xtra_query as $x) {
					$x = '%' . $x . '%';
					$sql_xtra[] = " LCASE(h.output) LIKE LCASE(".$db->escape($x).") OR ".
						" LCASE(s.output) LIKE LCASE(".$db->escape($x).") ";
				}
				if (!empty($xtra_query) && is_array($xtra_query)) {
					$sql .= " AND (";
					$sql .= implode(' OR ', $sql_xtra);
					$sql .= " )";
				}
			}
			$sql .= ' ) ORDER BY s.host_name, s.service_description '.$limit_str;
			#echo $sql;
			$obj_info = self::query($db,$sql);
			return $obj_info && count($obj_info) > 0 ? $obj_info : false;
		}
		return false;
	}

	/**
	*	Search through several fields for a specific value
	*/
	public function search($value=false, $limit=false, $filter_service_on_state=false)
	{
		if (empty($value)) return false;
		$auth = Nagios_auth_Model::instance();
		$auth_str = '';
		if (!$auth->view_hosts_root && !$auth->view_services_root)
			$auth_str = " INNER JOIN contact_access ca ON ca.service = s.id AND ca.contact = ".$auth->id;

		$limit_str = sql::limit_parse($limit);
		$order_str = ' ORDER BY s.host_name, s.service_description';

		$sql_notes = '';
		if (config::get('config.show_notes', '*')) {
			$sql_notes = " OR LCASE(s.notes) LIKE LCASE(%s)";
		}

		$service_codes = array_flip(Current_status_Model::get_available_states('service'));
		if (is_array($value) && !empty($value)) {
			$query = false;
			$sql = false;
			foreach ($value as $val) {
				$val = '%'.$val.'%';
				$query = "SELECT id FROM service s ". $auth_str . "
					WHERE (LCASE(s.service_description) LIKE LCASE(".$this->db->escape($val).")
					OR LCASE(s.display_name) LIKE LCASE(".$this->db->escape($val).")".
					sprintf($sql_notes, $this->db->escape($val)).
					" OR LCASE(s.output) LIKE LCASE(".$this->db->escape($val)."))";

				if($filter_service_on_state) {
					// this means that "si:" has been used and we need to filter on state
					foreach($filter_service_on_state as $condition) {
						$condition = strtr(strtoupper($condition), $service_codes);
						$query .= " AND current_state = ".$this->db->escape($condition)." ";
					}
				}
				$queries[] = $query;
			}
			if (!empty($queries)) {
				$sql = 'SELECT s.*, h.current_state AS host_state, h.address FROM service s, host h WHERE s.id IN ('.
					implode(' UNION ALL ', $queries).') AND s.host_name=h.host_name'.$order_str.$limit_str;
			}
		} else {
			$value = '%'.$value.'%';
			$sql = "SELECT s.instance_id, s.id, s.host_name,
				s.service_description, s.display_name, s.is_volatile,
				s.check_command, s.initial_state, s.max_check_attempts,
				s.check_interval, s.retry_interval, s.active_checks_enabled,
				s.passive_checks_enabled, s.check_period, s.parallelize_check,
				s.obsess_over_service, s.check_freshness,
				s.freshness_threshold, s.event_handler, s.event_handler_args,
				s.event_handler_enabled, s.low_flap_threshold,
				s.high_flap_threshold, s.flap_detection_enabled,
				s.flap_detection_options, s.process_perf_data,
				s.retain_status_information, s.retain_nonstatus_information,
				s.notification_interval, s.first_notification_delay,
				s.notification_period, s.notification_options,
				s.notifications_enabled, s.stalking_options, s.notes,
				s.notes_url, s.action_url, s.icon_image, s.icon_image_alt,
				s.failure_prediction_enabled, s.problem_has_been_acknowledged,
				s.acknowledgement_type, s.host_problem_at_last_check,
				s.check_type, s.current_state, s.last_state, s.last_hard_state,
				s.output, s.long_output, s.perf_data, s.state_type,
				s.next_check, s.should_be_scheduled, s.last_check,
				s.current_attempt, s.current_event_id, s.last_event_id,
				s.current_problem_id, s.last_problem_id, s.last_notification,
				s.next_notification, s.no_more_notifications,
				s.check_flapping_recovery_notifi, s.last_state_change,
				s.last_hard_state_change, s.last_time_ok, s.last_time_warning,
				s.last_time_unknown, s.last_time_critical, s.has_been_checked,
				s.is_being_freshened, s.notified_on_unknown,
				s.notified_on_warning, s.notified_on_critical,
				s.current_notification_number, s.current_notification_id,
				s.latency, s.execution_time, s.is_executing, s.check_options,
				s.pending_flex_downtime, s.is_flapping, s.flapping_comment_id,
				s.percent_state_change, s.modified_attributes, s.max_attempts,
				s.process_performance_data, s.last_update, s.timeout,
				s.start_time, s.end_time, s.early_timeout, s.return_code,
				h.current_state AS host_state, h.address, (h.scheduled_downtime_depth + s.scheduled_downtime_depth) as scheduled_downtime_depth ".
			"FROM service s ".$auth_str.", host h WHERE s.id in (SELECT DISTINCT id FROM service s ".
			"WHERE ((LCASE(s.service_description) LIKE LCASE(".$this->db->escape($value).")".
			" OR LCASE(s.display_name) LIKE LCASE(".$this->db->escape($value).") ".
			sprintf($sql_notes, $this->db->escape($value)).
			" OR LCASE(s.output) LIKE LCASE(".$this->db->escape($value)."))))".
			" AND (s.host_name=h.host_name)".$order_str.$limit_str;
		}
		$obj_info = $this->query($this->db,$sql);
		return $obj_info;
	}

	/**
	 * Fetch and calculate status for all services for current user
	 * @return bool
	 */
	public function service_status()
	{
		$auth = Nagios_auth_Model::instance();
		$auth_str = '';
		if (!$auth->view_hosts_root && !$auth->view_services_root)
			$auth_str = " INNER JOIN contact_access ca ON ca.service = s.id AND ca.contact = ".$auth->id;
		$sql = "SELECT ".
				"s.*, ".
				"h.current_state AS host_status ".
			"FROM ".
				"service s ".$auth_str.
				", host h ".
			"WHERE ".
				"s.host_name = h.host_name ";

		$result = $this->query($this->db,$sql);
		return count($result) ? $result : false;
	}

	/**
	*	Fetch performance data for checks (active/passive)
	*/
	public function performance_data($checks_state=1)
	{
		# only allow 0/1
		$checks_state = $checks_state==1 ? 1 : 0;
		$active_passive = $checks_state == 1 ? 'active' : 'passive';
		$auth = Nagios_auth_Model::instance();
		$auth_str = '';
		if (!$auth->view_hosts_root && !$auth->view_services_root)
			$auth_str = " INNER JOIN contact_access ca ON ca.service = s.id AND ca.contact = ".$auth->id;

		$extra_sql = "";
		if ($checks_state == 1) {
			# fields only needed for active checks
			$extra_sql = ", SUM(execution_time) AS exec_time, MIN(execution_time) AS min_exec_time, ".
				"MAX(execution_time) AS max_exec_time, ".
				"MIN(latency) AS min_latency, MAX(latency) AS max_latency, SUM(latency) AS sum_latency ";
		}
		$sql = "SELECT COUNT(id) AS cnt, ".
			"SUM(percent_state_change) AS tot_perc_change, ".
			"MIN(percent_state_change) AS min_perc_change, ".
			"MAX(percent_state_change) AS max_perc_change ".
			$extra_sql .
			"FROM ".$this->table.' s '.$auth_str." ".
			"WHERE active_checks_enabled=".$checks_state;

		$result = $this->query($this->db,$sql);
		if (count($result)) {
			foreach ($result as $row) {
				if ($checks_state == 1) { # active checks
					$this->total_active_service_checks = !is_null($row->cnt) ? $row->cnt : 0;
					$this->total_service_execution_time = !is_null($row->exec_time) ? $row->exec_time : 0;
					$this->min_service_execution_time = !is_null($row->min_exec_time) ? $row->min_exec_time : 0;
					$this->max_service_execution_time = !is_null($row->max_exec_time) ? $row->max_exec_time : 0;
					$this->total_service_percent_change_a =  !is_null($row->tot_perc_change) ? $row->tot_perc_change : 0;
					$this->min_service_percent_change_a = !is_null($row->min_perc_change) ? $row->min_perc_change : 0;
					$this->max_service_percent_change_a = !is_null($row->max_perc_change) ? $row->max_perc_change : 0;
					$this->total_service_latency = !is_null($row->sum_latency) ? $row->sum_latency : 0;
					$this->min_service_latency = !is_null($row->min_latency) ? $row->min_latency : 0;
					$this->max_service_latency = !is_null($row->max_latency) ? $row->max_latency : 0;
				} else{
					$this->total_passive_service_checks = !is_null($row->cnt) ? $row->cnt : 0;
					$this->total_service_percent_change_b =  !is_null($row->tot_perc_change) ? $row->tot_perc_change : 0;
					$this->min_service_percent_change_b = !is_null($row->min_perc_change) ? $row->min_perc_change : 0;
					$this->max_service_percent_change_b = !is_null($row->max_perc_change) ? $row->max_perc_change : 0;
				}
			}
		}
		unset($sql);
		#

		$this->compute_last_check($checks_state, 60);			# checks_1min
		$this->compute_last_check($checks_state, 300);			# checks_5min
		$this->compute_last_check($checks_state, 900);			# checks_15min
		$this->compute_last_check($checks_state, 3600);			# checks_1hour
		$this->compute_last_check($checks_state, false, true);	# checks_start
		$this->compute_last_check($checks_state, false, false);	# checks_ever


	}

	/**
	*	Compute how many checks made in a specific time frame
	* 	Doesn't return anything but rather sets some class variables
	* 	depending on input
	*/
	public function compute_last_check($checks_state=1, $time_arg=false, $prog_start=false)
	{
		# only allow 0/1
		$checks_state = $checks_state==1 ? 1 : 0;
		$active_passive = $checks_state == 1 ? 'active' : 'passive';
		$auth = Nagios_auth_Model::instance();
		$auth_str = '';
		if (!$auth->view_hosts_root && !$auth->view_services_root) {
			$auth_str = " INNER JOIN contact_access ca ON ca.service = s.id AND ca.contact = ".$auth->id;
		}

		$sql = false;
		$class_var = false;
		if ($prog_start !== false) {
			$sql = "SELECT COUNT(s.id) AS cnt FROM ".$this->table." s".$auth_str.", program_status ps WHERE last_check>=ps.program_start AND s.active_checks_enabled=".$checks_state;
			$class_var = 'start';
		} else {
			$sql = "SELECT COUNT(s.id) AS cnt FROM ".$this->table." s".$auth_str." WHERE last_check>=(UNIX_TIMESTAMP()-".(int)$time_arg.") AND active_checks_enabled=".$checks_state;
			switch ($time_arg) {
				case 60:
					$class_var = '1min';
					break;
				case 300:
					$class_var = '5min';
					break;
				case 900:
					$class_var = '15min';
					break;
				case 3600:
					$class_var = '1hour';
					break;
			}
		}

		if (empty($sql) && empty($class_var)) {
			$sql = "SELECT COUNT(*) AS cnt FROM ".$this->table." WHERE last_check>0 AND active_checks_enabled=".$checks_state." ".$where;
			$class_var = 'ever';
		}
		$class_var = $active_passive.'_'.$this->table.'_checks_'.$class_var;

		$result = $this->query($this->db,$sql);
		if (count($result)) {
			foreach ($result as $row) {
				$this->{$class_var} = !is_null($row->cnt) ? $row->cnt : 0;
			}
		}
	}

	/**
	*	Generate all performance data needed for performance info page
	* 	Wraps calls to performance data for both active and passive checks
	*/
	public function get_performance_data()
	{
		$this->performance_data(1);	# generate active check performance data
		$this->performance_data(0);	# generate passive check performance data
	}

	/**
	*	Fetch info from regexp query
	*/
	public function regexp_where($field=false, $regexp=false, $limit=false)
	{
		if (empty($field) || empty($regexp)) {
			return false;
		}
		if ($field == 'service_name') {
			$field = 'service_description';
		}
		$auth = Nagios_auth_Model::instance();
		$auth_str = '';
		if (!$auth->view_hosts_root && !$auth->view_services_root)
			$auth_str = " INNER JOIN contact_access ca ON ca.service = s.id AND ca.contact = ".$auth->id;
		$limit_str = sql::limit_parse($limit);
		if (!isset($this->db) || !is_object($this->db)) {
			$db = Database::instance();
		} else {
			$db = $this->db;
		}

		$sql = "SELECT *, ".sql::concat('host_name', ';', 'service_description')." AS service_name FROM service s ".$auth_str." WHERE ".
			$field." REGEXP ".$db->escape($regexp)." ".$limit_str;
		$obj_info = self::query($db,$sql);
		return count($obj_info)>0 ? $obj_info : false;
	}
}
