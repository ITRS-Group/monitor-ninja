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
	private $period = false;
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
		if (!$options['rpttimeperiod'])
			return;
		$result = TimePeriodPool_Model::fetch_by_key($options['rpttimeperiod']);
		if (empty($result))
			return;
		$this->set_timeperiod_data($result);
	}

	/**
	 * Export internal state, only to be able to write tests so we can isolate and verify the timeperiod import
	 */
	public function test_export() {
		return array('period' => $this->period, 'exceptions' => $this->tp_exceptions, 'excludes' => $this->tp_excludes);
	}

	/**
	 * Setup the data to base the timeperiod on
	 *
	 * You really shouldn't be using this, unless you're a test and want to mock a timeperiod
	 *
	 * @param $period A timeperiod db result
	 * @return true on success, false otherwise
	 */
	public function set_timeperiod_data(TimePeriod_Model $period=NULL)
	{
		if (!$period) {
			$this->period = false;
			return true;
		}

		/* Unpack timeranges from start,stop,start,stop to start-stop tuples */
		$this->period = array();
		foreach($period->get_days() as $i => $day) {
			if(empty($day))
				continue;
			$this->period[$i] = array_map(function ($row) {
				return array(
					'start' => $row[0],
					'stop' => $row[1]
				);
			}, array_chunk($day, 2));
		}
		foreach ($period->get_exceptions_calendar_dates() as $ex) {
			$this->add_timeperiod_exception(self::DATERANGE_CALENDAR_DATE,
				$ex['syear'], $ex['smon'] + 1, $ex['smday'], 0, 0, $ex['eyear'],
				$ex['emon'] + 1, $ex['emday'], 0, 0, $ex['skip_interval'],
				$ex['times']);
		}

		foreach ($period->get_exceptions_month_date() as $ex) {
			$this->add_timeperiod_exception(self::DATERANGE_MONTH_DATE, 0,
				$ex['smon'] + 1, $ex['smday'], 0, 0, 0, $ex['emon'] + 1,
				$ex['emday'], 0, 0, $ex['skip_interval'], $ex['times']);
		}

		foreach ($period->get_exceptions_month_day() as $ex) {
			$this->add_timeperiod_exception(self::DATERANGE_MONTH_DAY, 0, 0,
				$ex['smday'], 0, 0, 0, 0, $ex['emday'], 0, 0,
				$ex['skip_interval'], $ex['times']);
		}

		foreach ($period->get_exceptions_month_week_day() as $ex) {
			$this->add_timeperiod_exception(self::DATERANGE_MONTH_WEEK_DAY, 0,
				$ex['smon'] + 1, 0, $ex['swday'], $ex['swday_offset'], 0,
				$ex['emon'] + 1, 0, $ex['ewday'], $ex['ewday_offset'],
				$ex['skip_interval'], $ex['times']);
		}

		foreach ($period->get_exceptions_week_day() as $ex) {
			$this->add_timeperiod_exception(self::DATERANGE_WEEK_DAY, 0, 0, 0,
				$ex['swday'], $ex['swday_offset'], 0, 0, 0, $ex['ewday'],
				$ex['ewday_offset'], $ex['skip_interval'], $ex['times']);
		}

		foreach ($period->get_exclusions() as $exclude) {
			$this->tp_excludes[] = Old_Timeperiod_Model::instance(array(
				'start_time' => $this->start_time,
				'end_time' => $this->end_time,
				'rpttimeperiod' => $exclude
			));
		}

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
	private function tp_next($when, $what = 'start')
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
	private function add_timeperiod_exception($dateperiod_type,
	                                  $syear, $smon, $smday, $swday, $swday_offset,
	                                  $eyear, $emon, $emday, $ewday, $ewday_offset,
	                                  $skip_interval, $timeranges)
	{
		$days_per_month = reports::$days_per_month;

		if (!isset($this->tp_exceptions['unresolved']))
			$this->tp_exceptions['unresolved'] = array();

		assert($dateperiod_type >= 0 && $dateperiod_type < self::DATERANGE_TYPES); // can only fail if programmer messed up

		/* Unpack timeranges from start,stop,start,stop to start-stop tuples */
		$timeranges = array_map(function ($row) {
			return array(
				'start' => $row[0],
				'stop' => $row[1]
			);
		}, array_chunk($timeranges, 2));

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
	 * Return true if both the start date and end date is the same day
	 *
	 * @param $dr A daterange
	 * @return true if condition holds
	 */
	private function is_daterange_single_day(&$dr)
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
	 * Converts a date into timestamp, with some extra features such as
	 * negative days of month to use days from the end of the month.
	 * As for time, 00:00:00 of the day is used.
	 *
	 * @param $year Year.
	 * @param $month Month.
	 * @param $monthday - Day of month, can be negative.
	 * @return The resulting timestamp.
	 */
	private function calculate_time_from_day_of_month($year, $month, $monthday)
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
	private function calculate_time_from_weekday_of_month($year, $month, $weekday, $weekday_offset)
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
	private function timeranges_overlap(&$range1, &$range2, $inclusive=false)
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
	private function merge_timeranges(&$src_range, &$dst_range)
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
	private function add_timerange_to_set($range, &$timerange_set)
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
	private function merge_timerange_sets(&$timerange_set1, &$timerange_set2)
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
