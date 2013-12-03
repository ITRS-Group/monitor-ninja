<?php
/**
 * Reports model
 * Responsible for fetching data for avail and SLA reports. This class
 * must be instantiated to work properly.
 *
 * ## State interaction in subreports
 * Given two objects, assuming only two states per object type, would interact
 * such that the non-OK state overrules the OK state completely as such:
 *                                               host
 *                                   UP            |          DOWN
 *                      | scheduled  | unscheduled | scheduled  | unscheduled
 *          ------------++++++++++++++++++++++++++++++++++++++++++++++++++++++
 *           scheduled  +  sched up  | unsched up  | sched down | unsched down
 *      UP  ------------+------------+-------------+------------+-------------
 *           unscheduled+ unsched up | unsched up  | sched down | unsched down
 * host ----------------+------------+-------------+------------+-------------
 *           scheduled  + sched down | sched down  | sched down | unsched down
 *      DOWN------------+------------+-------------+------------+-------------
 *           unscheduled+unsched down| unsched down|unsched down| unsched down
 *
 * When two sub-objects have different non-OK states, the outcome depends on
 * whether scheduleddowntimeasuptime is used or not. If the option is used,
 * then the service with the worst non-scheduled state is used. If the option
 * is not used, the worst state is used, prioritizing any non-scheduled state.
 *
 * This applies to non-"cluster mode" reports. If you're in cluster mode, this
 * applies backwards exactly.
 */
class Status_Reports_Model extends Reports_Model
{
	protected $st_is_service = false; /**< Whether the objects in this report are services */
	protected $st_source = false; /**< Array of (non-group) objects that are part of this report */
	protected $calculator = false; /**< The top-level calculator that represents this report */

	/**
	 * Constructor
	 * @param $options An instance of Report_options
	 * @param $db_table Database name
	 */
	public function __construct(Report_options $options, $db_table='report_data')
	{
		$this->db_table = $db_table;
		parent::__construct($options);
	}

	/**
	 * Get log details for host/service
	 *
	 * @return PDO result object on success. FALSE on error.
	 */
	public function uptime_query()
	{
		$event_type = Reports_Model::HOSTCHECK;
		if ($this->st_is_service) {
			$event_type = Reports_Model::SERVICECHECK;
		}

		# this query works out automatically, as we definitely don't
		# want to get all state entries for a hosts services when
		# we're only asking for uptime of the host
		$sql = "SELECT host_name, service_description, " .
			"state,timestamp AS the_time, hard, event_type";
		# output is a TEXT field, so it needs an extra disk
		# lookup to fetch and we don't always need it
		if ($this->options['include_trends'])
			$sql .= ", output";

		$sql .= " FROM ".$this->db_table." ";

		$time_first = 'timestamp >='.$this->options['start_time'];
		$time_last = 'timestamp <='.$this->options['end_time'];
		$process = false;
		$purehost = false;
		$objsel = false;
		$downtime = 'event_type=' . Reports_Model::DOWNTIME_START . ' OR event_type=' . Reports_Model::DOWNTIME_STOP;
		$softorhardcheck = 'event_type=' . $event_type;

		if (!$this->options['assumestatesduringnotrunning'])
			$process = 'event_type < 200';

		if (!$this->options['includesoftstates']) {
			$softorhardcheck .= ' AND hard=1';
		}

		if ($this->st_is_service) {
			$hostname = array();
			$servicename = array();
			foreach ($this->st_source as $hst_srv) {
				$ary = explode(';', $hst_srv, 2);
				$hostname[] = $this->db->escape($ary[0]);
				$servicename[] = $this->db->escape($ary[1]);
			}
			$purehost = "host_name IN (".join(", ", $hostname) . ") AND (service_description = '' OR service_description IS NULL)";

			if (count($hostname) == 1) {
				$hostname = array_pop($hostname);
				$objsel = "host_name = $hostname AND service_description IN (".join(", ", $servicename) . ")";
			} else {
				foreach ($hostname as $i => $host) {
					$svc = $servicename[$i];
					$objsel[] = "host_name = $host AND service_description = $svc";
				}
				$objsel = '('.implode(') OR (', $objsel).')';
			}

			$sql_where = sql::combine('and',
				$time_first,
				$time_last,
				sql::combine('or',
					$process,
					sql::combine('or',
						sql::combine('and',
							$purehost,
							$downtime),
						sql::combine('and',
							$objsel,
							sql::combine('or',
								$downtime,
								$softorhardcheck)))));
		} else {
			$objsel = "host_name IN ('" . join("', '", $this->st_source) . "') AND (service_description = '' OR service_description IS NULL)";

			$sql_where = sql::combine('and',
				$time_first,
				$time_last,
				sql::combine('or',
					$process,
					sql::combine('and',
						$objsel,
						sql::combine('or',
							$downtime,
							$softorhardcheck))));
		}

		$sql .= 'WHERE ' .$sql_where . ' ORDER BY timestamp';

		return $this->db->query($sql)->result(false);
	}

	/**
	 * Calculate uptime between two timestamps for host/service
	 * @return array or false on error
	 *
	 */
	public function get_uptime()
	{
		if (!$this->options['host_name'] && !$this->options['hostgroup'] && !$this->options['service_description'] && !$this->options['servicegroup']) {
			return false;
		}

		$is_running = !$this->get_last_shutdown();

		switch ($this->options['report_type']) {
		 case 'services':
		 case 'servicegroups':
			$this->st_is_service = true;
			break;
		}
		$objects = $this->options->get_report_members();
		$this->st_source = $objects;

		$calculator_type = false;
		switch ((int)$this->options['sla_mode']) {
		 case 0:
			$calculator_type = 'WorstStateCalculator';
			break;
		 case 1:
			$calculator_type = 'AverageStateCalculator';
			break;
		 case 2:
			$calculator_type = 'BestStateCalculator';
			break;
		 default:
			die("Don't know how to do anything with this\n");
			break;
		}

		$this->calculator = new $calculator_type($this->options, $this->timeperiod);
		$optclass = get_class($this->options);

		$subs = array();

		$initial_states = $this->get_initial_states($this->st_is_service ? 'service' : 'host', $objects);
		$downtimes = $this->get_initial_dt_depths($this->st_is_service ? 'service' : 'host', $objects);
		foreach ($objects as $object) {
			$opts = new $optclass($this->options);
			$opts[$this->st_is_service ? 'service_description' : 'host_name'] = array($object);
			$sub = new SingleStateCalculator($opts, $this->timeperiod);
			if (isset( $initial_states[$object]))
				$initial_state = $initial_states[$object];
			else
				$initial_state = Reports_Model::STATE_PENDING;

			if (isset( $downtimes[$object]))
				$initial_depth = $downtimes[$object];
			else
				$initial_depth = 0;

			if (!$initial_depth && $this->st_is_service) { /* Is host scheduled? */
				$srv = explode(';', $object);
				if (isset($downtimes[$srv[0].';']) && $downtimes[$srv[0].';'])
					$initial_depth = 1;
			}
			$sub->initialize($initial_state, $initial_depth, $is_running);
			$subs[$object] = $sub;
		}

		switch ($this->options['report_type']) {
		 case 'servicegroups':
		 case 'hostgroups':
			$groups = $this->options[$this->options->get_value('report_type')];
			$all_subs = $subs;
			$subs = array();
			foreach ($groups as $group) {
				$opts = new $optclass($this->options);
				$opts[$this->options->get_value('report_type')] = array($group);
				$members = $opts->get_report_members();
				$these_subs = array();
				foreach ($members as $member)
					$these_subs[$member] = $all_subs[$member];
				$this_sub = new $calculator_type($opts, $this->timeperiod);
				$this_sub->set_sub_reports($these_subs);
				$this_sub->initialize(Reports_Model::STATE_PENDING, Reports_Model::STATE_PENDING, $is_running);
				$subs[$group] = $this_sub;
			}
			break;
		 case 'hosts':
		 case 'services':
			$this_sub = new $calculator_type($this->options, $this->timeperiod);
			$this_sub->set_sub_reports($subs);
			$this_sub->initialize(Reports_Model::STATE_PENDING, Reports_Model::STATE_PENDING, $is_running);
			$subs = array($this_sub);
			break;
		}

		$this->calculator->set_sub_reports($subs);
		$this->calculator->initialize(Reports_Model::STATE_PENDING, Reports_Model::STATE_PENDING, $is_running);

		$this->st_parse_all_rows();
		$this->calculator->finalize();
		return $this->calculator->get_data();
	}

	/**
	 * Get latest (useful) process shutdown event
	 *
	 * @return Timestamp when of last shutdown event prior to $start_time
	 */
	public function get_last_shutdown()
	{
		# If we're assuming states during program downtime,
		# we don't really need to know when the last shutdown
		# took place, as the initial state will be used regardless
		# of whether or not Monitor was up and running.
		if ($this->options['assumestatesduringnotrunning']) {
			return 0;
		}

		$query = "SELECT timestamp, event_type FROM ".
			$this->db_table.
			" WHERE timestamp <".$this->options['start_time'].
			" ORDER BY timestamp DESC LIMIT 1";
		$dbr = $this->db->query($query)->result(false);

		if (!$dbr || !($row = $dbr->current()))
			return false;

		$event_type = $row['event_type'];
		if ($event_type==Reports_Model::PROCESS_SHUTDOWN || $event_type==Reports_Model::PROCESS_RESTART)
			$last_shutdown = $row['timestamp'];
		else
			$last_shutdown = 0;

		return $last_shutdown;
	}


	/**
	 * Runs the main query and loops through the results one by one
	 */
	private function st_parse_all_rows()
	{
		$dbr = $this->uptime_query();
		foreach ($dbr as $row) {
			$this->calculator->add_event($row);
		}
	}

	/**
	 * Fetch information about SCHEDULED_DOWNTIME status for multiple objects
	 *
	 * @return array of Depth of initial downtime.
	 */
	protected function get_initial_dt_depths( $type = 'host', $names = array() )
	{
		$objectmatches = array();
		if( $type == 'service' ) {
			foreach( $names as $name ) {
				list( $host, $srv ) = explode( ';', $name, 2 );
				$objectmatches[] = '(host_name = '
						. $this->db->escape($host)
						. ' AND (service_description = "" OR service_description IS NULL'
						. ' OR service_description = '
						. $this->db->escape($srv)
						. '))';
			}
		} else {
			foreach( $names as $name ) {
				$objectmatches[] = '(host_name = '
						. $this->db->escape($name)
						. ' AND (service_description = "" OR service_description IS NULL))';
			}
		}

		$sql  = "SELECT DISTINCT lsc.host_name as host_name, lsc.service_description as service_description, rd.event_type as event_type FROM (";
		$sql .= "SELECT host_name, service_description, max( timestamp ) as timestamp FROM ".$this->db_table;
		$sql .= " WHERE (".implode(' OR ',$objectmatches).")";
		$sql .= " AND (event_type = ".Reports_Model::DOWNTIME_START." OR event_type = ".Reports_Model::DOWNTIME_STOP.")";
		$sql .= " AND timestamp < ".$this->options['start_time'];
		$sql .= " GROUP BY host_name,service_description";
		$sql .= ") AS lsc";
		$sql .= " LEFT JOIN ".$this->db_table." AS rd";
		$sql .= " ON lsc.host_name = rd.host_name";
		$sql .= " AND lsc.service_description = rd.service_description";
		$sql .= " AND lsc.timestamp = rd.timestamp";
		$sql .= " AND (event_type = ".Reports_Model::DOWNTIME_START." OR event_type = ".Reports_Model::DOWNTIME_STOP.")";

		$dbr = $this->db->query($sql)->result(false);

		$downtimes = array();
		foreach( $dbr as $staterow ) {
			$in_downtime = (int)($staterow['event_type'] == Reports_Model::DOWNTIME_START);
			if ( $type == 'service' ) {
				$downtimes[ $staterow['host_name'] . ';' . $staterow['service_description'] ] = $in_downtime;
			} else {
				$downtimes[ $staterow['host_name'] ] = $in_downtime;
			}
		}

		return $downtimes;
	}

	/**
	 * Get inital states of a set of objects
	 *
	 * @return array of initial states
	 */
	protected function get_initial_states( $type = 'host', $names = array() )
	{
		$objectmatches = array();
		if( $type == 'service' ) {
			foreach( $names as $name ) {
				list( $host, $srv ) = explode( ';', $name, 2 );
				$objectmatches[] = '(host_name = '
						. $this->db->escape($host)
						. ' AND service_description = '
						. $this->db->escape($srv)
						. ')';
			}
		} else {
			foreach( $names as $name ) {
				$objectmatches[] = '(host_name = '
						. $this->db->escape($name)
						. ' AND (service_description = "" OR service_description IS NULL))';
			}
		}

		$sql  = "SELECT DISTINCT lsc.host_name as host_name, lsc.service_description as service_description, rd.state as state FROM (";
		$sql .= "SELECT host_name, service_description, max( timestamp ) as timestamp FROM ".$this->db_table;
		$sql .= " WHERE (".implode(' OR ',$objectmatches).")";
		if ( $type == 'service' ) {
			$sql .= " AND event_type = ".Reports_Model::SERVICECHECK;
		} else {
			$sql .= " AND event_type = ".Reports_Model::HOSTCHECK;
		}
		if (!$this->options['includesoftstates'])
			$sql .= " AND hard = 1";
		$sql .= " AND timestamp < ".$this->options['start_time'];
		$sql .= " GROUP BY host_name,service_description";
		$sql .= ") AS lsc";
		$sql .= " LEFT JOIN ".$this->db_table." AS rd";
		$sql .= " ON lsc.host_name = rd.host_name";
		$sql .= " AND lsc.service_description = rd.service_description";
		$sql .= " AND lsc.timestamp = rd.timestamp";
		if ( $type == 'service' ) {
			$sql .= " AND event_type = ".Reports_Model::SERVICECHECK;
		} else {
			$sql .= " AND event_type = ".Reports_Model::HOSTCHECK;
		}

		$dbr = $this->db->query($sql)->result(false);

		$states = array();
		if ( $type == 'service' ) {
			foreach( $dbr as $staterow ) {
				$states[ $staterow['host_name'] . ';' . $staterow['service_description'] ] = $staterow['state'];
			}
		} else {
			foreach( $dbr as $staterow ) {
				$states[ $staterow['host_name'] ] = $staterow['state'];
			}
		}

		return $states;
	}
}
