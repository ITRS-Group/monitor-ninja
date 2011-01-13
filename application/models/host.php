<?php defined('SYSPATH') OR die('No direct access allowed.');

class Host_Model extends Model {
	private $auth = false;
	private $host_list = false; # List of hosts to get status for
	private $service_host_list = false;
	private $table = "host";

	public $total_host_execution_time = 0;
	public $min_host_execution_time = 0;
	public $max_host_execution_time = 0;
	public $total_host_percent_change_a = 0;
	public $min_host_percent_change_a = 0;
	public $max_host_percent_change_a = 0;
	public $total_host_latency = 0;
	public $min_host_latency = 0;
	public $max_host_latency = 0;

	/***** ACTIVE HOST CHECKS *****/
	public $total_active_host_checks = 0;
	public $active_host_checks_1min = 0;
	public $active_host_checks_5min = 0;
	public $active_host_checks_15min = 0;
	public $active_host_checks_1hour = 0;
	public $active_host_checks_start = 0;
	public $active_host_checks_ever = 0;

	/***** PASSIVE HOST CHECKS *****/
	public $passive_host_checks_1min = 0;
	public $total_passive_host_checks = 0;
	public $passive_host_checks_5min = 0;
	public $passive_host_checks_15min = 0;
	public $passive_host_checks_1hour = 0;
	public $passive_host_checks_start = 0;
	public $passive_host_checks_ever = 0;

	public $total_host_percent_change_b = 0;
	public $min_host_percent_change_b = 0;
	public $max_host_percent_change_b = 0;

	/*
	* Only show services
	* for each host if this is set to true
	* Accepts 'all' as input, which will return
	* all hosts the user has been granted access to.
	*/
	public $show_services = false;

	public $state_filter = false; # value of current_state to filter for
	public $sort_field ='';
	public $sort_order='ASC'; # ASC/DESC
	public $service_filter = false;
	public $serviceprops = false;
	public $hostprops = false;
	public $num_per_page = false;
	public $offset = false;
	public $count = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	/**
	 * Fetch all onfo on a host. The returned object
	 * will contain all database fields for the host object.
	 * @param $name The host_name of the host
	 * @param $id The id of the host
	 * @return Host object on success, false on errors
	 */
	public function get_hostinfo($name=false, $id=false)
	{

		$id = (int)$id;
		$name = trim($name);

		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_info = false;

		if (!empty($id)) {
			if (!array_key_exists($id, $auth_hosts)) {
				return false;
			} else {
				$host_info = $this->db
					->select('*, (UNIX_TIMESTAMP() - last_state_change) AS duration, UNIX_TIMESTAMP() AS cur_time')
					->where('host', array('id' => $id));
			}
		} elseif (!empty($name)) {
			if (!array_key_exists($name, $this->auth->hosts_r)) {
				return false;
			} else {
				$host_info = $this->db
					->select('*, (UNIX_TIMESTAMP() - last_state_change) AS duration, UNIX_TIMESTAMP() AS cur_time')
					->getwhere('host', array('host_name' => $name));
			}
		} else {
			return false;
		}
		return $host_info !== false ? $host_info->current() : false;
	}

	/**
	 * Determine if user is authorized to view info on a specific host.
	 * Accepts either hostID or host_name as input
	 *
	 * @param $name The host_name of the host.
	 * @param $id The id of the host
	 * @return True if authorized, false if not.
	 */
	public function authorized_for($name=false, $id=false)
	{
		$id = (int)$id;
		$name = trim($name);
		$is_auth = false;

		$auth_hosts = $this->auth->get_authorized_hosts();

		if (!empty($id)) {
			if (!array_key_exists($id, $auth_hosts)) {
				return false;
			}
		} elseif (!empty($name)) {
			if (!array_key_exists($name, $auth->hosts_r)) {
				return false;
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	*
	*	Fetch host info filtered on specific field and value
	*/
	public function get_where($field=false, $value=false, $limit=false, $exact=false)
	{
		if (empty($field) || empty($value)) {
			return false;
		}
		if (!isset($this->auth) || !is_object($this->auth)) {
			$auth = new Nagios_auth_Model();
			$auth_hosts = $auth->get_authorized_hosts();
		} else {
			$auth_hosts = $this->auth->get_authorized_hosts();
		}
		$host_ids = array_keys($auth_hosts);
		$limit_str = sql::limit_parse($limit);
		if (!isset($this->db) || !is_object($this->db)) {
			$db = new Database();
		} else {
			$db = $this->db;
		}

		if (!$exact) {
			$value = '%' . $value . '%';
			$sql = "SELECT * FROM host WHERE LCASE(".$field.") LIKE LCASE(".$db->escape($value).") ";
		} else {
			$sql = "SELECT * FROM host WHERE ".$field." = ".$db->escape($value)." ";
		}
		$sql .= "AND id IN(".implode(',', $host_ids).") ".$limit_str;
		$host_info = $db->query($sql);
		return count($host_info)>0 ? $host_info : false;
	}

	/**
	*	Search through several fields for a specific value
	*/
	public function search($value=false, $limit=false, $xtra_query = false)
	{
		if (empty($value)) return false;
		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_ids = array_keys($auth_hosts);
		if (empty($host_ids))
			return false;

		$host_ids = implode(',', $host_ids);
		$limit_str = sql::limit_parse($limit);
		$sql_xtra = false;
		if (!empty($xtra_query)) {
			if (is_array($xtra_query)) {
				foreach ($xtra_query as $x) {
					$sql_xtra[] = " AND LCASE(output) LIKE LCASE(".$this->db->escape($x).") ";
				}
			} else {
				$sql_xtra[] = " AND LCASE(output) LIKE LCASE(".$this->db->escape($xtra_query).") ";
			}
			if (!empty($sql_xtra)) {
				$sql_xtra = implode(' ', $sql_xtra);
			}
		}

		if (is_array($value) && !empty($value)) {
			$query = false;
			$sql = false;
			foreach ($value as $val) {
				$query_str = '';
				$val = '%'.$val.'%';
				$query_str = "(SELECT DISTINCT * FROM host WHERE (LCASE(host_name)".
				" LIKE LCASE(".$this->db->escape($val).")".
				" OR LCASE(alias) LIKE LCASE(".$this->db->escape($val).")".
				" OR LCASE(display_name) LIKE LCASE(".$this->db->escape($val).")".
				" OR LCASE(address) LIKE LCASE(".$this->db->escape($val).")";
				if (!empty($sql_xtra)) {
					$query_str = $query_str.') '. $sql_xtra;
				} else {
					$query_str .= " OR LCASE(output) LIKE LCASE(".$this->db->escape($val)."))";
				}
				$query_str .= " AND id IN (".$host_ids.") )";
				$query[] = $query_str;
			}
			if (!empty($query)) {
				$sql = implode(' UNION ', $query).' ORDER BY host_name '.$limit_str;
			}
		} else {
			$value = '%'.$value.'%';
			$sql = "SELECT DISTINCT * FROM host WHERE (LCASE(host_name)".
			" LIKE LCASE(".$this->db->escape($value).")".
			" OR LCASE(alias) LIKE LCASE(".$this->db->escape($value).")".
			" OR LCASE(display_name) LIKE LCASE(".$this->db->escape($value).")".
			" OR LCASE(address) LIKE LCASE(".$this->db->escape($value).")".
			" OR LCASE(output) LIKE LCASE(".$this->db->escape($value)."))".
			" AND id IN (".$host_ids.") ORDER BY host_name ".$limit_str;
		}
		#echo Kohana::debug($sql);
		$host_info = $this->db->query($sql);
		return $host_info;
	}

	/**
	*	Fetch parents for a specific host
	*/
	public function get_parents($host_name=false)
	{
		if (empty($host_name))
			return false;
		$host_query = $this->auth->authorized_host_query();
		if ($host_query === true) {
			# don't use auth_host fields etc
			$auth_host_alias = 'h';
			$auth_from = ', host AS '.$auth_host_alias;
			$auth_where = ' AND ' . $auth_host_alias . ".host_name = '" . $host_name . "'";
		} else {
			$auth_host_alias = $host_query['host_field'];
			$auth_from = ' ,'.$host_query['from'];
			$auth_where = ' AND '.sprintf($host_query['where'], "'".$host_name."'");
		}
		$sql = "SELECT parent.* " .
			"FROM " .
				"host_parents AS hp, " .
				"host AS parent " . $auth_from .
			" WHERE ".
				$auth_host_alias . ".id=hp.host " . $auth_where .
				" AND parent.id=hp.parents " .
			"ORDER BY parent.host_name";
		$result = $this->db->query($sql);
		return $result;
	}

	/**
	 * Fetch hosts for current user and return
	 * array of host IDs
	 * @return array host IDs or false
	 */
	public static function authorized_hosts()
	{
		# fetch hosts for current user
		$auth = new Nagios_auth_Model();
		$user_hosts = $auth->get_authorized_hosts();
		$hostlist = array_keys($user_hosts);
		if (!is_array($hostlist) || empty($hostlist)) {
			return false;
		}
		sort($hostlist);
		return $hostlist;
	}

	/**
	*	Wrapper to set internal sort_field value
	*/
	public function set_sort_field($val)
	{
		$this->sort_field = trim($val);
	}

	/**
	*	Wrapper to set and validate internal sort_order value
	*/
	public function set_sort_order($val)
	{
		$val = trim($val);
		$val = strtoupper($val);
		switch ($val) {
			case 'ASC':
				$this->sort_order = $val;
				break;
			case 'DESC':
				$this->sort_order = $val;
				break;
			default: $this->sort_order = 'ASC';
		}
	}

	/**
	*	Public method to set the private host_list variable.
	*	This is because we always want to validate authorization
	* 	etc for all hosts passed in.
	*/
	public function set_host_list($host_names=false)
	{
		if (!is_array($host_names)) {
			$host_names = trim($host_names);
		}
		if (empty($host_names)) {
			return false;
		}
		$retval = false;
		if (!$this->show_services) {
			# fetch available hosts for user
			$hosts = $this->auth->get_authorized_hosts();
			$host_r = $this->auth->hosts_r; # flipped array => host_names are keys, ID is value

			if (is_array($host_names)) {
				foreach ($host_names as $temp_host) {
					$temp_host = trim($temp_host);
					if (array_key_exists($temp_host, $host_r)) {
						$retval[] = $host_r[$temp_host];
					}
				}
			} else {
				if (strtolower($host_names) === 'all') {
					# return all to fetch all relations
					# via contact and contactgroups
					$retval = 'all';
				} elseif (array_key_exists($host_names, $host_r)) {
					$retval[] = $host_r[$host_names];
				}
			}
		} else {
			$services = $this->get_host_on_services();
			if (!empty($services) && is_array($services)) {
				if (is_array($host_names)) {
					foreach ($host_names as $temp_host) {
						$temp_host = trim($temp_host);
						if (in_array($temp_host, $services)) {
							$this->service_host_list[] = $this->db->escape($temp_host);
						}
					}
				} else {
					if (strtolower($host_names) === 'all') {
						# return all to fetch all relations
						# via contact and contactgroups
						$retval = 'all';
					} elseif (in_array($host_names, $services)) {
						$this->service_host_list[] = $this->db->escape($host_names);
					}
				}
			}
		}
		$this->host_list = $retval;
	}

	/**
	*	Fetch info on what hosts current user
	* 	has services.
	*/
	public function get_host_on_services()
	{
		$services = $this->auth->get_authorized_services();

		if (empty($services)) {
			return false;
		}

		$service_hosts = array();
		foreach ($services as $s) {
			$parts = explode(';', $s);
			if (!empty($parts)) {
				if (!in_array($parts[0], $service_hosts)) {
					$service_hosts[] = $parts[0];
				}
			}
		}
		return !empty($service_hosts) ? $service_hosts : false;
	}

	/**
	 * Fetch status data for a subset of hosts
	 * (and their related services if show_services is set to true).
	 */
	public function get_host_status()
	{
		if (!empty($this->host_list) && $this->host_list !== 'all')
			$host_str = implode(', ', $this->host_list);

		$filter_sql = '';
		$filter_host_sql = false;
		$filter_service_sql = false;
		$where_str = false;
		$from = 'host ';

		if (!empty($this->state_filter)) {
			$filter_host_sql = " AND 1 << %scurrent_state & ".$this->state_filter." ";
		}
		if ($this->service_filter!==false && !empty($this->service_filter)) {
			$filter_service_sql = " AND 1 << %scurrent_state & $this->service_filter ";
		}

		if (!$this->show_services) {
			# host query part
			if (!$this->auth->view_hosts_root) {
				$from .= ', contact_access AS ca ';
				$where = ' WHERE ca.contact='.$this->auth->id.' AND ca.service IS NULL AND ca.host=host.id ';
			} else {
				$where = 'WHERE 1 ';
			}

			if (!empty($filter_host_sql)) {
				$filter_sql .= sprintf($filter_host_sql, 'host.');
			}
			if (!empty($filter_service_sql)) {
				$filter_sql .= sprintf($filter_service_sql, 'service.');
			}

			# this should never happen but added just to be on the safe side
			if ($this->serviceprops !== false) {
				$from .= ', service ';
				$where .= ' AND service.host_name=host.host_name ';
			}

			$serviceprops_sql = $this->build_service_props_query($this->serviceprops, 'service.');
			$hostprops_sql = $this->build_host_props_query($this->hostprops, 'host.');

			# remove possible table aliases just to be on the safe side here
			# should never happen but if users copy/paste an URL from a service
			# listing (changing status/service to status/host) we might run into trouble
			$this->sort_field = str_replace('s.', '',$this->sort_field);
			$this->sort_field = str_replace('h.', '',$this->sort_field);

			$this->sort_field = empty($this->sort_field) ? 'host_name' : $this->sort_field;

			# when we have a valid host_list, i.e not 'all'
			# then we should filter on these hosts
			if ($this->host_list !== 'all' && !empty($host_str)) {
				$where .= empty($where) ?  "" : " AND ";
				$where .= "host.id IN(".$host_str.") ";
			}

			# only host listing
			$sql = "SELECT DISTINCT ".
					"host.instance_id AS host_instance_id,".
					"host.id AS host_id,".
					"host.host_name, ".
					"host.address, ".
					"host.alias, ".
					"host.current_state, ".
					"host.last_check,".
					"host.next_check,".
					"host.should_be_scheduled,".
					"host.notes_url,".
					"host.notifications_enabled,".
					"host.active_checks_enabled,".
					"host.icon_image,".
					"host.icon_image_alt,".
					"host.is_flapping,".
					"host.action_url,".
					"(UNIX_TIMESTAMP() - "."host.last_state_change) AS duration,".
					"UNIX_TIMESTAMP() AS cur_time,".
					"host.current_attempt,".
					"host.max_check_attempts,".
					"host.problem_has_been_acknowledged,".
					"host.scheduled_downtime_depth,".
					"host.output,".
					"host.long_output ".
				"FROM ".$from.$where.
					$filter_sql.$hostprops_sql.$serviceprops_sql;

			$order = " ORDER BY ".$this->sort_field." ".$this->sort_order;

			$sql .= $order;

		} else {
			$from .= ', service';
			$where = '';
			if (!$this->auth->view_hosts_root || !$this->auth->view_services_root) {
				$from .= ', contact_access AS ca ';

				# match authorized services against service.host_name
				$where = ' WHERE ca.contact='.$this->auth->id.' AND service.host_name=host.host_name'.
					' AND ca.host IS NULL AND ca.service=service.id ';
			} else {
				$where = ' WHERE service.host_name=host.host_name ';
			}

			if (!empty($filter_host_sql)) {
				$filter_sql .= sprintf($filter_host_sql, 'host.');
			}
			if (!empty($filter_service_sql)) {
				$filter_sql .= sprintf($filter_service_sql, 'service.');
			}

			$serviceprops_sql = $this->build_service_props_query($this->serviceprops, 'service.');
			$hostprops_sql = $this->build_host_props_query($this->hostprops, 'host.');

			if (empty($this->sort_field)) {
				$this->sort_field = 'host_name, service_description';
			} else {
				$this->sort_field = str_replace('s.', '',$this->sort_field);
				$this->sort_field = str_replace('h.', '',$this->sort_field);
			}

			# build list of fields to fetch
			$field_list = "host.id AS host_id,".
					"host.instance_id AS host_instance_id,".
					"host.host_name,".
					"host.address,".
					"host.alias,".
					"host.current_state AS host_state,".
					"host.problem_has_been_acknowledged AS hostproblem_is_acknowledged,".
					"host.scheduled_downtime_depth AS hostscheduled_downtime_depth,".
					"host.notifications_enabled AS host_notifications_enabled,".
					"host.active_checks_enabled AS host_active_checks_enabled,".
					"host.action_url AS host_action_url,".
					"host.icon_image AS host_icon_image,".
					"host.icon_image_alt AS host_icon_image_alt,".
					"host.is_flapping AS host_is_flapping,".
					"host.notes_url AS host_nots_url,".
					"service.id AS service_id,".
					"service.instance_id AS service_instance_id,".
					"service.service_description,".
					"service.current_state,".
					"service.last_check,".
					"service.next_check,".
					"service.should_be_scheduled,".
					"service.notifications_enabled,".
					"service.active_checks_enabled,".
					"service.action_url,".
					"service.notes_url,".
					"service.icon_image,".
					"service.icon_image_alt,".
					"service.passive_checks_enabled,".
					"service.problem_has_been_acknowledged,".
					"service.scheduled_downtime_depth,".
					"service.is_flapping as service_is_flapping,".
					"(UNIX_TIMESTAMP() - service.last_state_change) AS duration, UNIX_TIMESTAMP() AS cur_time,".
					"service.current_attempt,".
					"service.max_check_attempts,".
					"service.output,".
					"service.long_output,".
					"service.output AS service_output,".
					"service.long_output AS service_long_output ";

			# when we have a valid host_list, i.e not 'all'
			# then we should filter on these hosts
			if ($this->host_list !== 'all' && !empty($host_str)) {
				$where .= empty($where) ?  "" : " AND ";
				$where .= "host.id IN(".$host_str.") ";
			}

			$sql = "SELECT ".$field_list." FROM ".
					$from.$where.
					$filter_sql.$hostprops_sql.$serviceprops_sql;

			if (is_array($this->host_list)) {
				$host_list = false;
				$hosts = $this->auth->get_authorized_hosts();
				if (!empty($this->host_list) && is_array($this->host_list)) {
					foreach ($this->host_list as $host) {
						if (array_key_exists($host, $hosts)) {
							$host_list[] = $this->db->escape($hosts[$host]);
						}
					}
				}
			}

			if (!empty($host_list) && is_array($host_list)) {
				$where_str = ' service.host_name IN ('.implode(',', $host_list).')';
			} elseif (is_array($this->service_host_list)) {
				$where_str = ' service.host_name IN ('.implode(',', $this->service_host_list).')';
			}

			if (!empty($where_str)) {
				$sql .= ' AND '.$where_str;
			}

			$sql .= " ORDER BY ".$this->sort_field." ".$this->sort_order;

		}

		if ($this->count == false && !empty($this->num_per_page) && $this->offset !== false) {
			$sql .= ' LIMIT '.$this->num_per_page.' OFFSET '.$this->offset;
		}

		if ($this->count === true && empty($filter_sql) && empty($hostprops_sql)
			&& empty($serviceprops_sql) && !empty($this->auth->id)) {

			$where_str = false;

			# this is one of the most common queries for this method
			# so we try to speed up this by making special case.
			# We are only interested in how many hosts or services there are (for this user)
			if (!$this->show_services) {

				if (is_array($this->host_list)) {
					#$hosts = $this->auth->get_authorized_hosts();
					$host_ids = implode(',', $this->host_list);
					$where_str = ' id IN('.$host_ids.')';
				}
				$sql = "SELECT COUNT(*) AS cnt FROM host";
				if (!$this->auth->view_hosts_root) {
					$sql .= " INNER JOIN contact_access ca ON host.id=ca.host ".
						"WHERE ca.contact=".$this->auth->id." AND ca.service IS NULL";
					if (!empty($where_str)) {
						$sql .= ' AND '.$where_str;
					}
				} else {
					if (!empty($where_str)) {
						$sql .= ' WHERE '.$where_str;
					}
				}
			} else {
				if (!empty($host_list) && is_array($host_list)) {
					$where_str = ' host_name IN ('.implode(',', $host_list).')';
				} elseif (is_array($this->service_host_list)) {
					$where_str = ' host_name IN ('.implode(',', $this->service_host_list).')';
				}
				$sql = "SELECT COUNT(*) AS cnt FROM service";
				if (!$this->auth->view_hosts_root && !$this->auth->view_services_root) {
					$sql .= " INNER JOIN contact_access ca ON service.id=ca.service ".
						"WHERE ca.contact=".$this->auth->id." AND ca.service IS NOT NULL";
					if (!empty($where_str)) {
						$sql .= ' AND '.$where_str;
					}
				} else {
					if (!empty($where_str)) {
						$sql .= ' WHERE '.$where_str;
					}
				}
			}
			$result = $this->db->query($sql);
			return $result ? $result->current()->cnt : 0;
		}

		$result = $this->db->query($sql);
		if ($this->count === true) {
			return $result ? count($result) : 0;
		}
		return $result;
	}

	/**
	*	Build a string to be used in a sql query to filter on different service properties
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
			$ret_str .= ' AND '.$table_alias.'check_type='.Current_status_Model::SERVICE_CHECK_PASSIVE.' ';
		if ($serviceprops & nagstat::SERVICE_ACTIVE_CHECK)
			$ret_str .= ' AND '.$table_alias.'check_type='.Current_status_Model::SERVICE_CHECK_ACTIVE.' ';
		if ($serviceprops & nagstat::SERVICE_HARD_STATE)
			$ret_str .= ' AND '.$table_alias.'state_type=1 ';
		if ($serviceprops & nagstat::SERVICE_SOFT_STATE)
			$ret_str .= ' AND '.$table_alias.'state_type=0 ';

		return $ret_str;
	}

	/**
	*	Build a string to be used in a sql query to filter on different host properties
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
			$ret_str .= ' AND '.$table_alias.'state_type=1 ';
		if ($hostprops & nagstat::HOST_SOFT_STATE)
			$ret_str .= ' AND '.$table_alias.'state_type=0 ';

		return $ret_str;
	}

	/**
	 * Fetch status for single object (host/service)
	 * Will fetch status for both host and service if
	 * both params are present.
	 */
	public static function object_status($host_name=false, $service_description=false)
	{
		$host_name = trim($host_name);
		if (empty($host_name)) {
			return false;
		}
		$auth = new Nagios_auth_Model();

		$service_description = trim($service_description);
		# check credentials for host
		$host_list = $auth->get_authorized_hosts();

		$db = new Database();
		if (empty($service_description)) {
			$sql = "SELECT *, (UNIX_TIMESTAMP() - last_state_change) AS duration, UNIX_TIMESTAMP() AS cur_time FROM host WHERE host_name='".$host_name."'";
		} else {
			$service_list = $auth->get_authorized_services();
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
					h.notes_url AS host_notes_url,
					h.notes,
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
					s.notes_url,
					s.notes AS service_notes,
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
					UNIX_TIMESTAMP() AS cur_time,
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
					s.output,
					s.long_output
				FROM
					host h,
					service s
				WHERE
					h.host_name=".$db->escape($host_name)." AND
					s.host_name=h.host_name AND
					s.service_description=".$db->escape($service_description);
		}
		$result = $db->query($sql);
		#echo $sql;
		return $result;
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
			$hostlist = self::authorized_hosts();
			if (empty($hostlist)) {
				return false;
			}
			$str_hostlist = implode(', ', $hostlist);
			$where_w_alias = "AND t.id IN (".$str_hostlist.")";
			$where = "AND id IN (".$str_hostlist.")";
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

		$result = $this->db->query($sql);
		if (count($result)) {
			foreach ($result as $row) {
				if ($checks_state == 1) { # active checks
					$this->total_active_host_checks = !is_null($row->cnt) ? $row->cnt : 0;
					$this->total_host_execution_time = !is_null($row->exec_time) ? $row->exec_time : 0;
					$this->min_host_execution_time = !is_null($row->min_exec_time) ? $row->min_exec_time : 0;
					$this->max_host_execution_time = !is_null($row->max_exec_time) ? $row->max_exec_time : 0;
					$this->total_host_percent_change_a =  !is_null($row->tot_perc_change) ? $row->tot_perc_change : 0;
					$this->min_host_percent_change_a = !is_null($row->min_perc_change) ? $row->min_perc_change : 0;
					$this->max_host_percent_change_a = !is_null($row->max_perc_change) ? $row->max_perc_change : 0;
					$this->total_host_latency = !is_null($row->sum_latency) ? $row->sum_latency : 0;
					$this->min_host_latency = !is_null($row->min_latency) ? $row->min_latency : 0;
					$this->max_host_latency = !is_null($row->max_latency) ? $row->max_latency : 0;
				} else{
					$this->total_passive_host_checks = !is_null($row->cnt) ? $row->cnt : 0;
					$this->total_host_percent_change_b =  !is_null($row->tot_perc_change) ? $row->tot_perc_change : 0;
					$this->min_host_percent_change_b = !is_null($row->min_perc_change) ? $row->min_perc_change : 0;
					$this->max_host_percent_change_b = !is_null($row->max_perc_change) ? $row->max_perc_change : 0;
				}
			}
		}
		unset($sql);

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
			$hostlist = self::authorized_hosts();
			if (empty($hostlist)) {
				return false;
			}
			$str_hostlist = implode(', ', $hostlist);
			$where_w_alias = "AND t.id IN (".$str_hostlist.")";
			$where = "AND id IN (".$str_hostlist.")";
		}

		$sql = false;
		$class_var = false;
		if ($prog_start !== false) {
			$sql = "SELECT COUNT(t.id) AS cnt FROM ".$this->table." AS t, program_status ps WHERE last_check>=ps.program_start AND t.active_checks_enabled=".$checks_state." ".$where_w_alias;
			$class_var = 'start';
		} else {
			$sql = "SELECT COUNT(*) AS cnt FROM ".$this->table." WHERE last_check>=(unix_timestamp()-".(int)$time_arg.") AND active_checks_enabled=".$checks_state." ".$where;
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

		$result = $this->db->query($sql);
		if (count($result)) {
			foreach ($result as $row) {
				$this->{$class_var} = !is_null($row->cnt) ? $row->cnt : 0;
			}
		}
	}

	public function get_hosts_for_group($name)
	{
		if (empty($name))
			return false;

		$auth_hosts = self::authorized_hosts();
		$host_str = join(',', $auth_hosts);
		$db = new Database();
		$sql = "SELECT DISTINCT h.* " .
			"FROM host h, hostgroup hg, host_hostgroup hhg " .
			"WHERE hg.hostgroup_name = " . $db->escape($name) .
			"AND hhg.hostgroup = hg.id AND h.id = hhg.host " .
			"AND h.id IN(" . $host_str . ")";

		return $db->query($sql);
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
	*	Fetch host info from regexp query
	*/
	public function regexp_where($field=false, $regexp=false, $limit=false)
	{
		if (empty($field) || empty($regexp)) {
			return false;
		}
		if (!isset($this->auth) || !is_object($this->auth)) {
			$auth = new Nagios_auth_Model();
			$auth_hosts = $auth->get_authorized_hosts();
		} else {
			$auth_hosts = $this->auth->get_authorized_hosts();
		}
		$host_ids = array_keys($auth_hosts);
		$limit_str = sql::limit_parse($limit);
		if (!isset($this->db) || !is_object($this->db)) {
			$db = new Database();
		} else {
			$db = $this->db;
		}

		$sql = "SELECT * FROM host WHERE ".$field." REGEXP ".$db->escape($regexp)." ".
		 "AND id IN(".implode(',', $host_ids).") ".$limit_str;
		$host_info = $db->query($sql);
		return count($host_info)>0 ? $host_info : false;
	}

	/**
	*	Fetch all services for a single host
	*/
	public function get_services($host_name=false)
	{
		if (empty($host_name)) {
			return false;
		}
		if (!isset($this->db) || !is_object($this->db)) {
			$db = new Database();
		} else {
			$db = $this->db;
		}
		$auth = new Nagios_auth_Model();
		$auth_svc = $auth->get_authorized_services();
		$obj_ids = array_keys($auth_svc);

		$sql = "SELECT service_description FROM service WHERE host_name = ".$db->escape($host_name).
			" AND id IN(".implode(',', $obj_ids).") ORDER BY service_description";

		$data = $db->query($sql);
		return count($data)>0 ? $data : false;
	}

	/**
	*	Create an associative array with IP/Address => host_name
	*/
	public function addr_name()
	{
		$hosts = self::authorized_hosts();
		$hostlist = false;
		$db = new Database();
		$sql = "SELECT host_name, address FROM host WHERE id IN (".implode(',', $hosts).")";
		$data = $db->query($sql);
		if (count($data)>0) {
			foreach ($data as $row) {
				$hostlist[$row->address] = $row->host_name;
			}
		}
		return $hostlist;
	}
}
