<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for reports
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class reports_Core
{
	public static $days_per_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	public static $valid_weekdays = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
	public static $valid_months = array
	(
		1  => 'january',
		2  => 'february',
		3  => 'march',
		4  => 'april',
		5  => 'may',
		6  => 'june',
		7  => 'july',
		8  => 'august',
		9  => 'september',
		10 => 'october',
		11 => 'november',
		12 => 'december'
	);

	public function lib_reports_assert_handler($filename, $line, $code)
	{
		if (!posix_isatty(0))
			echo "<pre>\n";

		echo "ASSERT FAILED\n";
		debug_print_backtrace();

		echo "File: $filename\n\n";
		echo "Line: $line\n";
		echo "Assertion: $code\n";

		if (!posix_isatty(0))
			echo "</pre>\n";
	}

	public function percent($dividend, $divisor)
	{
		if (!$dividend || !$divisor)
			return 0;

		return ($dividend / $divisor) * 100;
	}

	/**
	 * Assigns color to labels to be used in a piechart
	 */
	public function get_color_values($labels=false)
	{
		if (empty($labels)) return false;
		$green 	= '#88cd18';
		$yellow = '#ffd92f';
		$orange = '#ff9d08';
		$red 	= '#f7261b';
		$grey 	= '#a0a084';

		$return = false;
		$colors = array(
			'OK' => $green,
			'UP' => $green,
			'WARNING' => $yellow,
			'UNREACHABLE' => $orange,
			'UNKNOWN' => $orange,
			'DOWN' => $red,
			'CRITICAL' => $red,
			'UNDETERMINED' => $grey
		);
		foreach ($labels as $key) {
			$return[] = array($colors[strtoupper($key)], NULL, NULL);
		}
		return $return;
	}

	/**
	*	Converts an html hexadecimal color from the form "#rrggbb" to its separate components
	*	@param string $color_str Color represented as hexadecimal html representation
	*	@return array The RGB components as a triplet with decimal values from 0 to 255.
	*/
	public function convert_hex_color($color_str)
	{
		if (empty($color_str)) return false;
		$color_str = str_replace('#', '', $color_str);
		$c1 = substr($color_str, 0, 2);
		$c2 = substr($color_str, 2, 2);
		$c3 = substr($color_str, 4, 2);
		return array(hexdec($c1), hexdec($c2), hexdec($c3) );
	}

	/**
	*	Fetch date ranges from reports class
	*/
	public function get_date_ranges()
	{
		$reports = new Reports_Model();
		return $reports->get_date_ranges();
	}
}

