<?php

/**
 * Class NinjaDateTime
 *
 * Inherits from PHP stdlib DateTime.
 * Provides a set of methods to make working with downtime dates more convenient.
 */
class NinjaDateTime extends DateTime {
	/**
	 * NinjaDateTime constructor
	 *
	 * @param $date_str
	 * @throws Exception
	 */
	function __construct($date_str) {
		parent::__construct($date_str);
	}

	/**
	 * Returns a clone of self, with time set to midnight - to simplify full-day comparison operations.
	 *
	 * @return NinjaDateTime
	 * @throws Exception
	 */
	public function get_day_start() {
		$clone = new NinjaDateTime($this->get_date());
		$clone->setTime(0, 0, 0);
		return $clone;
	}

	/**
	 * Get current day of month
	 *
	 * @return int
	 */
	public function get_day_of_month() {
		return (int)$this->format('d');
	}

	/**
	 * Get last day of current month
	 *
	 * @return int
	 */
	public function get_last_day_of_month() {
		return (int)$this->format('t');
	}

	/**
	 * Get current day of week (0-6)
	 *
	 * @return int
	 */
	public function get_day_of_week() {
		$day_number = (int)$this->format('N');
		return $day_number === 7 ? 0 : $day_number;
	}

	/**
	 * Get current week
	 *
	 * @return int
	 */
	public function get_week() {
		return (int)$this->format('W');
	}

	/**
	 * Get current month
	 *
	 * @return int
	 */
	public function get_month() {
		return (int)$this->format('m');
	}

	/**
	 * Get current month string (e.g. september)
	 *
	 * @return string
	 */
	public function get_month_string() {
		return (string)$this->format('F');
	}

	/**
	 * Get current full year (e.g. 1984)
	 *
	 * @return int
	 */
	public function get_year() {
		return (int)$this->format('Y');
	}

	/**
	 * Check if current day is the last of month
	 *
	 * @return bool
	 */
	public function is_last_dom() {
		return $this->get_last_day_of_month() === $this->get_day_of_month();
	}

	/**
	 * Get current date as a string (e.g. 1984-09-26)
	 *
	 * @return string
	 */
	public function get_date() {
		return $this->format('Y-m-d');
	}

	/**
	 * Get current time as string (e.g. 18:03:25)
	 *
	 * @return string
	 */
	public function get_time() {
		return $this->format('H:i:s');
	}

	/**
	 * Get current full date
	 *
	 * @return string
	 */
	public function get_datetime() {
		return $this->format('Y-m-d H:i:s');
	}
}
