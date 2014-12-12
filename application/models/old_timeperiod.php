<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * A model used by reports to lookup when report times and timeperiods start
 * and stop. This sounds real easy, until you start to look at nagios'
 * less trivial timeperiod definitions.
 *
 * You should call the instance() method to get a timeperiod instance,
 * which might be shared with other users. This class will call instance()
 * itself, so exceptions to exceptions work.
 */
class Old_Timeperiod_Model extends Model
{
	/** Please do not touch this outside of tests, it's a cache for performance purposes */
	static public $precreated = array();
	/** Definition of the regular (weekday) timeperiod definitions */
	protected $period = false;
	/** Report start */
	public $start_time = false;
	/** Report stop */
	public $end_time = false;

	public $tp_exceptions = array(); /**< Timeperiod exceptions */
	public $tp_excludes = array(); /**< Timeperiod excludes */

	const DATERANGE_CALENDAR_DATE = 0; /**< eg: 2001-01-01 - 2010-11-21 / 3 (specific complete calendar date) */
	const DATERANGE_MONTH_DATE = 1;  	/**< eg: july 4 - november 15 / 3 (specific month) */
	const DATERANGE_MONTH_DAY = 2;  	/**< eg: day 1 - 25 / 5  (generic month)  */
	const DATERANGE_MONTH_WEEK_DAY = 3; /**< eg: thursday 1 april - tuesday 2 may / 2 (specific month) */
	const DATERANGE_WEEK_DAY = 4;  		/**< eg: thursday 3 - monday 4 (generic month) */
	const DATERANGE_TYPES = 5; /**< FIXME: incomprehensible magic */

	/**
	 * Return an instance from a Report_options object
	 * @param $options Report_options class, or possibly an array mock
	 */
	public static function instance($options) {
		$key = $options['rpttimeperiod'].$options['start_time'].$options['end_time'];
		if (isset(self::$precreated[$key]))
			return self::$precreated[$key];
		$obj = new self($options);
		self::$precreated[$key] = $obj;
		return $obj;
	}

	/**
	 * Warning: you should not use this directly - see instance()
	 */
	public function __construct($options) {
		$this->start_time = $options['start_time'];
		$this->end_time = $options['end_time'];
		if ($this->end_time === NULL)
			$this->end_time = time();
		if (!$options['rpttimeperiod'])
			return;
		$result = self::get($options['rpttimeperiod'], true);
		if (empty($result))
			return;
		$this->set_timeperiod_data($result);
	}

	/**
	 * Setup the data to base the timeperiod on
	 *
	 * You really shouldn't be using this, unless you're a test and want to mock a timeperiod
	 *
	 * @param $period A timeperiod db result
	 * @return true on success, false otherwise
	 */
	public function set_timeperiod_data($period=NULL)
	{
		$valid_weekdays = reports::$valid_weekdays;

		if (!$period) {
			$this->period = false;
			return true;
		}

		$this->period = array();

		unset($period['id']);
		unset($period['timeperiod_name']);
		unset($period['alias']);
		unset($period['instance_id']);

		$includes = $period;

		$errors = 0;
		foreach ($includes as $k => $v) {
			if (empty($v)) {
				continue;
			}
			$errors += $this->set_timeperiod_variable($k, $v) === false;
		}

		if(!empty($period['excludes']))
		{
			foreach($period['excludes'] as $exclude)
			{
				$this->tp_excludes[] = Old_Timeperiod_Model::instance(array('start_time' => $this->start_time, 'end_time' => $this->end_time, 'rpttimeperiod' => $exclude));
			}
		}

		if ($errors)
			return false;
		return true;
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
	public function active_time($start, $stop) {
		# if no timeperiod is set, the entire duration is active
		if ($this->period === false)
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
		if ($this->period === false)
			return $when;

		# if there is a report timeperiod set that doesn't have
		# any 'start' entry (ie, all days are empty, such as for
		# the "none" timeperiod), we can't possibly find either
		# start or stop, so we can break out early.
		# No one sane will want to take a report from such a timeperiod,
		# but in case the user misclicks, we should behave properly.
		if (empty($this->period) && empty($this->tp_exceptions)){
			return 0;
		}

		if ($what === 'start') {
			# try to find the next valid timestamp in this timeperiod,
			# that is not valid in any of the exceptions.
			# if we make it through a whole loop without $when changing, we
			# must have found next tp start.
			$main_when = false;
			while ($main_when !== $when && $when <= $this->end_time) {
				$main_when = $when = $this->tp_flat_next($when, 'start');
				foreach ($this->tp_excludes as $exclude) {
					$tmp_when = $exclude->tp_next($when, 'stop');
					if ($tmp_when !== 0) # 0 => no more tp entries => ignore
						$when = $tmp_when;
				}
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

			foreach ($this->tp_excludes as $exclude) {
				$whens[] = $exclude->tp_next($when, 'start');
			}
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
		elseif (!empty($this->period[$day]))
			$ents = $this->period[$day];
		# we have no entries today, so if we're looking for something outside
		# a timeperiod, everything is.
		elseif ($what === 'stop')
			return $when;

		if ($what === 'start') {
			foreach ($ents as $ent) {
				if ($ent['start'] <= $day_seconds && $ent['stop'] > $day_seconds)
					return $when;

				if ($ent['start'] > $day_seconds)
					return $midnight_to_when + $ent['start'];
			}
		} else {
			foreach ($ents as $ent) {
				if ($ent['start'] > $day_seconds)
					return $when;
				if ($ent['start'] <= $day_seconds && $ent['stop'] > $day_seconds)
					return $midnight_to_when + $ent['stop'];
			}
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
			elseif (!empty($this->period[$day]))
				$ents = $this->period[$day];

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
	public function inside($timestamp) {
		return ($this->tp_next($timestamp, 'start') === $timestamp);
	}

	/**
	 * Resolve timeperiods, both the actual timeperiods and the exclusions
	 */
	public function resolve_timeperiods()
	{
		if ($this->start_time === false || $this->end_time === false) {
			throw new Exception("Timeperiods cannot be resolved unless report start and end time is set");
		}

		if ($this->end_time < $this->start_time) {
			throw new Exception("Report time set to end before start");
		}

		$start_year = date('Y', $this->start_time);
		$end_year = date('Y', $this->end_time);

		if (!isset($this->tp_exceptions['unresolved']))
			return true;

		for($day_time = $this->start_time ; $day_time <= $this->end_time ; $day_time += 86400)
		{
			$check_exception = true;

			$day_shift = strtotime(date("Y-m-d", $day_time));
			$day = date('z', $day_time);
			$day_year  = date('Y', $day_time);
			$day_month = date('n', $day_time);

			for($i=0,$n=count($this->tp_exceptions['unresolved']) ; $i<$n ; $i++)
			{
				$x =& $this->tp_exceptions['unresolved'][$i];

				if($x['syear'] > date('Y', $this->end_time))
					continue;


				# find out if there is an exception during this day
				switch($x['type'])
				{
				 case self::DATERANGE_CALENDAR_DATE:/* eg: 2008-12-25 */
					# set fields: syear, smon, smday, eyear, emon, emday, skip_interval

					$exp_start = mktime(0,0,0, $x['smon'], $x['smday'], $x['syear']);

					# unspecified end date - two possibilities
					if(self::is_daterange_single_day($x))
						$exp_end = $exp_start;
					else
						$exp_end = mktime(0,0,0, $x['emon'], $x['emday'], $x['eyear']);

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

				# day shift is "midnight, today", $exp_start and $exp_end are always whole days
				if (!$check_exception || $exp_start < $day_shift || $exp_end > $day_shift)
					continue;

				if(!isset($this->tp_exceptions[$day_year]))
					$this->tp_exceptions[$day_year] = array();

				if(!isset($this->tp_exceptions[$day_year][$day]))
					$this->tp_exceptions[$day_year][$day] = array();

				# if so, merge timeranges with existing for this day
				$this->tp_exceptions[$day_year][$day] = self::merge_timerange_sets($this->tp_exceptions[$day_year][$day], $x['timeranges']);
			}
		}
		unset($this->tp_exceptions['unresolved']);
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
	 * 		array('start' => 28800, 'stop' => 43200),
	 * 		array('start' => 46800, 'stop' => 61200)
	 * );
	 */
	protected function tp_parse_day($str)
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
	protected function add_timeperiod_exception($dateperiod_type,
	                                  $syear, $smon, $smday, $swday, $swday_offset,
	                                  $eyear, $emon, $emday, $ewday, $ewday_offset,
	                                  $skip_interval, $timeranges)
	{
		$days_per_month = reports::$days_per_month;

		if (!isset($this->tp_exceptions['unresolved']))
			$this->tp_exceptions['unresolved'] = array();

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
			$this->period[array_search($name, $valid_weekdays)] = $this->tp_parse_day($value);
			return true;
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
	 * Fetch info on a timeperiod
	 *
	 * @param $period string: Timeperiod name
	 * @return an array of the timeperiod's properties
	 */
	public static function get($period)
	{
		$db = Database::instance();
		$query = 'SELECT * FROM timeperiod ' .
			'WHERE timeperiod_name = ' .$db->escape($period);
		$res = $db->query($query);
		if (!$res) {
			return false;
		}

		$res = $res->result(false);
		$res = $res->current();

		if ($res) {
			$query = "SELECT variable, value FROM custom_vars WHERE obj_type = 'timeperiod' AND obj_id = {$res['id']}";
			$exception_res = $db->query($query);
			foreach ($exception_res as $exception) {
				$res[$exception->variable] = $exception->value;
			}

			$query = "SELECT tp.timeperiod_name FROM timeperiod tp".
					 " JOIN timeperiod_exclude ON exclude = id ".
					 " WHERE timeperiod = {$res['id']}";

			$exclude_res = $db->query($query);
			// it might seem appropriate to be all recursive about this,
			// but the recursiveness is already done on a model-level, so
			// we have no use for anything but name
			foreach ($exclude_res as $exclude) {
				$res['excludes'][] = $exclude->timeperiod_name;
			}
		}
		return $res;
	}

	private static $timeperiods_all = false;
	
	/**
	 * Fetch all timperiods
	 * @return db result
	 */
	public static function get_all()
	{
		if( self::$timeperiods_all !== false )
			return self::$timeperiods_all;
		$result = array();
		$res = Livestatus::instance()->getTimeperiods(array('columns'=>array('name')));
		foreach ($res as $row) {
			$result[$row['name']] = $row['name'];
		}
		self::$timeperiods_all = $result;
		return $result;
	}
}
