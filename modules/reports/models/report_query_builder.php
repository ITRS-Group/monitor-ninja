<?php

/**
 * Class for building report sql queries
 *
 * Should eventually be shared between summary and status reports. Isn't currently.
 */
class Report_query_builder_Model extends Model
{
	/* oh no, it leaked out from the summary report model
	 * (contains a mapping from each group to each object, so alert summary
	 * can sum up group reports)
	 */
	public $host_hostgroup; /**< array(host => array(hgrop1, hgroupx...)) */
	public $service_servicegroup; /**< array(service => array(sgroup1, sgroupx...))*/

	protected $db_table = false; /**< The table we'll be operating on */
	protected $options = false; /**< An options object (or array) to work with */

	/**
	 * Create new report query builder
	 * @param $db_table The table name
	 * @param $options The options object to work with
	 */
	function __construct($db_table, $options) {
		parent::__construct();
		$this->db_table = $db_table;
		$this->options = $options;
	}

	/**
	 * Create the base of the query to use when calculating
	 * alert summary. Each caller is responsible for adding
	 * sorting and limit options as necessary.
	 *
	 * @param $fields string Comma separated list of database columns the caller needs
	 * @return string (sql)
	 */
	function build_alert_summary_query($fields = null)
	{
		if(!$fields) {
			// default to the most commonly used fields
			$fields = 'host_name, service_description, state, hard';
		}
		$auth = op5auth::instance();
		$softorhard = false;
		$alert_types = false;
		$downtime = false;
		$flapping = false;
		$process = false;
		$time_first = false;
		$time_last = false;
		$id_first = false;
		$id_last = false;
		$wildcard_filter = false;

		$hosts = false;
		$services = false;
		if ($this->options['report_type'] == 'servicegroups') {
			$hosts = $services = array();
			foreach ($this->options['objects'] as $sg) {
				$res = Livestatus::instance()->getServices(array('columns' => array('host_name', 'description'), 'filter' => array('groups' => array('>=' => $sg))));
				foreach ($res as $o) {
					$name = implode(';', $o);
					# To be able to sum up alert totals:
					if (empty($services[$name])) {
						$services[$name] = array();
					}
					$services[$name][$sg] = $sg;
					if (empty($hosts[$o['host_name']])) {
						$hosts[$o['host_name']] = array();
					}
					$hosts[$o['host_name']][$sg] = $sg;
				}
			}
			$this->service_servicegroup['host'] = $hosts;
			$this->service_servicegroup['service'] = $services;
		} elseif ($this->options['report_type'] == 'hostgroups') {
			$hosts = array();
			foreach ($this->options['objects'] as $hg) {
				$res = Livestatus::instance()->getHosts(array('columns' => array('host_name'), 'filter' => array('groups' => array('>=' => $hg))));
				foreach ($res as $row) {
					# To be able to sum up alert totals:
					if (empty($hosts[$row['host_name']])) {
						$hosts[$row['host_name']] = array();
					}
					$hosts[$row['host_name']][$hg] = $hg;
				}
			}
			$this->host_hostgroup = $hosts;
		} elseif ($this->options['report_type'] == 'services') {
			$services = false;
			if($this->options['objects'] === Report_options::ALL_AUTHORIZED) {
				$services = Report_options::ALL_AUTHORIZED;
			} else {
				foreach ($this->options['objects'] as $srv) {
					$services[$srv] = $srv;
				}
			}
		} elseif ($this->options['report_type'] == 'hosts') {
			$hosts = false;
			if($this->options['objects'] === Report_options::ALL_AUTHORIZED) {
				$hosts = Report_options::ALL_AUTHORIZED;
			} else {
				if (is_array($this->options['objects'])) {
					foreach ($this->options['objects'] as $hn)
						$hosts[$hn] = $hn;
				} else {
					$hosts[$this->options['objects']] = $this->options['objects'];
				}
			}
		}

		if (empty($hosts) && empty($services)) {
			return "SELECT $fields FROM $this->db_table LIMIT 0";
		}

		$object_selection = false;
		if(($hosts === Report_options::ALL_AUTHORIZED) || ($services === Report_options::ALL_AUTHORIZED)) {
			// screw filters, we're almighty
		} elseif ($services) {
			if ($services !== true) {
				$object_selection .= "(";
				$orstr = '';
				# Must do this the hard way to allow host_name indices to
				# take effect when running the query, since the construct
				# "concat(host_name, ';', service_description)" isn't
				# indexable
				foreach ($services as $srv => $discard) {
					$ary = explode(';', $srv);
					$h = $ary[0];
					$s = $ary[1];
					$object_selection .= $orstr . "(host_name = '" . $h . "'\n    AND (" ;
					if ($s) { /* this if-statement can probably just go away */
						$object_selection .= "service_description = '" . $s . "' OR ";
					}
					$object_selection .= "event_type = 801))";
					$orstr = "\n OR ";
				}
			}
			if (!empty($object_selection))
				$object_selection .= ')';
		} elseif ($hosts && $hosts !== true) {
			$object_selection = "host_name IN(\n '" .
				join("',\n '", array_keys($hosts)) . "')";
		}
		switch ($this->options['state_types']) {
			case 0:
			case 3:
			default:
				break;
			case 1:
				$softorhard = 'hard = 0';
				break;
			case 2:
				$softorhard = 'hard = 1';
				break;
		}

		$included_states = array();
		foreach ($this->options->get_alternatives('host_filter_status') as $k => $v) {
			if (!isset($this->options['host_filter_status'][$k]) || $this->options['host_filter_status'][$k] != Reports_Model::HOST_EXCLUDED)
				$included_states[] = $this->db->escape($k);
		}
		if (!$included_states)
			$host_states_sql = false;
		else
			$host_states_sql = '(event_type = ' . Reports_Model::HOSTCHECK . ' ' .
				' AND state IN('.implode(', ', $included_states).'))';

		$included_states = array();
		foreach ($this->options->get_alternatives('service_filter_status') as $k => $v) {
			if (!isset($this->options['service_filter_status'][$k]) || $this->options['service_filter_status'][$k] != Reports_Model::SERVICE_EXCLUDED)
				$included_states[] = $this->db->escape($k);
		}
		if (!$included_states)
			$service_states_sql = false;
		else
			$service_states_sql = '(event_type = ' . Reports_Model::SERVICECHECK .
				' AND state IN('.implode(', ', $included_states).'))';

		$alert_types = sql::combine('or', $host_states_sql, $service_states_sql);

		if (isset($this->options['include_downtime']) && $this->options['include_downtime'])
			$downtime = 'event_type < 1200 AND event_type > 1100';

		if (isset($this->options['include_flapping']) && $this->options['include_flapping'])
			$flapping = 'event_type == 1000 OR event_type == 1001';

		if (isset($this->options['include_process']) && $this->options['include_process'])
			$process = 'event_type < 200';

		if($this->options['start_time']) {
			$time_first = 'timestamp >= ' . (int)$this->options['start_time'];
		}
		if($this->options['end_time']) {
			$time_last = 'timestamp <= ' . (int)$this->options['end_time'];
		}
		if($this->options['start_id']) {
			$id_first = 'id >= ' . (int)$this->options['start_id'];
		}
		if($this->options['end_id']) {
			$id_last = 'id <= ' . (int)$this->options['end_id'];
		}

		if(isset($this->options['filter_output']) && $this->options['filter_output']) {
			# convert fnmatch wildcards to sql ditos
			$wc_str = $this->options['filter_output'];
			$wc_str = preg_replace("/(?!\\\)\*/", '\1%', $wc_str);
			$wc_str = preg_replace("/(?!\\\)\?/", '\1_', $wc_str);
			# case insensitive. This also works on oracle
			$wc_str = strtoupper($wc_str);
			$wc_str = '%' . $wc_str . '%';
			$wc_str_esc = $this->db->escape($wc_str);
			$wildcard_filter = "\n UPPER(output) LIKE $wc_str_esc" .
				"\n OR UPPER(host_name) LIKE $wc_str_esc " .
				"\n OR UPPER(service_description) LIKE $wc_str_esc";
		}

		$query = "SELECT " . $fields . "\nFROM " . $this->db_table;
		$query .= ' WHERE '.
			sql::combine('and',
				$time_first,
				$time_last,
				$id_first,
				$id_last,
				sql::combine('or',
					$process,
					sql::combine('and',
						$object_selection,
						sql::combine('or',
							$downtime,
							$flapping,
							sql::combine('and',
								$softorhard,
								$alert_types)))),
				$wildcard_filter
			);


		$extra_sql = array();
		$db = $this->db; // for closures
		$implode_str = ') OR (';
		// summa summarum: Don't use the API unless you're *authorized* (this is really slow)
		if ($this->options->is_any_state_included("host_filter_status") && !$auth->authorized_for("host_view_all")) {
			$ls = op5Livestatus::instance();
			$hosts = $ls->query("hosts", null, array("name"), array('auth' => $auth->get_user()));
			$objtosql = function($e) use ($db) {
							return $db->escape(current($e));
						};
			if (!empty($hosts[1])) {
				$extra_sql[] = sql::combine(
						"AND",
						"host_name IN (".
						implode(", ",array_map($objtosql,$hosts[1])).")",
						"service_description = ''"
						);
			}
			else {
				$extra_sql[] = "service_description != ''";
				$implode_str = ') AND (';
			}
		}

		// summa summarum: Don't use the API unless you're *authorized* (this is really slow)
		if ($this->options->is_any_state_included("service_filter_status") && !$auth->authorized_for('service_view_all')) {
			$ls = op5Livestatus::instance();
			$services = $ls->query("services", null, array("host_name", "description"), array('auth' => $auth->get_user()));
			$objtosql = function($e) use ($db) {
							return '('.$db->escape($e[0]).', '.$db->escape($e[1]).')';
						};
			if (!empty($services[1])) {
				$extra_sql[] = "(host_name, service_description) IN (".
						implode(", ",array_map($objtosql,$services[1])).")";
			}
			else {
				$extra_sql[] = "service_description = ''";
				$implode_str = ') AND (';
			}
		}

		if(count($extra_sql) > 0) {
			/* The innermost parenthesis matches the parenthesis in $implode_str */
			$query .= " AND ((".implode($implode_str, $extra_sql)."))";
		}

		return $query;
	}
}
