<?php

/*require_once '../modules/monitoring/includes/datetime.php';
require_once '../modules/monitoring/includes/downtime.php';*/


/**
 *  Mappings for convenience when writing tests, instead of using numbers.
 */

// Days
define('SUNDAY', 0);
define('MONDAY', 1);
define('TUESDAY', 2);
define('WEDNESDAY', 3);
define('THURSDAY', 4);
define('FRIDAY', 5);
define('SATURDAY', 6);

// Months
define('JANUARY', 1);
define('FEBRUARY', 2);
define('MARCH', 3);
define('APRIL', 4);
define('MAY', 5);
define('JUNE', 6);
define('JULY', 7);
define('AUGUST', 8);
define('SEPTEMBER', 9);
define('OCTOBER', 10);
define('NOVEMBER', 11);
define('DECEMBER', 12);

// Ordinals/occurrences
define('FIRST', 1);
define('SECOND', 2);
define('THIRD', 3);
define('FOURTH', 4);
define('FIFTH', 5);
define('LAST', 'last');

// Units
define('DAY', 'day');
define('WEEK', 'week');
define('MONTH', 'month');
define('YEAR', 'year');

function mock_date($date_str) {
	return new NinjaDateTime($date_str);
}

class DowntimeModel {
	public $recurrence = 0;
	public $recurrence_on = 0;
	public $recurrence_ends = "0";
	public $duration;
	public $start_date, $start_time;
	public $end_date, $end_time;
	public $fixed;
	public $exclude_days;
	public $objects;
	public $downtime_type;
	public $comment, $author;

	function __construct() {
		// Set defaults
		$this->set_start();
		$this->set_end();
		$this->set_fixed();
		$this->set_objects();
		$this->set_downtime_type();
		$this->set_exclude_days();
		$this->set_duration();

		$this->comment = '__COMMENT__TEST__';
		$this->author = '__AUTHOR__TEST__';
	}

	public function get_author() {
		return $this->author;
	}

	public function get_comment() {
		return $this->comment;
	}

	public function get_start_date() {
		return $this->start_date;
	}

	public function get_start_time_string() {
		return $this->start_time;
	}

	public function get_end_date() {
		return $this->end_date;
	}

	public function get_end_time_string() {
		return $this->end_time;
	}

	public function get_recurrence() {
		return $this->recurrence ? json_encode($this->recurrence) : $this->recurrence;
	}

	public function get_recurrence_on() {
		return $this->recurrence_on ? json_encode($this->recurrence_on) : $this->recurrence_on;
	}

	public function get_recurrence_ends() {
		return $this->recurrence_ends;
	}

	public function get_fixed() {
		return $this->fixed;
	}

	public function get_objects() {
		return $this->objects;
	}

	public function get_exclude_days() {
		return $this->exclude_days;
	}

	public function get_downtime_type() {
		return $this->downtime_type;
	}

	public function get_duration() {
		return $this->duration;
	}

	public function set_duration($value = 0) {
		$this->duration = $value;
	}

	public function set_exclude_days($value = '') {
		$this->exclude_days = $value;
	}

	public function set_objects($value = array()) {
		$this->objects = json_encode($value);
	}

	public function set_downtime_type($value = 'hosts') {
		$this->downtime_type = $value;
	}

	public function set_fixed($value = true) {
		$this->fixed = (int)$value;
	}

	public function set_recurrence_ends($value) {
		$this->recurrence_ends = $value;
	}

	public function set_start($value = '1970-01-01 00:00') {
		$start = new NinjaDateTime($value);
		$this->start_date = $start->get_date();
		$this->start_time = $start->get_time();
	}

	public function set_end($value = '1970-01-01 01:00') {
		$end = new NinjaDateTime($value);
		$this->end_date = $end->get_date();
		$this->end_time = $end->get_time();
	}
}


class DowntimeDayModel extends DowntimeModel {
	/**
	 * Set day recurrence to the mock object.
	 *
	 * Example:
	 * $stepping=43
	 *
	 * Can be translated into "on every <stepping> day"
	 *
	 * @param $stepping int every Nth month
	 */
	public function set_recurrence($stepping) {
		$this->recurrence = array(
			'label' => 'custom',
			'no' => $stepping,
			'text' => 'day'
		);

		$this->recurrence_on = 0;
	}
}

class DowntimeWeekModel extends DowntimeModel {
	/**
	 * Set week recurrence to the mock object
	 *
	 * Example:
	 * $stepping=3, $weekdays=[1, 3, 4]
	 *
	 * Can be translated into "on <weekdays> every <stepping> week"
	 *
	 * @param $stepping int every Nth week
	 * @param $weekdays array weekdays [1, 2, 3, ...]
	 */
	public function set_recurrence($stepping, $weekdays) {
		$this->recurrence = array(
			'label' => 'custom',
			'no' => $stepping,
			'text' => 'week'
		);

		$this->recurrence_on = array();
		foreach($weekdays as $weekday) {
			array_push($this->recurrence_on, array('day' => $weekday));
		}
	}
}


class DowntimeMonthModel extends DowntimeModel {
	/**
	 * Set month recurrence to the mock object.
	 *
	 * Example:
	 * $occurrence=3, $dow=2, $stepping=3
	 *
	 * Can be translated into "<occurrence> <dow> of every <stepping> month"
	 *
	 * @param $stepping int every Nth month
	 * @param $dow int day of week (1-7)
	 * @param $occurrence int occurrence in month (1-4 + last)
	 */
	public function set_recurrence($stepping, $dow, $occurrence) {
		$this->recurrence = array(
			'label' => 'custom',
			'no' => $stepping,
			'text' => 'month'
		);

		$this->recurrence_on = array(
			'day_no' => $occurrence,
			'day' => $dow
		);
	}
}

class DowntimeYearModel extends DowntimeModel {
	/**
	 * Set year recurrence on the mock object.
	 *
	 * Example:
	 * $occurrence=3, $dow=2, $month=5, $stepping=3
	 *
	 * Can be translated into "<occurrence> <dow> of each <month> every Nth <year>"
	 *
	 * @param $stepping int every Nth year
	 * @param $month int month (1-12)
	 * @param $dow int day of week (1-7)
	 * @param $occurrence int occurrence in month (1-4 + last)
	 */
	public function set_recurrence($stepping, $month, $dow, $occurrence) {
		$this->recurrence = array(
			'label' => 'custom',
			'no' => $stepping,
			'text' => 'year'
		);

		$this->recurrence_on = array(
			'day_no' => $occurrence,
			'month' => $month,
			'day' => $dow
		);
	}
}
