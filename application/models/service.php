<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate service status data
 */
class Service_Model extends Model
{
	private $auth = false;
	private $table = "service";

	/***** ACTIVE SERVICE CHECKS *****/
	public $total_active_service_checks = 0;
	public $active_service_checks_1min = 0;
	public $active_service_checks_5min = 0;
	public $active_service_checks_15min = 0;
	public $active_service_checks_1hour = 0;
	public $active_service_checks_start = 0;
	public $active_service_checks_ever = 0;
	public $total_service_execution_time = 0;
	public $min_service_execution_time = 0;
	public $max_service_execution_time = 0;
	public $total_service_percent_change_a = 0;
	public $min_service_percent_change_a = 0;
	public $max_service_percent_change_a = 0;
	public $min_service_latency = 0;
	public $max_service_latency = 0;
	public $total_service_latency = 0;

	/***** PASSIVE SERVICE CHECKS *****/
	public $passive_service_checks_1min = 0;
	public $total_passive_service_checks = 0;
	public $passive_service_checks_5min = 0;
	public $passive_service_checks_15min = 0;
	public $passive_service_checks_1hour = 0;
	public $passive_service_checks_start = 0;
	public $min_service_percent_change_b = 0;
	public $max_service_percent_change_b = 0;
	public $total_service_percent_change_b = 0;
	public $passive_service_checks_ever = 0;

	/***** CHECK STATS *****/
	public $passive_host_checks_start = 0;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

        /**
         Workaround for PDO queries: runs $db->query($sql), copies
         the resultset to an array, closes the resultset, and returns
         the array.
         */
        private static function query($db,$sql)
        {
            $res = $db->query($sql);
            $rc = array();
            foreach($res as $row) {
                $rc[] = $row;
            }
            unset($res);
            return $rc;
        }

	/**
	 * Fetch info on a specific service by either id or name
	 * @param $id Id of the service
	 * @param $name Name of the service. This must be in the form
	 *              hostname;service_description
	 * @return Service object on success, false on errors
	 */
	public function get($host_name=false, $service_description=false)
	{
		return Host_Model::object_status($host_name, $service_description);
	}

	/**
	*	Fetch services that belongs to a specific service- or hostgroup
	*/
	public function get_services_for_group($group=false, $type='service')
	{
		$type = trim($type);
		if (empty($group) || empty($type)) {
			return false;
		}
		$auth = new Nagios_auth_Model();
		$auth_services = self::authorized_services();
		$service_str = !empty($auth_services) ? implode(', ', array_keys($auth_services)) : false;
		if ($auth->view_hosts_root || $auth->view_services_root) {
			$auth_str = '';
		} elseif (!empty($service_str)) {
			$auth_str = " AND s.id IN(".$service_str.")";
		} else {
			# not authorized_for all hosts or services and no hosts
			# so we return false here.
			return false;
		}

		switch ($type) {
			case 'service':
				$sql = "SELECT
					s.*
				FROM
					service s,
					servicegroup sg,
					service_servicegroup ssg
				WHERE
					sg.servicegroup_name=".$this->db->escape($group)." AND
					ssg.servicegroup = sg.id AND
					s.id=ssg.service ".$auth_str."
				ORDER BY
					s.service_description";
					break;
			case 'host':
				$sql = "SELECT
					s.*
				FROM
					service s,
					host h,
					hostgroup hg,
					host_hostgroup hhg
				WHERE
					hg.hostgroup_name=".$this->db->escape($group)." AND
					hhg.hostgroup = hg.id AND
					s.host_name=h.host_name AND
					hhg.host = h.id ".$auth_str."
				ORDER BY
					s.service_description";
				break;
		}
		if (!empty($sql)) {
                        $result = self::query($db,$sql);
			return count($result) > 0 ? $result : false;
		}
		return false;
	}

	/**
	*	Fetch services that belongs to a specific service- or hostgroup
	*/
	public function get_hosts_for_group($group=false, $type='servicegroup')
	{
		$type = trim($type);
		if (empty($group) || empty($type)) {
			return false;
		}
		$auth_hosts = Host_Model::authorized_hosts();
		$host_str = implode(', ', array_values($auth_hosts));
		$db = new Database();
		switch ($type) {
			case 'servicegroup':
				$sql = "SELECT
					DISTINCT h.*
				FROM
					service s,
					host h,
					servicegroup sg,
					service_servicegroup ssg
				WHERE
					sg.servicegroup_name=".$db->escape($group)." AND
					ssg.servicegroup = sg.id AND
					s.id=ssg.service AND
					h.host_name=s.host_name AND
					h.id IN(".$host_str.")
				ORDER BY
					h.host_name";
				case 'hostgroup':
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
		$auth = new Nagios_auth_Model();
		$sql_join = false;
		$sql_where = false;
		if (!$auth->view_hosts_root && !$auth->view_services_root) {
			$obj_ids = self::authorized_services();
			$sql_join = ' INNER JOIN contact_access ON contact_access.service=service.id';
			$sql_where = ' AND contact_access.contact= '.(int)$auth->id;
		}

		$db = new Database();
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
	*
	*
	*/
	public function multi_search($host_name=array(), $service=array(), $xtra_query=false, $limit=false)
	{
		if (empty($host_name) || empty($service)) {
			return false;
		}

		$auth = new Nagios_auth_Model();
		if (!$auth->view_hosts_root && !$auth->view_services_root) {
			$obj_ids = self::authorized_services();
		}
		$db = new Database();

		$sql = "SELECT s.*, h.current_state AS host_state FROM service s INNER JOIN host h ON s.host_name = h.host_name WHERE s.id IN (SELECT DISTINCT s.id ".
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
			if (!$auth->view_hosts_root && !$auth->view_services_root) {
				$sql .= " AND s.id IN(".implode(',', $obj_ids).") ";
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
	public function search($value=false, $limit=false, $xtra_query=false)
	{
		if (empty($value)) return false;
		$auth_obj = self::authorized_services();
		$obj_ids = is_array($auth_obj) ? implode(',',$auth_obj) : false;
		if (empty($obj_ids))
			return false;

		$limit_str = sql::limit_parse($limit);
		$order_str = ' ORDER BY s.host_name, s.service_description';
		if (is_array($value) && !empty($value)) {
			$query = false;
			$sql = false;
			foreach ($value as $val) {
				$val = '%'.$val.'%';
				$query[] = "SELECT id FROM service ".
			"WHERE (LCASE(s.host_name) LIKE LCASE(".$this->db->escape($val).")".
			" OR LCASE(s.service_description) LIKE LCASE(".$this->db->escape($val).")".
			" OR LCASE(s.display_name) LIKE LCASE(".$this->db->escape($val).")".
			" OR LCASE(s.output) LIKE LCASE(".$this->db->escape($val)."))".
			" AND s.id IN (".$obj_ids.")";
			}
			if (!empty($query)) {
				$sql = 'SELECT s.*, h.current_state AS host_state, h.address FROM service s, host h WHERE s.id IN ('.
					implode(' UNION ALL ', $query).') AND s.host_name=h.host_name'.$order_str.$limit_str;
			}
		} else {
			$value = '%'.$value.'%';
			$sql = "SELECT s.*, h.current_state AS host_state, h.address ".
			"FROM service s, host h WHERE s.id in (SELECT DISTINCT id FROM service s ".
			"WHERE ((LCASE(s.host_name) LIKE LCASE(".$this->db->escape($value).")".
			" OR LCASE(s.service_description) LIKE LCASE(".$this->db->escape($value).")".
			" OR LCASE(s.display_name) LIKE LCASE(".$this->db->escape($value).") ".
			" OR LCASE(s.output) LIKE LCASE(".$this->db->escape($value)."))))".
			" AND (s.host_name=h.host_name)".
			" AND s.id IN (".$obj_ids.") ".$order_str.$limit_str;
		}
		$obj_info = $this->query($this->db,$sql);
		return $obj_info;
	}

	/**
	 * Fetch services for current user and return
	 * an array of service IDs
	 * @return array service IDs or false
	 */
	public static function authorized_services()
	{
		# fetch services for current user
		$auth = new Nagios_auth_Model();
		$user_services = $auth->get_authorized_services();
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
	 * Fetch and calculate status for all services for current user
	 * @return bool
	 */
	public function service_status()
	{
		$auth = new Nagios_auth_Model();
		if ($auth->view_hosts_root || $auth->view_services_root) {
			# user authorized for all services
			$sql = "SELECT ".
				"s.*, ".
				"h.current_state AS host_status ".
			"FROM ".
				"service s, ".
				"host h ".
			"WHERE ".
				"s.host_name = h.host_name ";
		} else {
			$servicelist = self::authorized_services();
			if (empty($servicelist)) {
				return false;
			}
			# @@@FIXME: Redesign needed to get all services via contact and contactgroup and hosts
			$str_servicelist = implode(', ', $servicelist);
			$where = "AND s.id IN (".$str_servicelist.")";
			$sql = "SELECT ".
					"s.*, ".
					"h.current_state AS host_status ".
				"FROM ".
					"service s, ".
					"host h ".
				"WHERE ".
					"s.host_name = h.host_name " . $where;
		}

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
		$auth = new Nagios_auth_Model();
		if ($auth->view_hosts_root || $auth->view_services_root) {
			$where = '';
			$where_w_alias = '';
		} else {
			$servicelist = self::authorized_services();
			if (empty($servicelist)) {
				return false;
			}
			$str_servicelist = implode(', ', $servicelist);
			$where_w_alias = "AND t.id IN (".$str_servicelist.")";
			$where = "AND id IN (".$str_servicelist.")";
		}

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
			"FROM ".$this->table." ".
			"WHERE active_checks_enabled=".$checks_state." ".$where;

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
		$auth = new Nagios_auth_Model();
		if ($auth->view_hosts_root || $auth->view_services_root) {
			$where = '';
			$where_w_alias = '';
		} else {
			$servicelist = self::authorized_services();
			if (empty($servicelist)) {
				return false;
			}
			$str_servicelist = implode(', ', $servicelist);
			$where_w_alias = "AND t.id IN (".$str_servicelist.")";
			$where = "AND id IN (".$str_servicelist.")";
		}

		$sql = false;
		$class_var = false;
		if ($prog_start !== false) {
			$sql = "SELECT COUNT(t.id) AS cnt FROM ".$this->table." t, program_status ps WHERE last_check>=ps.program_start AND t.active_checks_enabled=".$checks_state." ".$where_w_alias;
			$class_var = 'start';
		} else {
			$sql = "SELECT COUNT(*) AS cnt FROM ".$this->table." WHERE last_check>=(UNIX_TIMESTAMP()-".(int)$time_arg.") AND active_checks_enabled=".$checks_state." ".$where;
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
		if (!isset($this->auth) || !is_object($this->auth)) {
			$auth = new Nagios_auth_Model();
			$auth_obj = $auth->get_authorized_services();
		} else {
			$auth_obj = $this->auth->get_authorized_services();
		}
		$obj_ids = array_keys($auth_obj);
		$limit_str = sql::limit_parse($limit);
		if (!isset($this->db) || !is_object($this->db)) {
			$db = new Database();
		} else {
			$db = $this->db;
		}

		$sql = "SELECT *, ".sql::concat('host_name', ';', 'service_description')." AS service_name FROM service WHERE ".
			$field." REGEXP ".$db->escape($regexp)." ".
		 	"AND id IN(".implode(',', $obj_ids).") ".$limit_str;
		$obj_info = self::query($db,$sql);
		return count($obj_info)>0 ? $obj_info : false;
	}
}
