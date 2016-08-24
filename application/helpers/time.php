<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Thrown by time::get_limits()
 */
class InvalidTimePeriod_Exception extends Exception {}

/**
 * Help class for converting seconds to readable string of days, hours etc
 */
class time
{
	/**
	 * Convert a given nr of sec to string,
	 * day, hour, minute, second
	 */
	public static function to_string($t=0)
	{
		$neg = false;

		# translate the abbreviations
		# seems weird but I suppose someone will want this anyway
		$d = _('d'); // day
		$h = _('h'); // hour
		$m = _('m'); // minute
		$s = _('s'); // second
		$negative = _("negative") . " ";

		if (!$t) return "0$d 0$h 0$m 0$s";
		if ($t < 0) {
			$neg = 1;
			$t = 0 - $t;
		}

		$days = $t / 86400;
		$days = floor($days);
		$hrs = ($t / 3600) % 24;
		$mins = ($t / 60) % 60;
		$secs = $t % 60;

		$timestring = "";
		if ($neg) $timestring .= $negative;
		if ($days > 10) {
			$timestring .= $days.$d;
			if ($hrs || $mins || $secs) $timestring .= " ";
		} else {
			if ($days) {
				$timestring .= $days.$d;
				if ($hrs || $mins || $secs) $timestring .= " ";
				if ($hrs) {
					$timestring .= $hrs.$h;
					if ($mins || $secs) $timestring .= " ";
				}
			} else {
				if ($hrs) {
					$timestring .= $hrs.$h;
					if ($mins || $secs) $timestring .= " ";
					if ($mins) {
						$timestring .= $mins.$m;
						if ($secs) $timestring .= " ";
					}
				} else {
					if ($mins) {
						$timestring .= $mins.$m;
						if ($secs) $timestring .= " ";
					}
					if ($secs) $timestring .= $secs.$s;
				}
			}
		}

		return trim($timestring);

	}

	/**
	 * @param $time_period string
	 * @param $now int, Unix timestamp (you probably want to pass the result of time())
	 * @return array(start_time, end_time)
	 * @throws InvalidTimePeriod_Exception if the $time_period is invalid
	 */
	public static function get_limits($time_period, $now) {
		$year_now = date('Y', $now);
		$month_now = date('m', $now);
		$day_now = date('d', $now);

		switch ($time_period) {
			case 'today':
			       $time_start = mktime(0, 0, 0, $month_now, $day_now, $year_now);
			       $time_end = $now;
			       break;
			case 'last24hours':
			       $time_start = mktime(date('H', $now), date('i', $now), date('s', $now), $month_now, $day_now -1, $year_now);
			       $time_end = $now;
			       break;
			case 'yesterday':
			       $time_start = mktime(0, 0, 0, $month_now, $day_now -1, $year_now);
			       $time_end = mktime(0, 0, 0, $month_now, $day_now, $year_now);
			       break;
			case 'thisweek':
			       $time_start = strtotime('last monday', strtotime('tomorrow', $now));
			       $time_end = $now;
			       break;
			case 'last7days':
			       $time_start = strtotime('now - 7 days', $now);
			       $time_end = $now;
			       break;
			case 'lastweek':
			       $time_start = strtotime('monday last week', strtotime('midnight -1 sec', $now));
			       $time_end = strtotime('monday', strtotime('midnight -1 sec', $now));
			       break;
			case 'thismonth':
			       $time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       $time_end = $now;
			       break;
			case 'last31days':
			       $time_start = strtotime('now - 31 days', $now);
			       $time_end = $now;
			       break;
			case 'lastmonth':
			       $time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01 -1 month');
			       $time_end = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       break;
			case 'thisyear':
			       $time_start = strtotime('midnight '.$year_now.'-01-01');
			       $time_end = $now;
			       break;
			case 'lastyear':
			       $time_start = strtotime('midnight '.$year_now.'-01-01 -1 year');
			       $time_end = strtotime('midnight '.$year_now.'-01-01');
			       break;
			case 'last12months':
			       $time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01 -12 months');
			       $time_end = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       break;
			case 'last3months':
			       $time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01 -3 months');
			       $time_end = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       break;
			case 'last6months':
			       $time_start = strtotime('midnight '.$year_now.'-'.$month_now.'-01 -6 months');
			       $time_end = strtotime('midnight '.$year_now.'-'.$month_now.'-01');
			       break;
			case 'lastquarter':
				$t = getdate($now);
				if($t['mon'] <= 3){
					$lqstart = 'midnight '.($t['year']-1)."-10-01";
					$lqend = 'midnight '.($t['year'])."-01-01";
				} elseif ($t['mon'] <= 6) {
					$lqstart = 'midnight '.$t['year']."-01-01";
					$lqend = 'midnight '.$t['year']."-04-01";
				} elseif ($t['mon'] <= 9){
					$lqstart = 'midnight '.$t['year']."-04-01";
					$lqend = 'midnight '.$t['year']."-07-01";
				} else {
					$lqstart = 'midnight '.$t['year']."-07-01";
					$lqend = 'midnight '.$t['year']."-10-01";
				}
				$time_start = strtotime($lqstart);
				$time_end = strtotime($lqend);
				break;
			default:
				throw new InvalidTimePeriod_Exception("'$time_period' is not a valid value for \$time_period");
		}

		if($time_start === false) {
			throw new InvalidTimePeriod_Exception("The time_period '$time_period' could not be resolved ('now' is $now)'. This is a bug, please report it to op5");
		}

		if($time_end === false) {
			throw new InvalidTimePeriod_Exception("The time_period '$time_period' could not be resolved ('now' is $now)'. This is a bug, please report it to op5");
		}

		if($time_start > $now)
			$time_start = $now;

		if($time_end > $now)
			$time_end = $now;

		return array($time_start, $time_end);
	}

}
