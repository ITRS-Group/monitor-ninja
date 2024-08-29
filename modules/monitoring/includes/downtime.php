<?php

/**
 * Class NoRecurrenceException
 */
class NoRecurrenceException extends Exception {
	/**
	 * NoRecurrenceException constructor.
	 *
	 * @param $message string
	 * @param $code int
	 * @param $previous Throwable|null
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

/**
 * Class Downtime
 */
class Downtime {
	/**
	 * object RecurringDowntimePool_Model
	 */
	public $model;

	/**
	 * NinjaDateTime schedule start
	 */
	public $start;

	/**
	 * NinjaDateTime schedule end
	 */
	public $end;

	/**
	 * array
	 */
	public $cmd_mappings = array(
		'hosts' => 'SCHEDULE_HOST_DOWNTIME',
		'services' => 'SCHEDULE_SVC_DOWNTIME',
		'hostgroups' => 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME',
		'servicegroups' => 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME'
	);

	/**
	 * Provides helpers for working with scheduled downtime.
	 *
	 * Downtime constructor.
	 * @param $model object (RecurringDowntimePool_Model)
	 * @throws Exception
	 */
	function __construct($model) {
		$this->model = $model;
		$this->start = $this->get_scheduled_start();
		$this->end = $this->get_scheduled_end();
	}

	/**
	 * Returns the ordinal (or occurrence) for the given number.
	 *
	 * @param $number int|mixed
	 * @return string monthly occurrence (e.g. third)
	 * @throws Exception UnexpectedValueException
	 */
	public function get_month_occurrence($number) {
		$occurrences = array(
			1 => 'first',
			2 => 'second',
			3 => 'third',
			4 => 'fourth',
			5 => 'last',
		);

		if(!array_key_exists($number, $occurrences)) {
			throw new UnexpectedValueException(
				sprintf('Missing occurrence mapping for number %s', $number)
			);
		}
		return $occurrences[$number];
	}

	/**
	 * Create downtime window using the given $target_date, with the scheduled start and end time.
	 *
	 * @param $target_date NinjaDateTime
	 * @return array
	 */
	public function get_window($target_date) {
		// Get schedule start-end delta
		$downtime_seconds = $this->end->getTimestamp() - $this->start->getTimestamp();

		// Target date is used as base and will have its time modified
		$start = clone $target_date;

		// Set time from scheduled start
		$start->setTime(
			$this->start->format('H'),
			$this->start->format('i'),
			$this->start->format('s')
		);

		// Clone the start DateTime object and add delta seconds.
		$end = clone $start;
		$end->modify(sprintf('+%d seconds', $downtime_seconds));

		return array(
			'start' => $start,
			'end' => $end
		);
	}

	/**
	 * Returns a formatted downtime command.
	 *
	 * @param $obj_name string
	 * @param $downtime_window array
	 * @param $comment_prefix string comment prefix
	 * @return array command map
	 */
	public function get_command_mappings($obj_name, $downtime_window, $comment_prefix = '') {
		$downtime_type = $this->model->get_downtime_type();
		if(!array_key_exists($downtime_type, $this->cmd_mappings)) {
			throw new UnexpectedValueException("Missing mapping for downtime type: $downtime_type");
		}

		return array(
			'cmd' => $this->cmd_mappings[$downtime_type],
			'obj_name' => $obj_name,
			'start' => $downtime_window['start']->getTimestamp(),
			'end' => $downtime_window['end']->getTimestamp(),
			'is_fixed' => $this->model->get_fixed(),
			'trigger_id' => 0,
			'duration' => $this->model->get_duration(),
			'author' => $this->model->get_author(),
			'comment' => $comment_prefix . $this->model->get_comment()
		);
	}

	/**
	 * Converts command mappings to a Nagios-interpretable string format.
	 *
	 * @param $obj_name string
	 * @param $downtime_window array
	 * @param $comment_prefix string comment prefix
	 * @return string Nagios external command
	 */
	public function get_command($obj_name, $downtime_window, $comment_prefix = 'AUTO: ') {
		$command = $this->get_command_mappings($obj_name, $downtime_window, $comment_prefix);
		return implode(';', array_values($command));
	}

	/**
	 * Converts day of week number to string, starting with sunday.
	 *
	 * @param $day_number int
	 * @return string day of week name
	 * @throws Exception UnexpectedValueException
	 */
	public function dow_to_string($day_number) {
		$week = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

		if(!array_key_exists($day_number, $week)) {
			throw new UnexpectedValueException(
				sprintf('Missing day of week idx %s', $day_number)
			);
		}
		return $week[$day_number];
	}

	/**
	 * Creates a NinjaDateTime object from the given date-time string
	 *
	 * @param $date_str string as Y-m-d
	 * @param $time_str string as H:i:s
	 * @return NinjaDateTime
	 * @throws Exception UnexpectedValueException
	 */
	private function dt_from_str($date_str, $time_str) {
		$dt_str = sprintf('%s %s', $date_str, $time_str);
		return new NinjaDateTime($dt_str);
	}

	/**
	 * Returns NinjaDateTime given start date and time strings
	 *
	 * @return NinjaDateTime
	 * @throws Exception UnexpectedValueException
	 */
	public function get_scheduled_start() {
		$date_str = $this->model->get_start_date();
		$time_str = $this->model->get_start_time_string();
		return $this->dt_from_str($date_str, $time_str);
	}

	/**
	 * Returns NinjaDateTime given end date and time strings
	 *
	 * @return NinjaDateTime
	 * @throws Exception UnexpectedValueException
	 */
	public function get_scheduled_end() {
		$date_str = $this->model->get_end_date();
		$time_str = $this->model->get_end_time_string();
		return $this->dt_from_str($date_str, $time_str);
	}
}

/**
 * RecurringDowntime class
 *
 * Used with recurring schedules, inherits from Downtime.
 */
class RecurringDowntime extends Downtime {
	/**
	 * object recurrence object
	 */
	public $recurrence;

	/**
	 * array nested recurrence map
	 */
	public $recurrence_on;

	/**
	 * NinjaDateTime
	 */
	public $recurrence_ends;

	/**
	 * array
	 */
	public $exclude_days;

	/**
	 * Provides a helpers for working with recurring downtime.
	 *
	 * @param $model object
	 * @throws Exception UnexpectedValueException
	 * @throws NoRecurrenceException
	 */
	function __construct($model) {
		parent::__construct($model);

		$this->recurrence = $this->get_recurrence();
		$this->recurrence_on = $this->get_recurrence_on();
		$this->recurrence_ends = $this->get_recurrence_ends();
		$this->exclude_days = explode(',', $model->get_exclude_days());
	}

	/**
	 * Returns recurrence for schedule.
	 *
	 * @return object
	 * @throws Exception UnexpectedValueException
	 * @throws NoRecurrenceException
	 */
	private function get_recurrence() {
		$recurrence = json_decode($this->model->get_recurrence());
		if(!$recurrence) {
			throw new NoRecurrenceException('RecurringSchedule.schedule must have a recurrence');
		}

		return $recurrence;
	}

	/**
	 * Returns recurrence for schedule.
	 *
	 * @return array decoded recurrence_on
	 */
	private function get_recurrence_on() {
		return json_decode($this->model->get_recurrence_on(), true);
	}

	/**
	 * Returns recurrence end date as a NinjaDateTime object.
	 *
	 * @return NinjaDateTime
	 * @throws Exception
	 */
	private function get_recurrence_ends() {
		$ends = $this->model->get_recurrence_ends();
		if(!$ends) {
			// Return never-ending recurrences as a NinjaDateTime object as well, for consistency.
			return new NinjaDateTime('2199-12-31');
		}
		return new NinjaDateTime($this->model->get_recurrence_ends());
	}

	/**
	 * Checks if the given $target_date is excluded.
	 *
	 * The input, $target_date, is a NinjaDateTime object or--to simplify testing--a
	 * parsable date string, that is compared against an array of ranges or single
	 * dates contained in `$this->exclude_days`:
	 * ['2019-03-13 to 2019-03-16', '2019-03-13 to 2019-03-18', ...]
	 *
	 * @param $target_date mixed NinjaDateTime|str
	 * @return bool
	 * @throws Exception UnexpectedValueException Invalid date-exclusion format
	 */
	public function is_excluded($target_date) {
		if(gettype($target_date) === 'string') {
			$target_date = new NinjaDateTime($target_date);
		}

		foreach($this->exclude_days as $item) {
			$parsed = explode('to', $item);
			if(sizeof($parsed) === 1) {
				// Single date, set to and from to idx 0.
				$from = $to = trim($parsed[0]);
			} elseif(sizeof($parsed) === 2) {
				// Date range, trim and set to and from.
				$from = trim($parsed[0]);
				$to = trim($parsed[1]);
			} else {
				throw new UnexpectedValueException('Invalid date-exclusion format');
			}

			$start = new DateTime($from);
			$end = new DateTime($to);

			// Check if the provided $target_date is within the to-from range.
			if($target_date >= $start && $target_date <= $end) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Returns true if the given date matches the relative occurrence of the schedule
	 *
	 * if [target_date] is on [occurrence] [weekday] : return true
	 *
	 * @param $target_date NinjaDateTime
	 * @return boolean
	 * @throws Exception UnexpectedValueException
	 */
	public function match_day_of_month($target_date) {
		$occurrence_in_month = $this->recurrence_on['day_no'];

		// Handle inconsistent `day_no`
		if($occurrence_in_month === 'last') {
			$occurrence = 'last';
		} elseif(!is_int($occurrence_in_month)) {
			throw new UnexpectedValueException(
				"Unknown occurrence in month: $occurrence_in_month"
			);
		} else {
			// Get ordinal from number (e.g. 1 => first)
			$occurrence = $this->get_month_occurrence($this->recurrence_on['day_no']);
		}

		// Get day-of-week (e.g. 1 => monday) name from number
		$dow = $this->dow_to_string($this->recurrence_on['day']);

		// Is this recurrence for a specific month?
		if(array_key_exists('month', $this->recurrence_on) && $target_date->get_month() !== $this->recurrence_on['month']) {
			return false;
		}

		// Create relative dt; e.g. "last wednesday of september 1984"
		$relative_fmt = sprintf(
			'%s %s of %s %s',
			$occurrence, $dow, $target_date->get_month_string(), $target_date->get_year()
		);
		$dt = new NinjaDateTime($relative_fmt);

		// Return true if day numbers matches
		return $dt->get_day_of_month() === $target_date->get_day_of_month();
	}

	/**
	 * Plucks values by key from a map.
	 *
	 * input:
	 * 'day'
	 *
	 * recurrence_on:
	 * ['day' => 1, 'day' => 2, 'day' => 3]
	 *
	 * output:
	 * [1, 2, 3]
	 *
	 * @param $key string The key to pluck
	 * @return array plucked items
	 */
	public function pluck_recurrence($key) {
		$plucked = array();
		$on = $this->recurrence_on;

		// Check if `recurrence_on` is an associative array (legacy format).
		if(array_keys($on) !== range(0, count($on) - 1)) {
			// Legacy format: check if the given key exists
			if(array_key_exists($key, $on)) {
				array_push($plucked, $this->recurrence_on[$key]);
			}
		} else {
			// With the new format, objects are always contained in an array - iterate.
			foreach ($on as $recurrence) {
				if (array_key_exists($key, $recurrence)) {
					array_push($plucked, $recurrence[$key]);
				}
			}
		}
		return $plucked;
	}

	/**
	 * Calculates the difference in years between the given target date and the object's start date.
	 *
	 * @param $target_date NinjaDateTime
	 * @return int
	 * @throws Exception UnexpectedValueException
	 */
	private function get_years_delta($target_date) {
		return $target_date->get_year() - $this->start->get_year();
	}

	/**
	 * Calculates the difference in days between the given date and the object's start date.
	 *
	 * Performs a full-day diff using the PHP built-in DateTime.diff method.
	 *
	 * @param $target_date NinjaDateTime
	 * @return int days delta
	 * @throws Exception
	 */
	private function get_days_delta($target_date) {
		$from = $this->start->get_day_start();
		$to = $target_date->get_day_start();
		return($to->diff($from)->days);
	}

	/**
	 * Checks if the provided $date's year matches the schedule's stepping.
	 *
	 * @param $target_date NinjaDateTime
	 * @return bool
	 * @throws Exception UnexpectedValueException
	 */
	public function match_year_interval($target_date) {
		$delta = $this->get_years_delta($target_date);
		return $this->match_interval($delta);
	}

	/**
	 * Checks if the provided $date's month matches the schedule's stepping.
	 *
	 * @param $target_date NinjaDateTime
	 * @return bool
	 * @throws Exception UnexpectedValueException
	 */
	public function match_month_interval($target_date) {
		$month_start = $this->start->get_month();
		$month_end = $target_date->get_month();

		// Get diff in years
		$year_delta = $this->get_years_delta($target_date);

		// Get diff in months, with support for months over years
		$full_month_delta = $year_delta * 12 + ($month_end - $month_start);

		return $this->match_interval($full_month_delta);
	}

	/**
	 * Checks if the provided $date's week matches the schedule's stepping.
	 *
	 *
	 * @param $target_date NinjaDateTime
	 * @return bool
	 * @throws Exception UnexpectedValueException
	 */
	public function match_week_interval($target_date) {
		$diff = $this->get_days_delta($target_date);
		$week_diff = (int)(round($diff / 7));
		$w_int = $this->match_interval($week_diff);
		var_dump($target_date);
		var_dump($week_diff);
		return $this->match_interval($week_diff);
	}

	/**
	 * Checks if the provided $date's day matches the schedule's stepping.
	 *
	 * @param $target_date NinjaDateTime
	 * @return bool
	 * @throws Exception UnexpectedValueException
	 */
	public function match_day_interval($target_date) {
		$diff = $this->get_days_delta($target_date);
		return $this->match_interval($diff);
	}

	/**
	 * Returns true if $amount coincides with the schedule's stepping.
	 *
	 * This is done by ensuring the remainder after division of:
	 * [units of time between schedule-start and given date] by [schedule interval]
	 * equals 0.
	 *
	 * @param $amount int amount of time
	 * @return bool
	 * @throws Exception UnexpectedValueException
	 */
	private function match_interval($amount) {
		return $amount % $this->recurrence->no === 0;
	}
}
