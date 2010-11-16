<?php defined('SYSPATH') OR die('No direct access allowed.');

class Host_Model extends Model {
	private $auth = false;
	private $host_list = false; # List of hosts to get status for
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
				# return all to fetch all relations
				# via contact and contactgroups
				$retval = 'all';
			} elseif (array_key_exists($host_names, $host_r)) {
				$retval[] = $host_r[$host_names];
			}
		}
		$this->host_list = $retval;
	}

	/**
	 * Fetch status data for a subset of hosts
	 * (and their related services if show_services is set to true).
	 * FIXME: rewrite me please!
	 */
	public function get_host_status()
	{
		if (!empty($this->host_list) && $this->host_list !== 'all')
			$host_str = implode(', ', $this->host_list);

		$filter_sql = '';
		$filter_host_sql = false;
		$filter_service_sql = false;
		$h = $this->show_services ? 'auth_host.' : '';
		$s = !$this->show_services ? '' : 'auth_service.';
		if (!empty($this->state_filter)) {
			$filter_host_sql = " AND 1 << %scurrent_state & ".$this->state_filter." ";
		}
		if ($this->service_filter!==false && !empty($this->service_filter)) {
			$filter_service_sql = " AND 1 << %scurrent_state & $this->service_filter ";
		}

		# host query part
		$host_query_parts = $this->auth->authorized_host_query();
		$auth_from_host = '';
		$auth_where_host = '';
		if ($host_query_parts !== true) {
			# build sql query when user isn't authorized_for_all_hosts
			$auth_from_host = $host_query_parts['from'];

			$auth_host_field_host = $host_query_parts['host_field'];

			$auth_where_host = "WHERE auth_host.id = auth_host_contactgroup.host ".
				"AND auth_host_contactgroup.contactgroup = auth_contact_contactgroup.contactgroup ".
				"AND auth_contact_contactgroup.contact=auth_contact.id AND auth_contact.contact_name=".
				$this->db->escape(Auth::instance()->get_user()->username);
		} else {
			$auth_service_field_host = 's';
			$auth_host_field_host = 'h';
			$auth_from_host = ' host '.$auth_host_field_host;
			$auth_where_host = 'WHERE 1 ';
		}

		if (!$this->show_services) {
			if (!empty($filter_host_sql)) {
				$filter_sql .= sprintf($filter_host_sql, $auth_host_field_host.'.');
			}
			if (!empty($filter_service_sql)) {
				$filter_sql .= sprintf($filter_service_sql, '');
			}
			$serviceprops_sql = $this->build_service_props_query($this->serviceprops, $s);
			$hostprops_sql = $this->build_host_props_query($this->hostprops, $auth_host_field_host.'.');
			$this->sort_field = empty($this->sort_field) ? 'host_name' : $this->sort_field;

			# make sure we have 'WHERE' in sql query if we have
			# filter or host/service props
			if (
				(
					!empty($filter_sql) ||
					!empty($hostprops_sql) ||
					!empty($serviceprops_sql)
				)
					&& $auth_where_host == ''
			) {
				$auth_where_host = ' WHERE 1 ';
			}

			# when we have a valid host_list, i.e not 'all'
			# then we should filter on these hosts
			if ($this->host_list !== 'all' && !empty($host_str)) {
				$auth_where_host .= empty($auth_where_host) ?  " " : " AND ";
				$auth_where_host .= $auth_host_field_host.".id IN(".$host_str.") ";
			}

			# only host listing
			$sql = "
				SELECT
					".$auth_host_field_host.".id AS host_id,
					".$auth_host_field_host.".host_name,
					".$auth_host_field_host.".address,
					".$auth_host_field_host.".alias,
					".$auth_host_field_host.".current_state,
					".$auth_host_field_host.".last_check,
					".$auth_host_field_host.".next_check,
					".$auth_host_field_host.".should_be_scheduled,
					".$auth_host_field_host.".notes_url,
					".$auth_host_field_host.".notifications_enabled,
					".$auth_host_field_host.".active_checks_enabled,
					".$auth_host_field_host.".icon_image,
					".$auth_host_field_host.".icon_image_alt,
					".$auth_host_field_host.".is_flapping,
					".$auth_host_field_host.".action_url,
					(UNIX_TIMESTAMP() - ".$auth_host_field_host.".last_state_change) AS duration,
					UNIX_TIMESTAMP() AS cur_time,
					".$auth_host_field_host.".current_attempt,
					".$auth_host_field_host.".max_check_attempts,
					".$auth_host_field_host.".problem_has_been_acknowledged,
					".$auth_host_field_host.".scheduled_downtime_depth,
					".$auth_host_field_host.".output,
					".$auth_host_field_host.".long_output
				FROM ".$auth_from_host."
					".$auth_where_host."
					".$filter_sql.$hostprops_sql.$serviceprops_sql;

			$order = " ORDER BY ".$this->sort_field." ".$this->sort_order;

			# take into account that users might be authenticated
			# directly through contact - host_contact
			$from_contact = " host AS auth_host, ".
			"contact AS auth_contact, ".
			"host_contact AS auth_host_contact, ".
			"service AS auth_service ";
			$where_contact = "	auth_host.id = auth_host_contact.host ".
			"AND auth_host_contact.contact=auth_contact.id ".
			"AND auth_contact.contact_name=".$this->db->escape(Auth::instance()->get_user()->username).
			" AND auth_service.host_name = auth_host.host_name";

			$sql2 = "
				SELECT
					".$auth_host_field_host.".id AS host_id,
					".$auth_host_field_host.".host_name,
					".$auth_host_field_host.".address,
					".$auth_host_field_host.".alias,
					".$auth_host_field_host.".current_state,
					".$auth_host_field_host.".last_check,
					".$auth_host_field_host.".next_check,
					".$auth_host_field_host.".should_be_scheduled,
					".$auth_host_field_host.".notes_url,
					".$auth_host_field_host.".notifications_enabled,
					".$auth_host_field_host.".active_checks_enabled,
					".$auth_host_field_host.".icon_image,
					".$auth_host_field_host.".icon_image_alt,
					".$auth_host_field_host.".is_flapping,
					".$auth_host_field_host.".action_url,
					(UNIX_TIMESTAMP() - ".$auth_host_field_host.".last_state_change) AS duration,
					UNIX_TIMESTAMP() AS cur_time,
					".$auth_host_field_host.".current_attempt,
					".$auth_host_field_host.".max_check_attempts,
					".$auth_host_field_host.".problem_has_been_acknowledged,
					".$auth_host_field_host.".scheduled_downtime_depth,
					".$auth_host_field_host.".output,
					".$auth_host_field_host.".long_output
				FROM ".$from_contact."
				WHERE
					".$where_contact."
					".$filter_sql.$hostprops_sql.$serviceprops_sql;

			if (!$this->auth->view_hosts_root) {
				$sql = '('.$sql.') UNION ('.$sql2.') ';
			}
			$sql .= $order;

		} else {
			$auth_query_parts = $this->auth->authorized_service_query();
			$auth_from = '';
			$auth_where = '';
			if ($auth_query_parts !== true && !$this->auth->view_hosts_root) {
				$auth_from = $auth_query_parts['from'];

				# match authorized services against service.host_name
				$auth_where = $auth_query_parts['where'].' AND ';

				# what aliases are used for host and service field
				$auth_service_field = $auth_query_parts['service_field'];
				$auth_host_field = $auth_query_parts['host_field'];
			} else {
				$auth_service_field = 's';
				$auth_host_field = 'h';
				$auth_from = ' host '.$auth_host_field.', service '.$auth_service_field;
				$auth_where = '';
			}
			if (!empty($filter_host_sql)) {
				$filter_sql .= sprintf($filter_host_sql, $auth_host_field.'.');
			}
			if (!empty($filter_service_sql)) {
				$filter_sql .= sprintf($filter_service_sql, $auth_service_field.'.');
			}

			$auth_from_host = '';
			$auth_where_host = '';
			if ($host_query_parts !== true) {
				$auth_from_host = $host_query_parts['from'].', service AS auth_service';

				# what aliases are used for host and service field
				$auth_service_field_host = 'auth_service';
				$auth_host_field_host = $host_query_parts['host_field'];
				# match authorized services against service.host_name
				if (isset($host_str) && !empty($host_str)) {
					$auth_where_xtra = $auth_host_field_host.".id IN(".$host_str.") AND ";
				} else {
					$auth_where_xtra = '';
				}
				$auth_where_host = " WHERE ".$auth_where_xtra.sprintf($host_query_parts['where'], $auth_service_field_host.'.host_name');
			} else {
				$auth_service_field_host = 's';
				$auth_host_field_host = 'h';
				$auth_from_host = ' host '.$auth_host_field_host.', service '.$auth_service_field_host;
				$auth_where_host = '';
			}

			$serviceprops_sql = $this->build_service_props_query($this->serviceprops, $auth_service_field.'.');
			$hostprops_sql = $this->build_host_props_query($this->hostprops, $auth_host_field.'.');

			if (empty($this->sort_field)) {
				$this->sort_field = 'host_name, service_description';
			} else {
				$this->sort_field = str_replace('s.', '',$this->sort_field);
				$this->sort_field = str_replace('h.', '',$this->sort_field);
			}

			# build list of fields to fetch, to be used in several queries
			# using UNION we must have the same fields or it won't work
			$field_list = "<HOST_ALIAS>.id AS host_id,".
					"<HOST_ALIAS>.host_name,".
					"<HOST_ALIAS>.address,".
					"<HOST_ALIAS>.alias,".
					"<HOST_ALIAS>.current_state AS host_state,".
					"<HOST_ALIAS>.problem_has_been_acknowledged AS hostproblem_is_acknowledged,".
					"<HOST_ALIAS>.scheduled_downtime_depth AS hostscheduled_downtime_depth,".
					"<HOST_ALIAS>.notifications_enabled AS host_notifications_enabled,".
					"<HOST_ALIAS>.active_checks_enabled AS host_active_checks_enabled,".
					"<HOST_ALIAS>.action_url AS host_action_url,".
					"<HOST_ALIAS>.icon_image AS host_icon_image,".
					"<HOST_ALIAS>.icon_image_alt AS host_icon_image_alt,".
					"<HOST_ALIAS>.is_flapping AS host_is_flapping,".
					"<HOST_ALIAS>.notes_url AS host_nots_url,".
					"<SERVICE_ALIAS>.id AS service_id,".
					"<SERVICE_ALIAS>.service_description,".
					"<SERVICE_ALIAS>.current_state,".
					"<SERVICE_ALIAS>.last_check,".
					"<SERVICE_ALIAS>.next_check,".
					"<SERVICE_ALIAS>.should_be_scheduled,".
					"<SERVICE_ALIAS>.notifications_enabled,".
					"<SERVICE_ALIAS>.active_checks_enabled,".
					"<SERVICE_ALIAS>.action_url,".
					"<SERVICE_ALIAS>.notes_url,".
					"<SERVICE_ALIAS>.icon_image,".
					"<SERVICE_ALIAS>.icon_image_alt,".
					"<SERVICE_ALIAS>.passive_checks_enabled,".
					"<SERVICE_ALIAS>.problem_has_been_acknowledged,".
					"<SERVICE_ALIAS>.scheduled_downtime_depth,".
					"<SERVICE_ALIAS>.is_flapping as service_is_flapping,".
					"(UNIX_TIMESTAMP() - <SERVICE_ALIAS>.last_state_change) AS duration, UNIX_TIMESTAMP() AS cur_time,".
					"<SERVICE_ALIAS>.current_attempt,".
					"<SERVICE_ALIAS>.max_check_attempts,".
					"<SERVICE_ALIAS>.output,".
					"<SERVICE_ALIAS>.long_output,".
					"<SERVICE_ALIAS>.output AS service_output,".
					"<SERVICE_ALIAS>.long_output AS service_long_output";

			# service query part
			# replace table aliases for next query
			$field_list_tmp = preg_replace('/<HOST_ALIAS>/', $auth_host_field, $field_list);
			$field_list_tmp = preg_replace('/<SERVICE_ALIAS>/', $auth_service_field, $field_list_tmp);

			# when we have a valid host_list, i.e not 'all'
			# then we should filter on these hosts
			if ($this->host_list !== 'all' && !empty($host_str)) {
				$auth_where .= empty($auth_where) ?  "" : " ";
				$auth_where .= $auth_host_field.".id IN(".$host_str.") AND ";
			}
			$sql = "SELECT ".$field_list_tmp.
				" FROM ".
					$auth_from.
				" WHERE ".
					$auth_where.
					$auth_service_field.".host_name = ".$auth_host_field.".host_name ".
					$filter_sql.$hostprops_sql.$serviceprops_sql;

			# host query part using UNION (only needed if not authorized_for_all_hosts)
			if (!$this->auth->view_hosts_root) {

				# replace table aliases for next query
				$field_list_tmp = preg_replace('/<HOST_ALIAS>/', $auth_host_field_host, $field_list);
				$field_list_tmp = preg_replace('/<SERVICE_ALIAS>/', $auth_service_field_host, $field_list_tmp);
				$filter_sql = '';
				if (!empty($filter_host_sql)) {
					$filter_sql .= sprintf($filter_host_sql, $auth_host_field_host.'.');
				}
				if (!empty($filter_service_sql)) {
					$filter_sql .= sprintf($filter_service_sql, $auth_service_field_host.'.');
				}

				# build new {host,service}props query part for new table aliases
				$serviceprops_sql = $this->build_service_props_query($this->serviceprops, $auth_service_field_host.'.');
				$hostprops_sql = $this->build_host_props_query($this->hostprops, $auth_host_field_host.'.');

				$sql2 = "SELECT ".$field_list_tmp.
					" FROM ".
						$auth_from_host.
						$auth_where_host.
						$filter_sql.$hostprops_sql.$serviceprops_sql;

				if ($this->host_list !== 'all' && !empty($host_str)) {
					$auth_where = $auth_host_field.".id IN(".$host_str.") AND ";
				} else {
					$auth_where = '';
				}
				# query hosts and services through contact -> host
				$sql_contact = "SELECT ".$field_list_tmp.
					" FROM host AS auth_host, contact AS auth_contact, host_contact AS auth_hostcontact, service AS auth_service ".
						"WHERE ".$auth_where." auth_host.id=auth_hostcontact.host ".
						"AND auth_hostcontact.contact=auth_contact.id ".
						"AND auth_contact.contact_name=".$this->db->escape(Auth::instance()->get_user()->username).
						"AND auth_service.host_name=auth_host.host_name".
						$filter_sql.$hostprops_sql.$serviceprops_sql;

				# query hosts and services through contact -> service
				$sql_svc_contact = "SELECT ".$field_list_tmp.
					" FROM host AS auth_host, contact AS auth_contact, service_contact AS auth_servicecontact, service AS auth_service ".
						"WHERE ".$auth_where." auth_service.id=auth_servicecontact.service ".
						"AND auth_servicecontact.contact=auth_contact.id ".
						"AND auth_contact.contact_name=".$this->db->escape(Auth::instance()->get_user()->username).
						"AND auth_service.host_name=auth_host.host_name".
						$filter_sql.$hostprops_sql.$serviceprops_sql;

				# join service and host queries with UNION()
				# to fetch all services for an authenticated contact
				$sql = '('.$sql.') UNION ('.$sql2.') UNION (' . $sql_contact . ') UNION (' . $sql_svc_contact . ') ';
			}

			# add order query part
			$sql = $sql." ORDER BY ".$this->sort_field." ".$this->sort_order;

		}
		if ($this->count == false && !empty($this->num_per_page) && $this->offset !== false) {
			$sql .= ' LIMIT '.$this->num_per_page.' OFFSET '.$this->offset;
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
			$ret_str .= ' AND '.$table_alias.'check_type=1 ';
		if ($serviceprops & nagstat::SERVICE_SOFT_STATE)
			$ret_str .= ' AND '.$table_alias.'check_type=0 ';

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
