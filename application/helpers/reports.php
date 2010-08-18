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
	 * Converts an html hexadecimal color from the form "#rrggbb" to
	 * its separate components.
	 * @param $color_str string: Color in html hex
	 * @return False on error. Array of 3 decimal values on success
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
	 * Fetch date ranges from reports class
	 * @return Array of date ranges
	 */
	public function get_date_ranges()
	{
		$reports = new Reports_Model();
		return $reports->get_date_ranges();
	}

	/**
	*	Format report value output
	*/
	public function format_report_value($val)
	{
		$return = 0;
		if ($val == '0.000' || $val == '100.000')
			$return = number_format($val, 0);
		else
			$return = number_format($val, 3);

		return $return;
	}

	public function is_proper_report_item($k, $data)
	{
		if (is_array($data) && !empty($data['states']) && is_array($data['states']))
			return true;

		return false;
	}

	// used for automatic test cases
	public function print_test_settings($test=false)
	{
		# report uses reports model default settings
		if (!isset($test['start_time']) || !isset($test['end_time'])) {
			echo $this->translate->_('Empty report settings. We need start_time and end_time')."\n";
			print_r($test);
			exit(1);
		}

		foreach ($test as $k => $v) {
			if (is_array($v) && count($v) === 1)
				$v = array_pop($v);

			if (is_array($v)) {
				echo "\t$k {\n";
				foreach ($v as $v2) {
					echo "\t\t$v2\n";
				}
				echo "\t}\n";
				continue;
			}
			echo "\t$k = $v\n";
		}
	}

	/**
	*	Create common translated javascript strings
	*/
	public function js_strings()
	{
		$t = $this->translate;
		$js_strings = false;
		$js_strings .= "var _ok_str = '".$t->_('OK')."';\n";
		$js_strings .= "var _cancel_str = '".$t->_('Cancel')."';\n";
		$js_strings .= "var _reports_err_str_noobjects = '".sprintf($t->_("Please select what objects to base the report on by moving %sobjects from the left selectbox to the right selectbox"), '<br />')."';\n";
		$js_strings .= "var _reports_invalid_startdate = \"".$t->_("You haven't entered a valid Start date")."\";\n";
		$js_strings .= "var _reports_invalid_enddate = \"".$t->_("You haven't entered a valid End date")."\";\n";
		$js_strings .= "var _reports_invalid_timevalue = \"".$t->_("You haven't entered a valid time value")."\";\n";
		$js_strings .= "var _reports_enddate_infuture = '".sprintf($t->_("You have entered an End date in the future.%sClick OK to change this to current time or cancel to modify."), '\n')."';\n";
		$js_strings .= "var _reports_enddate_lessthan_startdate = '".$t->_("You have entered an End date before Start Date.")."';\n";
		$js_strings .= "var _reports_send_now = '".$t->_('Send this report now')."';\n";
		$js_strings .= "var _reports_send = '".$t->_('Send')."';\n";
		$js_strings .= "var _reports_errors_found = '".$t->_('Found the following error(s)')."';\n";
		$js_strings .= "var _reports_please_correct = '".$t->_('Please correct this and try again')."';\n";
		$js_strings .= "var _reports_schedule_interval_error = '".$t->_(' -Please select a schedule interval')."';\n";
		$js_strings .= "var _reports_schedule_recipient_error = '".$t->_(' -Please enter at least one recipient')."';\n";
		$js_strings .= "var _reports_invalid_email = '".$t->_('You have entered an invalid email address')."';\n";
		$js_strings .= "var _label_direct_link = '".$t->_('Direct link')."';\n";

		return $js_strings;
	}
}
