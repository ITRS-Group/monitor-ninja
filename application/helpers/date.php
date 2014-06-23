<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Help with formatting datestamps
 */
class date {

	private static function _nice_format_duration($start_time, $end_time) {
		$duration = $end_time - $start_time;
		$days = $duration / 86400;
		$hours = ($duration % 86400) / 3600;
		$minutes = ($duration % 3600) / 60;
		$seconds = ($duration % 60);
		return sprintf("%s: %dd %dh %dm %ds", _("Duration"),
			   $days, $hours, $minutes, $seconds);
	}

	/**
	 * Outputs a nicely formatted version of "2003-03-12 21:14:34 to 2003-03-12 21:14:35<br>
	 * Duration: 0d 0h 0m 1s"
	 *
	 * @param $start_time int timestamp
	 * @param $end_time int timestamp
	 */
	public static function duration($start_time, $end_time)
	{
		$fmt = "Y-m-d H:i:s";
		$duration = date($fmt, $start_time) . " to " .
			date($fmt, $end_time) . "<br />\n";

		$duration .= self::_nice_format_duration($start_time, $end_time)."\n";
		return $duration;
	}

	/**
	 * Return array of abbrivated weekday names
	 * NOTE: Are you Really Sure you need this?
	 */
	static function abbr_day_names()
	{
		return array(
			_('Sun'),
			_('Mon'),
			_('Tue'),
			_('Wed'),
			_('Thu'),
			_('Fri'),
			_('Sat')
		);
	}

	/**
	 * Return array of abbrivated month names
	 * NOTE: Are you Really Sure you need this?
	 */
	static function abbr_month_names()
	{
		return array(
			_('Jan'),
			_('Feb'),
			_('Mar'),
			_('Apr'),
			_('May'),
			_('Jun'),
			_('Jul'),
			_('Aug'),
			_('Sep'),
			_('Oct'),
			_('Nov'),
			_('Dec')
		);
	}

	/**
	 * Return array of full weekday names
	 * NOTE: Are you Really Sure you need this?
	 */
	static function day_names()
	{
		return array(
			_('Sunday'),
			_('Monday'),
			_('Tuesday'),
			_('Wednesday'),
			_('Thursday'),
			_('Friday'),
			_('Saturday')
		);
	}

	/**
	 * Return array of full month names
	 * NOTE: Are you Really Sure you need this?
	 */
	static function month_names()
	{
		return array(
			_('January'),
			_('February'),
			_('March'),
			_('April'),
			_('May'),
			_('June'),
			_('July'),
			_('August'),
			_('September'),
			_('October'),
			_('November'),
			_('December')
		);
	}

	/**
	 * Offset from UTC
	 *
	 * @param $timezone string = null, defaults to php.ini's value
	 * @return int seconds
	 */
	static function utc_offset($timezone = null) {
		if(!$timezone) {
			$timezone = date_default_timezone_get();
		}
		if($timezone == 'UTC') {
			return 0;
		}
		$utc = new DateTimeZone('UTC');
		$remote_dtz = new DateTimeZone($timezone);

		$origin_dt = new DateTime("now", $utc);
		$remote_dt = new DateTime("now", $remote_dtz);

		return $remote_dtz->getOffset($remote_dt);
	}
}
