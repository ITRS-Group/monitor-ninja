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
	var $db_start_time = 0; /**< earliest database timestamp we look at */
	var $db_end_time = 0;   /**< latest database timestamp we look at */

	public $last_shutdown = false; /**< Last nagios shutdown event- 0 if we started it again */


	var $st_raw = array(); /**< Mapping between the raw states and the time spent there */
	var $st_log = false; /**< The log array */
	var $st_prev_row = array(); /**< The last db row, so we can get duration */
	var $st_running = 0; /**< Is nagios running? */
	var $st_last_dt_init = 1; /**< set to FALSE on nagios restart, and a timestamp on first DT start after restart, so we can exclude duplicate downtime_start */
	var $st_dt_depth = 0; /**< The downtime depth */
	var $st_is_service = false; /**< Whether this is a service */
	var $st_inactive = 0; /**< Time we have no information about */
	var $st_text = array(); /**< Mapping between state integers and state text */
	var $st_sub = array(); /**< Map of sub report [state => [downtime_status => [indexes]]] */
	var $st_sub_discrepancies = 0; /**< Sub report appears to be weirded out */
	private $st_source = false; /**< The source object. Can be object array, can be host_name, can be host_name;service_description */
	private $host_name = false; /**< The source object's host name, if it's just one. Set for services. */
	private $service_description = false; /**< The source object's service description, if it's just one. Only description for services */

	/**
	 * The calculated state of the object, taking such things
	 * as scheduled downtime counted as uptime into consideration
	 */
	private $st_obj_state = false;

	/** The real state of the object */
	private $st_real_state = false;

	/** The state template for hosts */
	private $state_tpl_host = array(
		'HOST_NAME' => '',
		'TIME_UP_SCHEDULED' => 0,
		'TIME_UP_UNSCHEDULED' => 0,
		'TIME_DOWN_SCHEDULED' => 0,
		'TIME_DOWN_UNSCHEDULED' => 0,
		'TIME_UNREACHABLE_SCHEDULED' => 0,
		'TIME_UNREACHABLE_UNSCHEDULED' => 0,
		'TIME_UNDETERMINED_NOT_RUNNING' => 0,
		'TIME_UNDETERMINED_NO_DATA' => 0,
		);

	/** The state template for services */
	private $state_tpl_svc = array(
		'HOST_NAME' => '',
		'SERVICE_DESCRIPTION' => '',
		'TIME_OK_SCHEDULED' => 0,
		'TIME_OK_UNSCHEDULED' => 0,
		'TIME_WARNING_SCHEDULED' => 0,
		'TIME_WARNING_UNSCHEDULED' => 0,
		'TIME_UNKNOWN_SCHEDULED' => 0,
		'TIME_UNKNOWN_UNSCHEDULED' => 0,
		'TIME_CRITICAL_SCHEDULED' => 0,
		'TIME_CRITICAL_UNSCHEDULED' => 0,
		'TIME_UNDETERMINED_NOT_RUNNING' => 0,
		'TIME_UNDETERMINED_NO_DATA' => 0,
	);

	public $initial_dt_depth = false; /**< The initial downtime depth. NOTE: this is scary, what if there's a dozen 365 day long downtimes active at once or bugs caused us to forget to end downtimes? */
	public $db_table = 'report_data'; /**< Report table name */
	public $sub_reports = array(); /**< An array of sub-reports for this report */
	public $states = array(); /**< The final array of report states */
	public $last_shutdown = false; /**< Last nagios shutdown event- 0 if we started it again */
	private $st_state_calculator = 'invalid'; /**< The calculatior method - defaults to invalid value to discover invalid use (I just spend 8 hours debugging one such instance, so change at your own risk) */

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
	*	Check that we have a valid database installed and usable.
	*/
	public function _self_check()
	{
		try {
			# this will result in error if db_name section
			# isn't set in config/database.php
			$db = Database::instance();
		} catch (Kohana_Database_Exception $e) {
			return false;
		}
		$table_exists = false;
		if (isset($db)) {
			try {
				$table_exists = $db->table_exists($this->db_table);
			} catch (Kohana_Database_Exception $e) {
				return false;
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Manually excluded states are excluded here.
	 *
	 * @param $state int
	 * @return int
	 */
	private function filter_excluded_state($state) {
		if ($this->st_is_service && isset($this->options['service_filter_status'][$state]))
			return $this->options['service_filter_status'][$state];
		if (!$this->st_is_service && isset($this->options['host_filter_status'][$state]))
			return $this->options['host_filter_status'][$state];
		return $state;
	}

	/**
	 * Adjust report start and end time so that the provided timestamp is included
	 *
	 * @param $t A timestamp
	 */
	public function register_db_time($t)
	{
		if (!$this->db_start_time || $t < $this->db_start_time)
			$this->db_start_time = $t;
		if (!$this->db_end_time || $t > $this->db_end_time)
			$this->db_end_time = $t;
		$this->debug['db_start_time'] = $this->db_start_time;
		$this->debug['db_end_time'] = $this->db_end_time;
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

		# register first and last possible database entry times
		$this->register_db_time($this->options['start_time']);
		$this->register_db_time($this->options['end_time']);

		$this->debug = $this->options->options;

		$this->get_last_shutdown();

		$servicename = $hostname = false;
		$res_group = false;

		switch ($this->options['report_type']) {
		 case 'services':
		 case 'servicegroups':
			$servicename = $this->options->get_report_members();
			break;
		 case 'hosts':
		 case 'hostgroups':
			$hostname = $this->options->get_report_members();
			break;
		}

		if ($servicename) {
			$initial_states = $this->get_initial_states('service', $servicename);
			$downtimes = $this->get_initial_dt_depths('service', $servicename);
			foreach ($servicename as $service) {
				$srv = explode(';', $service);
				$optclass = get_class($this->options);
				$opts = new $optclass($this->options);
				$opts['service_description'] = $service;
				$opts['master'] = $this;
				$opts['keep_logs'] = $this->options['keep_sub_logs'];
				$sub_class = new Status_Reports_Model($opts, $this->db_table);
				$sub_class->register_db_time($opts['start_time']);
				$sub_class->register_db_time($opts['end_time']);
				$sub_class->st_source = $service;
				$sub_class->host_name =  $srv[0];
				$sub_class->service_description = $srv[1];
				$sub_class->last_shutdown = $this->last_shutdown;
				if( isset( $initial_states[$service] ) ) {
					$sub_class->initial_state = $initial_states[$service];
				} else {
					$sub_class->initial_state = self::STATE_PENDING;
				}
				$sub_class->prefetched_dt_depth = false;
				if( isset( $downtimes[$service] ) && $downtimes[$service] )
					$sub_class->prefetched_dt_depth = true;
				if( isset( $downtimes[$srv[0].';'] ) && $downtimes[$srv[0].';'] ) /* Host scheduled */
					$sub_class->prefetched_dt_depth = true;
				$sub_class->st_init();
				$this->sub_reports[$service] = $sub_class;
			}
			$this->st_source = $servicename;
		} else if ($hostname) {
			$initial_states = $this->get_initial_states('host', $hostname);
			$downtimes = $this->get_initial_dt_depths('host', $hostname);
			foreach ($hostname as $host) {
				$optclass = get_class($this->options);
				$opts = new $optclass($this->options);
				$opts['keep_logs'] = $this->options['keep_sub_logs'];
				$opts['host_name'] = $host;
				$opts['master'] = $this;
				$sub_class = new Status_Reports_Model($opts, $this->db_table);
				$sub_class->register_db_time($opts['start_time']);
				$sub_class->register_db_time($opts['end_time']);
				$sub_class->st_source = $host;
				$sub_class->host_name = $host;
				$sub_class->last_shutdown = $this->last_shutdown;
				if( isset( $initial_states[$host] ) ) {
					$sub_class->initial_state = $initial_states[$host];
				} else {
					$sub_class->initial_state = self::STATE_PENDING;
				}
				if( isset( $downtimes[$host] ) ) {
					$sub_class->prefetched_dt_depth = $downtimes[$host];
				} else {
					$sub_class->prefetched_dt_depth = false;
				}
				$sub_class->st_init();
				$this->sub_reports[$host] = $sub_class;
			}
			$this->st_source = $hostname;
		} else {
			return false;
		}

		# Grab master's report-results _FIRST_ as sub-reports
		# are fed its data from the same query
		$this->st_init();
		$this->st_parse_all_rows();
		$sub_return = false;
		foreach ($this->sub_reports as $id => $rpt) {
			$return[] = $rpt->st_finalize();
			$this->register_db_time($rpt->db_start_time);
			$this->register_db_time($rpt->db_end_time);
		}
		$master_return = $this->st_finalize();
		foreach ($master_return as $k => $v)
			$return[$k] = $v;

		# stash the debugging stuff in the return array, but only
		# for the master report
		if (empty($this->options['master'])) {
			$this->debug['db_end_time'] = $this->db_end_time;
			$this->debug['db_start_time'] = $this->db_start_time;
			foreach ($this->debug as $k => $v) {
				if ($v === false)
					unset($this->debug[$k]);
			}

			return $return;
		}
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
	 * Calculate the time spent in different states as total and percentage.
	 *
	 * @param $state State times. Has the format:<br>
	 * array("X:Y:Z" => seconds, 	...). Where X, Y and Z are numeric states and rhs argument is the number of seconds in that state
	 * @param $conv State translation table. E.g. for hostgroups:<br> array(0 => 'UP', '1' => 'DOWN', '2' => 'UNREACHABLE', '-1' => 'PENDING')
	 * @return array A huge array with all possible states and time spent in that state. States called PERCENT_* contains percentages rather than a number of seconds.
	 */
	public function convert_state_table($state, $conv)
	{
		$cstate = array();
		$cstate['TIME_UNDETERMINED_NO_DATA'] = 0;
		$cstate['TIME_UNDETERMINED_NOT_RUNNING'] = 0;
		$cstate['TIME_DOWN_COUNTED_AS_UP'] = 0;
		$cstate['TOTAL_TIME_UNSCHEDULED'] = 0;
		$cstate['TOTAL_TIME_SCHEDULED'] = 0;
		$cstate['TOTAL_TIME_UNDETERMINED'] = 0;
		$cstate['TOTAL_TIME_KNOWN'] = 0;
		$cstate['TOTAL_TIME_ACTIVE'] = 0;
		foreach ($state as $s => $duration) {
			$known = false;
			$cstate['TOTAL_TIME_ACTIVE'] += $duration;
			$ary = explode(':', $s);
			$is_running = intval($ary[0]);
			$current_state = intval($ary[2]);
			$in_dt = $ary[1] != 0;
			$p3 = $in_dt ? '' : 'UN';
			$p3 .= 'SCHEDULED';

			if (!$is_running)
				$cstate['TIME_UNDETERMINED_NOT_RUNNING'] += $duration;

			$p1 = $is_running ? '_' : '_UNKNOWN_';

			# this is where we hack in scheduled downtime as uptime
			if ($in_dt && $this->options['scheduleddowntimeasuptime']) {
				$real_state = $conv[$current_state];
				$p2 = $conv[0];
				if ($real_state !== 'UP' && $real_state !== 'OK')
					$cstate['TIME_DOWN_COUNTED_AS_UP'] += $duration;
			}
			elseif (isset($conv[$current_state])) {
				$p2 = $conv[$current_state];

				if ($p2 === 'PENDING')
					$cstate['TIME_UNDETERMINED_NO_DATA'] += $duration;
			}
			else {
				$p2 = "BAD_BUG_ERROR";
			}

			if (!$is_running || $p2 === 'PENDING') {
				$known = false;
				$cstate['TOTAL_TIME_UNDETERMINED'] += $duration;
			}
			else {
				$cstate['TOTAL_TIME_KNOWN'] += $duration;
				$known = true;
			}

			$tot_state = "TOTAL_TIME_$p2";
			if (!isset($cstate[$tot_state]))
				$cstate[$tot_state] = $duration;
			else
				$cstate[$tot_state] += $duration;

			if ($known) {
				$kstate = "KNOWN_TIME_$p2";
				if (!isset($cstate[$kstate]))
					$cstate[$kstate] = $duration;
				else
					$cstate[$kstate] += $duration;
			}

			# scheduled/unscheduled totals
			$cstate['TOTAL_TIME_' . $p3] += $duration;

			$cname = 'TIME' . $p1 . $p2 . '_' . $p3;
			if (!isset($cstate[$cname]))
				$cstate[$cname] = $duration;
			else
				$cstate[$cname] += $duration;

			if ($known) {
				$cname = 'KNOWN_' . $cname;
				if (!isset($cstate[$cname]))
					$cstate[$cname] = $duration;
				else
					$cstate[$cname] += $duration;
			}
		}

		$sched_junk = array('_SCHEDULED', '_UNSCHEDULED');
		foreach (array('KNOWN_', '') as $known) {
			foreach ($conv as $s) {
				foreach ($sched_junk as $dt_str) {
					$entry = $known . "TIME_$s" . $dt_str;
					if (!isset($cstate[$entry]))
						$cstate[$entry] = 0;
					$entry = "KNOWN_TIME_$s" . $dt_str;
				}
			}
		}

		# For each $state, we need to calculate
		# PERCENT_TOTAL_TIME_$state,
		# PERCENT_TIME_$state_SCHEDULED,
		# PERCENT_TIME_$state_UNSCHEDULED,
		# PERCENT_KNOWN_TIME_$state,
		# PERCENT_KNOWN_TIME_$state_SCHEDULED,
		# PERCENT_KNOWN_TIME_$state_UNSCHEDULED
		$conv['UNDETERMINED'] = 'UNDETERMINED';
		$div = $cstate['TOTAL_TIME_ACTIVE'];
		foreach ($conv as $state) {
			$str = 'TIME_' . $state;
			foreach (array('TOTAL_', 'KNOWN_') as $prefix) {
				$full_str = $prefix . $str;

				if (!isset($cstate[$full_str]))
					$cstate[$full_str] = 0;
				$cstate['PERCENT_' . $full_str] =
					reports::percent($cstate[$full_str], $div);
			}

			foreach (array('', 'KNOWN_') as $known) {
				foreach ($sched_junk as $dt_str) {
					$perc_str = 'PERCENT_' . $known . $str . $dt_str;
					$cstate[$perc_str] =
						reports::percent(arr::search($cstate, $str . $dt_str), $div);
				}
			}

			$str = 'PERCENT_KNOWN_TIME_' . $state;
			$cstate[$str] =
				$cstate[$str . '_SCHEDULED'] + $cstate[$str . '_UNSCHEDULED'];
		}

		# mop up the oddballs and special cases
		$cstate['PERCENT_TIME_UNDETERMINED_NOT_RUNNING'] =
			reports::percent($cstate['TIME_UNDETERMINED_NOT_RUNNING'], $div);
		$cstate['PERCENT_TIME_UNDETERMINED_NO_DATA'] =
			reports::percent($cstate['TIME_UNDETERMINED_NO_DATA'], $div);
		$cstate['PERCENT_TIME_DOWN_COUNTED_AS_UP'] =
			reports::percent($cstate['TIME_DOWN_COUNTED_AS_UP'], $div);

		return $cstate;
	}

	/**
	 * Update the raw uptime array
	 *
	 * @param $end_time When the event ends - start time is taken from st_prev_row
	 */
	public function st_update($end_time)
	{
		$prev_time = $this->st_prev_row['the_time'];
		$duration = $end_time - $prev_time;
		$active = intval($this->timeperiod->active_time($prev_time, $end_time));
		$this->st_inactive += ($end_time - $prev_time) - $active;

		$st = "$this->st_running:$this->st_dt_depth:$this->st_obj_state";
		if ($active) {
			if (!isset($this->st_raw[$st]))
				$this->st_raw[$st] = $active;
			else
				$this->st_raw[$st] += $active;
		}
	}

	/**
	 * Strictly for debugging purposes. Enable this if you get
	 * weird values from the reporting tool for some reason
	 */
	public function check_st_sub_discrepancies($row = false)
	{
		$disc_desc = array();
		$disc = 0;
		$st_sub_totals = 0;

		if (!$this->sub_reports)
			return;

		foreach ($this->st_sub as $state => $objs) {
			$cnt = count($objs[0]) + count($objs[1]);
			if ($cnt < 0)
				echo "WARN: $state count is $cnt\n";
			$st_sub_totals += $cnt;
		}

		$actual = $this->st_sub;
		$stash = array();
		$statecnt = array();
		foreach ($actual as $state => $ary) {
			$actual[$state] = 0;
			foreach ($ary as $objs) {
				$statecnt[$state] += count($objs);
			}
		}
		$real = $actual;
		foreach ($this->sub_reports as $rpt) {
			$actual[$rpt->st_obj_state]++;
			$real[$rpt->st_real_state]++;
			$stash[$rpt->st_obj_state][] = $rpt;
		}

		foreach ($actual as $state => $cnt) {
			if ($statecnt[$state] !== $cnt) {
				$disc_desc[] = "DISCREPANCY: ($state): actual=$cnt; st_sub=" . $statecnt[$state] . "\n";
				$disc++;
			}
		}
		if ($disc != $this->st_sub_discrepancies) {
			echo "Discrepancy change. Old=$this->st_sub_discrepancies; New=$disc\n";
			echo "Last row was: "; print_r($row);
			print_r($disc_desc);
			$src = $row['host_name'];
			foreach ($this->sub_reports as $rpt) {
				if ($rpt->st_source == $src) {
					echo "Current state of offending object: "; print_r($rpt);
					exit(1);
				}
			}
		}
		$this->st_sub_discrepancies = $disc;
	}

	/**
	 * Take a database row object, and parse it
	 * @param $row Database row
	 */
	public function st_parse_row($row = false)
	{
		$obj_name = $obj_type = false;
		if (!empty($row['service_description'])) {
			$obj_name = $row['host_name'] . ";" . $row['service_description'];
			$obj_type = 'Service';
		}
		elseif (!empty($row['host_name'])) {
			$obj_name = $row['host_name'];
			$obj_type = 'Host';
		}

		$rpts = array();
		foreach ($this->sub_reports as $idx => $sr) {
			if ($obj_name === $sr->st_source || ($obj_name === $sr->host_name && $row['event_type'] >= self::DOWNTIME_START) || $row['event_type'] <= self::PROCESS_SHUTDOWN)
				$rpts[$idx] = $sr;
		}

		$this->st_update($row['the_time']);
		foreach ($rpts as $rpt) {
			$rpt->st_update($row['the_time']);
		}

		switch($row['event_type']) {
		 case self::PROCESS_START:
		 case self::PROCESS_SHUTDOWN:
			if ($row['event_type'] == self::PROCESS_START) {
				$row['output'] = 'Monitor started';
			}
			else {
				$row['output'] = 'Monitor shut down';
			}
			// meta-obj or not, we need to get is_running right
			$rpts[] = $this;
			foreach ($rpts as $rpt) {
				$rpt->st_last_dt_init = false;
				if ($row['event_type'] == self::PROCESS_START) {
					$row['state'] = $rpt->st_real_state;
					$rpt->st_running = 1;
				} else if ($this->options['assumestatesduringnotrunning']) {
					$row['state'] = $rpt->st_real_state;
				} else {
					$row['state'] = -1;
					$rpt->st_running = 0;
				}
				$rpt->st_update_log(false, $row);
			}
			$this->calculate_object_state();
			return 0;
		 case self::DOWNTIME_START:
			if(!isset($row['output']) || !$row['output']) {
				$row['output'] = $obj_type . ' has entered a period of scheduled downtime';
			}
			foreach ($rpts as $idx => $rpt) {
				$add = 0;
				# we are always spammed with downtime events after restart, so
				# don't increase the downtime depth if we're already in downtime
				if (!$rpt->st_last_dt_init || $rpt->st_last_dt_init === $row['the_time']) {
					$rpt->st_last_dt_init = $row['the_time'];
					if (!$rpt->st_dt_depth) {
						$add = 1;
					}
				}
				else {
					$add = 1;
				}
				if ($add) {
					$rpt->st_dt_depth++;
					unset($this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth-1][$idx]);
					$this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx] = $idx;
					$rpt->calculate_object_state();
				}
			}
			break;
		 case self::DOWNTIME_STOP:
			if(!isset($row['output']) || !$row['output']) {
				$row['output'] = $obj_type . ' has exited a period of scheduled downtime';
			}
			foreach ($rpts as $idx => $rpt) {
				# old merlin versions created more end events than start events, so
				# never decrement if we're already at 0.
				if ($rpt->st_dt_depth) {
					$rpt->st_dt_depth--;
					unset($this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth+1][$idx]);
					$this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx] = $idx;
					$rpt->calculate_object_state();
				}
			}
			break;
		 case self::SERVICECHECK:
		 case self::HOSTCHECK:
			$state = $row['state'];

			foreach ($rpts as $idx => $rpt) {
				# update the real state of the object
				if ($rpt->st_source === $obj_name) {
					$rpt->st_real_state = $row['state'];

					if ($rpt->st_obj_state != $state) {
						unset($this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx]);
						$this->st_sub[$state][$rpt->st_dt_depth][$idx] = $idx;
					}
				}
				$rpt->calculate_object_state();
			}
			break;
		 default:
			//ERROR
		}

		$this->calculate_object_state();

		foreach ($rpts as $rpt) {
			switch ($row['event_type']) {
			 case self::DOWNTIME_START:
			 case self::DOWNTIME_STOP:
				$row['state'] = $rpt->st_real_state;
				break;
			 default:
				break;
			}
			$rpt->st_update_log(false, $row);
			$this->st_update_log($rpt, $row);
		}
	}

	/**
	 * Calculate worst state for either hosts or services
	 */
	public function st_worst()
	{
		if (empty($this->sub_reports)) {
			return $this->st_obj_state;
		}

		/*
		 * Welcome to todays installment of "The world sucks and I'm tired of
		 * trying to fix it"!
		 *
		 * So, states. States have codes. If you've written plugins
		 * you'll think the "badness" increases with the numeric code. This is
		 * incorrect, of course, because then state comparison would be simple.
		 */
		if ($this->st_is_service)
			$states = array(self::SERVICE_CRITICAL, self::SERVICE_WARNING, self::SERVICE_UNKNOWN, self::SERVICE_OK, self::SERVICE_PENDING);
		else
			$states = array(self::HOST_DOWN, self::HOST_UNREACHABLE, self::HOST_UP, self::HOST_PENDING);

		$final_state = self::SERVICE_OK;

		// Loop through states in order of badness.
		foreach ($states as $state) {
			$keys = array_keys($this->st_sub[$state]);
			// Sort downtime states outside downtime first
			sort($keys);
			foreach ($keys as $in_dt) {
				if (empty($this->st_sub[$state][$in_dt]))
					continue;
				// This would look OK but isn't, go look for non-OK
				// (remember, we sorted, so $in_dt is only true after passing false)
				if ($this->options['scheduleddowntimeasuptime'] && $in_dt)
					break 1;
				// Else, we're done, this is the worst.
				$this->st_dt_depth = $in_dt;
				$final_state = $state;
				break 2;
			}
		}

		// So, scheduleddowntimeasuptime and worst not in sched_down is OK?
		// Maybe there's a non-OK in sched_down...
		if ($this->options['scheduleddowntimeasuptime'] && $final_state === 0) {
			foreach ($states as $state) {
				foreach ($this->st_sub[$state] as $dt_depth => $ary) {
					if (!empty($ary)) {
						$this->st_dt_depth = $dt_depth;
						$final_state = $state;
						break;
					}
				}
			}
		}
		return $final_state;
	}

	/**
	 * Calculate best state for either hosts or services
	 */
	public function st_best()
	{
		if (empty($this->sub_reports)) {
			return $this->st_obj_state;
		}

		if ($this->st_is_service)
			$states = array(self::SERVICE_OK, self::SERVICE_WARNING, self::SERVICE_CRITICAL, self::SERVICE_UNKNOWN, self::SERVICE_PENDING);
		else
			$states = array(self::HOST_UP, self::HOST_DOWN, self::HOST_UNREACHABLE, self::HOST_PENDING);

		$final_state = $states[count($states) - 1];

		foreach ($states as $state) {
			$keys = array_keys($this->st_sub[$state]);
			// Sort downtime states outside downtime first
			sort($keys);
			foreach ($keys as $in_dt) {
				if (!empty($this->st_sub[$state][$in_dt])) {
					$final_state = $state;
					$this->st_dt_depth = $in_dt;
					break 2;
				}
			}
		}
		return $final_state;
	}

	/**
	 * Calculate the object state, based on the chosen state calculator.
	 *
	 * If there is sub reports, the argument will be ignored. Otherwise, use
	 * either the argument or the object's real state, according to magical
	 * properties inherent in the numbers themselves.
	 *
	 * @param $state a nagios state, or not
	 */
	public function calculate_object_state($state = false)
	{
		if ($this->sub_reports) {
			$func = $this->st_state_calculator;
			$state = $this->$func();
		}

		if ($state === false) {
			$state = $this->st_real_state;
		}

		$this->st_obj_state = $this->filter_excluded_state($state);
	}

	/**
	 * Initialize the the state machine for this report
	 */
	public function st_init()
	{
		$this->timeperiod->resolve_timeperiods();
		# single object reports always gets a log
		if (!$this->options['master'] && empty($this->sub_reports)) {
			$this->options['keep_logs'] = true;
		}

		# if user asked for it, we preserve the log
		if ($this->options['keep_logs']) {
			$this->st_log = array();
		}

		if ($this->options['service_description'] || $this->options['servicegroup']) {
			$this->st_is_service = true;
		}
		else {
			# we need at least a service or a host
			if (!$this->options['host_name'] && !$this->options['hostgroup'])
				return false;
		}

		$this->st_text = empty($this->st_is_service) ? self::$host_states : self::$service_states;
		$this->st_text = array_map('strtoupper', $this->st_text);

		$this->st_state_calculator = $this->options['cluster_mode'] ? 'st_best' : 'st_worst';

		# prime the state counter for sub-objects
		if (!empty($this->sub_reports)) {
			foreach ($this->st_text as $st => $discard)
				$this->st_sub[$st] = array();
			foreach ($this->sub_reports as $idx => $rpt) {
				$this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx] = $idx;
			}
			$this->calculate_object_state();
		}
		else {
			$this->st_dt_depth = intval(!!$this->get_initial_dt_depth());
			$initial_state = $this->get_initial_state();
			$this->st_real_state = $this->filter_excluded_state($initial_state);
			$this->calculate_object_state($this->st_real_state);
		}

		# $last_shutdown is only set if monitor is stopped, so set
		# $this->st_running to its boolean inverse.
		$this->st_running = intval(!$this->last_shutdown);

		$fout = "";
		if (!$this->st_running)
			$fevent_type = self::PROCESS_SHUTDOWN;
		else {
			if ($this->st_is_service)
				$fevent_type = self::SERVICECHECK;
			else
				$fevent_type = self::HOSTCHECK;
		}
		$state = ($this->st_running || $this->options['assumestatesduringnotrunning']) ? $this->st_obj_state : -1;
		$this->st_prev_row = array
			('state' => $state,
			 'the_time' => $this->options['start_time'],
			 'event_type' => $fevent_type,
			 'downtime_depth' => $this->st_dt_depth);

		# if we're actually going to use the log, we'll need
		# to generate a faked initial message for it.
		if ($this->options['keep_logs']) {
			$fout = sprintf("Report period start. Daemon is%s running, " .
			                "we're%s in scheduled downtime, state is %s (%d)",
			                $this->st_running ? '' : ' not',
			                $this->st_dt_depth ? '' : ' not',
			                $this->st_text[$state], $state);
			$this->st_prev_row['output'] = $fout;

			if (!empty($hostname) && is_string($hostname))
				$this->st_prev_row['host_name'] = $hostname;

			if (!empty($servicename) && is_string($servicename))
				$this->st_prev_row['service_description'] = $servicename;
		}
	}

	/**
	 * Runs the main query and loops through the results one by one
	 */
	private function st_parse_all_rows()
	{
		$dbr = $this->uptime_query();
		foreach ($dbr as $row) {
			$this->st_parse_row($row);
		}
	}

	private function st_update_log($sub = false, $row = false)
	{
		if($row) {
			$row['state'] = $this->filter_excluded_state($row['state']);
		}
		if (!$this->options['keep_logs']) {
			$this->st_prev_row = $row;
			return;
		}

		# called from st_finalize(), so bail out early
		if (!$sub && !$row) {
			$this->st_prev_row['duration'] = $this->options['end_time'] - $this->st_prev_row['the_time'];
			$active = $this->timeperiod->active_time($this->st_prev_row['the_time'], $this->options['end_time']);
			if ($active > 0 || $active === $this->st_prev_row['duration'])
				$this->st_log[] = $this->st_prev_row;
			else
				$this->st_log[] = array(
					'output' => '(event outside of timeperiod)',
					'the_time' => $this->st_prev_row['the_time'],
					'duration' => $this->st_prev_row['duration'],
					'state' => -2,
					'hard' => 1
				);
			return;
		}

		# we mangle $row here, since $this->st_prev_row is always
		# derived from it, except when it's the initial
		# state which always has (faked) output
		if (empty($row['output']))
			$row['output'] = '(No output)';

		if ($sub) {
			$output = $sub->st_source . ' went from ' . $sub->st_prev_row['state'] .
				' to ' . $row['state'];
			$row['hard'] = 1;
			$row['output'] = $output;
			unset($row['host_name']);
			unset($row['service_description']);
		}

		if ($this->options['scheduleddowntimeasuptime'] && $this->st_dt_depth)
			$row['state'] = self::STATE_OK;

		# don't save states without duration for master objects
		$duration = $row['the_time'] - $this->st_prev_row['the_time'];
		if ($duration || $sub) {
			$this->st_prev_row['duration'] = $duration;
			$active = $this->timeperiod->active_time($this->st_prev_row['the_time'], $row['the_time']);
			if ($active > 0 || ($duration === $active))
				$this->st_log[] = $this->st_prev_row;
			else
				$this->st_log[] = array(
					'output' => '(event outside of timeperiod)',
					'the_time' => $this->st_prev_row['the_time'],
					'duration' => $this->st_prev_row['duration'],
					'state' => -2,
					'hard' => 1
				);
		}

		$this->st_prev_row = $row;
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
	 * Finalize the report, calculating real uptime from our internal
	 * meta-format.
	 *
	 * @return array
	 */
	private function st_finalize()
	{
		// gather remaining time. If they match, it'll be 0
		$this->st_update($this->options['end_time']);
		$this->st_update_log();

		$converted_state = $this->convert_state_table($this->st_raw, $this->st_text);

		# state template array depends on what we are checking
		$tpl = $this->state_tpl_host;
		if ($this->st_is_service)
			$tpl = $this->state_tpl_svc;
		foreach ($tpl as $t => $discard)
			if (!isset($converted_state[$t]))
				$converted_state[$t] = 0;

		if (empty($this->sub_reports)) {
			$converted_state['HOST_NAME'] = $this->host_name;
			if ($this->st_is_service)
				$converted_state['SERVICE_DESCRIPTION'] = $this->service_description;
		} else {
			if ($this->st_is_service) {
				unset($converted_state['HOST_NAME']);
				$converted_state['SERVICE_DESCRIPTION'] = $this->st_source;
			}
			else
				$converted_state['HOST_NAME'] = $this->st_source;
		}

		# now add the time we didn't count due
		# to the selected timeperiod
		$converted_state['TIME_INACTIVE'] = $this->st_inactive;

		$this->states = $converted_state;
		$total_time = $this->options['end_time'] - $this->options['start_time'];
		if (!empty($this->sub_reports)) {
			$log = array();
			foreach ($this->sub_reports as $sr) {
				$log[$sr->st_source] = $sr->st_log;
			}
		}
		else {
			$log = array($this->st_source => $this->st_log);
		}

		$res =  array('source' => $this->st_source, 'log' => $log, 'states' => $converted_state, 'tot_time' => $total_time);
		$groupname = $this->options['hostgroup'] ? $this->options['hostgroup'] : $this->options['servicegroup'];
		if (count($groupname) === 1)
			$res['groupname'] = $groupname[0];
		else if (empty($this->options['master']))
			$res['groupname'] = $groupname;

		return $res;
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
			$in_downtime = ($staterow['event_type'] == Reports_Model::DOWNTIME_START);
			if ( $type == 'service' ) {
				$downtimes[ $staterow['host_name'] . ';' . $staterow['service_description'] ] = $in_downtime;
			} else {
				$downtimes[ $staterow['host_name'] ] = $in_downtime;
			}
		}

		return $downtimes;
	}

	/**
	 * Fetch information about SCHEDULED_DOWNTIME status
	 *
	 * @return Depth of initial downtime.
	 */
	public function get_initial_dt_depth()
	{
		if( isset($this->prefetched_dt_depth) ) {
			$this->initial_dt_depth = $this->prefetched_dt_depth;
			return $this->initial_dt_depth;
		}
		if ($this->initial_dt_depth != false)
			return $this->initial_dt_depth;

		if (is_array($this->st_source)) {
			return false;
		}

		$sql = "SELECT timestamp, event_type FROM " .
			$this->db_table . " " .
			"WHERE timestamp <= " . $this->options['start_time'] . " AND " .
			"(event_type = " . self::DOWNTIME_START .
			" OR event_type = " .self::DOWNTIME_STOP . ") " .
			" AND host_name = ".$this->db->escape($this->host_name);

		if (empty($this->service_description))
			$sql .= " AND (service_description IS NULL OR service_description = '')";
		else
			$sql .= " AND (service_description IS NULL OR service_description = '' " .
				"OR service_description = ".$this->db->escape($this->service_description).')';

		$sql .= " ORDER BY timestamp DESC LIMIT 1";

		$dbr = $this->db->query($sql)->result(false);
		if (!$dbr || !($row = $dbr->current()))
			return false;

		$this->register_db_time($row['timestamp']);
		$this->initial_dt_depth = $row['event_type'] == self::DOWNTIME_START;

		return $this->initial_dt_depth;
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

	/**
	 * Get initital state from db. This is usually required when
	 * selecting states for a host/service when the selected start
	 * time doesn't exactly match a record in db. Note that initial
	 * state can only be obtained for a single object.
	 *
	 * @return FALSE on error. Record from database on success.
	 */
	public function get_initial_state()
	{
		if (empty($this->host_name) && empty($this->service_description))
			return false;

		/* Someone force-pushed a initial_state to us, so we don't need to look for it... */
		if( isset( $this->initial_state ) ) {
			return $this->initial_state;
		}

		$sql = "SELECT timestamp, state FROM " .
			$this->db_table .
			" WHERE host_name = ".$this->db->escape($this->host_name);
		if (!$this->service_description)
			$sql .= " AND (service_description = '' OR service_description IS NULL)";
		else
			$sql .= " AND service_description = " . $this->db->escape($this->service_description);
		$sql .= " AND event_type = ";

		if ($this->service_description) {
			$sql .= self::SERVICECHECK;
		} else {
			$sql .= self::HOSTCHECK;
		}
		if (!$this->options['includesoftstates'])
			$sql .= ' AND hard = 1';

		$sql .= ' ';
		$base_sql = $sql;
		$sql .= "AND timestamp < " . $this->options['start_time'] .
			" ORDER BY timestamp DESC LIMIT 1";

		# first try to fetch the real initial state so
		# we don't have to assume
		$dbr = $this->db->query($sql)->result(false);
		if ($dbr && ($row = $dbr->current())) {
			$initial_state = $row['state'];
		} else {
			$initial_state = self::STATE_PENDING;
		}

		return $initial_state;
	}
}
