<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Help with formatting datestamps
 */
class date_Core {

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
}
