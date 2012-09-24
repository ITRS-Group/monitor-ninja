<?php defined('SYSPATH') OR die('No direct access allowed.');

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
 *          ------------+------------+-------------+------------+-------------
 *           scheduled  |  sched up  | unsched up  | sched down | unsched down
 *      UP  ------------+------------+-------------+------------+-------------
 *           unscheduled| unsched up | unsched up  | sched down | unsched down
 * host ----------------+------------+-------------+------------+-------------
 *           scheduled  | sched down | sched down  | sched down | unsched down
 *      DOWN------------+------------+-------------+------------+-------------
 *           unscheduled|unsched down| unsched down|unsched down| unsched down
 *
 * When two sub-objects have different non-OK states, the outcome depends on
 * whether scheduleddowntimeasuptime is used or not. If the option is used,
 * then the service with the worst non-scheduled state is used. If the option
 * is not used, the worst state is used, prioritizing any non-scheduled state.
 *
 * This applies to non-"cluster mode" reports. If you're in cluster mode, this
 * applies backwards exactly.
 */
class Reports_Model extends Model
{
	// state codes
	const STATE_PENDING = -1; /**< Magical state for unchecked objects. In other parts of ninja, 6 is used for this */
	const STATE_OK = 0; /**< "Everything is fine"-state */
	const HOST_UP = 0; /**< Host is up */
	const HOST_DOWN = 1; /**< Host is down */
	const HOST_UNREACHABLE = 2; /**< Host is unreachable */
	const HOST_PENDING = -1; /**< Magical state for unchecked hosts. In other parts of ninja, 6 is used for this */
	const HOST_EXCLUDED = -2; /**< Magical state when a host event falls outside of the specified timeperiod */
	const HOST_ALL = 7; /**< Bitmask for any non-magical host state */
	const SERVICE_OK = 0; /**< Service is up */
	const SERVICE_WARNING = 1; /**< Service is warning */
	const SERVICE_CRITICAL = 2; /**< Service is critical */
	const SERVICE_UNKNOWN = 3; /**< Service is unknown */
	const SERVICE_PENDING = -1; /**< Magical state for unchecked services. In other parts of ninja, 6 is used for this */
	const SERVICE_EXCLUDED = -2; /**< Magical state when a service event falls outside of the specified timeperiod */
	const SERVICE_ALL = 15; /**< Bitmask for any non-magical service state */
	const PROCESS_SHUTDOWN = 103; /**< Nagios code for when it is shut down */
	const PROCESS_RESTART = 102; /**< Nagios code for when it is restarted - not normally added to report_data, check for stop and start instead */
	const PROCESS_START = 100; /**< Nagios code for when it is started */
	const SERVICECHECK = 701; /**< Nagios code for a service check */
	const HOSTCHECK =  801; /**< Nagios code for a host check */
	const DOWNTIME_START = 1103; /**< Nagios code for downtime start */
	const DOWNTIME_STOP = 1104; /**< Nagios code for downtime stop, either because it ended or because it was deleted */
	const PERC_DEC = 3; /**< Nr of decimals in returned percentage */
	const DEBUG = true; /**< Debug bool - can't see this is ever false */

	var $db_start_time = 0; /**< earliest database timestamp we look at */
	var $db_end_time = 0;   /**< latest database timestamp we look at */
	var $debug = array(); /**< Array of the debug information that we print during unit tests */
	var $completion_time = 0; /**< The time it took to generate the report */

	# alert summary options
	private $summary_result = array();
	private $host_hostgroup; /**< array(host => array(hgroup1, hgroupx...)) */
	private $service_servicegroup; /**< array(service => array(sgroup1, sgroupx...))*/

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
	public $db_name = 'merlin'; /**< Report database name */
	const db_name = 'merlin'; /**< Report database name, FIXME: again, 4 teh lulz */
	public $db_table = 'report_data'; /**< Report table name */
	const db_table = 'report_data'; /**< Report table name, FIXME: again, 4 teh lulz */
	public $sub_reports = array(); /**< An array of sub-reports for this report */
	public $last_shutdown = false; /**< Last nagios shutdown event- 0 if we started it again */
	public $states = array(); /**< The final array of report states */
	private $st_state_calculator = 'st_best';

	/** A map of state ID => state name for hosts. FIXME: one of a gazillion */
	static public $host_states = array(
		self::HOST_UP => 'up',
		self::HOST_DOWN => 'down',
		self::HOST_UNREACHABLE => 'unreachable',
		self::HOST_PENDING => 'pending',
		self::HOST_EXCLUDED => 'excluded');

	/** A map of state ID => state name for services. FIXME: one of a gazillion */
	static public $service_states = array(
		self::SERVICE_OK => 'ok',
		self::SERVICE_WARNING => 'warning',
		self::SERVICE_CRITICAL => 'critical',
		self::SERVICE_UNKNOWN => 'unknown',
		self::SERVICE_PENDING => 'pending',
		self::SERVICE_EXCLUDED => 'excluded');

	/** The provided options */
	protected $options = false;
	/** The timeperiod associated with this report */
	protected $timeperiod;

	/**
	 * Constructor
	 * @param $options An instance of Report_options
	 * @param $db_name Database name
	 * @param $db_table Database name
	 */
	public function __construct(Report_options $options, $db_name='merlin', $db_table='report_data')
	{
		parent::__construct();
		if (self::DEBUG === true) {
			assert_options(ASSERT_ACTIVE, 1);
			assert_options(ASSERT_WARNING, 0);
			assert_options(ASSERT_QUIET_EVAL, 0);
			assert_options(ASSERT_BAIL, 1);

			# use report helper callback
			assert_options(ASSERT_CALLBACK, array('reports', 'lib_reports_assert_handler'));
		}

		$this->db_table = $db_table;
		$this->db_name = $db_name;
		$this->st_obj_state = self::STATE_PENDING;

		/** The real state of the object */
		$this->st_real_state = self::STATE_PENDING;
		/** Will be overwritten at report start, if report object exist */
		$this->st_prev_row = array(
			'the_time' => 0,
			'state' => self::STATE_PENDING,
			'output' => 'No data found (are you trying to generate a report with nonexisting objects?)'
		);

		$this->options = $options;
		$this->timeperiod = Timeperiod_Model::instance($options);
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
				$table_exists = $db->table_exists(self::db_table);
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

		$servicename = $this->options['service_description'];
		$hostname = $this->options['host_name'];
		$res_group = false;

		if ($this->options['hostgroup']) {
			$hostname = $this->options->get_report_members();
		} elseif ($this->options['servicegroup']) {
			$servicename = $this->options->get_report_members();
		}

		if ($servicename) {
			foreach ($servicename as $service) {
				$optclass = get_class($this->options);
				$opts = new $optclass($this->options);
				$opts['service_description'] = $service;
				$opts['master'] = $this;
				$opts['keep_logs'] = $this->options['keep_sub_logs'];
				$sub_class = new Reports_Model($opts, $this->db_name, $this->db_table);
				$sub_class->register_db_time($opts['start_time']);
				$sub_class->register_db_time($opts['end_time']);
				$sub_class->st_source = $service;
				$srv = explode(';', $service);
				$sub_class->host_name =  $srv[0];
				$sub_class->service_description = $srv[1];
				$sub_class->last_shutdown = $this->last_shutdown;
				$sub_class->st_init();
				$this->sub_reports[$service] = $sub_class;
			}
			$this->st_source = $servicename;
		} else if ($hostname) {
			foreach ($hostname as $host) {
				$optclass = get_class($this->options);
				$opts = new $optclass($this->options);
				$opts['keep_logs'] = $this->options['keep_sub_logs'];
				$opts['host_name'] = $host;
				$opts['master'] = $this;
				$sub_class = new Reports_Model($opts, $this->db_name, $this->db_table);
				$sub_class->register_db_time($opts['start_time']);
				$sub_class->register_db_time($opts['end_time']);
				$sub_class->st_source = $host;
				$sub_class->host_name = $host;
				$sub_class->last_shutdown = $this->last_shutdown;
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
		if ($this->last_shutdown != false)
			return $this->last_shutdown;

		# If we're assuming states during program downtime,
		# we don't really need to know when the last shutdown
		# took place, as the initial state will be used regardless
		# of whether or not Monitor was up and running.
		if ($this->options['assumestatesduringnotrunning']) {
			$this->last_shutdown = 0;
			return 0;
		}

		$query = "SELECT timestamp, event_type FROM ".
			$this->db_name.".".$this->db_table.
			" WHERE timestamp <".$this->options['start_time'].
			" ORDER BY timestamp DESC LIMIT 1";
		$dbr = $this->db->query($query)->result(false);

		if (!$dbr || !($row = $dbr->current()))
			return false;

		$this->register_db_time($row['timestamp']);
		$event_type = $row['event_type'];
		if ($event_type==self::PROCESS_SHUTDOWN || $event_type==self::PROCESS_RESTART)
			$this->last_shutdown = $row['timestamp'];
		else
			$this->last_shutdown = 0;

		return $this->last_shutdown;
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
	 * Retrieve a user-friendly representation for nagios codes
	 *
	 * @param $event_type
	 * @param $object_type = null (host or service)
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public static function event_type_to_string($event_type, $object_type = null) {
		switch($event_type) {
			case self::PROCESS_SHUTDOWN:
				return _('Monitor shut down');
			case self::PROCESS_RESTART:
				return _('Monitor restart');
			case self::PROCESS_START:
				return _('Monitor started');
			case self::SERVICECHECK:
				return _('Service alert');
			case self::HOSTCHECK:
				return _('Host alert');
			case self::DOWNTIME_START:
				return _($object_type . ' has entered a period of scheduled downtime');
			case self::DOWNTIME_STOP:
				return _($object_type . ' has exited a period of scheduled downtime');
			default:
				throw new InvalidArgumentException("Invalid event type '$event_type' in ".__METHOD__.":".__LINE__);
		}
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

		# prime the state counter for sub-objects
		if (!empty($this->sub_reports)) {
			foreach ($this->st_text as $st => $discard)
				$this->st_sub[$st] = array();
			foreach ($this->sub_reports as $idx => $rpt) {
				$rpt->calculate_object_state();
				$this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx] = $idx;
			}
			$this->calculate_object_state();
		}
		else {
			$this->st_dt_depth = intval(!!$this->get_initial_dt_depth());
			$this->st_real_state = $this->filter_excluded_state($this->get_initial_state());
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

		$this->st_state_calculator = $this->options['cluster_mode'] ? 'st_best' : 'st_worst';
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
	 * Finalize the report, calculating real uptime from our internal
	 * meta-format.
	 *
	 * @return array
	 */
	private function st_finalize()
	{
		# gather remaining time. If they match, it'll be 0
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
		$groupname = $this->options['hostgroup'] ? $this->options['hostgroup'] : $this->options['servicegroup'];
		if (count($groupname) === 1)
			$groupname = $groupname[0];
		if (!empty($this->sub_reports)) {
			$log = array();
			foreach ($this->sub_reports as $sr) {
				$log[$sr->st_source] = $sr->st_log;
			}
		}
		else {
			$log = array($this->st_source => $this->st_log);
		}

		return array('source' => $this->st_source, 'log' => $log, 'states' => $converted_state, 'tot_time' => $total_time, 'groupname' => $groupname);
	}

	/**
	 * Get log details for host/service
	 *
	 * @return PDO result object on success. FALSE on error.
	 */
	public function uptime_query()
	{
		$event_type = self::HOSTCHECK;
		if ($this->st_is_service) {
			$event_type = self::SERVICECHECK;
		}

		# this query works out automatically, as we definitely don't
		# want to get all state entries for a hosts services when
		# we're only asking for uptime of the host
		$sql = "SELECT host_name, service_description, " .
			"state,timestamp AS the_time, hard, event_type";
		# output is a TEXT field, so it needs an extra disk
		# lookup to fetch and we don't always need it
		if ($this->options['keep_logs'])
			$sql .= ", output";

		$sql .= " FROM ".$this->db_name.".".$this->db_table." ";

		$time_first = 'timestamp >='.$this->options['start_time'];
		$time_last = 'timestamp <='.$this->options['end_time'];
		$process = false;
		$purehost = false;
		$objsel = false;
		$downtime = 'event_type=' . self::DOWNTIME_START . ' OR event_type=' . self::DOWNTIME_STOP;
		$softorhardcheck = 'event_type=' . ($this->st_is_service ? self::SERVICECHECK : self::HOSTCHECK);

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
	 * Fetch information about SCHEDULED_DOWNTIME status
	 *
	 * @return Depth of initial downtime.
	 */
	public function get_initial_dt_depth()
	{
		if ($this->initial_dt_depth != false)
			return $this->initial_dt_depth;

		if (is_array($this->st_source)) {
			return false;
		}

		$sql = "SELECT timestamp, event_type FROM " .
			$this->db_name . "." . $this->db_table . " " .
			"WHERE timestamp <= " . $this->options['start_time'] . " AND " .
			"(event_type = " . self::DOWNTIME_START .
			" OR event_type = " .self::DOWNTIME_STOP . ") " .
			" AND host_name = ".$this->db->escape($this->host_name);

		if (empty($this->service_description))
			$sql .= " AND service_description IS NULL OR service_description = ''";
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

		$sql = "SELECT timestamp, state FROM " .
			$this->db_name . "." . $this->db_table .
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

	/**
	 * Create the base of the query to use when calculating
	 * alert summary. Each caller is responsible for adding
	 * sorting and limit options as necessary.
	 *
	 * @param $fields Database fields the caller needs
	 */
	private function build_alert_summary_query($fields = false)
	{
		# default to the most commonly used fields
		if (!$fields) {
			$fields = 'host_name, service_description, state, hard';
		}

		$softorhard = false;
		$alert_types = false;
		$downtime = false;
		$process = false;
		$time_first = false;
		$time_last = false;

		$hosts = false;
		$services = false;
		if ($this->options['servicegroup']) {
			$hosts = $services = array();
			$smod = new Service_Model();
			foreach ($this->options['servicegroup'] as $sg) {
				$res = $smod->get_services_for_group($sg);
				foreach ($res as $o) {
					$name = $o->host_name . ';' . $o->service_description;
					if (empty($services[$name])) {
						$services[$name] = array();
					}
					$services[$name][$sg] = $sg;
					if (empty($hosts[$o->host_name])) {
						$hosts[$o->host_name] = array();
					}
					$hosts[$o->host_name][$sg] = $sg;
				}
			}
			$this->service_servicegroup['host'] = $hosts;
			$this->service_servicegroup['service'] = $services;
			$services = false;
		} elseif ($this->options['hostgroup']) {
			$hosts = array();
			$hmod = new Host_Model();
			foreach ($this->options['hostgroup'] as $hg) {
				$res = $hmod->get_hosts_for_group($hg);
				foreach ($res as $o) {
					$name = $o->host_name;
					if (empty($hosts[$name])) {
						$hosts[$name] = array();
					}
					$hosts[$name][$hg] = $hg;
				}
			}
			$this->host_hostgroup = $hosts;
		} elseif ($this->options['service_description']) {
			$services = false;
			if($this->options['service_description'] === Report_options::ALL_AUTHORIZED) {
				$services = Report_options::ALL_AUTHORIZED;
			} else {
				foreach ($this->options['service_description'] as $srv) {
					$services[$srv] = $srv;
				}
			}
		} elseif ($this->options['host_name']) {
			$hosts = false;
			if($this->options['host_name'] === Report_options::ALL_AUTHORIZED) {
				$hosts = Report_options::ALL_AUTHORIZED;
			} else {
				if (is_array($this->options['host_name'])) {
					foreach ($this->options['host_name'] as $hn)
						$hosts[$hn] = $hn;
				} else {
					$hosts[$this->options['host_name']] = $this->options['host_name'];
				}
			}
		}

		if (empty($hosts) && empty($services)) {
			return false;
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
					$object_selection .= $orstr . "(host_name = '" . $h . "' ";
					if (!$s)
						$object_selection .= "AND (service_description = '' OR service_description IS NULL))";
					else
						$object_selection .= "AND service_description = '" . $s . "')";
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
		 case 0: case 3: default:
			break;
		 case 1:
			$softorhard = 'hard = 0';
			break;
		 case 2:
			$softorhard = 'hard = 1';
			break;
		}

		if (!$this->options['host_states'] || $this->options['host_states'] == self::HOST_ALL) {
			$host_states_sql = 'event_type = ' . self::HOSTCHECK;
		} else {
			$x = array();
			$host_states_sql = '(event_type = ' . self::HOSTCHECK . ' ' .
				'AND state IN(';
			for ($i = 0; $i < self::HOST_ALL; $i++) {
				if (1 << $i & $this->options['host_states']) {
					$x[$i] = $i;
				}
			}
			$host_states_sql .= join(',', $x) . '))';
		}

		if (!$this->options['service_states'] || $this->options['service_states'] == self::SERVICE_ALL) {
			$service_states_sql = 'event_type = ' . self::SERVICECHECK;
		} else {
			$x = array();
			$service_states_sql = '(event_type = ' . self::SERVICECHECK .
				"\nAND state IN(";
			for ($i = 0; $i < self::SERVICE_ALL; $i++) {
				if (1 << $i & $this->options['service_states']) {
					$x[$i] = $i;
				}
			}
			$service_states_sql .= join(',', $x) . '))';
		}

		switch ($this->options['alert_types']) {
		case 1:
			$alert_types = $host_states_sql;
			break;
		case 2:
			$alert_types = $service_states_sql;
			break;
		 case 3:
			$alert_types = sql::combine('or', $host_states_sql, $service_states_sql);
			break;
		}

		if ($this->options['include_downtime'])
			$downtime = 'event_type < 1200 AND event_type > 1100';

		if ($this->options['include_process'])
			$process = 'event_type < 200';

		$time_first = 'timestamp >= ' . $this->options['start_time'];
		$time_last = 'timestamp <= ' . $this->options['end_time'];

		$query = "SELECT " . $fields . "\nFROM " . $this->db_table;
		$query .= ' WHERE '.
			sql::combine('and',
				$time_first,
				$time_last,
				sql::combine('or',
					$process,
					sql::combine('and',
						$object_selection,
						sql::combine('or',
							$downtime,
							sql::combine('and',
								$softorhard,
								$alert_types)))));

		return $query;
	}

	/**
	 * Given a query, generate debug information.
	 *
	 * While it's made for testing summary queries, it's completely generic
	 */
	public function test_summary_query($query)
	{
		$dbr = $this->db->query("EXPLAIN " . $query)->result(false);
		if (!$dbr) {
			echo Kohana::debug($this->db->errorinfo(), explode("\n", $query));
		}
		return $dbr->current();
	}

	/**
	 * Used by summary model to generate debug information for queries
	 */
	public function test_summary_queries()
	{
		$this->options['host_name'] = Nagios_auth_Model::instance()->get_authorized_hosts();
		$result = array();
		for ($host_state = 1; $host_state <= 7; $host_state++) {
			$this->options['host_states'] = $host_state;
			for ($service_state = 1; $service_state <= 15; $service_state++) {
				$this->options['service_states'] = $service_state;
				for ($state_types = 1; $state_types <= 3; $state_types++) {
					$this->options['state_types'] = $state_types;
					for ($alert_types = 1; $alert_types <= 3; $alert_types++) {
						$this->options['alert_types'] = $alert_types;
						$query = $this->build_alert_summary_query(false);
						if (!$query)
							return "FAIL: host_state:$host_state;service_state:$service_state;state_type:$state_types;alert_types:$alert_types;";
						$result[$query] = $this->test_summary_query($query);
					}
				}
			}
		}
		return $result;
	}


	private function comparable_state($row)
	{
		return $row['state'] << 1 | $row['hard'];
	}

	/**
	 * Get alert summary for "top (hard) alert producers"
	 *
	 * @return Array in the form { rank => array() }
	 */
	public function top_alert_producers()
	{
		$start = microtime(true);
		$host_states = $this->options['host_states'];
		$service_states = $this->options['service_states'];
		$query = $this->build_alert_summary_query();

		$dbr = $this->db->query($query);
		if (!is_object($dbr)) {
			return false;
		}
		$dbr = $dbr->result(false);
		$result = array();
		$pstate = array();
		foreach ($dbr as $row) {
			if (empty($row['service_description'])) {
				$name = $row['host_name'];
				$interesting_states = $host_states;
			} else {
				$name = $row['host_name'] . ';' . $row['service_description'];
				$interesting_states = $service_states;
			}

			# only count true state-changes
			$state = $this->comparable_state($row);
			if (isset($pstate[$name]) && $pstate[$name] === $state) {
				continue;
			}
			$pstate[$name] = $state;

			# if we're not interested in this state, just move along
			if (!(1 << $row['state'] & $interesting_states)) {
				continue;
			}

			if (empty($result[$name])) {
				$result[$name] = 1;
			} else {
				$result[$name]++;
			}
		}

		# sort the result and return only the necessary items
		arsort($result);
		if ($this->options['summary_items'] > 0) {
			$result = array_slice($result, 0, $this->options['summary_items'], true);
		}

		$i = 1;
		$this->summary_result = array();
		foreach ($result as $obj => $alerts) {
				$ary = array();
			if (strstr($obj, ';')) {
				$obj_ary = explode(';', $obj);
				$ary['host_name'] = $obj_ary[0];
				$ary['service_description'] = $obj_ary[1];
				$ary['event_type'] = self::SERVICECHECK;
			} else {
				$ary['host_name'] = $obj;
				$ary['event_type'] = self::HOSTCHECK;
			}
			$ary['total_alerts'] = $alerts;
			$this->summary_result[$i++] = $ary;
		}
		$this->completion_time = microtime(true) - $start;
		return $this->summary_result;
	}

	private function set_alert_total_totals(&$result)
	{
		foreach ($result as $name => $ary) {
			$ary['total'] = 0;
			foreach ($ary as $type => $state_ary) {
				if ($type === 'total')
					continue;
				$ary[$type . '_totals'] = array('soft' => 0, 'hard' => 0);
				$ary[$type . '_total'] = 0;
				foreach ($state_ary as $sh) {
					$ary[$type . '_totals']['soft'] += $sh[0];
					$ary[$type . '_totals']['hard'] += $sh[1];
					$ary[$type . '_total'] += $sh[0] + $sh[1];
					$ary['total'] += $sh[0] + $sh[1];
				}
			}
			$result[$name] = $ary;
		}
	}

	private function alert_totals_by_host($dbr)
	{
		$template = $this->summary_result;
		$result = array();
		foreach ($this->options['host_name'] as $hn) {
			$result[$hn] = $template;
		}
		$pstate = array();
		foreach ($dbr as $row) {
			if (empty($row['service_description'])) {
				$type = 'host';
				$sname = $row['host_name'];
			} else {
				$type = 'service';
				$sname = $row['host_name'] . ';' . $row['service_description'];
			}

			# only count real state-changes
			$state = $this->comparable_state($row);
			if (isset($pstate[$sname]) && $pstate[$sname] === $pstate[$sname]) {
				continue;
			}
			$pstate[$sname] = $state;

			$name = $row['host_name'];
			$result[$name][$type][$row['state']][$row['hard']]++;
		}

		return $result;
	}

	private function alert_totals_by_service($dbr)
	{
		$template = $this->summary_result;
		$result = array();
		foreach ($this->options['service_description'] as $name) {
			$result[$name] = $template;
		}
		$type = 'service';
		$pstate = array();
		foreach ($dbr as $row) {
			$name = $row['host_name'] . ';' . $row['service_description'];
			$state = $this->comparable_state($row);
			if (isset($pstate[$name]) && $pstate[$name] === $state) {
				continue;
			}
			$pstate[$name] = $state;
			$result[$name][$type][$row['state']][$row['hard']]++;
		}

		return $result;
	}


	private function alert_totals_by_hostgroup($dbr)
	{
		# pre-load the result set to keep conditionals away
		# from the inner loop
		$template = $this->summary_result;
		$result = array();
		foreach ($this->options['hostgroup'] as $hostgroup) {
			$result[$hostgroup] = $template;
		}

		$pstate = array();
		foreach ($dbr as $row) {
			if (empty($row['service_description'])) {
				$type = 'host';
				$name = $row['host_name'];
			} else {
				$type = 'service';
				$name = $row['host_name'] . ';' . $row['service_description'];
			}
			$state = $this->comparable_state($row);
			if (isset($pstate[$name]) && $pstate[$name] === $state) {
				continue;
			}
			$pstate[$name] = $state;
			$hostgroups = $this->host_hostgroup[$row['host_name']];
			foreach ($hostgroups as $hostgroup) {
				$result[$hostgroup][$type][$row['state']][$row['hard']]++;
			}
		}
		return $result;
	}


	private function alert_totals_by_servicegroup($dbr)
	{
		# pre-load the result set to keep conditionals away
		# from the inner loop
		$template = $this->summary_result;
		$result = array();
		foreach ($this->options['servicegroup'] as $servicegroup) {
			$result[$servicegroup] = $template;
		}

		$pstate = array();
		foreach ($dbr as $row) {
			if (empty($row['service_description'])) {
				$type = 'host';
				$name = $row['host_name'];
			} else {
				$type = 'service';
				$name = $row['host_name'] . ';' . $row['service_description'];
				if (!isset($this->service_servicegroup[$type][$name]))
					continue;
			}
			$state = $this->comparable_state($row);
			if (isset($pstate[$name]) && $pstate[$name] === $state) {
				continue;
			}
			$pstate[$name] = $state;

			$servicegroups = $this->service_servicegroup[$type][$name];
			foreach ($servicegroups as $sg) {
				$result[$sg][$type][$row['state']][$row['hard']]++;
			}
		}
		return $result;
	}

	/**
	 * Get alert totals. This is identical to the toplist in
	 * many respects, but the result array is different.
	 *
	 * @return Array of counts divided by object types and states
	 */
	public function alert_totals()
	{
		$this->completion_time = microtime(true);
		$query = $this->build_alert_summary_query();

		$dbr = $this->db->query($query)->result(false);
		if (!is_object($dbr)) {
			echo Kohana::debug($this->db->errorinfo(), explode("\n", $query));
		}

		# preparing the result array in advance speeds up the
		# parsing somewhat. Completing it either way makes it
		# easier to write templates for it as well.
		# We stash it in $this->summary_result so all functions
		# can take advantage of it
		for ($state = 0; $state < 4; $state++) {
			$this->summary_result['host'][$state] = array(0, 0);
			$this->summary_result['service'][$state] = array(0, 0);
		}
		unset($this->summary_result['host'][3]);

		$result = false;
		# groups must be first here, since the other variables
		# are expanded in the build_alert_summary_query() method
		switch ($this->options['report_type']) {
		 case 'servicegroups':
			$result = $this->alert_totals_by_servicegroup($dbr);
			break;
		 case 'hostgroups':
			$result = $this->alert_totals_by_hostgroup($dbr);
			break;
		 case 'services':
			$result = $this->alert_totals_by_service($dbr);
			break;
		 case 'hosts':
			$result = $this->alert_totals_by_host($dbr);
			break;
		}

		$this->set_alert_total_totals($result);
		$this->summary_result = $result;
		$this->completion_time = microtime(true) - $this->completion_time;
		return $this->summary_result;
	}

	/**
	 * Find and return the latest $this->options['summary_items'] alert
	 * producers according to the search criteria.
	 */
	public function recent_alerts()
	{
		$this->completion_time = microtime(true);
		$query = $this->build_alert_summary_query('*');

		if ($query === false) {
			return false;
		}

		$query .= ' ORDER BY timestamp '.($this->options['oldest_first']?'ASC':'DESC');
		if ($this->options['summary_items'] > 0) {
			$query .= " LIMIT " . $this->options['summary_items'];
			if ($this->options['page'])
				$query .= ' OFFSET ' . ($this->options['summary_items'] * ($this->options['page'] - 1));
		}

		$query = 'SELECT data.*, comments.username, comments.user_comment FROM ('.$query.') data LEFT JOIN ninja_report_comments comments ON data.timestamp = comments.timestamp AND data.host_name = comments.host_name AND data.service_description = comments.service_description AND data.event_type = comments.event_type';

		$dbr = $this->db->query($query)->result(false);
		if (!is_object($dbr)) {
			echo Kohana::debug($this->db->errorinfo(), explode("\n", $query));
		}

		$this->summary_result = array();
		foreach ($dbr as $row) {
			if ($this->timeperiod->inside($row['timestamp']))
				$this->summary_result[] = $row;
		}

		$this->completion_time = microtime(true) - $this->completion_time;
		return $this->summary_result;
	}

	/**
	 * Add a new comment to the event pointed to by the timestamp/event_type/host_name/service
	 */
	public static function add_event_comment($timestamp, $event_type, $host_name, $service, $comment, $username) {
		$db = Database::instance();
		$db->query('DELETE FROM ninja_report_comments WHERE timestamp='.$db->escape($timestamp).' AND event_type = '.$db->escape($event_type).' AND host_name = '.$db->escape($host_name).' AND service_description = '.$db->escape($service));
		$db->query('INSERT INTO ninja_report_comments(timestamp, event_type, host_name, service_description, comment_timestamp, username, user_comment) VALUES ('.$db->escape($timestamp).', '.$db->escape($event_type).', '.$db->escape($host_name).', '.$db->escape($service).', UNIX_TIMESTAMP(), '.$db->escape($username).', '.$db->escape($comment).')');
		return true;
	}

	/**
	 * Fetch and print the SQL insert statements we need to run to duplicate
	 * the data-set the report used to generate its data.
	 */
	public static function print_db_lines($prefix, $table = 'report_data', $test, $db_start_time, $db_end_time)
	{
		$db = Database::instance();
		$return_str = '';
		$start = $db_start_time;
		$stop = $db_end_time;
		$query = "SELECT * FROM ".$table." " .
			"WHERE timestamp >= ".$db_start_time." AND timestamp <= ".$db_end_time;
		if (!empty($test['service_description'])) {
			$ignore_event = 801;
			$objects = $test['service_description'];
			$otype = 'concat(concat(host_name, ";"), service_description)';
			if (!is_array($objects))
				$objects = array($objects);
			$objects[] = ';';
		} else {
			$ignore_event = 701;
			$objects = $test['host_name'];
			$otype = 'host_name';
			if (!is_array($objects))
				$objects = array($objects);
			$objects[] = '';
		}
		$query .= " AND event_type != ".$ignore_event." " .
			"AND ".$otype." IN ('" . join("', '", $objects) . "') ";
		$res = $db->query($query)->result(false);

		if (!$res) {
			return;
		}

		$return_str .= "\tsql {\n";
		foreach ($res as $row) {
			$return_str .= "\t\tINSERT INTO ".$table."(" . join(',', array_keys($row)) . ')';
			$return_str .=" VALUES(";
			$first = true;
			foreach ($row as $v) {
				if (!$first)
					$return_str .= ",";
				else
					$first = false;
				$return_str .= $db->escape($v);
			}
			$return_str .= ");\n";
		}
		$return_str .= "\t}\n";

		return $return_str;
	}

	/**
	*	Fetch alert history for histogram report
	* 	@param $slots array with slots to fill with data
	* 	@return array with keys: min, max, avg, data
	*/
	public function histogram($slots=false)
	{
		if (empty($slots) || !is_array($slots))
			return false;

		$breakdown = $this->options['breakdown'];
		$report_type = $this->options['report_type'];
		$newstatesonly = $this->options['newstatesonly'];

		# compute what event counters we need depending on report type
		$events = false;
		switch ($report_type) {
			case 'hosts': case 'hostgroups':
				if (!$this->options['host_states'] || $this->options['host_states'] == self::HOST_ALL) {
					$events = array(0 => 0, 1 => 0, 2 => 0);
				} else {
					$events = array();
					for ($i = 0; $i < 7; $i++) {
						if (1 << $i & $this->options['host_states']) {
							$events[$i] = 0;
						}
					}
				}
				break;
			case 'services': case 'servicegroups':
				if (!$this->options['service_states'] || $this->options['service_states'] == self::SERVICE_ALL) {
					$events = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
				} else {
					$events = array();
					for ($i = 0; $i < 15; $i++) {
						if (1 << $i & $this->options['service_states']) {
							$events[$i] = 0;
						}
					}
				}
				break;
		}

		# add event (state) counters to slots
		$fixed_slots = false;
		foreach ($slots as $s => $l) {
			$fixed_slots[$l] = $events;
		}

		# fields to fetch from db
		$fields = 'timestamp, event_type, host_name, service_description, state, hard, retry';
		$query = $this->build_alert_summary_query($fields);

		$data = false;

		# tell histogram_data() how to treat timestamp
		$date_str = false;
		switch ($breakdown) {
			case 'monthly':
				$date_str = 'n';
				break;
			case 'dayofmonth':
				$date_str = 'j';
				break;
			case 'dayofweek':
				$date_str = 'N';
				break;
			case 'hourly':
				$date_str = 'H';
				break;
		}

		$data = $this->histogram_data($query, $date_str, $fixed_slots, $newstatesonly);

		$min = $events;
		$max = $events;
		$avg = $events;
		$sum = $events;
		if (!empty($data)) {
			foreach ($data as $slot => $slotstates) {
				foreach ($slotstates as $id => $val) {
					if ($val > $max[$id]) $max[$id] = $val;
					if ($val < $min[$id]) $min[$id] = $val;
					$sum[$id] += $val;
				}
			}
			foreach ($max as $v => $k) {
				if ($k != 0) {
					$avg[$v] = number_format(($k/count($data)), 2);
				}
			}
			return array('min' => $min, 'max' => $max, 'avg' => $avg, 'sum' => $sum, 'data' => $data);
		}
		return false;
	}

	/**
	*	Populate slots for histogram
	*
	* 	@param $query sql
	* 	@param $date_str string for use in PHP date()
	* 	@param $slots array with slots to fill with data
	* 	@param $newstatesonly bool Used to decide if to ignore repated events or not
	* 	@return array Populated slots array with found data
	*/
	public function histogram_data($query, $date_str='j' , $slots=false, $newstatesonly=false)
	{
		if (empty($slots)) {
			return false;
		}

		$res = $this->db->query($query)->result(false);
		if (!$res) {
			return false;
		}
		$last_state = null;
		foreach ($res as $row) {
			if ($newstatesonly) {
				if ($row['state'] != $last_state) {
					# only count this state if it differs from the last
					$slots[date($date_str, $row['timestamp'])][$row['state']]++;
				}
			} else {
				$slots[date($date_str, $row['timestamp'])][$row['state']]++;
			}
			$last_state = $row['state'];
		}
		return $slots;
	}
}
