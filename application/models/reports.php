<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Reports model
 * Responsible for fetching data for avail and SLA reports. This class
 * must be instantiated to work properly.
 */
class Reports_Model extends Model
{
	// state codes
	const STATE_PENDING = -1;
	const STATE_OK = 0;
	const HOST_UP = 0;
	const HOST_DOWN = 1;
	const HOST_UNREACHABLE = 2;
	const HOST_PENDING = -1;
	const SERVICE_OK = 0;
	const SERVICE_WARNING = 1;
	const SERVICE_CRITICAL = 2;
	const SERVICE_UNKNOWN = 3;
	const SERVICE_PENDING = -1;
	const PROCESS_SHUTDOWN = 103;
	const PROCESS_RESTART = 102;
	const PROCESS_START = 100;
	const SERVICECHECK = 701;
	const HOSTCHECK =  801;
	const DOWNTIME_START = 1103;
	const DOWNTIME_STOP = 1104;
	const PERC_DEC = 3; // nr of decimals in returned percentage
	const DEBUG = true;
	const DATERANGE_CALENDAR_DATE = 0; 	/* eg: 2008-12-25 - 2009-02-01 / 6 */
	const DATERANGE_MONTH_DATE = 1;  	/* eg: july 4 - november 15 / 3 (specific month) */
	const DATERANGE_MONTH_DAY = 2;  	/* eg: day 1 - 25 / 5  (generic month)  */
	const DATERANGE_MONTH_WEEK_DAY = 3; /* eg: thursday 1 april - tuesday 2 may / 2 (specific month) */
	const DATERANGE_WEEK_DAY = 4;  		/* eg: thursday 3 - monday 4 (generic month) */
	const DATERANGE_TYPES = 5;

	var $db = false; # PDO database object.

	var $db_start_time = 0; # earliest database timestamp we look at
	var $db_end_time = 0;   # latest database timestamp we look at
	var $debug = array();
	var $completion_time = 0;

	# alert summary options
	var $alert_types = 3; # host and service alerts by default
	var $state_types = 3; # soft and hard states by default
	var $host_states = 7; # all host states by default
	var $service_states = 15; # all service states by default
	var $summary_items = 25; # max items to return
	var $summary_result = array();
	var $summary_query = '';
	private $host_hostgroup; /** array(host => array(hgroup1, hgroupx...)) */
	private $service_servicegroup; /** same as $host_hostgroup */

	var $st_raw = array(); # raw states
	var $st_needs_log = false;
	var $st_log = false;
	var $st_prev_row = array();
	var $st_running = 0;
	var $st_dt_depth = 0;
	var $st_is_service = false;
	var $st_source = false;
	var $st_inactive = 0;
	var $st_text = array();
	var $st_sub = array(); # only used by the master report
	var $st_sub_discrepancies = 0;
	var $st_obj_type = '';
	var $st_state_calculator = 'st_worst';
	var $st_last_dt_start_depth = array();

	/**
	 * The calculated state of the object, taking such things
	 * as scheduled downtime counted as uptime into consideration
	 */
	private $st_obj_state = false;

	/** The real state of the object */
	private $st_real_state = false;

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

	public $master = false;
	public $id = '';
	public $result = array();
	public $csv_result = array();
	public $old_csv_result = array();
	public $sub_results = array();
	public $use_average = false;
	public $assume_states_during_not_running = false;
	public $initial_assumed_host_state = false;
	public $initial_assumed_service_state = false;
	public $scheduled_downtime_as_uptime = false;
	public $include_soft_states = true; /* NOTE: defaults to true */
	public $report_timeperiod = false;
	public $timeperiods_resolved = false; #whether timeperiod exceptions and exclusions have been resolved
	public $initial_state = false;
	public $initial_dt_depth = false;
	public $start_time = false;
	public $end_time = false;
	public $assume_initial_states = null;
	public $host_name = false;
	public $service_description = false;
	public $servicegroup = false;
	public $hostgroup = false;
	public $db_name = 'monitor_reports';
	const db_name = 'monitor_reports';
	public $db_table = 'report_data';
	const db_table = 'report_data';
	public $sub_reports = array();
	public $last_shutdown = false;
	public $states = array();
	public $tp_exceptions = array();

	public $options = array();

	/**
	 * Constructor
	 * @param $db_name Database name
	 * @param $db_table Database name
	 */
	public function __construct($db_name='monitor_reports', $db_table='report_data', $db = false)
	{
		if (self::DEBUG === true) {
			assert_options(ASSERT_ACTIVE, 1);
			assert_options(ASSERT_WARNING, 0);
			assert_options(ASSERT_QUIET_EVAL, 0);
			assert_options(ASSERT_BAIL, 1);

			# use report helper callback
			assert_options(ASSERT_CALLBACK, array('reports', 'lib_reports_assert_handler'));
		}

		$this->st_obj_state = self::STATE_PENDING;

		/** The real state of the object */
		$this->st_real_state = self::STATE_PENDING;

		if ($db) {
			$this->db = $db;
		} else {
			if (!empty($db_name))
				$this->db_name 	= $db_name;
			if (!empty($db_table))
				$this->db_table = $db_table;

			$this->db = pdodb::instance('mysql', $this->db_name);
		}
	}

	/**
	*	Check that we have a valid database installed and usable.
	*/
	public function _self_check()
	{
		try {
			# this will result in error if db_name section
			# isn't set in config/database.php
			$db = new Database(self::db_name);
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

	public function set_report_timeperiod($period=NULL)
	{
		$valid_weekdays = reports::$valid_weekdays;

		if (empty($period)) {
			$this->report_timeperiod = false;
			return true;
		}

		$this->report_timeperiod = array();

		$res = Timeperiod_Model::get($period);
		if (!count($res))
			return false;
		$result = $res->current();
		$timeperiod_id = $result['id'];
		unset($result['id']);
		unset($result['timeperiod_name']);
		unset($result['alias']);
		unset($result['instance_id']);

		$includes = $result;

		$result_set = Timeperiod_Model::excludes($timeperiod_id);

		if(count($result_set))
		{
			foreach($result_set as $result_row) # for each exclude
			{
				foreach($valid_weekdays as $i => $weekday) # each weekday
				{
					if(!isset($includes[$weekday]))
					{
						# no time to include this day
						continue;
					}
					$exclude_ranges =& $result_row[$weekday];
					if (empty($exclude_ranges))
					{
						# have include, no exclude
						$this->report_timeperiod[$i] = $this->tp_parse_day($includes[$weekday]);
					}
					else
					{
						# have both include and exclude
						$include        = $this->tp_parse_day($includes[$weekday]);
						$exclude_ranges = $this->tp_parse_day($exclude_ranges);
						$this->report_timeperiod[$i] = subtract_timerange_sets($include, $exclude_ranges);
					}
				}
			}
		} else { # use old set_option() based functionality
			$errors = 0;
			foreach ($includes as $k => $v) {
				if (empty($v)) {
					continue;
				}
				$errors += $this->set_option($k, $v) === false;
			}

			if ($errors)
				return false;
		}

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
		if (empty($this->report_timeperiod))
			return 0;

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
		if (!empty($this->tp_exceptions[$year][$tm_yday])) {
			$ents = $this->tp_exceptions[$year][$tm_yday];
		}
		elseif (!empty($this->report_timeperiod[$day]))
			$ents = $this->report_timeperiod[$day];

		foreach ($ents as $ent) {
			if ($ent[$what] <= $day_seconds && $ent[$other] > $day_seconds)
				return $when;

			if ($ent[$what] > $day_seconds)
				return $midnight_to_when + $ent[$what];
		}

		$orig_day = $day;
		$loops = 0;
		for ($day = $orig_day + 1; $loops < 7; $day++) {
			$ents = false;
			$loops++;
			if ($day > 6)
				$day = 0;

			$midnight_to_when += 86400;

			if (!empty($this->tp_exceptions[$year][$tm_yday + $day]))
				$ents = $this->tp_exceptions[$year][$tm_yday + $day];
			elseif (!empty($this->report_timeperiod[$day]))
				$ents = $this->report_timeperiod[$day];

			# no exceptions, no timeperiod entry
			if (!$ents)
				continue;

			foreach ($ents as $ent)
				return $midnight_to_when + $ent[$what];
		}

		# @@@FIXME: no print statements in models
		echo "upsadaisy, I fell through\n";

		return 0;
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
		if ($start >= $stop || $this->report_timeperiod === false)
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
		for (;;) {
			if (($nstart = $this->tp_next($nstop, 'start')) > $stop)
				$nstart = $stop;
			if (($nstop = $this->tp_next($nstart, 'stop')) > $stop)
				$nstop = $stop;

			$active += $nstop - $nstart;
			if ($nstart >= $stop || $nstop >= $stop)
				return $active;
		}

		/* never reached */
		# @@@FIXME: no print statements in models
		echo "HALALIIIII!\n";
		exit(1);
	}

	public function register_db_time($t)
	{
		if (!$this->db_start_time || $t < $this->db_start_time)
			$this->db_start_time = $t;
		if (!$this->db_end_time || $t > $this->db_end_time)
			$this->db_end_time = $t;
		$this->debug['db_start_time'] = $this->db_start_time;
		$this->debug['db_end_time'] = $this->db_end_time;
	}

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
			 'report_timeperiod' => 'string',
			 'scheduled_downtime_as_uptime' => 'bool',
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
			 'use_average' => 'bool');

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
			break;

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
			break;
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
			break;
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
			break;
		 case 'servicegroup_name':
			$this->host_name = false;
			$this->service_description = false;
			$this->hostgroup = false;
			$this->servicegroup = $value;
			return true;
			break;
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
		# @@@FIXME: support exclude
		# @@@FIXME: support exceptions
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
	 * Throws Exception if any parameter has bogus values.
	 */
	public function add_timeperiod_exception($dateperiod_type,
	                                  $syear, $smon, $smday, $swday, $swday_offset,
	                                  $eyear, $emon, $emday, $ewday, $ewday_offset,
	                                  $skip_interval, $timeranges)
	{
		$days_per_month = reports::$days_per_month;

		if (!isset($this->tp_exceptions['unresolved']))
			$this->tp_exception['unresolved'] = array();

		assert($dateperiod_type >= 0 && $dateperiod_type < self::DATERANGE_TYPES); # can only fail if programmer messed up
		$timeranges = $this->tp_parse_day($timeranges);

		$this->tp_exceptions['unresolved'][] = array
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

	public function set_options($options)
	{
		$errors = false;
		foreach ($options as $name => $value)
			$errors |= intval(!$this->set_option($name, $value));

		return $errors ? false : true;
	}

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

		$this->timeperiods_resolved = empty($this->tp_exceptions['unresolved']);
		if ($this->timeperiods_resolved)
			return;

		$all_exceptions =& $this->tp_exceptions;
		$unres_exceptions =& $all_exceptions['unresolved'];

		$start_year = date('Y', $start_time);
		$end_year = date('Y', $end_time);
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

				if (!$check_exception || $exp_start > $day_time || $exp_end < $day_time)
					continue;

				if(!isset($all_exceptions[$day_year]))
					$all_exceptions[$day_year] = array();

				if(!isset($all_exceptions[$day_year][$day]))
					$all_exceptions[$day_year][$day] = array();

				# if so, merge timeranges with existing for this day
				$all_exceptions[$day_year][$day] = self::merge_timerange_sets($all_exceptions[$day_year][$day], $x['timeranges']);
			}
		}
		unset($this->tp_exceptions['unresolved']);
		$this->timeperiods_resolved = true;
	}


	public function set_master($master)
	{
		$this->master = $master;
		$this->set_options($master->options);
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

			if (!count($res_group))
				return false;

			$res_group->result(false);

			foreach ($res_group as $row) {
				$hostname[] = $row['host_name'];
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
				$servicename[] = $row['host_name'] . ';' . $row['service_description'];
			}
		}

		if (is_array($hostname) && is_array($servicename))
			return false;

		if (is_array($hostname)) {
			// == multiple hosts ==
			foreach ($hostname as $host) {
				$sub_class = new Reports_Model(false, false, $this->db);
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
					$sub_class = new Reports_Model(false, false, $this->db);
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
		$dbr = $this->db->query($query);

		if (!$dbr || !$dbr->rowCount())
			return false;

		$row = $dbr->fetch();

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
			$in_dt = $ary[1] == 1;
			$p3 = $in_dt ? '' : 'UN';
			$p3 .= 'SCHEDULED';

			if (!$is_running)
				$cstate['TIME_UNDETERMINED_NOT_RUNNING'] += $duration;

			$p1 = $is_running ? '_' : '_UNKNOWN_';

			# this is where we hack in scheduled downtime as uptime
			if ($in_dt && $this->scheduled_downtime_as_uptime) {
				$p2 = $conv[0];
			}
			elseif (isset($conv[$ary[2]])) {
				$p2 = $conv[$ary[2]];

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
						reports::percent(self::get_array_var($cstate, $str . $dt_str), $div);
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

		return $cstate;
	}

	# this just lets us safely access array variables
	# that might not be set, optionally specifying a default
	# to return in case the variable isn't found.
	# Note that $k (for key) can be an array
	public function get_array_var($ary, $k, $def = false)
	{
		if (is_array($k))
			$try = $k;
		else
			$try = array($k);

		foreach ($try as $k)
			if (isset($ary[$k]))
				return $ary[$k];

		return $def;
	}

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

		if ($this->st_needs_log) {
			$this->st_prev_row['duration'] = $duration;
			$this->st_log[] = $this->st_prev_row;
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

		foreach ($this->st_sub as $state => $cnt) {
			if ($cnt < 0)
				echo "WARN: $state count is $cnt\n";
			$st_sub_totals += $cnt;
		}

		$actual = $this->st_sub;
		$stash = array();
		foreach ($actual as $state => $cnt)
			$actual[$state] = 0;
		$real = $actual;
		foreach ($this->sub_reports as $rpt) {
			$actual[$rpt->st_obj_state]++;
			$real[$rpt->st_real_state]++;
			$stash[$rpt->st_obj_state][] = $rpt;
		}

		foreach ($actual as $state => $cnt) {
			if ($this->st_sub[$state] !== $cnt) {
				$disc_desc[] = "DISCREPANCY: ($state): actual=$cnt; st_sub=" . $this->st_sub[$state] . "\n";
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

	public function st_parse_row($row = false)
	{
		$this->register_db_time($row['the_time']);

		$this->st_update($row['the_time']);

		$obj_name = $sub = false;
		if (!empty($row['service_description'])) {
			$obj_name = $row['host_name'] . ";" . $row['service_description'];
		}
		elseif (!empty($row['host_name'])) {
			$obj_name = $row['host_name'];
		}

		# sub-reports must be be st_update()d before we
		# set its state in the case statement below
		$sub = false;
		if ($obj_name && isset($this->sub_reports[$obj_name])) {
			$sub = $this->sub_reports[$obj_name];
			$sub->st_update($row['the_time']);
		}

		# if we get an event and monitor is stopped, we
		# consider it started. This is necessary to prevent
		# us from generating bogus reports in case there's
		# a missing PROCESS_START event in the database
		$is_running = intval($row['event_type'] !== self::PROCESS_SHUTDOWN);

		# only update sub-reports on statechange
		if ($is_running != $this->st_running) {
			foreach ($this->sub_reports as $rpt)
				$rpt->st_running = $is_running;
			$this->st_running = $is_running;
		}

		switch($row['event_type']) {
		 case self::DOWNTIME_START:
			$row['output'] = $this->st_obj_type . ' has entered a period of scheduled downtime';
			# Due to a bug in the nagios module (not the import
			# program), DOWNTIME_START events sometimes have a
			# downtime_depth of 0 in the database (which is clearly
			# bogus). We work around it by making sure it's always
			# at least 1.
			if (!$row['downtime_depth'])
				$row['downtime_depth'] = 1;
			# For the same reason, we must save the dt depth for
			# when we encounter the DOWNTIME_STOP event later, so
			# we know which value it *should* have.
			$this->st_last_dt_start_depth[$obj_name] = $row['downtime_depth'];

			# update this sub-object's status to OK if we're supposed
			# to calculate scheduled downtime as uptime
			if ($this->scheduled_downtime_as_uptime && $sub && $sub->st_obj_state != self::STATE_OK) {
				$sub->st_dt_depth++;
				$sub->calculate_object_state();
				$sub->sub->st_obj_state = self::STATE_OK;
				$this->st_sub[$sub->st_real_state]--;
				$this->st_sub[$sub->st_obj_state]++;
			}
			if ($sub) {
				# sub->st_dt_depth must be set before we get the
				# common downtime depth here
				$sub->st_dt_depth = intval(!!$row['downtime_depth']);
				$this->st_dt_depth = $this->get_common_downtime_state();
			} else {
				$this->st_dt_depth = intval(!!$row['downtime_depth']);
			}
			break;
		 case self::DOWNTIME_STOP:
			$row['output'] = $this->st_obj_type . ' has exited a period of scheduled downtime';

			# Similar to the fix above, we check the downtime
			# depth of the last start event here to make sure
			# it's decremented by one (unless it's already zero).
			if ($row['downtime_depth']) {
				if (!isset($this->st_last_dt_start_depth[$obj_name]))
					$row['downtime_depth'] = 0;
				elseif ($row['downtime_depth'] == $this->st_last_dt_start_depth[$obj_name]) {
					$row['downtime_depth'] = --$this->st_last_dt_start_depth[$obj_name];
				}
			}

			if ($sub) {
				# sub->st_dt_depth must be set before we retrieve the
				# common downtime depth
				$sub->st_dt_depth = intval(!!$row['downtime_depth']);
				$this->st_dt_depth = $this->get_common_downtime_state();
			} else {
				$this->st_dt_depth = intval(!!$row['downtime_depth']);
			}

			# possibly restore the actual object state if
			# scheduled downtime ends
			if ($this->scheduled_downtime_as_uptime) {
				if ($sub && !$sub->st_dt_depth && $sub->st_obj_state != $sub->st_real_state) {
					$this->st_sub[$sub->st_obj_state]--;
					$this->st_sub[$sub->st_real_state]++;
					$sub->calculate_object_state();
				}
				else
					$this->calculate_object_state();
			}
			break;

		 case self::SERVICECHECK:
		 case self::HOSTCHECK:
			$state = $row['state'];
			if ($sub && $sub->scheduled_downtime_as_uptime && $sub->st_dt_depth)
				$state = self::STATE_OK;

			# update the real state of the object
			if ($sub)
				$sub->st_real_state = $row['state'];
			else
				$this->st_real_state = $row['state'];

			if ($sub && $sub->st_obj_state != $state) {
				$this->st_sub[$sub->st_obj_state]--;
				$this->st_sub[$state]++;
				$this->st_obj_state = $this->st_worst();
			}
			break;
		 default:
			//ERROR
		}

		if ($sub)
			$sub->calculate_object_state();

		$this->calculate_object_state();

		# fairly nifty debugging check. This is the place to
		# call it if you're going to.
		#$this->check_st_sub_discrepancies($row);

		# this must come after the case table above
		if ($sub) {
			$sub->st_prev_row = $row;
		}

		# we mangle $row here, since $this->st_prev_row is always
		# derived from it, except when it's the initial
		# state which always has (faked) output
		if (empty($row['output']))
			$row['output'] = '(No output)';

		$this->st_prev_row = $row;
	}

	public function st_worst()
	{
		if (empty($this->sub_reports))
			return $this->st_obj_state;

		if ($this->st_is_service)
			return $this->get_worst_service_state();
		return $this->get_worst_host_state();
	}

	public function st_best()
	{
		if (empty($this->sub_reports))
			return $this->st_obj_state;

		if ($this->st_is_service)
			return $this->get_best_service_state();
		return $this->get_best_host_state();
	}

	public function calculate_object_state($state = false)
	{
		if ($this->sub_reports) {
			$func = $this->st_state_calculator;
			$this->st_obj_state = $this->$func();
			return;
		}

		if (!$state)
			$state = $this->st_real_state;

		# if we're counting scheduled downtime as uptime and
		# we're in downtime, the calculated state is always OK
		if ($this->scheduled_downtime_as_uptime && $this->st_dt_depth) {
			$this->st_obj_state = self::STATE_OK;
		} else {
			$this->st_obj_state = $state;
		}
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

		if (!$this->master && empty($this->sub_reports)) {
			$this->st_needs_log = true;
		}
		if ($this->st_needs_log === true) {
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

		# id must always be set properly for single-object reports
		if (empty($this->id)) {
			$this->id = $hostname;

			if (!empty($servicename))
				$this->id .= ";$servicename";
		}

		# prime the state counter for sub-objects
		if (!empty($this->sub_reports)) {
			foreach ($this->st_text as $st => $discard)
				$this->st_sub[$st] = 0;
			foreach ($this->sub_reports as $rpt) {
				$rpt->st_dt_depth = $rpt->initial_dt_depth;
				$rpt->calculate_object_state();
				$this->st_sub[$rpt->st_obj_state]++;
			}
			$func = $this->st_state_calculator;
			$this->st_obj_state = $this->$func();
			$this->st_dt_depth = $this->get_common_downtime_state();
		}
		else {
			$this->st_dt_depth = intval(!!$this->get_initial_dt_depth($hostname, $servicename));
			$this->st_real_state = $this->get_initial_state($hostname, $servicename);
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
			                $this->st_text[$this->st_real_state], $this->st_real_state);
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
		while ($row = $dbr->fetch(PDO::FETCH_ASSOC)) {
			$this->st_parse_row($row);
		}
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
		return array('source' => $this->st_source, 'log' => $this->st_log, 'states' => $converted_state, 'tot_time' => $total_time, 'groupname' => $groupname);
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
			"state,timestamp AS the_time, hard, event_type, " .
			"downtime_depth";
		# output is a TEXT field, so it needs an extra disk
		# lookup to fetch and we don't (usually) need it
		if ($this->st_needs_log)
			$sql .= ", output";

		$sql .= " FROM ".$this->db_name.".".$this->db_table." " .
			"WHERE timestamp >=".$this->start_time." " .
			"AND timestamp <=".$this->end_time." ";
		if (is_array($hostname) && empty($servicename)) {
			$sql .= "AND (host_name IN ('" . join("', '", $hostname) . "') AND service_description = '') ";
		}
		elseif (is_array($servicename)) {
			$sql .= "AND concat(host_name, ';', service_description) IN ('" .
				join("', '", $servicename) . "') ";
		}
		else {
			if (empty($servicename)) $servicename = '';
			$sql .= "AND host_name=".$this->db->quote($hostname)." " .
			"AND service_description=".$this->db->quote($servicename)." ";
		}
		if (!$this->include_soft_states)
			$sql .= 'AND hard = 1 ';
		$sql .= "AND ( " .
			"event_type=" . $event_type . ' ' .
			"OR event_type=" . self::DOWNTIME_START . ' ' .
			"OR event_type=" . self::DOWNTIME_STOP . ' ';
		if (!$this->assume_states_during_not_running)
			$sql .= "OR event_type=".self::PROCESS_SHUTDOWN.
			" OR event_type=".self::PROCESS_START;
		$sql .= ") ORDER BY id";

		return $this->db->query($sql);
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

		$sql = "SELECT id, timestamp, event_type, downtime_depth FROM " .
			$this->db_name . "." . $this->db_table . " " .
			"WHERE timestamp <= " . $this->start_time . " AND " .
			"(event_type = " . self::DOWNTIME_START .
			" OR event_type = " .self::DOWNTIME_STOP . ") " .
			" AND host_name = " . $this->db->quote($hostname);

		if (!empty($service_description)) {
			$sql .= "AND service_description=".$this->db->quote($service_description);
		}
		$sql .= " ORDER BY id DESC LIMIT 1";

		$dbr = $this->db->query($sql);
		if (!$dbr || !$dbr->rowCount())
			return false;

		$row = $dbr->fetch(PDO::FETCH_ASSOC);

		$this->register_db_time($row['timestamp']);
		$this->initial_dt_depth = $row['downtime_depth'];
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
			" WHERE host_name = " . $this->db->quote($host_name) .
			" AND service_description = " . $this->db->quote($service_description) .
			" AND event_type = ";

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
			" ORDER BY id DESC LIMIT 1";

		# first try to fetch the real initial state so
		# we don't have to assume
		$dbr = $this->db->query($sql);
		if ($dbr && $dbr->rowCount()) {
			$row = $dbr->fetch(PDO::FETCH_ASSOC);
			$this->initial_state = $row['state'];
			return $this->initial_state;
		}

		# There is no real initial state, so check if we should
		# assume something. If it's a real state, return early
		if ($assumed_state > 0) {
			$this->initial_state = $assumed_state;
			return $this->initial_state;
		}

		$sql = false;
		$state = $assumed_state;
		# state == -1 is magic for "use current state as initial"
		# it's fairly bonkers to do that, and will yield different
		# results for historical data based on present state, but
		# it's supported in the old cgi's, so we must keep this
		# mouldering wreck of insanity alive...
		if ($state == -1) {
			$dbr = $this->db->query($base_sql . "ORDER BY id DESC LIMIT 1");
		}

		# Using the first real state found in the database as
		# the assumed initial state is a lot less evil than the
		# above black voodoo, as reports for last year will always
		# look the same, no matter what the current state is.
		elseif ($state == -3) {
			$dbr = $this->db->query($base_sql . "ORDER BY id ASC LIMIT 1");
		}

		if (!$dbr || !$dbr->rowCount()) {
			# this is only reached if there is no state at all
			# in the database. It should usually be an error,
			# unless one tries to take a report from, say, last
			# year on a host that was added less than 30 seconds
			# ago. Either way, we're out of options so do nothing.
			# $state will default to STATE_PENDING further down
		} else {
			$row = $dbr->fetch();
			$state = $row['state'];
		}
		/* state assumption logic end */

		if ($state === false || $state < 0 || is_null($state))
			$state = self::STATE_PENDING;

		$this->initial_state = $state;
		return $state;
	}

	public function get_common_downtime_state()
	{
		if (!$this->sub_reports)
			return $this->st_dt_depth;

		foreach ($this->sub_reports as $rpt) {
			if (!$rpt->st_dt_depth)
				return 0;
		}

		return 1;
	}

	public function get_best_host_state()
	{
		if (!empty($this->st_sub[self::HOST_UP]))
			return self::HOST_UP;
		if (!empty($this->st_sub[self::HOST_DOWN]))
			return self::HOST_DOWN;
		if (!empty($this->st_sub[self::HOST_UNREACHABLE]))
			return self::HOST_UNREACHABLE;
		if (!empty($this->st_sub[self::HOST_PENDING]))
			return self::HOST_PENDING;

		# not reached
		return self::HOST_DOWN;
	}

	public function get_best_service_state()
	{
		if (!empty($this->st_sub[self::SERVICE_OK]))
			return self::SERVICE_OK;
		if (!empty($this->st_sub[self::SERVICE_WARNING]))
			return self::SERVICE_WARNING;
		if (!empty($this->st_sub[self::SERVICE_CRITICAL]))
			return self::SERVICE_CRITICAL;
		# Is UNKNOWN 'better' than WARNING or CRITICAL?
		if (!empty($this->st_sub[self::SERVICE_UNKNOWN]))
			return self::SERVICE_UNKNOWN;
		if (!empty($this->st_sub[self::SERVICE_PENDING]))
			return self::SERVICE_PENDING;

		# not reached
		return self::SERVICE_CRITICAL;
	}

	public function get_worst_host_state()
	{
		if (!empty($this->st_sub[self::HOST_DOWN]))
			return self::HOST_DOWN;
		if (!empty($this->st_sub[self::HOST_UNREACHABLE]))
			return self::HOST_UNREACHABLE;
		if (!empty($this->st_sub[self::HOST_PENDING]) && empty($this->st_sub[self::HOST_UP]))
			return self::HOST_PENDING;
		return  self::HOST_UP;
	}

	public function get_worst_service_state()
	{
		if (!empty($this->st_sub[self::SERVICE_CRITICAL]))
			return self::SERVICE_CRITICAL;
		if (!empty($this->st_sub[self::SERVICE_WARNING]))
			return self::SERVICE_WARNING;
		if (!empty($this->st_sub[self::SERVICE_UNKNOWN]))
			return self::SERVICE_UNKNOWN;

		if (!empty($this->st_sub[self::SERVICE_PENDING]) && empty($this->st_sub[self::SERVICE_OK]))
			return self::SERVICE_PENDING;

		return self::SERVICE_OK;
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
	 * @return boolean
	 */
	public function set_timeperiod_variable($name, $value)
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
				$syear, $smon, $smday, 0, 0, $eyear, $emon, $emday, 0, 0, $skip_interval, $timeranges);
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
				$syear, $smon, $smday, 0, 0, $eyear, $emon, $emday, 0, 0, $skip_interval, $timeranges);
			return true;
		}

		$items = array_filter(sscanf($input,"%4d-%2d-%2d - %4d-%2d-%2d %[0-9:, -]"));
		if(count($items) == 7)
		{
			list($syear, $smon, $smday, $eyear, $emon, $emday, $timeranges) = $items;

			/* add timerange exception */
			$this->add_timeperiod_exception(self::DATERANGE_CALENDAR_DATE,
				$syear, $smon, $smday, 0, 0, $eyear, $emon, $emday, 0, 0, 0, $timeranges);
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
				$syear, $smon, $smday, 0, 0, $eyear, $emon, $emday, 0, 0, 0, $timeranges);
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
					0, $smon, 0, $swday, $swday_offset, 0, $emon, 0, $ewday, $ewday_offset, $skip_interval,  $timeranges);
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
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, $skip_interval, $timeranges);
				return true;
			}
			elseif(in_array($str1, $valid_months) && in_array($str2, $valid_months))
			{
				$smon = $month_numbers[$str1];
				$emon = $month_numbers[$str2];
				/* february 1 - march 15 / 3 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0,
					0, $emon, $emday, 0, 0, $skip_interval, $timeranges);
				return true;
			}
			else if(!strcmp($str1,"day")  && !strcmp($str2,"day"))
			{
				/* day 4 - 6 / 2 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, $skip_interval, $timeranges);
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
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, $skip_interval, $timeranges);
				return true;
			}
			else if(in_array($str1, $valid_months))
			{
				$smon = $month_numbers[$str1];
				$emon = $smon;
				/* february 3 - 5 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0,
					0, $emon, $emday, 0, 0, $skip_interval, $timeranges);
				return true;
			}
			else if(!strcmp($str1, "day"))
			{
				/* day 1 - 4 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, $skip_interval, $timeranges);
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
					0, $smon, 0, $swday, $swday_offset, 0, $emon, 0, $ewday, $ewday_offset, 0, $timeranges);
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
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, 0, $timeranges);
				return true;
			}
			else if(in_array($str1, $valid_months))
			{
				/* february 3 - 5 */
				$smon = $month_numbers[$str1];
				$emon = $smon;
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0, 0, $emon, $emday, 0, 0, 0, $timeranges);
				return true;
			}
			else if(!strcmp($str1,"day"))
			{
				/* day 1 - 4 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, 0, $timeranges);
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
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, 0, $timeranges);
				return true;
			}
			elseif(in_array($str1, $valid_months) && in_array($str2, $valid_months))
			{
				/* february 1 - march 15 */
				$smon = $month_numbers[$str1];
				$emon = $month_numbers[$str2];
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0, 0, $emon, $emday, 0, 0, 0, $timeranges);
				return true;
			}
			else if(!strcmp($str1,"day")  && !strcmp($str2,"day"))
			{
				/* day 1 - day 5 */
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, 0, $timeranges);
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
					0, 0, 0, $swday, $swday_offset, 0, 0, 0, $ewday, $ewday_offset, 0, $timeranges);
				return true;
			}
			elseif(in_array($str1, $valid_months))
			{
				/* february 3 */
				$smon = $month_numbers[$str1];
				$emon = $smon;
				$emday = $smday;
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE,
					0, $smon, $smday, 0, 0, 0, $emon, $emday, 0, 0, 0, $timeranges);
				return true;
			}
			else if(!strcmp($str1,"day"))
			{
				/* day 1 */
				$emday = $smday;
				$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY,
					0, 0, $smday, 0, 0, 0, 0, $emday, 0, 0, 0, $timeranges);
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
					0, $smon, 0, $swday, $swday_offset, 0, $emon, 0, $ewday, $ewday_offset, 0, $timeranges);
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
				$range = merge_timeranges($range, $testrange);

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

	public function print_timerange(&$r)
	{
		print "$r[start]-$r[stop]";
	}

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
			"FROM ".$this->db_table;
		$db = new Database($this->db_name);
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
	private function build_alert_summary_query($fields = '*')
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
			$this->host_hostgrop = $hosts;
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

		$object_selection = false;
		if ($hosts) {
			$object_selection = "AND host_name IN('" .
				join("', '", array_keys($hosts)) . "')";
		} elseif ($services) {
			$object_selection = "AND (";
			$orstr = '';
			# Must do this the hard way to allow host_name indices to
			# take effect when running the query, since the construct
			# "concat(host_name, ';', service_description)" isn't
			# indexable
			foreach ($services as $srv => $discard) {
				$ary = explode(';', $srv);
				$h = $ary[0];
				$s = $ary[1];
				$object_selection .= $orstr . "(host_name = '" . $h . "' " .
					"AND service_description = '" . $s . "')";
				$orstr = " OR ";
			}
			$object_selection .= ')';
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

		switch ($this->alert_types) {
		 case 1: $query .= 'AND ' . $host_states_sql; break;
		 case 2: $query .= 'AND ' . $service_states_sql; break;
		 case 3:
			$query .= 'AND (' . $host_states_sql .
				'OR ' . $service_states_sql . ') '; break;
		}

		switch ($this->alert_types) {
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

	public function test_summary_query($query = false)
	{
		if (!$query) {
			$query = $this->build_alert_summary_query();
		}
		$dbr = $this->db->query("EXPLAIN " . $query);
		return $dbr->fetch(PDO::FETCH_ASSOC);
	}

	public function test_summary_queries()
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
						$query = $this->build_alert_summary_query();
						$result[$query] = $this->test_summary_query($query);
					}
				}
			}
		}
		return $result;
	}


	/**
	 * Get alert summary for "top (hard) alert producers"
	 *
	 * @return Array in the form { rank => array() }
	 */
	public function top_alert_producers()
	{
		$start = microtime(true);
		$query = $this->build_alert_summary_query('host_name, service_description');

		$dbr = $this->db->query($query);
		if (!is_object($dbr)) {
			echo Kohana::debug($db->errorinfo(), $query);
			die;
		}
		$result = array();
		while ($row = $dbr->fetch(PDO::FETCH_ASSOC)) {
			if (empty($row['service_description'])) {
				$name = $row['host_name'];
			} else {
				$name = $row['host_name'] . ';' . $row['service_description'];
			}

			if (empty($this->summary_result[$name])) {
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

	/**
	 * Get alert totals. This is identical to the toplist in
	 * many respects, but the result array is different.
	 *
	 * @return Array of counts divided by object types and states
	 */
	public function alert_totals()
	{
		$this->completion_time = microtime(true);
		$query = $this->build_alert_summary_query('host_name, service_description, state, hard');

		$dbr = $this->db->query($query);
		if (!is_object($dbr)) {
			echo Kohana::debug($db->errorinfo(), $query);
			die;
		}

		# preparing the result array in advance speeds up the
		# parsing somewhat. Completing it either way makes it
		# easier to write templates for it as well
		for ($state = 0; $state < 4; $state++) {
			$this->summary_result['host'][$state] = array(0, 0);
			$this->summary_result['service'][$state] = array(0, 0);
		}
		unset($this->summary_result['host'][3]);
		while ($row = $dbr->fetch()) {
			if (empty($row['service_description'])) {
				$type = 'host';
			} else {
				$type = 'service';
			}
			$this->summary_result[$type][$row['state']][$row['hard']]++;
		}

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
		$query = $this->build_alert_summary_query();
		$query .= " ORDER BY timestamp DESC";
		if ($this->summary_items > 0) {
			$query .= " LIMIT " . $this->summary_items;
		}
		$this->summary_query = $query;

		$dbr = $this->db->query($query);
		if (!is_object($dbr)) {
			echo Kohana::debug($db->errorinfo(), $query);
			die;
		}

		$this->summary_result = array();
		while ($row = $dbr->fetch(PDO::FETCH_ASSOC)) {
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
		$db = pdodb::instance('mysql', 'monitor_reports');
		$return_str = '';
		$start = $db_start_time;
		$stop = $db_end_time;
		$query = "SELECT * FROM ".$table." " .
			"WHERE timestamp >= ".$db_start_time." AND timestamp <= ".$db_end_time;
		if (!empty($test['service_description'])) {
			$ignore_event = 801;
			$objects = $test['service_description'];
			$otype = 'concat(host_name, ";", service_description)';
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
		$res = $db->query($query);

		if (!$res) {
			return;
		}

		$return_str .= "\tsql {\n";
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			unset($row['id']);
			$return_str .= "\t\tINSERT INTO ".$table."(" . join(',', array_keys($row)) . ')';
			$return_str .=" VALUES(";
			$first = true;
			foreach ($row as $v) {
				if (!$first)
					$return_str .= ",";
				else
					$first = false;
				$return_str .= $db->quote($v);
			}
			$return_str .= ");\n";
		}
		$return_str .= "\t}\n";

		return $return_str;
	}

}
