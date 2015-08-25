<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Help with formatting datestamps
 */
class date {
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

		$duration .= _("Duration: ") . time::to_string($end_time - $start_time)."\n";
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


	/**
	*	Format a Nagios date format string to the
	*	PHP equivalent.
	*
	*	NOTE!!! cal::get_calendar_format has the same thing, without time
	*/
	public static function date_format($nagios_format_name=false)
	{
		static $default_nagios_format = null;
		if (empty($nagios_format_name)) {
			if (empty($default_nagios_format)) {
				$date_format_id = 'date_format';
				if (empty($nagios_format_name)) {
					# check nagios.cfg file
					$nagios_config = System_Model::parse_config_file('nagios.cfg');
					$default_nagios_format = $nagios_config[$date_format_id];
				}
			}
			$nagios_format_name = $default_nagios_format;
		}
		if (empty($nagios_format_name)) {
			return false;
		}
		$date_format = false;
		switch (strtolower($nagios_format_name)) {
			case 'us': # MM/DD/YYYY HH:MM:SS
				$date_format = 'm/d/Y H:i:s';
				break;
			case 'euro': # DD/MM/YYYY HH:MM:SS
				$date_format = 'd/m/Y H:i:s';
				break;
			case 'iso8601': # YYYY-MM-DD HH:MM:SS
				$date_format = 'Y-m-d H:i:s';
				break;
			case 'strict-iso8601': # YYYY-MM-DDTHH:MM:SS
				$date_format = 'Y-m-d\TH:i:s';
				break;
		}
		return $date_format;
	}

	/**
	 * Convert a date format string back to a timestamp
	 *
	 * @return false|int
	 */
	public static function timestamp_format($format_str = false, $date_str=false)
	{
		if(ctype_digit(strval(($date_str)))) {
			// assume UNIX timestamp
			return $date_str;
		}
		if (empty($format_str))
			$format_str = self::date_format(); # fetch if not set

		# use now as date if nothing supplied as input FIXME: isn't that extremely anti-useful?
		$date_str = empty($date_str) ? date($format_str) : $date_str;
		$dt = DateTime::createFromFormat($format_str, $date_str);
		if(!$dt) {
			return false;
		}
		return $dt->getTimestamp();
	}
}
