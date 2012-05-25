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
 * whether scheduled_downtime_as_uptime is used or not. If the option is used,
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
	const DATERANGE_CALENDAR_DATE = 0; 	/**< eg: 2008-12-25 - 2009-02-01 / 6 */
	const DATERANGE_MONTH_DATE = 1;  	/**< eg: july 4 - november 15 / 3 (specific month) */
	const DATERANGE_MONTH_DAY = 2;  	/**< eg: day 1 - 25 / 5  (generic month)  */
	const DATERANGE_MONTH_WEEK_DAY = 3; /**< eg: thursday 1 april - tuesday 2 may / 2 (specific month) */
	const DATERANGE_WEEK_DAY = 4;  		/**< eg: thursday 3 - monday 4 (generic month) */
	const DATERANGE_TYPES = 5; /**< FIXME: incomprehensible magic */

	var $db_start_time = 0; /**< earliest database timestamp we look at */
	var $db_end_time = 0;   /**< latest database timestamp we look at */
	var $debug = array(); /**< Array of the debug information that we print during unit tests */
	var $completion_time = 0; /**< The time it took to generate the report */

	# alert summary options
	var $alert_types = 3; /**< Bitmask of host(1) and service(2) alerts - both by default */
	var $state_types = 3; /**< Bitmask of soft(1) and hard(2) states - both by default */
	var $host_states = self::HOST_ALL; /**< Bitmask of host states to include */
	var $service_states = self::SERVICE_ALL; /**< Bitmask of service states to include */
	var $summary_items = 25; /**< Max items to return */
	private $summary_result = array();
	private $summary_query = '';
	private $host_hostgroup; /**< array(host => array(hgroup1, hgroupx...)) */
	private $service_servicegroup; /**< same as $host_hostgroup */

	var $st_raw = array(); /**< Mapping between the raw states and the time spent there */
	var $st_needs_log = false; /**< Generating a log of states takes time, so don't by default */
	var $keep_sub_logs = false; /**< This will be copied to any subreports' $st_needs_log */
	var $st_log = false; /**< The log array */
	var $st_prev_row = array(); /**< The last db row, so we can get duration */
	var $st_running = 0; /**< Is nagios running? */
	var $st_last_dt_init = 1; /**< set to FALSE on nagios restart, and a timestamp on first DT start after restart, so we can exclude duplicate downtime_start */
	var $st_dt_depth = 0; /**< The downtime depth */
	var $st_is_service = false; /**< Whether this is a service */
	var $st_source = false; /**< The source object */
	var $st_inactive = 0; /**< Time we have no information about */
	var $st_text = array(); /**< Mapping between state integers and state text */
	var $st_sub = array(); /**< Map of sub report [state => [downtime_status => [indexes]]] */
	var $st_sub_discrepancies = 0; /**< Sub report appears to be weirded out */
	var $st_obj_type = ''; /**< Object type (FIXME: haven't we already covered this?) */
	var $st_state_calculator = 'st_worst'; /**< Whether to use normal SLA (worst state) or clustered (best state) */

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

	public $master = false; /**< Master report, compare with sub_reports */
	public $id = ''; /**< Another way of saving and getting the object name (FIXME: just use $st_source?) */
	public $result = array(); /**< FIXME: If I remove this, something obscure will break, but this isn't even used, is it? */
	public $csv_result = array(); /**< FIXME: not used? */
	public $old_csv_result = array(); /**< FIXME: not used? */
	public $sub_results = array(); /**< FIXME: not used? */
	public $use_average = false; /**< Calculate average state */
	public $assume_states_during_not_running = false; /**< If true, monitor downtime is ignored */
	public $initial_assumed_host_state = false; /**< The state to start a host report with, if $assume_initial_states */
	public $initial_assumed_service_state = false; /**< The state to start a service report with, if $assume_initial_states */
	public $scheduled_downtime_as_uptime = false; /**< Cheat by calling downtimes uptime */
	public $include_soft_states = true; /**< NOTE: defaults to true */
	public $report_timeperiod = false; /**< Only include events and time in this timeperiod - not to be confused with the report start and stop */
	public $timeperiods_resolved = false; /**< whether timeperiod exceptions and exclusions have been resolved */
	public $initial_state = false; /**< Initial state actually used */
	public $initial_dt_depth = false; /**< The initial downtime depth. NOTE: this is scary, what if there's a dozen 365 day long downtimes active at once or bugs caused us to forget to end downtimes? */
	public $start_time = false; /**< Report start timestamp */
	public $end_time = false; /**< Report end timestamp */
	public $assume_initial_states = null; /**< Should we assume initial states? If yes, use initial_assumed_{host,service}_state */
	public $host_name = false; /**< The hosts affected by this report - could be a hostgroup, could be a service's host, could be all hosts in a servicegroup. Magic and scary */
	public $service_description = false; /**< The services affected by this report - false if report only affects hosts, but could also be a list of services that we're interested in, without the host part. FIXME: wait, so, what happens when there's a servicegroup? */
	public $servicegroup = false; /**< The name of one or several servicegroups to generate the report for */
	public $hostgroup = false; /**< The name of one or several hostgroups to generate the report for */
	public $db_name = 'merlin'; /**< Report database name */
	const db_name = 'merlin'; /**< Report database name, FIXME: again, 4 teh lulz */
	public $db_table = 'report_data'; /**< Report table name */
	const db_table = 'report_data'; /**< Report table name, FIXME: again, 4 teh lulz */
	public $sub_reports = array(); /**< An array of sub-reports for this report */
	public $last_shutdown = false; /**< Last nagios shutdown event- 0 if we started it again */
	public $states = array(); /**< The final array of report states */
	public $tp_exceptions = array(); /**< Timeperiod exceptions */
	public $tp_excludes = array(); /**< Timeperiod excludes */

	/** The provided options */
	public $options = array();

	/**
	 * Constructor
	 * @param $db_name Database name
	 * @param $db_table Database name
	 */
	public function __construct($db_name='merlin', $db_table='report_data')
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
		$this->st_prev_row = array(
			'the_time' => 0,
			'state' => self::STATE_PENDING,
			'output' => 'No data found (are you trying to generate a report
			with unexisting objects?)'
		);
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
		if ((isset($this->options['service_filter_status']) && $this->st_is_service && (!isset($this->options['service_filter_status'][$state]) || !$this->options['service_filter_status'][$state])) || isset($this->options['host_filter_status']) && !$this->st_is_service && (!isset($this->options['host_filter_status'][$state]) || !$this->options['host_filter_status'][$state])) {
			return self::HOST_EXCLUDED;
		}
		return $state;
	}


	/**
	 * Parses a timerange string
	 * FIXME: add more validation
	 * @param $str string
	 * @return An array of timeranges
	 * E.g:
	 * $str="08:00-12:00,13:00-17:00" gives:
	 * array
	 * (
	 * 		array('start' => '08:00', 'stop' => '12:00'),
	 * 		array('start' => '13:00', 'stop' => '17:00')
	 * );
	 */
	public function tp_parse_day($str)
	{
		if (!$str)
			return 0;

		$i = 0;
		$ret = array();

		$ents = explode(',', $str);
		foreach ($ents as $ent) {
			$start_stop = explode('-', $ent);
			$start_hour_minute = explode(':', $start_stop[0]);
			$start_hour = $start_hour_minute[0];
			$start_minute = $start_hour_minute[1];
			$stop_hour_minute = explode(':', $start_stop[1]);
			$stop_hour = $stop_hour_minute[0];
			$stop_minute = $stop_hour_minute[1];
			$stop_hour_minute = $start_hour_minute = $stop = $start = false;
			$start_second = ($start_hour * 3600) + ($start_minute * 60);
			$stop_second = ($stop_hour * 3600) + ($stop_minute * 60);
			if($start_second >= $stop_second)
			{
				# @@@FIXME: no print statements in models!
				print "Error: Skipping timerange $str, stop time is before start time<br>";
				continue;
			}
			$ret[$i]['start'] = $start_second;
			$ret[$i]['stop'] = $stop_second;
			$i++;
		}

		return $ret;
	}

	/**
	 * Only generate the report over the provided timeperiod
	 *
	 * @param $period A timeperiod name
	 * @return true on success, false otherwise
	 */
	public function set_report_timeperiod($period=NULL)
	{
		$valid_weekdays = reports::$valid_weekdays;

		if (empty($period)) {
			$this->report_timeperiod = false;
			return true;
		}

		$this->report_timeperiod = array();

		$result = Timeperiod_Model::get($period, true);
		if (empty($result))
			return false;
		$timeperiod_id = $result['id'];
		unset($result['id']);
		unset($result['timeperiod_name']);
		unset($result['alias']);
		unset($result['instance_id']);

		$includes = $result;

		$errors = 0;
		foreach ($includes as $k => $v) {
			if (empty($v)) {
				continue;
			}
			$errors += $this->set_option($k, $v) === false;
		}

		$result_set = Timeperiod_Model::excludes($timeperiod_id, true);

		if(!empty($result_set))
		{
			foreach($result_set as $i => $result_row) # for each exclude
			{
				$this->tp_excludes[$i] = array('timeperiod'=>array(), 'exceptions'=>array());
				unset($result_row['id']);
				unset($result_row['timeperiod_name']);
				unset($result_row['alias']);
				unset($result_row['instance_id']);
				foreach($valid_weekdays as $didx => $weekday) # each weekday
				{
					if(isset($result_row[$weekday]))
						$this->tp_excludes[$i]['timeperiod'][$didx] = $this->tp_parse_day($includes[$weekday]);
					unset($result_row[$weekday]);
				}
				foreach($result_row as $key => $val)
				{
					$this->set_timeperiod_variable($key, $val, $this->tp_excludes[$i]['exceptions']);
				}
			}
		}

		if ($errors)
			return false;
		return true;
	}

	/**
	 * Finds next start or stop of timeperiod from a given timestamp. If
	 * given time is in an inactive timeperiod and we're looking for a
	 * stop, current time is returned.
	 * Vice versa, if we're looking for start inside an active period,
	 * the current timestamp is returned.
	 *
	 * @param $when Current timestamp to start from.
	 * @param $what Whether to search for start or stop. Valid values are 'start' and 'stop'.
	 * @return The timestamp
	 */
	public function tp_next($when, $what = 'start')
	{
		if ($this->report_timeperiod === false)
			return $when;

		# if there is a report timeperiod set that doesn't have
		# any 'start' entry (ie, all days are empty, such as for
		# the "none" timeperiod), we can't possibly find either
		# start or stop, so we can break out early.
		# Noone sane will want to take a report from such a timeperiod,
		# but in case the user misclicks, we should behave properly.
		if (empty($this->report_timeperiod) && empty($this->tp_exceptions))
			return 0;

		if ($what === 'start') {
			# try to find the next valid timestamp in this timeperiod,
			# that is not valid in any of the exceptions.
			# if we make it through a whole loop without $when changing, we
			# must have found next tp start.
			$main_when = false;
			while ($main_when !== $when && $when <= $this->end_time) {
				$main_when = $when = $this->tp_flat_next($when, 'start');

				$tp_exceptions = $this->tp_exceptions;
				$report_timeperiod = $this->report_timeperiod;
				$tp_excludes = $this->tp_excludes;
				unset($this->tp_excludes);
				foreach ($tp_excludes as $exclude) {
					$this->tp_exceptions = $exclude['exceptions'];
					$this->report_timeperiod = $exclude['timeperiod'];
					$tmp_when = $this->tp_flat_next($when, 'stop');
					if ($tmp_when !== 0) # 0 => no more tp entries => ignore
						$when = $tmp_when;
				}
				$this->tp_exceptions = $tp_exceptions;
				$this->report_timeperiod = $report_timeperiod;
				$this->tp_excludes = $tp_excludes;
			}
			if ($when > $this->end_time)
				return 0;
			return $when;
		}
		else if ($what === 'stop') {
			# when this timeperiod stops, or any of the excludes start, we
			# have a stop, whatever happens first
			$whens = array();
			$whens[] = $this->tp_flat_next($when, 'stop');

			$tp_exceptions = $this->tp_exceptions;
			$report_timeperiod = $this->report_timeperiod;
			$tp_excludes = $this->tp_excludes;
			unset($this->tp_excludes);
			foreach ($tp_excludes as $exclude) {
				$this->tp_exceptions = $exclude['exceptions'];
				$this->report_timeperiod = $exclude['timeperiod'];
				$whens[] = $this->tp_flat_next($when, 'start');
			}
			$this->tp_exceptions = $tp_exceptions;
			$this->report_timeperiod = $report_timeperiod;
			$this->tp_excludes = $tp_excludes;

			$whens = array_filter($whens); // remove any 0

			if (empty($whens))
				return 0;
			return min($whens);
		}

		return 0;
	}
	
	/**
	 * Finds the next start or stop of timeperiod, ignoring excludes, from
	 * a given timestamp. Really just a helper for the above.
	 */
	private function tp_flat_next($when, $what)
	{
		$other = 'stop';
		if ($what === 'stop')
			$other = 'start';

		$tm = localtime($when, true);
		$year = $tm['tm_year'] + 1900; # stored as offsets since 1900
		$tm_yday = $tm['tm_yday'];
		$day = $tm['tm_wday'];
		$day_seconds = ($tm['tm_hour'] * 3600) + ($tm['tm_min'] * 60) + $tm['tm_sec'];

		$midnight_to_when = $when - $day_seconds;
		$ents = array();
		# see if we have an exception first
		if (!empty($this->tp_exceptions[$year][$tm_yday]))
			$ents = $this->tp_exceptions[$year][$tm_yday];
		# if not, look for regular weekday
		elseif (!empty($this->report_timeperiod[$day]))
			$ents = $this->report_timeperiod[$day];
		# we have no entries today, so if we're looking for something outside
		# a timeperiod, everything is.
		elseif ($what === 'stop')
			return $when;

		foreach ($ents as $ent) {
			if ($ent[$what] <= $day_seconds && $ent[$other] > $day_seconds)
				return $when;

			if ($ent[$what] > $day_seconds)
				return $midnight_to_when + $ent[$what];
		}

		$orig_day = $day;
		$loops = 0;
		for ($day = $orig_day + 1; $when + ($loops * 86400) < $this->end_time; $day++) {
			$loops++;
			$ents = false;
			if ($day > 6)
				$day = 0;

			$midnight_to_when += 86400;

			if (!empty($this->tp_exceptions[$year][$tm_yday + $loops]))
				$ents = $this->tp_exceptions[$year][$tm_yday + $loops];
			elseif (!empty($this->report_timeperiod[$day]))
				$ents = $this->report_timeperiod[$day];

			# no exceptions, no timeperiod entry
			if (!$ents)
				continue;

			foreach ($ents as $ent)
				return $midnight_to_when + $ent[$what];
		}

		return 0;
	}

	/**
	 * Returns whether the given timestamp is inside timeperiod
	 * @param $timestamp: A timestamp in the unix epoch notation
	 * @return TRUE if the timestamp is inside the timeperiod, FALSE otherwise
	 */
	function tp_inside($timestamp)
	{
		return ($this->tp_next($timestamp, 'start') === $timestamp);
	}

	/**
	 * Returns the number of active seconds "inside"
	 * the timeperiod during the start -> stop interval
	 * @param $start: A timestamp in the unix epoch notation
	 * @param $stop: A timestamp in the unix epoch notation
	 * @return The number of seconds included in both $stop - $start
	 *         and the timeperiod set for this report as an integer
	 *         in the unix epoch notation
	 */
	function tp_active_time($start, $stop)
	{
		# if no timeperiod is set, the entire duration is active
		if ($this->report_timeperiod === false)
			return $stop - $start;

		# a timeperiod without entries will cause us to never
		# find start or stop. otoh, it never has any active time,
		# so we simply return 0
		if ($start >= $stop)
			return 0;

		$nstart = $this->tp_next($start, 'start');
		# if there is no start event inside the timeperiod, or the
		# first ever $nstart is beyond our $stop parameter,
		# there are no active seconds between start and stop, so
		# break out early
		if (!$nstart || $nstart >= $stop)
			return 0;

		$nstop = $this->tp_next($nstart, 'stop');
		# If the first ever $nstop encountered is beyond our
		# $stop parameter, we can return early, as we won't
		# need to loop at all
		if ($nstop > $stop)
			return $stop - $nstart;

		$active = $nstop - $nstart;
		while ($nstart != 0) {
			if (($nstart = $this->tp_next($nstop, 'start')) > $stop)
				$nstart = $stop;
			if (($nstop = $this->tp_next($nstart, 'stop')) > $stop)
				$nstop = $stop;

			$active += $nstop - $nstart;
			if ($nstart >= $stop || $nstop >= $stop)
				return $active;
		}

		# we ran out of time periods before we reached $stop, so let's
		# show 'em what we've got, so far
		return $active;
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
	 * Set an option, with some validation
	 *
	 * @param $name Option name
	 * @param $value Option value
	 */
	public function set_option($name, $value)
	{
		$vtypes = array
			('report_period' => 'string',
			 'alert_types' => 'int',
			 'state_types' => 'int',
			 'host_states' => 'int',
			 'service_states' => 'int',
			 'summary_items' => 'int',
			 'cluster_mode' => 'bool',
			 'keep_logs' => 'bool',
			 'keep_sub_logs' => 'bool',
			 'report_timeperiod' => 'string',
			 'scheduled_downtime_as_uptime' => 'int',
			 'assume_initial_states' => 'bool',
			 'assume_states_during_not_running' => 'bool',
			 'initial_assumed_host_state' => 'string',
			 'initial_assumed_service_state' => 'string',
			 'include_soft_states' => 'bool',
			 'host_name' => 'list',
			 'service_description' => 'list',
			 'hostgroup' => 'list',
			 'hostgroup_name' => 'string',
			 'servicegroup' => 'list',
			 'servicegroup_name' => 'string',
			 'start_time' => 'timestamp',
			 'end_time' => 'timestamp',
			 'monday' => 'string',
			 'tuesday' => 'string',
			 'wednesday' => 'string',
			 'thursday' => 'string',
			 'friday' => 'string',
			 'saturday' => 'string',
			 'sunday' => 'string',
			 'exclude' => 'string',
			 'use_average' => 'bool',
			 'host_filter_status' => 'array',
			 'service_filter_status' => 'array',
			 'include_trends' => 'bool');

		# this will happen for timeperiod exceptions
		if (!isset($vtypes[$name]))
			return $this->set_timeperiod_variable($name, $value);

		switch ($vtypes[$name]) {
		 case 'bool':
			if ($value == 1 || !strcasecmp($value, "true") || !empty($value))
				$value = true;
			else
				$value = false;
			if (!is_bool($value))
				return false;
			break;
		 case 'int':
			if (!is_numeric($value) || $value != intval($value))
				return false;
			$value = intval($value);
			break;
		 case 'string':
			if (!is_string($value))
				return false;
			break;
		 case 'list':
			if (is_array($value) && count($value) === 1)
				$value = array_pop($value);
			if (is_string($value))
				break;
			/* fallthrough */
		 case 'array':
			if (!is_array($value))
				return false;
			break;
		 case 'timestamp':
			if (!is_numeric($value)) {
				if (strstr($value, '-') === false)
					return false;
				$value = strtotime($value);
				if ($value === false)
					return false;
			}
			break;
		 default:
			# this is an exception and should never ever happen
			return false;
		}

		switch ($name) {
		 case 'cluster_mode':
			# check things in 'cluster mode' (ie, consider a group of
			# objects ok if one of the objects is
			if ($value === true)
				$this->st_state_calculator = 'st_best';
			else
				$this->st_state_calculator = 'st_worst';
			break;
		 case 'report_period':
			return $this->calculate_time($value);

			# lots of fallthroughs. lowest must come first
		 case 'state_types': case 'alert_types':
			if ($value > 3)
				return false;
		 case 'host_states':
			if ($value > 7)
				return false;
		 case 'service_states':
			if ($value > 15)
				return false;
		 case 'summary_items':
			if ($value < 0)
				return false;
			$this->$name = $value;
			break;
			# fallthrough end

		 case 'keep_logs':
			# caller forces us to retain or discard all log-entries
			$this->st_needs_log = $value;
			break;
		 case 'keep_sub_logs':
			$this->keep_sub_logs = $value;
			break;
		 case 'scheduled_downtime_as_uptime':
			$this->scheduled_downtime_as_uptime = $value;
			break;
		 case 'assume_initial_states':
			$this->assume_initial_states = $value;
			if (!$this->assume_initial_states) {
				$this->initial_assumed_host_state = false;
				$this->initial_assumed_service_state = false;
			}
			break;
		 case 'initial_assumed_host_state': case 'initial_assumed_service_state':
			if ($value < -3 || !$this->assume_initial_states)
				return false;
			$this->$name = $value;
			break;
		 case 'report_timeperiod':
			return $this->set_report_timeperiod($value);
		 case 'start_time':
			$this->start_time = $value;
			break;
		 case 'end_time':
			$this->end_time = $value;
			break;
		 case 'use_average':
			$this->use_average = $value;
			break;
		 case 'assume_states_during_not_running':
			$this->assume_states_during_not_running = $value;
			break;
		 case 'include_soft_states':
			$this->include_soft_states = $value;
			break;
		 case 'host_name':
			$this->hostgroup = false;
			$this->servicegroup = false;
			$this->host_name = $value;
			return true;
		 case 'host_filter_status':
		 case 'service_filter_status':
			break;
		 case 'include_trends':
			if ($value === true) {
				$this->set_option('keep_logs', true);
				$this->set_option('keep_sub_logs', true);
			}
			break;
		 case 'service_description':
			$this->hostgroup = false;
			$this->servicegroup = false;
			$this->service_description = $value;
			if (!is_array($value) && !$this->host_name && strchr($value, ';')) {
				$parts = explode(';', $value);
				$this->host_name = $parts[0];
				$this->service_description = $parts[1];
			}
			return true;
		 case 'hostgroup':
			$this->hostgroup = $value;
			break;
		 case 'servicegroup':
			$this->servicegroup = $value;
			break;
		 case 'hostgroup_name':
			$this->host_name = false;
			$this->service_description = false;
			$this->servicegroup = false;
			$this->hostgroup = $value;
			return true;
		 case 'servicegroup_name':
			$this->host_name = false;
			$this->service_description = false;
			$this->hostgroup = false;
			$this->servicegroup = $value;
			return true;
		 case 'sunday':
			$this->report_timeperiod[0] = $this->tp_parse_day($value);
			break;
		 case 'monday':
			$this->report_timeperiod[1] = $this->tp_parse_day($value);
			break;
		 case 'tuesday':
			$this->report_timeperiod[2] = $this->tp_parse_day($value);
			break;
		 case 'wednesday':
			$this->report_timeperiod[3] = $this->tp_parse_day($value);
			break;
		 case 'thursday':
			$this->report_timeperiod[4] = $this->tp_parse_day($value);
			break;
		 case 'friday':
			$this->report_timeperiod[5] = $this->tp_parse_day($value);
			break;
		 case 'saturday':
			$this->report_timeperiod[6] = $this->tp_parse_day($value);
			break;
		 default:
			return false;
		}
		$this->options[$name] = $value;

		return true;
	}

	/**
	 * Adds a timeperiod exception to the report.
	 * FIXME: should probably validate more
	 * @param $dateperiod_type Indicates the type of exception. Se timeperiod_class.php for valid values.
	 * @param $syear Start year
	 * @param $smon Start month
	 * @param $smday Start day of month
	 * @param $swday Start weekday
	 * @param $swday_offset Start weekday offset
	 * @param $eyear End year
	 * @param $emon End month
	 * @param $emday End day of month
	 * @param $ewday End weekday
	 * @param $ewday_offset End weekday offset
	 * @param $skip_interval Interval to skip, such as: "every 3 weeks" etc
	 * @param $timeranges Array of timeranges.
	 * @param $ref A reference to a structure with exceptions, or False
	 * Throws Exception if any parameter has bogus values.
	 */
	public function add_timeperiod_exception($dateperiod_type,
	                                  $syear, $smon, $smday, $swday, $swday_offset,
	                                  $eyear, $emon, $emday, $ewday, $ewday_offset,
	                                  $skip_interval, $timeranges, &$ref)
	{
		if ($ref === false) {
			$ref =& $this->tp_exceptions;
		}

		$days_per_month = reports::$days_per_month;

		if (!isset($ref['unresolved']))
			$ref['unresolved'] = array();

		assert($dateperiod_type >= 0 && $dateperiod_type < self::DATERANGE_TYPES); # can only fail if programmer messed up
		$timeranges = $this->tp_parse_day($timeranges);

		$ref['unresolved'][] = array
		(
			'type' => $dateperiod_type,
			'syear' => $syear,
			'smon' => $smon,
			'smday' => $smday,
			'swday' => $swday,
			'swday_offset' => $swday_offset,
			'eyear' => $eyear,
			'emon' => $emon,
			'emday' => $emday,
			'ewday' => $ewday,
			'ewday_offset' => $ewday_offset,
			'skip_interval' => $skip_interval,
			'timeranges' => $timeranges,
		);
		$this->timeperiods_resolved = false;
	}

	/**
	 * Update the options for the report
	 * @param $options New options
	 */
	public function set_options($options)
	{
		$errors = false;
		foreach ($options as $name => $value)
			$errors |= intval(!$this->set_option($name, $value));

		return $errors ? false : true;
	}

	private function resolve_timeperiods_worker($start_time, $end_time, &$all_exceptions) {
		$start_year = date('Y', $start_time);
		$end_year = date('Y', $end_time);
		
		$unres_exceptions =& $all_exceptions['unresolved'];
		
		/*
		 * Goal:
		 * For every day of year affected by this exception,
		 * add timeranges to that day (if overlap with existing range, merge them)
		 *
		 * Plan: for every day within report period
		 * {
		 *		check conditions for current day
		 * 		if day should be included add timeranges
		 * }
		 */
		for($i=0,$n=count($unres_exceptions) ; $i<$n ; $i++)
		{
			$x =& $unres_exceptions[$i];

			if($x['syear'] > date('Y', $end_time))
				continue;

			for($day_time = $start_time ; $day_time < $end_time ; $day_time += 86400)
			{
				$check_exception = true;

				$day = date('z', $day_time);
				$day_year  = date('Y', $day_time);
				$day_month = date('n', $day_time);

				# find out if there is an exception during this day
				switch($x['type'])
				{
					# FIXME: More consequent scheme for adding 24h to end time

				 case self::DATERANGE_CALENDAR_DATE:/* eg: 2008-12-25 */
					# set fields: syear, smon, smday, eyear, emon, emday, skip_interval

					$exp_start = mktime(0,0,0, $x['smon'], $x['smday'], $x['syear']);

					# unspecified end date - two possibilities
					if(self::is_daterange_single_day($x))
					{
						if($x['skip_interval'] > 1)
						{
							// yyyy-mm-dd / dd means endless period
							$exp_end = $day_time + 86400;
						}
						else
						{
							// Whereas yyyy-mm-dd means single day period
							$exp_end = $exp_start;
						}
					}
					else
						$exp_end = mktime(24,0,0, $x['emon'], $x['emday'], $x['eyear']);

					break;
				 case self::DATERANGE_MONTH_DATE:
					/* eg: july 4 (specific month) */
					# set fields: smon, emon, smday, emday

					$exp_start = self::calculate_time_from_day_of_month($start_year, $x['smon'], $x['smday']);
					$exp_end   = self::calculate_time_from_day_of_month($end_year, $x['emon'], $x['emday']);

					# XXX: can *both* be zero here?
					if($exp_end < $exp_start)
					{
						$x['eyear'] = $day_year + 1;
						$exp_end = self::calculate_time_from_day_of_month($x['eyear'], $x['emon'], $x['emday']);
						$exp_end += 86400;
					}

					if($exp_end == 0) {
						echo "php is broken. goodie....\n";
						if($x['emday'] < 0) {
							$check_exception = false;
						}
						else {
							$exp_end = self::calculate_time_from_day_of_month($x['eyear'], $x['emon'], -1);
						}
					}

					assert($exp_end != 0);

					break;
				 case self::DATERANGE_MONTH_DAY:
					/* eg: day 21 (generic month) */
					# set field: smday, emday
					$exp_start = self::calculate_time_from_day_of_month($day_year, $day_month, $x['smday']);
					$exp_end   = self::calculate_time_from_day_of_month($day_year, $day_month, $x['emday']);

					# get midnight at end of day
					$exp_end += 86400;
					break;

				 case self::DATERANGE_MONTH_WEEK_DAY:/* eg: 3rd thursday (specific month) */
					# set field: smon, swday, swday_offset, emon, ewday, ewday_offset, skip_interval
					$exp_start = self::calculate_time_from_weekday_of_month($start_year, $x['smon'], $x['swday'], $x['swday_offset']);
					$exp_end   = self::calculate_time_from_weekday_of_month($end_year,   $x['emon'], $x['ewday'], $x['ewday_offset']);
					break;

				 case self::DATERANGE_WEEK_DAY:
					# eg: 3rd thursday (generic month)
					# set fields: swday, swday_offset, ewday, ewday_offset, skip_interval
					$exp_start = self::calculate_time_from_weekday_of_month($day_year, $day_month, $x['swday'], $x['swday_offset']);
					$exp_end   = self::calculate_time_from_weekday_of_month($day_year, $day_month, $x['ewday'], $x['ewday_offset']);
					break;
				}

				# This day might be totally uninteresting, in which case
				# we just ignore it
				if($x['skip_interval'] > 1) {
					$days_since_start = ($day_time - $exp_start) / 86400;
					$check_exception = !($days_since_start % $x['skip_interval']);
				}

				# We must add 1 day to exp_end, as times during that day must be included
				if (!$check_exception || $exp_start > $day_time || $exp_end + 86400 < $day_time)
					continue;

				if(!isset($all_exceptions[$day_year]))
					$all_exceptions[$day_year] = array();

				if(!isset($all_exceptions[$day_year][$day]))
					$all_exceptions[$day_year][$day] = array();

				# if so, merge timeranges with existing for this day
				$all_exceptions[$day_year][$day] = self::merge_timerange_sets($all_exceptions[$day_year][$day], $x['timeranges']);
			}
		}
	}

	/**
	 * Resolve timeperiods, both the actual timeperiods and the exclusions
	 *
	 * FIXME: exclusions from exclusions doesn't work
	 */
	public function resolve_timeperiods()
	{
		if ($this->start_time == false || $this->end_time == false) {
			throw new Exception("Timeperiods cannot be resolved unless report start and end time is set");
		}
		$start_time = $this->start_time;
		$end_time = $this->end_time;

		if ($end_time < $start_time) {
			throw new Exception("Report time set to end before start");
		}

		$this->resolve_timeperiods_worker($start_time, $end_time, $this->tp_exceptions);
		unset($this->tp_exceptions['unresolved']);

		foreach ($this->tp_excludes as $idx => &$exclude) {
			$this->resolve_timeperiods_worker($start_time, $end_time, $exclude['exceptions']);
			unset($exclude['exceptions']['unresolved']);
		}

		$this->timeperiods_resolved = true;
	}


	/**
	 * Set the master report, and copy required variables from it
	 *
	 * @param $master Master report
	 */
	public function set_master($master)
	{
		$this->master = $master;
		$this->set_options($master->options);
		$this->st_needs_log = $master->keep_sub_logs;
		$this->last_shutdown = $master->last_shutdown;
		$this->report_timeperiod = $master->report_timeperiod;
	}

	/**
	 * Calculate uptime between two timestamps for host/service
	 * @param $hostname If set to false, internal host_name is used.
	 * @param $servicename If set to false, internal service_description is used
	 * @param $start_time datetime or unix timestamp
	 * @param $end_time datetime or unix timestamp
	 * @param $hostgroup If set to false, internal hostgroup is used
	 * @param $servicegroup If set to false, internal servicegroup is used
	 * @return array or false on error
	 *
	 */
	public function get_uptime($hostname=false, $servicename=false, $start_time=0,
						$end_time=0, $hostgroup=false, $servicegroup=false)
	{
		if (is_array($servicename) && empty($servicename))
			$servicename = false;
		if (empty($hostname) && !empty($this->host_name))
			$hostname = $this->host_name;
		if (empty($servicename) && !empty($this->service_description))
			$servicename = $this->service_description;
		if (empty($hostgroup) && !empty($this->hostgroup))
			$hostgroup = $this->hostgroup;
		if (empty($servicegroup) && !empty($this->servicegroup))
			$servicegroup = $this->servicegroup;
		if (empty($start_time) && !empty($this->start_time))
			$start_time = $this->start_time;
		if (empty($end_time) && !empty($this->end_time))
			$end_time = $this->end_time;

		if (empty($hostname) && empty($hostgroup) && empty($servicename) && empty($servicegroup)) {
			return false;
		}

		# stash the report settings for debugging/test-creation purposes
		$this->options['start_time'] = $this->start_time = $start_time;
		$this->options['end_time'] = $this->end_time = $end_time;

		# register first and last possible database entry times
		$this->register_db_time($start_time);
		$this->register_db_time($end_time);

		$this->debug = $this->options;
		if (!is_array($hostname))
			$this->debug['host_name'] = $this->host_name = $hostname;
		if (!is_array($servicename))
			$this->debug['service_description'] = $this->service_description = $servicename;
		$this->debug['servicegroup_name'] = $this->servicegroup = $servicegroup;
		$this->debug['hostgroup_name'] = $this->hostgroup = $hostgroup;

		# We need to be able to see what host/service group we are fetching
		# data for. This is actually a hack since it should be set by using
		# set_option() but this will have to do for now...
		if (!empty($hostgroup)) $this->hostgroup = $hostgroup;
		if (!empty($servicegroup)) $this->servicegroup = $servicegroup;

		# these get copied to sub objects automagically
		# using the set_options() method
		$this->set_option('start_time', $start_time);
		$this->set_option('end_time', $end_time);

		$this->get_last_shutdown();

		$host 		= false;
		$service	= false;
		$res_group	= false;

		# When we have a single host or service, we don't need to
		# calculate group availability, so do that up front to
		# get the simple case out of the way immediately
		if (empty($hostgroup) && empty($servicegroup) &&
		    !is_array($hostname) && !is_array($servicename))
		{
			// == single host OR service ==
			$ret = $this->calculate_uptime($hostname, $servicename);
			foreach ($this->debug as $k => $v) {
				if ($v === false)
					unset($this->debug[$k]);
			}

			$ret[';testcase;'] = $this->debug;
			return $ret;
		}

		if (!empty($hostgroup)) {
			$hostname = array();
			// == Hostgroup ==
			$group_model = new Group_Model();
			$res_group = $group_model->get_group_info('host', $hostgroup);

			if (empty($res_group))
				return false;

			$res_group->result(false);

			foreach ($res_group as $row) {
				$hostname[$row['host_name']] = $row['host_name'];
			}
		} elseif (!empty($servicegroup)) {
			$servicename = array();
			// == Servicegroup ==
			$group_model = new Group_Model();
			$res_group = $group_model->get_group_info('service', $servicegroup);
			if (!count($res_group))
				return false;

			$res_group->result(false);

			foreach ($res_group as $row) {
				$name = $row['host_name'] . ';' . $row['service_description'];
				$servicename[$name] = $name;
			}
		}

		if (is_array($hostname) && is_array($servicename))
			return false;

		if (is_array($hostname)) {
			// == multiple hosts ==
			foreach ($hostname as $host) {
				$sub_class = new Reports_Model($this->db_name, $this->db_table);
				$sub_class->set_master($this);
				$sub_class->id = $host;
				$sub_class->st_init($host, false);
				$this->sub_reports[$sub_class->id] = $sub_class;
			}
		} else {
			if (is_array($servicename)) {
				// == multiple services ==
				foreach ($servicename as $service) {
					// split hostname, service_desciption on ';'
					$service_parts = explode(';', $service);
					$sub_class = new Reports_Model($this->db_name, $this->db_table);
					$sub_class->set_master($this);
					$sub_class->id = $service;
					$sub_class->st_init($service_parts[0], $service_parts[1]);
					$this->sub_reports[$sub_class->id] = $sub_class;
				}
			}
		}

		# Grab master's report-results _FIRST_ as sub-reports
		# are fed its data from the same query
		$this->st_init($hostname, $servicename);
		$this->st_parse_all_rows($hostname, $servicename);
		$sub_return = false;
		foreach ($this->sub_reports as $id => $rpt) {
			$return[] = $rpt->st_finalize();
			$this->register_db_time($rpt->db_start_time);
			$this->register_db_time($rpt->db_end_time);
			if (strpos($id, ';') !== false) {
				$this->debug['service_description'][] = $id;
			} else {
				$this->debug['host_name'][] = $id;
			}
		}
		$master_return = $this->st_finalize();
		foreach ($master_return as $k => $v)
			$return[$k] = $v;

		# stash the debugging stuff in the return array, but only
		# for the master report
		if (empty($this->master)) {
			$this->debug['db_end_time'] = $this->db_end_time;
			$this->debug['db_start_time'] = $this->db_start_time;
			foreach ($this->debug as $k => $v) {
				if ($v === false)
					unset($this->debug[$k]);
			}
			$return[';testcase;'] = $this->debug;

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
		if ($this->assume_states_during_not_running) {
			$this->last_shutdown = 0;
			return 0;
		}

		$query = "SELECT timestamp, event_type FROM ".
			$this->db_name.".".$this->db_table.
			" WHERE timestamp <".$this->start_time.
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
			if ($in_dt && $this->scheduled_downtime_as_uptime) {
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
		$active = intval($this->tp_active_time($prev_time, $end_time));
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
				if ($rpt->st_source = $src) {
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
				return _($obj_type . ' has entered a period of scheduled downtime');
			case self::DOWNTIME_STOP:
				return _($obj_type . ' has exited a period of scheduled downtime');
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
		if ($obj_name === $this->id || (is_string($this->id) && strpos($this->id, $obj_name.';') === 0 && $row['event_type'] >= self::DOWNTIME_START) || $row['event_type'] <= self::PROCESS_SHUTDOWN)
			$rpts[-1] = $this;
		foreach ($this->sub_reports as $idx => $sr) {
			if ($sr->id === $obj_name || (is_string($sr->id) && strpos($sr->id, $obj_name.';') === 0 && $row['event_type'] >= self::DOWNTIME_START) || $row['event_type'] <= self::PROCESS_SHUTDOWN)
				$rpts[$idx] = $sr;
		}

		$this->st_update($row['the_time']);
		foreach ($rpts as $rpt) {
			if ($rpt !== $this)
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
			foreach ($rpts as $rpt) {
				$rpt->st_last_dt_init = false;
				if ($row['event_type'] == self::PROCESS_START) {
					$row['state'] = $rpt->st_real_state;
					$rpt->st_running = 1;
				}
				else {
					if ($this->assume_states_during_not_running) {
						$row['state'] = $rpt->st_real_state;
					} else {
						$row['state'] = -1;
						$rpt->st_running = 0;
					}
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
					if ($rpt !== $this) {
						unset($this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth-1][$idx]);
						$this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx] = $idx;
						$rpt->calculate_object_state();
					}
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
					if ($rpt !== $this) {
						unset($this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth+1][$idx]);
						$this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx] = $idx;
						$rpt->calculate_object_state();
					}
				}
			}
			break;
		 case self::SERVICECHECK:
		 case self::HOSTCHECK:
			$state = $row['state'];

			foreach ($rpts as $idx => $rpt) {
				# update the real state of the object
				if ($rpt->id === $obj_name) {
					$rpt->st_real_state = $row['state'];

					if ($rpt !== $this && $rpt->st_obj_state != $state) {
						unset($this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx]);
						$this->st_sub[$state][$rpt->st_dt_depth][$idx] = $idx;
					}
				}
				if ($rpt !== $this)
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
			if (!in_array($this, $rpts))
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
				if ($this->scheduled_downtime_as_uptime && $in_dt)
					break 1;
				// Else, we're done, this is the worst.
				$this->st_dt_depth = $in_dt;
				$final_state = $state;
				break 2;
			}
		}

		// So, scheduled_downtime_as_uptime and worst not in sched_down is OK?
		// Maybe there's a non-OK in sched_down...
		if ($this->scheduled_downtime_as_uptime && $final_state === 0) {
			foreach ($states as $state) {
				if ($state === 0)
					break;
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

		if (false === $state) {
			$state = $this->st_real_state;
		}

		$this->st_obj_state = $this->filter_excluded_state($state);
	}

	/**
	 * Initialize the the state machine for this report
	 * @param $hostname The host(s) we're interested in
	 * @param $servicename The service(s) we're interested in
	 */
	public function st_init($hostname = false, $servicename = false)
	{
		if (!$this->timeperiods_resolved)
			$this->resolve_timeperiods();

		# single object reports always gets a log
		if (!$this->master && empty($this->sub_reports)) {
			$this->st_needs_log = true;
		}

		# if user asked for it, we preserve the log
		if ($this->st_needs_log) {
			$this->st_log = array();
		}

		if (!empty($servicename)) {
			$this->st_is_service = true;
			$this->st_obj_type = 'Service';
			$this->st_source = $servicename;
			if (is_array($servicename))
				$this->id = $servicename;
		}
		else {
			# we need at least a service or a host
			if (empty($hostname))
				return false;
			if (is_array($hostname))
				$this->id = $hostname;
			$this->st_obj_type = 'Host';
			$this->st_source = $hostname;
		}

		if (is_array($this->st_source))
			$this->st_source = implode(',', $this->st_source);

		$host_state_txt 	= array(0 => 'UP', 1 => 'DOWN', 2 => 'UNREACHABLE');
		$service_state_txt 	= array(0 => 'OK', 1 => 'WARNING', 2 => 'CRITICAL', 3 => 'UNKNOWN');
		$this->st_text = empty($servicename) ? $host_state_txt : $service_state_txt;
		$this->st_text[self::STATE_PENDING] = 'PENDING';
		$this->st_text[self::SERVICE_EXCLUDED] = 'EXCLUDED'; // also covers self::HOST_EXCLUDED

		# id must always be set properly for single-object reports
		if (empty($this->id)) {
			$this->id = $hostname;

			if (!empty($servicename))
				$this->id .= ";$servicename";
		}

		# prime the state counter for sub-objects
		if (!empty($this->sub_reports)) {
			foreach ($this->st_text as $st => $discard)
				$this->st_sub[$st] = array();
			foreach ($this->sub_reports as $idx => $rpt) {
				$rpt->scheduled_downtime_as_uptime = $this->scheduled_downtime_as_uptime;
				$rpt->calculate_object_state();
				$this->st_sub[$rpt->st_obj_state][$rpt->st_dt_depth][$idx] = $idx;
			}
			$this->calculate_object_state();
		}
		else {
			$this->st_dt_depth = intval(!!$this->get_initial_dt_depth($hostname, $servicename));
			$this->st_real_state = $this->filter_excluded_state($this->get_initial_state($hostname, $servicename));
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
		$this->st_prev_row = array
			('state' => $this->st_obj_state,
			 'the_time' => $this->start_time,
			 'event_type' => $fevent_type,
			 'downtime_depth' => $this->st_dt_depth);

		# if we're actually going to use the log, we'll need
		# to generate a faked initial message for it.
		if ($this->st_needs_log) {
			$fout = sprintf("Report period start. Daemon is%s running, " .
			                "we're%s in scheduled downtime, state is %s (%d)",
			                $this->st_running ? '' : ' not',
			                $this->st_dt_depth ? '' : ' not',
			                $this->st_text[$this->st_obj_state], $this->st_obj_state);
			$this->st_prev_row['output'] = $fout;

			if (!empty($hostname) && is_string($hostname))
				$this->st_prev_row['host_name'] = $hostname;

			if (!empty($servicename) && is_string($servicename))
				$this->st_prev_row['service_description'] = $servicename;
		}
	}

	/**
	 * The work-horse of the availability and SLA reports. This is
	 * generally the entry-point for all reports when options are set.
	 *
	 * @param $hostname The host(s) we're interested in.
	 * @param $servicename The service(s) we're interested in.
	 *
	 * @return FALSE on errors. Array of calculated uptime on succes.
	 * The array is in the form:
	 * array(
	 * 	'source' => string,
	 * 	'log' => array,
	 * 	'states' => array,
	 * 	'tot_time' => int,
	 * 	'groupname' => string
	 * 	);
	 */
	public function calculate_uptime($hostname=false, $servicename=false)
	{
		$this->st_init($hostname, $servicename);

		$this->st_parse_all_rows($hostname, $servicename);
		return $this->st_finalize();
	}

	/**
	 * Runs the main query and loops through the results one by one
	 */
	private function st_parse_all_rows($hostname = false, $servicename = false)
	{
		$dbr = $this->uptime_query($hostname, $servicename);
		foreach ($dbr as $row) {
			$this->st_parse_row($row);
		}
	}

	private function st_update_log($sub = false, $row = false)
	{
		if($row) {
			$row['state'] = $this->filter_excluded_state($row['state']);
		}
		if (!$this->st_needs_log) {
			$this->st_prev_row = $row;
			return;
		}

		# called from st_finalize(), so bail out early
		if (!$sub && !$row) {
			$this->st_prev_row['duration'] = $this->end_time - $this->st_prev_row['the_time'];
			$active = $this->tp_active_time($this->st_prev_row['the_time'], $this->end_time);
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
			$output = $sub->id . ' went from ' . $sub->st_prev_row['state'] .
				' to ' . $row['state'];
			$row['hard'] = 1;
			$row['output'] = $output;
			unset($row['host_name']);
			unset($row['service_description']);
		}

		if ($this->scheduled_downtime_as_uptime && $this->st_dt_depth)
			$row['state'] = self::STATE_OK;

		# don't save states without duration for master objects
		$duration = $row['the_time'] - $this->st_prev_row['the_time'];
		if ($duration || $sub) {
			$this->st_prev_row['duration'] = $duration;
			$active = $this->tp_active_time($this->st_prev_row['the_time'], $row['the_time']);
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
	 * @return See 'calculate_uptime()'
	 */
	private function st_finalize()
	{
		# gather remaining time. If they match, it'll be 0
		$this->st_update($this->end_time);
		$this->st_update_log();

		$converted_state = $this->convert_state_table($this->st_raw, $this->st_text);

		if ($this->use_average && !empty($this->sub_reports) > 0) {
			$converted_state = $this->calculate_average();
		}

		# state template array depends on what we are checking
		$tpl = $this->state_tpl_host;
		if ($this->st_is_service)
			$tpl = $this->state_tpl_svc;
		foreach ($tpl as $t => $discard)
			if (!isset($converted_state[$t]))
				$converted_state[$t] = 0;

		if (empty($this->sub_reports)) {
			$converted_state['HOST_NAME'] = $this->id;
			if ($this->st_is_service) {
				$srv = explode(';', $this->id);
				$converted_state['HOST_NAME'] = $srv[0];
				$converted_state['SERVICE_DESCRIPTION'] = $srv[1];
			}
		} else {
			if ($this->st_is_service) {
				unset($converted_state['HOST_NAME']);
				$converted_state['SERVICE_DESCRIPTION'] = $this->id;
			}
			else
				$converted_state['HOST_NAME'] = $this->id;
		}

		# now add the time we didn't count due
		# to the selected timeperiod
		$converted_state['TIME_INACTIVE'] = $this->st_inactive;

		$this->states = $converted_state;
		$total_time = $this->end_time - $this->start_time;
		$groupname = $this->hostgroup != '' ? $this->hostgroup : $this->servicegroup;
		if (!empty($this->sub_reports)) {
			$log = array();
			foreach ($this->sub_reports as $sr) {
				$log[$sr->id] = $sr->st_log;
			}
		}
		else {
			$log = array($this->id => $this->st_log);
		}
		return array('source' => $this->st_source, 'log' => $log, 'states' => $converted_state, 'tot_time' => $total_time, 'groupname' => $groupname);
	}

	/**
	 * Get log details for host/service
	 *
	 * @param $hostname The host(s) we're interested in.
	 * @param $servicename The service(s) we're interested in.
	 * @return PDO result object on success. FALSE on error.
	 */
	public function uptime_query($hostname=false, $servicename=false)
	{
		$event_type = self::HOSTCHECK;
		if ($servicename) {
			$event_type = self::SERVICECHECK;
		}

		# this query works out automatically, as we definitely don't
		# want to get all state entries for a hosts services when
		# we're only asking for uptime of the host
		$sql = "SELECT host_name, service_description, " .
			"state,timestamp AS the_time, hard, event_type";
		# output is a TEXT field, so it needs an extra disk
		# lookup to fetch and we don't always need it
		if ($this->st_needs_log)
			$sql .= ", output";

		$sql .= " FROM ".$this->db_name.".".$this->db_table." " .
			"WHERE timestamp >=".$this->start_time." " .
			"AND timestamp <=".$this->end_time." ";

		if (is_array($hostname) && empty($servicename)) {
			$sql .= "AND ((host_name IN ('" . join("', '", $hostname) . "') AND (service_description = '' OR service_description IS NULL)) OR (host_name = '' AND service_description = '')) ";
		}
		elseif (is_array($servicename)) {
			$sql .= "AND (concat(concat(host_name, ';'), service_description) IN ('" .
				join("', '", $servicename) . "') ";
			if (empty($hostname) || !is_array($hostname)) {
				$hostname = array();
				foreach ($servicename as $hst_srv) {
					$ary = explode(';', $hst_srv, 2);
					$hostname[$ary[0]] = $ary[0];
				}
			}
			$sql .= " OR (host_name IN ('" . join("', '", $hostname) . "') AND (" .
				"service_description = '' OR service_description IS NULL)) OR (host_name = '' AND service_description = '')) ";
		}
		else {
			if (empty($servicename)) $servicename = '';
			$sql .= "AND ((host_name=".$this->db->escape($hostname)." " .
				"AND (service_description IS NULL OR service_description = '' OR service_description=".$this->db->escape($servicename).")) OR (host_name = '' AND service_description = ''))";
		}

		$sql .= "AND ( ";
		if (!$this->include_soft_states) {
			# only the primary event type should care about hard/soft
			$sql .= '(event_type=' . $event_type . ' AND hard=1)';
		} else {
			$sql .= 'event_type=' . $event_type . ' ';
		}
		$sql .= "OR event_type=" . self::DOWNTIME_START . ' ' .
			"OR event_type=" . self::DOWNTIME_STOP . ' ';
		if(isset($this->options['host_filter_status']) && isset($this->options['host_filter_status'][3]) && $this->options['host_filter_status'][3]) {
			$sql .= "OR event_type=".self::PROCESS_SHUTDOWN.
				" OR event_type=".self::PROCESS_START;
		}

		$sql .= ") ";
		$sql .= ' ORDER BY timestamp';

		return $this->db->query($sql)->result(false);
	}

	/**
	 * Fetch information about SCHEDULED_DOWNTIME status
	 *
	 * @param $hostname string: The host we're interested in.
	 * @param $service_description string: The service we're interested in.
	 * @return Depth of initial downtime.
	 */
	public function get_initial_dt_depth($hostname=false, $service_description=false)
	{
		if ($this->initial_dt_depth != false)
			return $this->initial_dt_depth;

		if (empty($hostname)) {
			return false;
		}

		$sql = "SELECT timestamp, event_type FROM " .
			$this->db_name . "." . $this->db_table . " " .
			"WHERE timestamp <= " . $this->start_time . " AND " .
			"(event_type = " . self::DOWNTIME_START .
			" OR event_type = " .self::DOWNTIME_STOP . ") " .
			" AND host_name = " . $this->db->escape($hostname);

		if (empty($service_description))
			$sql .= " AND service_description IS NULL OR service_description = '' ";
		else
			$sql .= " AND (service_description IS NULL OR service_description = '' " .
				"OR service_description=".$this->db->escape($service_description) .
				")";
		
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
	 * @param $host_name string: The host we're interested in.
	 * @param $service_description string: The service we're interested in.
	 *
	 * @return FALSE on error. Record from database on success.
	 */
	public function get_initial_state($host_name = '', $service_description = '')
	{
		$assumed_state = $state = false;

		if ($this->initial_state !== false)
			return $this->initial_state;

		// we always need timestamp and at least a host_name
		if (empty($host_name)) {
			return false;
		}

		$service_description = $service_description === false ? '' : $service_description;
		$sql = "SELECT timestamp, state FROM " .
			$this->db_name . "." . $this->db_table .
			" WHERE host_name = " . $this->db->escape($host_name);
		if (!$service_description)
			$sql .= " AND (service_description = '' OR service_description IS NULL)";
		else
			$sql .= " AND service_description = " . $this->db->escape($service_description);
		$sql .= " AND event_type = ";

		if ($service_description !='' ) {
			$assumed_state = $this->initial_assumed_service_state;
			$sql .= self::SERVICECHECK;
		} else {
			$assumed_state = $this->initial_assumed_host_state;
			$sql .= self::HOSTCHECK;
		}
		if (!$this->include_soft_states)
			$sql .= ' AND hard = 1';

		$sql .= ' ';
		$base_sql = $sql;
		$sql .= "AND timestamp < " . $this->start_time .
			" ORDER BY timestamp DESC LIMIT 1";

		# first try to fetch the real initial state so
		# we don't have to assume
		$dbr = $this->db->query($sql)->result(false);
		if ($dbr && ($row = $dbr->current())) {
			$this->initial_state = $row['state'];
			return $this->initial_state;
		}

		# There is no real initial state, so check if we should
		# assume something. If it's a real state, return early
		if ($assumed_state > 0) {
			$this->initial_state = $assumed_state;
			return $this->initial_state;
		}

		# we must reset $dbr here to work around a bug in PDO or PHP
		$dbr = $sql = false;
		$state = $assumed_state;
		# state == -1 is magic for "use current state as initial"
		# it's fairly bonkers to do that, and will yield different
		# results for historical data based on present state, but
		# it's supported in the old cgi's, so we must keep this
		# mouldering wreck of insanity alive...
		if ($state == -1) {
			$dbr = $this->db->query($base_sql . "ORDER BY timestamp DESC LIMIT 1");
		}

		# Using the first real state found in the database as
		# the assumed initial state is a lot less evil than the
		# above black voodoo, as reports for last year will always
		# look the same, no matter what the current state is.
		elseif ($state == -3) {
			$dbr = $this->db->query($base_sql . "ORDER BY timestamp ASC LIMIT 1");
		}

		if ($dbr && ($row = $dbr->result(false)->current())) {
			$state = $row['state'];
		} else {
			# this is only reached if there is no state at all
			# in the database. It should usually be an error,
			# unless one tries to take a report from, say, last
			# year on a host that was added less than 30 seconds
			# ago. Either way, we're out of options so do nothing.
			# $state will default to STATE_PENDING further down
		}
		/* state assumption logic end */

		if ($state === false || $state < 0 || is_null($state))
			$state = self::STATE_PENDING;

		$this->initial_state = $state;
		return $state;
	}

	/**
	 * Calculate average values using every subreport
	 * @return array Average values for the group
	 */
	public function calculate_average()
	{
		if (empty($this->sub_reports)) {
			return;
		}

		$ret = array();
		$num_subs = 0;
		foreach($this->sub_reports as $report) {
			$num_subs++;
			foreach ($report->states as $k => $v) {
				if (!isset($ret[$k]))
					$ret[$k] = 0;

				$ret[$k] += $v;
			}
		}

		foreach ($ret as $k => $v) {
			$ret[$k] = $v / $num_subs;
		}

		return $ret;
	}

	/**
	 * Parses given input as a nagios 3 timeperiod variable. If valid,
	 * it is added to the report.
	 * Code is derived from the nagios 3 sources (xdata/xodtemplate.c)
	 * FIXME: find better way of adding 24h to end date
	 *
	 * @param $name The timeperiod style variable we want to parse
	 * @param $value The value of the timeperiod variable
	 * @param $ref A reference to a timeperiod exception structure, or false
	 * @return boolean
	 */
	public function set_timeperiod_variable($name, $value, &$ref=false)
	{
		$valid_weekdays = reports::$valid_weekdays;
		$valid_months = reports::$valid_months;

		$weekday_numbers = array_flip($valid_weekdays);
		$month_numbers = array_flip($valid_months);

		if(in_array($name, $valid_weekdays)) # add regular weekday include time
		{
			return $this->set_option($name, $value);
		}

		$input = "$name $value";

		# you could put this in one line but that will be too messy
		$items = array_filter(sscanf($input,"%4d-%2d-%2d - %4d-%2d-%2d / %d %[0-9:, -]"));
		if(count($items) == 8)
		{
			list($syear, $smon, $smday, $eyear, $emon, $emday, $skip_interval, $timeranges) = $items;

			/* add timerange exception */
			$this->add_timeperiod_exception(self::DATERANGE_CALENDAR_DATE,
				$syear, $smon, $smday, 0, 0, $eyear, $emon, $emday, 0, 0, $skip_interval, $timeranges, $ref);
			return true;
		}

		$items = array_filter(sscanf($input,"%4d-%2d-%2d / %d %[0-9:, -]"));
		if(count($items) == 5)
		{
			list($syear,$smon, $smday, $skip_interval, $timeranges) = $items;
			$eyear = $syear;
			$emon  = $smon;
			$emday = $smday;

			/* add timerange exception */
			$this->add_timeperiod_exception(self::DATERANGE_CALENDAR_DATE,
				$syear, $smon, $smday, 0, 0, $eyear, $emon, $emday, 0, 0, $skip_interval, $timeranges, $ref);
			return true;
		}

		$items = array_filter(sscanf($input,"%4d-%2d-%2d - %4d-%2d-%2d %[0-9:, -]"));
		if(count($items) == 7)
		{
			list($syear, $smon, $smday, $eyear, $emon, $emday, $timeranges) = $items;

			/* add timerange exception */
			$this->add_timeperiod_exception(self::DATERANGE_CALENDAR_DATE,
				$syear, $smon, $smday, 0, 0, $eyear, $emon, $emday, 0, 0, 0, $timeranges, $ref);
			return true;
		}

		$items=array_filter(sscanf($input,"%4d-%2d-%2d %[0-9:, -]"));
		if(count($items)==4)
		{
			list($syear, $smon, $smday, $timeranges) = $items;
			$eyear = $syear;
			$emon = $smon;
			$emday = $smday;
			/* add timerange exception */
			$this->add_timeperiod_exception(self::DATERANGE_CALENDAR_DATE,
				$syear, $smon, $smday, 0, 0, $eyear, $emon, $emday, 0, 0, 0, $timeranges, $ref);
			return true;
		}

		/* other types... */
		$items = array_filter(sscanf($input,"%[a-z] %d %[a-z] - %[a-z] %d %[a-z] / %d %[0-9:, -]"));
		if(count($items) == 8)
		{
			list($str1, $swday_offset, $str2, $str3, $ewday_offset, $str4, $skip_interval, $timeranges) = $items;
			/* wednesday 1 january - thursday 2 july / 3 */

			if(in_array($str1, $valid_weekdays) &&
				in_array($str2, $valid_months) &&
				in_array($str3, $valid_weekdays) &&
				in_array($str4, $valid_months))
			{
				$swday = $weekday_numbers[$str1];
				$smon = $month_numbers[$str2];
				$ewday = $weekday_numbers[$str3];
				$emon = $month_numbers[$str4];

				$this->add_timeperiod_exception(self::DATERANGE_MONTH_WEEK_DAY,
					0, $smon, 0, $swday, $swday_offset, 0, $emon, 0, $ewday, $ewday_offset, $skip_interval,  $timeranges, $ref);
				return true;
			}
			return false;
		}

		$items = array_filter(sscanf($input,"%[a-z] %d - %[a-z] %d / %d %[0-9:, -]"));
		if(count($items) == 6)
		{
			list($str1, $smday, $str2, $emday, $skip_interval, $timeranges) = $items;
			/* february 1 - march 15 / 3 */
			/* monday 2 - thursday 3 / 2 */
			/* day 4 - day 6 / 2 */
			if(in_array($str1, $valid_weekdays) && in_array($str2, $valid_weekdays))
			{
				/* monday 2 - thursday 3 / 2 */
				$swday = $weekday_numbers[$str1];
				$ewday = $weekday_numbers[$str2];
				$swday_offset = $smday;
				$ewday_offset = $emday;

				/* add timeperiod exception */
				$this->add_timeperiod_exception(self::DATERANGE_WEEK_DAY,
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, $skip_interval, $timeranges, $ref);
				return true;
			}
			elseif(in_array($str1, $valid_months) && in_array($str2, $valid_months))
			{
				$smon = $month_numbers[$str1];
				$emon = $month_numbers[$str2];
				/* february 1 - march 15 / 3 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0,
					0, $emon, $emday, 0, 0, $skip_interval, $timeranges, $ref);
				return true;
			}
			else if(!strcmp($str1,"day")  && !strcmp($str2,"day"))
			{
				/* day 4 - 6 / 2 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, $skip_interval, $timeranges, $ref);
				return true;
			}
			return false;
		}

		$items = array_filter(sscanf($input,"%[a-z] %d - %d / %d %[0-9:, -]"));
		if(count($items) == 5)
		{
			list($str1, $smday, $emday, $skip_interval, $timeranges) = $items;

			/* february 1 - 15 / 3 */
			/* monday 2 - 3 / 2 */
			/* day 1 - 25 / 4 */
			if(in_array($str1, $valid_weekdays))
			{
				$swday = $weekday_numbers[$str1];
				/* thursday 2 - 4 */
				$swday_offset = $smday;
				$ewday = $swday;
				$ewday_offset = $emday;
				$this->add_timeperiod_exception(self::DATERANGE_WEEK_DAY,
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, $skip_interval, $timeranges, $ref);
				return true;
			}
			else if(in_array($str1, $valid_months))
			{
				$smon = $month_numbers[$str1];
				$emon = $smon;
				/* february 3 - 5 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0,
					0, $emon, $emday, 0, 0, $skip_interval, $timeranges, $ref);
				return true;
			}
			else if(!strcmp($str1, "day"))
			{
				/* day 1 - 4 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, $skip_interval, $timeranges, $ref);
				return true;
			}
			return false;
		}

		$items = array_filter(sscanf($input,"%[a-z] %d %[a-z] - %[a-z] %d %[a-z] %[0-9:, -]"));
		if(count($items)  == 7)
		{
			list($str1, $swday_offset, $str2, $str3, $ewday_offset, $str4, $timeranges) = $items;

			/* wednesday 1 january - thursday 2 july */
			if(in_array($str1, $valid_weekdays) && in_array($str2, $valid_months) &&
				in_array($str3, $valid_weekdays) && in_array($str4, $valid_months))
			{
				$swday = $weekday_numbers[$str1];
				$smon = $month_numbers[$str2];
				$ewday = $weekday_numbers[$str3];
				$emon = $month_numbers[$str4];
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_WEEK_DAY,
					0, $smon, 0, $swday, $swday_offset, 0, $emon, 0, $ewday, $ewday_offset, 0, $timeranges, $ref);
				return true;
			}
			return false;
		}

		$items=array_filter(sscanf($input,"%[a-z] %d - %d %[0-9:, -]"));
		if(count($items) == 4)
		{
			list($str1, $smday, $emday, $timeranges) = $items;

			/* february 3 - 5 */
			/* thursday 2 - 4 */
			/* day 1 - 4 */
			if(in_array($str1, $valid_weekdays))
			{
				/* thursday 2 - 4 */
				$swday = $weekday_numbers[$str1];
				$swday_offset = $smday;
				$ewday = $weekday_numbers[$swday];
				$ewday_offset = $emday;
				$this->add_timeperiod_exception(self::DATERANGE_WEEK_DAY,
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, 0, $timeranges, $ref);
				return true;
			}
			else if(in_array($str1, $valid_months))
			{
				/* february 3 - 5 */
				$smon = $month_numbers[$str1];
				$emon = $smon;
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0, 0, $emon, $emday, 0, 0, 0, $timeranges, $ref);
				return true;
			}
			else if(!strcmp($str1,"day"))
			{
				/* day 1 - 4 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, 0, $timeranges, $ref);
				return true;
			}
			return false;
		}

		$items = array_filter(sscanf($input,"%[a-z] %d - %[a-z] %d %[0-9:, -]"));
		 if(count($items) == 5)
		{
			list($str1, $smday, $str2, $emday, $timeranges) = $items;
			/* february 1 - march 15 */
			/* monday 2 - thursday 3 */
			/* day 1 - day 5 */
			if(in_array($str1, $valid_weekdays) && in_array($str2, $valid_weekdays))
			{
				/* monday 2 - thursday 3 */
				$swday = $weekday_numbers[$str1];
				$ewday = $weekday_numbers[$str2];
				$swday_offset = $smday;
				$ewday_offset = $emday;
				$this->add_timeperiod_exception(self::DATERANGE_WEEK_DAY,
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, 0, $timeranges, $ref);
				return true;
			}
			elseif(in_array($str1, $valid_months) && in_array($str2, $valid_months))
			{
				/* february 1 - march 15 */
				$smon = $month_numbers[$str1];
				$emon = $month_numbers[$str2];
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0, 0, $emon, $emday, 0, 0, 0, $timeranges, $ref);
				return true;
			}
			else if(!strcmp($str1,"day")  && !strcmp($str2,"day"))
			{
				/* day 1 - day 5 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, 0, $timeranges, $ref);
				return true;
			}
			return false;
		}

		$items = array_filter(sscanf($input,"%[a-z] %d%*[ \t]%[0-9:, -]"));
		if(count($items) == 3)
		{
			list($str1, $smday, $timeranges) = $items;
			/* february 3 */
			/* thursday 2 */
			/* day 1 */
			if(in_array($str1, $valid_weekdays))
			{
				/* thursday 2 */
				$swday = $weekday_numbers[$str1];
				$swday_offset = $smday;
				$ewday = $swday;
				$ewday_offset = $swday_offset;
				$this->add_timeperiod_exception(self::DATERANGE_WEEK_DAY,
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, 0, $timeranges, $ref);
				return true;
			}
			elseif(in_array($str1, $valid_months))
			{
				/* february 3 */
				$smon = $month_numbers[$str1];
				$emon = $smon;
				$emday = $smday;
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0, 0, $emon, $emday, 0, 0, 0, $timeranges, $ref);
				return true;
			}
			else if(!strcmp($str1,"day"))
			{
				/* day 1 */
				$emday = $smday;
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, 0, $timeranges, $ref);
				return true;
			}
			return false;
		}

		$items = array_filter(sscanf($input,"%[a-z] %d %[a-z] %[0-9:, -]"));
		if(count($items) == 4)
		{
			list($str1, $swday_offset, $str2, $timeranges) = $items;

			/* thursday 3 february */
			if(in_array($str1, $valid_weekdays) && in_array($str2, $valid_months))
			{
				$swday = $weekday_numbers[$str1];
				$smon = $month_numbers[$str2];
				$emon = $smon;
				$ewday = $swday;
				$ewday_offset = $swday_offset;
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_WEEK_DAY,
					0, $smon, 0, $swday, $swday_offset, 0, $emon, 0, $ewday, $ewday_offset, 0, $timeranges, $ref);
				return true;
			}
			// return false;
		}

		# syntactically incorrect variable
		return false;
	}

	/**
	 * Converts a date into timestamp, with some extra features such as
	 * negative days of month to use days from the end of the month.
	 * As for time, 00:00:00 of the day is used.
	 *
	 * @param $year Year.
	 * @param $month Month.
	 * @param $monthday - Day of month, can be negative.
	 * @return The resulting timestamp.
	 */
	public function calculate_time_from_day_of_month($year, $month, $monthday)
	{
		$day = 0;

		/* positive day (3rd day) */
		if($monthday > 0)
		{
			$midnight = mktime(0,0,0, $month, $monthday, $year);

			/* if we rolled over to the next month, time is invalid */
			/* assume the user's intention is to keep it in the current month */
			if(date("n", $midnight) != $month)
				$midnight = 0;
		} else {/* negative offset (last day, 3rd to last day) */
			/* find last day in the month */
			$day = 32;
			do
			{
				/* back up a day */
				$day--;

				/* make the new time */
				$midnight = mktime(0,0,0, $month, $day, $year);

			} while(date("n", $midnight) != $month);

			/* now that we know the last day, back up more */

			/* -1 means last day of month, so add one to to make this correct - Mike Bird */
			$d = date("d", $midnight) + (($monthday < -30) ? -30 : $monthday + 1);
			$midnight = mktime(0,0,0, $month, $d, $year);

			/* if we rolled over to the previous month, time is invalid */
			/* assume the user's intention is to keep it in the current month */
			if(date("n", $midnight) != $month)
				$midnight = 0;
			}

		return $midnight;
	}

	/**
	 * Nagios supports exceptions such as "third monday in november 2010" - this
	 * converts such statements to unix timestamps.
	 *
	 * @param $year The year
	 * @param $month The month number
	 * @param $weekday The weekday's numeric presentation (0=sunday, 6=saturday)
	 * @param $weekday_offset Which occurence of the weekday, can be negative
	 */
	public function calculate_time_from_weekday_of_month($year, $month, $weekday, $weekday_offset)
	{
		$weeks = 0;
		$midnight = mktime(0,0,0, $month, 1, $year);
		/* how many days must we advance to reach the first instance of the weekday this month? */
		$days = $weekday - date("w", $midnight);
		if($days < 0)
			$days += 7;
		/* positive offset (3rd thursday) */
		if($weekday_offset > 0)
		{
			/* how many weeks must we advance (no more than 5 possible) */
			$weeks = ($weekday_offset > 5) ? 5 : $weekday_offset;
			$days += (($weeks - 1) * 7);

			/* make the new time */
			$midnight = mktime(0,0,0, $month, $days + 1, $year);
			/* if we rolled over to the next month, time is invalid */
			/* assume the user's intention is to keep it in the current month */
			if(date("n", $midnight) != $month)
				$midnight = 0;
		} else {	/* negative offset (last thursday, 3rd to last tuesday) */
			/* find last instance of weekday in the month */
			$days += (5*7);
			do
			{
				/* back up a week */
				$days -= 7;

				/* make the new time */
				$midnight = mktime(0,0,0, $month, $days + 1, $year);

				} while(date("n", $midnight) != $month);

			/* now that we know the last instance of the weekday, back up more */
			$weeks = ($weekday_offset < -5) ? -5 : $weekday_offset;
			$days = (($weeks + 1) * 7);

			$midnight = mktime(0,0,0, $month, $days + date("d", $midnight), $year);

			/* if we rolled over to the previous month, time is invalid */
			/* assume the user's intention is to keep it in the current month */
			if(date("n", $midnight) != $month)
				$midnight = 0;
		}
		return $midnight;
	}

	/**
	 * Determines if two timeranges overlap
	 * Note: stop time equal to start time in other range is NOT considered an overlap
	 *
	 * @param $range1 array('start'=> {timestamp}, 'stop' => {timestamp})
	 * @param $range2 array('start'=> {timestamp}, 'stop' => {timestamp})
	 * @param $inclusive Whether to count "straddling" periods as ovelapping,
	 * 	(Eg: start1 == stop2 or start2 == stop1)
	 * @return boolean
	 */
	public function timeranges_overlap(&$range1, &$range2, $inclusive=false)
	{
		if($inclusive)
			return ($range1['start'] <= $range2['stop'] && $range2['start'] <= $range1['stop']) ||
		           ($range2['start'] <= $range1['stop'] && $range1['start'] <= $range2['stop']);

		return ($range1['start'] < $range2['stop'] && $range2['start'] < $range1['stop']) ||
		       ($range2['start'] < $range1['stop'] && $range1['start'] < $range2['stop']);
	}

	/**
	 * Merges two timeranges into one.
	 *
	 * Assumes timeranges actually overlap and timeranges are correct (stop time after start time)
	 *
	 * @param $src_range array
	 * @param $dst_range array
	 * @return array
	 */
	public function merge_timeranges(&$src_range, &$dst_range)
	{
		return array('start' => min($src_range['start'], $dst_range['start']),
		             'stop'  => max($src_range['stop'],  $dst_range['stop']));
	}
	/**
	 * Subtract one timerange from another
	 *
	 * Assumes proper timeranges
	 *
	 * @param $timerange
	 * @param $subrange
	 * @return array
	 */
	public function subtract_timerange(&$timerange, &$subrange)
	{
		$start = $timerange['start'];
		$stop  = $timerange['stop'];

		$sub_start = $subrange['start'];
		$sub_stop  = $subrange['stop'];

		if($sub_start > $start && $sub_stop < $stop && $sub_start < $sub_stop)
		{
			return array
			(
				array('start' => $start, 'stop' => $sub_start),
				array('start' => $sub_stop, 'stop' => $stop)
			);
		}


		if($sub_stop > $start && $sub_stop < $stop)
			$stop = $sub_stop;

		if($sub_start < $stop && $sub_start > $start)
			$start = $sub_start;

		return array(array('start' => $start, 'stop' => $stop));
	}
	/**
	 * Add a new timerange to a set of timeranges.
	 * If new range overlaps an existing range, the two are merged to one.
	 *
	 * Assumes timeranges contain only valid values (eg: stop time after start time)
	 * Assumes the timerange set does not contain overlapping periods itself
	 *
	 * @param $range Array of range(s) to add
	 * @param $timerange_set The timerange set to add to
	 */
	public function add_timerange_to_set($range, &$timerange_set)
	{
		for($i=0 ; $i<count($timerange_set) ; $i++)
		{
			$testrange = $timerange_set[$i];
			if(self::timeranges_overlap($range, $testrange, true)) {
				# if range overlaps with current item, merge them and continue
				$range = self::merge_timeranges($range, $testrange);

				# remove the existing range, to later re-add it in the end
				unset($timerange_set[$i]);

				# to get the numerical indices back into sequence:
				$timerange_set = array_values($timerange_set);

				# Restart so we don't miss any element
				$i = 0;
			}
		}
		$timerange_set[] = $range;

		# recombobulate the indices
		$timerange_set = array_values($timerange_set);
	}

	/**
	 * Merge two sets of timeranges into one, with no overlapping ranges.
	 * Assumption: The argument sets may contain overlapping timeranges,
	 * which are wrong in principle, but we'll manage anyway.
	 *
	 * @param $timerange_set1 (of structure array( array('start' => 1203120, 'stop' => 120399), aray('start' => 140104, 'stop') ....)
	 * @param $timerange_set2 (of structure array( array('start' => 1203120, 'stop' => 120399), aray('start' => 140104, 'stop') ....)
	 * @return array
	 */
	public function merge_timerange_sets(&$timerange_set1, &$timerange_set2)
	{
		$resulting_timerange_set = array();

		# plan: add both ranges to third set, merging as we go along

		foreach($timerange_set1 as $range)
		{
			self::add_timerange_to_set($range, $resulting_timerange_set);
		}

		foreach($timerange_set2 as $range)
		{
			self::add_timerange_to_set($range, $resulting_timerange_set);
		}
		return $resulting_timerange_set;
	}

	/**
	 * Return true if both the start date and end date is the same day
	 *
	 * @param $dr A daterange
	 * @return true if condition holds
	 */
	public function is_daterange_single_day(&$dr)
	{
		if($dr['syear'] != $dr['eyear'])
			return false;
		if($dr['smon'] != $dr['emon'])
			return false;
		if($dr['smday'] != $dr['emday'])
			return false;
		if($dr['swday'] != $dr['ewday'])
			return false;
		if($dr['swday_offset'] != $dr['ewday_offset'])
			return false;

		return true;
	}

	/**
	 * Print the timerange $r
	 * @param $r A timerange
	 */
	public function print_timerange(&$r)
	{
		print "$r[start]-$r[stop]";
	}

	/**
	 * Take two sets of timeranges, and subtract all the ranges in the second
	 * from all the ranges in the first. The first set will be modified
	 * in-place.
	 *
	 * @param $set_include A set of original timeranges
	 * @param $set_exclude A set of timeranges to remove from the first arg
	 * @return $set_include, after manipulation
	 */
	public function subtract_timerange_sets(&$set_include, &$set_exclude)
	{
		for($i=0,$num_includes=count($set_include) ; $i<$num_includes ; $i++)
		{
			for($j=0,$num_excludes=count($set_exclude) ; $j<$num_excludes ; $j++)
			{
				$tmp = self::subtract_timerange($set_include[$i], $set_exclude[$j]);
				$set_include[$i] = $tmp[0];

				# if range was split into two, add other one at end (outside of loop)
				if(count($tmp) > 1) {
					$set_include[count($set_include)] = $tmp[1];
				}
			}
		}
		return $set_include;
	}

	/**
	*	Fetch info on first and last timestamp in db
	*/
	public function get_date_ranges()
	{
		$sql = "SELECT MIN(timestamp) AS min_date, ".
				"MAX(timestamp) AS max_date ".
			"FROM ".self::db_table;
		$db = Database::instance();
		$res = $db->query($sql);

		if (!$res)
			return false;
		$row = $res->current();
		$min_date = $row->min_date;
		$max_date = $row->max_date;
		return array($min_date, $max_date);
	}

	/**
	 * Create the base of the query to use when calculating
	 * alert summary. Each caller is responsible for adding
	 * sorting and limit options as necessary.
	 *
	 * @param $fields Database fields the caller needs
	 */
	private function build_alert_summary_query($fields = false, $auth = false)
	{
		$this->mangle_summary_options();

		# set some few defaults
		if (!$this->start_time)
			$this->start_time = 0;
		if (!$this->end_time)
			$this->end_time = time();

		# default to the most commonly used fields
		if (!$fields) {
			$fields = 'host_name, service_description, state, hard';
		}

		$hosts = false;
		$services = false;
		if ($this->servicegroup) {
			$hosts = $services = array();
			$smod = new Service_Model();
			foreach ($this->servicegroup as $sg) {
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
		} elseif ($this->hostgroup) {
			$hosts = array();
			$hmod = new Host_Model();
			foreach ($this->hostgroup as $hg) {
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
		} elseif ($this->service_description) {
			$services = false;
			foreach ($this->service_description as $srv) {
				$services[$srv] = $srv;
			}
		} elseif ($this->host_name) {
			$hosts = false;
			foreach ($this->host_name as $hn) {
				$hosts[$hn] = $hn;
			}
		}

		if (!$auth)
			$auth = new Nagios_auth_Model();
		if (empty($hosts) && $this->alert_types & 1) {
			if (!$auth->view_hosts_root) {
				$hosts = array();
				$host_list = $auth->get_authorized_hosts_r();
				if (!empty($host_list)) {
					foreach ($host_list as $h => $v) {
						$hosts[$h] = $h;
					}
				}
			}
			else {
				$hosts = true;
			}
		}
		if (empty($services) && $this->alert_types & 2) {
			if (!$auth->view_hosts_root && !$auth->view_services_root) {
				$services = array();
				$svc_list = $auth->get_authorized_services_r();
				if (!empty($svc_list)) {
					foreach ($svc_list as $s => $v) {
						$services[$s] = $s;
					}
				}
			}
			else {
				$services = true;
			}
		}

		# still empty?
		if (empty($hosts) && empty($services)) {
			return false;
		}

		$object_selection = false;
		if ($services) {
			$hosts_too = false;
			if ($hosts && $hosts !== true) {
				$object_selection = "\nAND (host_name IN(\n '" .
					join("',\n '", array_keys($hosts)) . "')";
				$hosts_too = true;
			}

			if ($services !== true) {
				if ($hosts_too === false)
					$object_selection .= "\nAND (";
				else
					$object_selection .= "\nOR ";
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
			$object_selection = "\nAND host_name IN(\n '" .
				join("',\n '", array_keys($hosts)) . "')";
		}

		if (empty($fields))
			$fields = '*';

		$query = "SELECT " . $fields . "\nFROM " . $this->db_table .
			"\nWHERE timestamp >= " . $this->start_time . " " .
			"AND timestamp <= " . $this->end_time . " ";
		if (!empty($object_selection)) {
			$query .= $object_selection . " ";
		}

		if (!$this->host_states || $this->host_states == self::HOST_ALL) {
			$this->host_states = self::HOST_ALL;
			$host_states_sql = 'event_type = ' . self::HOSTCHECK;
		} else {
			$x = array();
			$host_states_sql = '(event_type = ' . self::HOSTCHECK . ' ' .
				'AND state IN(';
			for ($i = 0; $i < self::HOST_ALL; $i++) {
				if (1 << $i & $this->host_states) {
					$x[$i] = $i;
				}
			}
			$host_states_sql .= join(',', $x) . '))';
		}

		if (!$this->service_states || $this->service_states == self::SERVICE_ALL) {
			$this->service_states = self::SERVICE_ALL;
			$service_states_sql = 'event_type = ' . self::SERVICECHECK;
		} else {
			$x = array();
			$service_states_sql = '(event_type = ' . self::SERVICECHECK .
				"\nAND state IN(";
			for ($i = 0; $i < self::SERVICE_ALL; $i++) {
				if (1 << $i & $this->service_states) {
					$x[$i] = $i;
				}
			}
			$service_states_sql .= join(',', $x) . '))';
		}

		switch ($this->alert_types) {
		 case 1: $query .= "\nAND " . $host_states_sql . ' '; break;
		 case 2: $query .= "\nAND " . $service_states_sql . ' '; break;
		 case 3:
			$query .= "\nAND (" . $host_states_sql .
				" OR " . $service_states_sql . ') '; break;
		}

		switch ($this->state_types) {
		 case 0: case 3: default:
			break;
		 case 1:
			$query .= "\nAND hard = 0 ";
			break;
		 case 2:
			$query .= "\nAND hard = 1 ";
			break;
		}

		$this->summary_query = $query;
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
	public function test_summary_queries($auth = false)
	{
		$result = array();
		for ($host_state = 1; $host_state <= 7; $host_state++) {
			$this->host_states = $host_state;
			for ($service_state = 1; $service_state <= 15; $service_state++) {
				$this->service_states = $service_state;
				for ($state_types = 1; $state_types <= 3; $state_types++) {
					$this->state_types = $state_types;
					for ($alert_types = 1; $alert_types <= 3; $alert_types++) {
						$this->alert_types = $alert_types;
						$query = $this->build_alert_summary_query(false, $auth);
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
		$host_states = $this->host_states;
		$service_states = $this->service_states;
		$this->host_states = self::HOST_ALL;
		$this->service_states = self::SERVICE_ALL;
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
		if ($this->summary_items > 0) {
			$result = array_slice($result, 0, $this->summary_items, true);
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
		foreach ($this->host_name as $hn) {
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
		foreach ($this->service_description as $name) {
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
		foreach ($this->hostgroup as $hostgroup) {
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
		foreach ($this->servicegroup as $servicegroup) {
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

	private function mangle_summary_options()
	{
		if (!empty($this->hostgroup) && !is_array($this->hostgroup)) {
			$this->hostgroup = array($this->hostgroup);
		}
		if (!empty($this->servicegroup) && !is_array($this->servicegroup)) {
			$this->servicegroup = array($this->servicegroup);
		}
		if (is_string($this->host_name) && is_string($this->service_description)) {
			$this->service_description =
				array($this->host_name . ';' . $this->service_description);
			$this->host_name = false;
		}
		if (!empty($this->host_name) && !is_array($this->host_name)) {
			$this->host_name = array($this->host_name);
		}
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
			die;
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
		if ($this->servicegroup) {
			$result = $this->alert_totals_by_servicegroup($dbr);
		} elseif ($this->hostgroup) {
			$result = $this->alert_totals_by_hostgroup($dbr);
		} elseif ($this->service_description) {
			$result = $this->alert_totals_by_service($dbr);
		} elseif ($this->host_name) {
			$result = $this->alert_totals_by_host($dbr);
		}

		$this->set_alert_total_totals($result);
		$this->summary_result = $result;
		$this->completion_time = microtime(true) - $this->completion_time;
		return $this->summary_result;
	}

	/**
	 * Find and return the latest $this->summary_items alert
	 * producers according to the search criteria.
	 */
	public function recent_alerts()
	{
		$this->completion_time = microtime(true);
		$query = $this->build_alert_summary_query('*');

		if ($query === false) {
			return false;
		}

		$query .= " ORDER BY timestamp DESC";
		if ($this->summary_items > 0) {
			$query .= " LIMIT " . $this->summary_items;
		}
		$this->summary_query = $query;

		$dbr = $this->db->query($query)->result(false);
		if (!is_object($dbr)) {
			echo Kohana::debug($this->db->errorinfo(), explode("\n", $query));
			die;
		}

		$this->summary_result = array();
		foreach ($dbr as $row) {
			if ($this->tp_inside($row['timestamp']))
				$this->summary_result[] = $row;
		}

		$this->completion_time = microtime(true) - $this->completion_time;
		return $this->summary_result;
	}

	/**
	 * Calculates $this->start_time and $this->end_time based on an
	 * availability report style period such as "today", "last24hours"
	 * or "lastmonth".
	 *
	 * @param $report_period The textual period to set our options by
	 * @return false on errors, true on success
	 */
	private function calculate_time($report_period)
	{
		$year_now 	= date('Y', time());
		$month_now 	= date('m', time());
		$day_now	= date('d', time());
		$week_now 	= date('W', time());
		$weekday_now = date('w', time())-1;
		$time_start	= false;
		$time_end	= false;
		$now = time();

		switch ($report_period) {
		 case 'today':
			$time_start = mktime(0, 0, 0, $month_now, $day_now, $year_now);
			$time_end 	= time();
			break;
		 case 'last24hours':
			$time_start = mktime(date('H', time()), date('i', time()), date('s', time()), $month_now, $day_now -1, $year_now);
			$time_end 	= time();
			break;
		 case 'yesterday':
			$time_start = mktime(0, 0, 0, $month_now, $day_now -1, $year_now);
			$time_end 	= mktime(0, 0, 0, $month_now, $day_now, $year_now);
			break;
		 case 'thisweek':
			$time_start = strtotime('today - '.$weekday_now.' days');
			$time_end 	= time();
			break;
		 case 'last7days':
			$time_start	= strtotime('now - 7 days');
			$time_end	= time();
			break;
		 case 'lastweek':
			$time_start = strtotime('midnight last monday -7 days');
			$time_end	= strtotime('midnight last monday');
			break;
		 case 'thismonth':
			$time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			$time_end	= time();
			break;
		 case 'last31days':
			$time_start = strtotime('now - 31 days');
			$time_end	= time();
			break;
		 case 'lastmonth':
			$time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01 -1 month');
			$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			break;
		 case 'thisyear':
			$time_start = strtotime('midnight '.$year_now.'-01-01');
			$time_end	= time();
			break;
		 case 'lastyear':
			$time_start = strtotime('midnight '.$year_now.'-01-01 -1 year');
			$time_end	= strtotime('midnight '.$year_now.'-01-01');
			break;
		 case 'last12months':
			$time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -12 months');
			$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			break;
		 case 'last3months':
			$time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -3 months');
			$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			break;
		 case 'last6months':
			$time_start	= strtotime('midnight '.$year_now.'-'.$month_now.'-01 -6 months');
			$time_end	= strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			break;
		 case 'lastquarter':
			$t = getdate();
			if($t['mon'] <= 3){
				$lqstart = ($t['year']-1)."-10-01";
				$lqend = ($t['year']-1)."-12-31";
			} elseif ($t['mon'] <= 6) {
				$lqstart = $t['year']."-01-01";
				$lqend = $t['year']."-03-31";
			} elseif ($t['mon'] <= 9){
				$lqstart = $t['year']."-04-01";
				$lqend = $t['year']."-06-30";
			} else {
				$lqstart = $t['year']."-07-01";
				$lqend = $t['year']."-09-30";
			}
			$time_start = strtotime($lqstart);
			$time_end = strtotime($lqend);
			break;
		 case 'custom':
			# we'll have "start_time" and "end_time" in
			# the options when this happens
			return true;
		 default:
			# unknown option, ie bogosity
			return false;
		}

		if($time_start > $now)
			$time_start = $now;

		if($time_end > $now)
			$time_end = $now;

		$this->start_time = $time_start;
		$this->end_time = $time_end;
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
	*	Build alert history query based
	* 	on supplied options
	*/
	public function build_alert_history_query($fields='*', $report_type=false)
	{
		# set some few defaults
		if (!$this->start_time)
			$this->start_time = 0;
		if (!$this->end_time)
			$this->end_time = time();

		$hosts = false;
		$services = false;
		if ($this->servicegroup) {
			$services = array();
			$smod = new Service_Model();
			if (!is_array($this->servicegroup)) {
				$this->servicegroup = array($this->servicegroup);
			}

			foreach ($this->servicegroup as $sg) {
				$res = $smod->get_services_for_group($sg);
				foreach ($res as $o) {
					$name = $o->host_name . ';' . $o->service_description;
					if (empty($services[$name])) {
						$services[$name] = array();
					}
					$services[$name][$sg] = $sg;
				}
			}
			$this->service_servicegroup = $services;
		} elseif ($this->hostgroup) {
			$hosts = array();
			$hmod = new Hostgroup_Model();
			if (!is_array($this->hostgroup)) {
				$this->hostgroup = array($this->hostgroup);
			}
			foreach ($this->hostgroup as $hg) {
				$res = $hmod->get_hosts_for_group($hg);
				foreach ($res as $o) {
					$name = $o->host_name;
					if (empty($hosts[$name])) {
						$hosts[$name] = array();
					}
					$hosts[$name][$hg] = $hg;
				}
			}
			$this->host_hostgrop = $hosts;
		} elseif ($this->service_description) {
			$services = false;
			if (is_array($this->service_description) && !empty($this->service_description)) {
				foreach ($this->service_description as $srv) {
					$services[$srv] = $srv;
				}
			} else {
				$services[$this->host_name.';'.$this->service_description] = $this->host_name.';'.$this->service_description;
			}
		} elseif ($this->host_name) {
			$hosts = false;
			if (is_array($this->host_name) && !empty($this->host_name)) {
				foreach ($this->host_name as $hn) {
					$hosts[$hn] = $hn;
				}
			} else {
				$hosts[$this->host_name] = $this->host_name;
			}
		}

		$object_selection = false;
		if ($hosts) {
			$object_selection = "AND host_name IN('" .
				join("', '", array_keys($hosts)) . "')";
		} elseif ($services) {
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
					$object_selection .= "AND service_description = '{$s}')";
				$orstr = " OR ";
			}
			if ($object_selection)
				$object_selection = 'AND ('.$object_selection.')';
		}

		if (empty($fields))
			$fields = '*';

		$query = "SELECT " . $fields . " FROM " . $this->db_table . " " .
			"WHERE timestamp >= " . $this->start_time . " " .
			"AND timestamp <= " . $this->end_time . " ";
		if (!empty($object_selection)) {
			$query .= $object_selection . " ";
		}

		if (!$this->host_states || $this->host_states == 7) {
			$this->host_states = 7;
			$host_states_sql = 'event_type = ' . self::HOSTCHECK . ' ';
		} else {
			$x = array();
			$host_states_sql = '(event_type = ' . self::HOSTCHECK . ' ' .
				'AND state IN(';
			for ($i = 0; $i < 7; $i++) {
				if (1 << $i & $this->host_states) {
					$x[$i] = $i;
				}
			}
			$host_states_sql .= join(',', $x) . ')) ';
		}

		if (!$this->service_states || $this->service_states == 15) {
			$this->service_states = 15;
			$service_states_sql = 'event_type = ' . self::SERVICECHECK . ' ';
		} else {
			$x = array();
			$service_states_sql = '(event_type = ' . self::SERVICECHECK . ' ' .
				'AND state IN(';
			for ($i = 0; $i < 15; $i++) {
				if (1 << $i & $this->service_states) {
					$x[$i] = $i;
				}
			}
			$service_states_sql .= join(',', $x) . ')) ';
		}

		switch ($report_type) {
			case 'hosts': case 'hostgroups': $query .= 'AND ' . $host_states_sql; break;
			case 'services': case 'servicegroups': $query .= 'AND ' . $service_states_sql; break;
		}

		switch ($this->state_types) {
		 case 0: case 3: default:
			break;
		 case 1:
			$query .= "AND hard = 0 ";
			break;
		 case 2:
			$query .= "AND hard = 1 ";
			break;
		}

		$this->summary_query = $query;
		return $query;
	}

	/**
	*	Fetch alert history for histogram report
	* 	@param $options array with values needed for report
	* 	@param $slots array with slots to fill with data
	* 	@return array with keys: min, max, avg, data
	*/
	public function alert_history($options=false, $slots=false)
	{
		if (empty($slots) || !is_array($slots))
			return false;

		$breakdown = $options['breakdown'];
		$report_type = $options['report_type'];
		$newstatesonly = $options['newstatesonly'];

		# compute what event counters we need depending on report type
		$events = false;
		switch ($report_type) {
			case 'hosts': case 'hostgroups':
				if (!$this->host_states || $this->host_states == 7) {
					$events = array(0 => 0, 1 => 0, 2 => 0);
				} else {
					$events = array();
					for ($i = 0; $i < 7; $i++) {
						if (1 << $i & $this->host_states) {
							$events[$i] = 0;
						}
					}
				}
				break;
			case 'services': case 'servicegroups':
				if (!$this->service_states || $this->service_states == 15) {
					$events = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
				} else {
					$events = array();
					for ($i = 0; $i < 15; $i++) {
						if (1 << $i & $this->service_states) {
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
		$query = $this->build_alert_history_query($fields, $report_type);

		$data = false;

		# tell alert_history_data() how to treat timestamp
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

		$data = $this->alert_history_data($date_str, $fixed_slots, $newstatesonly);

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
	* 	@param $date_str string for use in PHP date()
	* 	@param $slots array with slots to fill with data
	* 	@param $newstatesonly bool Used to decide if to ignore repated events or not
	* 	@return array Populated slots array with found data
	*/
	public function alert_history_data($date_str='j' , $slots=false, $newstatesonly=false)
	{
		if (empty($this->summary_query) || empty($slots)) {
			return false;
		}

		$res = $this->db->query($this->summary_query)->result(false);
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
