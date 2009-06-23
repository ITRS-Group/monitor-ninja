<?php defined('SYSPATH') OR die('No direct access allowed.');

class Host_Model extends Model {
	private $auth = false;
	private $host_list = false; # List of hosts to get status for

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
					->select('*, (UNIX_TIMESTAMP() - last_state_change) AS duration')
					->where('host', array('id' => $id));
			}
		} elseif (!empty($name)) {
			if (!array_key_exists($name, $this->auth->hosts_r)) {
				return false;
			} else {
				$host_info = $this->db
					->select('*, (UNIX_TIMESTAMP() - last_state_change) AS duration')
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
	public function get_where($field=false, $value=false, $limit=false)
	{
		if (empty($field) || empty($value)) {
			return false;
		}
		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_ids = array_keys($auth_hosts);
		$host_info = $this->db
			->from('host')
			->like($field, $value)
			->in('id', $host_ids)
			->limit($limit)
			->get();
		return $host_info;
	}

	/**
	*	Search through several fields for a specific value
	*/
	public function search($value=false, $limit=false)
	{
		if (empty($value)) return false;
		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_ids = array_keys($auth_hosts);
		$host_info = $this->db
			->select('DISTINCT *')
			->from('host')
			->orlike(
				array(
					'host_name' => $value,
					'alias' => $value,
					'display_name' => $value,
					'address' => $value
				)
			)
			->in('id', $host_ids)
			->limit($limit)
			->get();
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
				# return all authenticated host IDs
				$retval = array_keys($hosts);
			} elseif (array_key_exists($host_names, $host_r)) {
				$retval[] = $host_r[$host_names];
			}
		}
		$this->host_list = $retval;
	}

	/**
	 * Fetch status data for a subset of hosts
	 * (and their related services if show_services is set to true).
	 */
	public function get_host_status()
	{
		/* $host_list = false, $show_services = false, $state_filter=false,
		$sort_field='', $sort_order='ASC', $service_filter=false, $serviceprops=false, $hostprops=false,
		$num_per_page=false, $offset=false, $count=false
		*/

		if (empty($this->host_list)) {
			return false;
		}

		#$num_per_page = (int)$num_per_page;
		$host_str = implode(', ', $this->host_list);
		$filter_sql = '';
		$filter_host_sql = false;
		$filter_service_sql = false;
		$h = $this->show_services ? 'auth_host.' : '';
		$s = !$this->show_services ? '' : 'auth_service.';
		if (!empty($this->state_filter)) {
			$filter_host_sql = "AND 1 << %scurrent_state & ".$this->state_filter." ";
		}
		if ($this->service_filter!==false && !empty($this->service_filter)) {
			$filter_service_sql = " AND 1 << %scurrent_state & $this->service_filter ";
		}

		if (!$this->show_services) {
			if (!empty($filter_host_sql)) {
				$filter_sql .= sprintf($filter_host_sql, '');
			}
			if (!empty($filter_service_sql)) {
				$filter_sql .= sprintf($filter_service_sql, '');
			}
			$serviceprops_sql = $this->build_service_props_query($this->serviceprops, $s);
			$hostprops_sql = $this->build_host_props_query($this->hostprops, $h);
			$this->sort_field = empty($this->sort_field) ? 'host_name' : $this->sort_field;
			# only host listing
			if ($this->count === true) {
			$sql = "
					SELECT
						COUNT(*) AS cnt
					FROM host
					WHERE
						id IN (".$host_str.")
						".$filter_sql.$hostprops_sql.$serviceprops_sql;
			} else {
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
						output
					FROM host
					WHERE
						id IN (".$host_str.")
						".$filter_sql.$hostprops_sql.$serviceprops_sql."
					ORDER BY
						".$this->sort_field." ".$this->sort_order;
			}
		} else {
			$auth_query_parts = $this->auth->authorized_service_query();
			$auth_from = '';
			$auth_where = '';
			$service_in_query = '';
			if ($auth_query_parts !== true) {
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

			$serviceprops_sql = $this->build_service_props_query($this->serviceprops, $auth_service_field.'.');
			$hostprops_sql = $this->build_host_props_query($this->hostprops, $auth_host_field.'.');

			if (empty($this->sort_field)) {
				$this->sort_field = $auth_host_field.'.host_name, '.$auth_service_field.'.service_description';
			} else {
				$this->sort_field = str_replace('s.', $auth_service_field.'.',$this->sort_field);
				$this->sort_field = str_replace('h.', $auth_host_field.'.',$this->sort_field);
			}

			if ($this->count === true) {
			$sql = "
					SELECT
						COUNT(*) AS cnt
					FROM
						".$auth_from."
					WHERE
						".$auth_host_field.".id IN (".$host_str.") AND
						".$auth_where.
						$auth_service_field.".host_name = ".$auth_host_field.".host_name
						".$filter_sql.$hostprops_sql.$serviceprops_sql;

			} else {
				$sql = "SELECT ".
						$auth_host_field.".id AS host_id,".
						$auth_host_field.".host_name,".
						$auth_host_field.".address,".
						$auth_host_field.".alias,".
						$auth_host_field.".current_state AS host_state,".
						$auth_host_field.".problem_has_been_acknowledged AS hostproblem_is_acknowledged,".
						$auth_host_field.".scheduled_downtime_depth AS hostscheduled_downtime_depth,".
						$auth_host_field.".notifications_enabled AS host_notifications_enabled,".
						$auth_host_field.".action_url AS host_action_url,".
						$auth_host_field.".icon_image AS host_icon_image,".
						$auth_host_field.".icon_image_alt AS host_icon_image_alt,".
						$auth_host_field.".is_flapping AS host_is_flapping,".
						$auth_host_field.".notes_url,".
						$auth_service_field.".id AS service_id,".
						$auth_service_field.".service_description,".
						$auth_service_field.".current_state,".
						$auth_service_field.".last_check,".
						$auth_service_field.".notifications_enabled,".
						$auth_service_field.".active_checks_enabled,".
						$auth_service_field.".action_url,".
						$auth_service_field.".icon_image,".
						$auth_service_field.".icon_image_alt,".
						$auth_service_field.".passive_checks_enabled,".
						$auth_service_field.".problem_has_been_acknowledged,".
						$auth_service_field.".scheduled_downtime_depth,".
						$auth_service_field.".is_flapping as service_is_flapping,".
						"(UNIX_TIMESTAMP() - ".$auth_service_field.".last_state_change) AS duration,".
						$auth_service_field.".current_attempt,".
						$auth_service_field.".output".
					" FROM ".
						$auth_from.
					" WHERE ".
						$auth_host_field.".id IN (".$host_str.") AND ".
						$auth_where.
						$auth_service_field.".host_name = ".$auth_host_field.".host_name ".
						$filter_sql.$hostprops_sql.$serviceprops_sql.
					" ORDER BY ".
						$this->sort_field." ".$this->sort_order;
			}
		}
		if ($this->count == false && $this->num_per_page !== false && $this->offset !== false) {
			$sql .= ' LIMIT '.$this->offset.', '.$this->num_per_page;
		}
		$result = $this->db->query($sql);
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
			$ret_str .= ' AND '.$table_alias.'state_type='.nagstat::HARD_STATE.' ';
		if ($hostprops & nagstat::HOST_SOFT_STATE)
			$ret_str .= ' AND '.$table_alias.'state_type='.nagstat::SOFT_STATE.' ';

		return $ret_str;
	}
}
