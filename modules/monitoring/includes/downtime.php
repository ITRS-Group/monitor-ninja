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
	 * RecurringDowntimePool_Model
	 *
	 * @var object
	 */
	public $model;

	/**
	 * Schedule start
	 *
	 * @var NinjaDateTime
	 */
	public $start;

	/**
	 * Schedule end
	 *
	 * @var NinjaDateTime
	 */
	public $end;

	/**
	 * Mappings to determine which command to send to Nagios.
	 *
	 * @var array
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
	 * Returns number's ordinal
	 *
	 * @param $number int|mixed
	 * @return string number's ordinal
	 * @throws Exception UnexpectedValueException
	 */
	public function get_ordinal($number) {
		$number -= 1; // number 1 becomes idx 0 of $ordinals.
		$ordinals = array('first', 'second', 'third', 'fourth');
		if(!array_key_exists($number, $ordinals)) {
			throw new UnexpectedValueException(
				sprintf('Missing number ordinal map for idx %s', $number)
			);
		}
		return $ordinals[$number];
	}

	/**
	 * Returns a formatted downtime command.
	 *
	 * This method mainly exists to simplify testing.
	 *
	 * @param $obj_name string
	 * @param $comment_prefix string comment prefix
	 * @return array command map
	 */
	public function get_command_mappings($obj_name, $comment_prefix = '') {
		$downtime_type = $this->model->get_downtime_type();
		if(!array_key_exists($downtime_type, $this->cmd_mappings)) {
			throw new UnexpectedValueException("Missing mapping for downtime type: $downtime_type");
		}

		return array(
			'cmd' => $this->cmd_mappings[$downtime_type],
			'obj_name' => $obj_name,
			'start' => $this->start->getTimestamp(),
			'end' => $this->end->getTimestamp(),
			'is_fixed' => $this->model->get_fixed(),
			'duration' => $this->model->get_duration(),
			'author' => $this->model->get_author(),
			'comment' => $comment_prefix . $this->model->get_comment()
		);
	}

	/**
	 * Converts command mappings to a Nagios-interpretable string format.
	 *
	 * @param $obj_name string
	 * @param $comment_prefix string comment prefix
	 * @return string Nagios external command
	 */
	public function get_command($obj_name, $comment_prefix = 'AUTO: ') {
		$command = $this->get_command_mappings($obj_name, $comment_prefix);
		$cmd_fmt = 'cmd;obj_name;start;end;is_fixed;0;duration;author;comment';
		return str_replace(array_keys($command), array_values($command), $cmd_fmt);
	}

	/**
	 * Converts day of week number to string, starting with monday.
	 *
	 * @param $number int
	 * @return string day of week name
	 * @throws Exception UnexpectedValueException
	 */
	public function dow_to_string($number) {
		$number -= 1; // day 1 becomes idx 0 of $days.
		$days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');

		if(!array_key_exists($number, $days)) {
			throw new UnexpectedValueException(
				sprintf('Missing day of week map for idx %s', $number)
			);
		}
		return $days[$number];
	}

	/**
	 * Creates a DateTime object using the given date and time
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
	 * Returns MonDateTime given start date and time strings
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
	 * Returns MonDateTime given end date and time strings
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
	 * Downtime recurrence object
	 *
	 * @var object
	 */
	public $recurrence;

	/**
	 * Nested recurrence map
	 *
	 * @var array
	 */
	public $recurrence_on;

	/**
	 * Recurrence ends
	 *
	 * @var NinjaDateTime
	 */
	public $recurrence_ends;

	/**
	 * (string)ranges or (string)single dates to exclude (parsed in `is_excluded`).
	 *
	 * @var array
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
			$occurrence = $this->get_ordinal($this->recurrence_on['day_no']);
		}

		// Get day-of-week (e.g. 1 => monday) name from number
		$dow = $this->dow_to_string($this->recurrence_on['day']);

		// Is this recurrence for a specific month?
		if(in_array('month', $this->recurrence_on) && $target_date->get_month() !== $this->recurrence_on['month']) {
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
		foreach($this->recurrence_on as $item) {
			if(!array_key_exists($key, $item)) {
				continue;
			}
			array_push($plucked, $item[$key]);
		}
		return $plucked;
	}

	/**
	 * Checks if the provided $date's year matches the schedule's stepping.
	 *
	 * @param $date NinjaDateTime
	 * @return bool
	 * @throws Exception UnexpectedValueException
	 */
	public function match_year_interval($date) {
		$delta = (int)($date->get_year() - $this->start->get_year());
		return $this->match_interval($delta);
	}

	/**
	 * Checks if the provided $date's month matches the schedule's stepping.
	 *
	 * @param $date NinjaDateTime
	 * @return bool
	 * @throws Exception UnexpectedValueException
	 */
	public function match_month_interval($date) {
		$from = $this->start->get_dom_first();
		$to = $date->get_dom_first();

		$diff = $to->diff($from);

		// Get number of months, potentially over multiple years.
		$months = ($diff->m + ($diff->y * 12));

		return $this->match_interval($months);
	}

	/**
	 * Checks if the provided $date's week matches the schedule's stepping.
	 *
	 * @param $date NinjaDateTime
	 * @return bool
	 * @throws Exception UnexpectedValueException
	 */
	public function match_week_interval($date) {
		$from = $this->start->get_day_start();
		$to = $date->get_day_start();
		$diff = $to->diff($from)->days;

		return $this->match_interval($diff / 7);
	}

	/**
	 * Checks if the provided $date's day matches the schedule's stepping.
	 *
	 * @param $date NinjaDateTime
	 * @return bool
	 * @throws Exception UnexpectedValueException
	 */
	public function match_day_interval($date) {
		$from = $this->start->get_day_start();
		$to = $date->get_day_start();

		return $this->match_interval($to->diff($from)->days);
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
