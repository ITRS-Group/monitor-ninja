<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for reports
 */
class reports
{
	/** Colors for status in trends graph and such */
	public static $colors = array(
		'green' => '#aade53',
		'yellow' => '#ffd92f',
		'orange' => '#ff9d08',
		'red' 	=> '#f7261b',
		'grey' 	=> '#a19e95',
		'lightblue' => '#EAF0F2', # actual color is #ddeceb, but it is hardly visible
		'white' => '#ffffff',
		'transparent' => 'transparent'
	);

	/** Array of month_number => days_in_month */
	public static $days_per_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	/** Array of weekday names */
	public static $valid_weekdays = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
	/** Array of month names */
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

	/**
	 * Called by PHP as an assert callback to format errors usefully
	 */
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

	/**
	 * Generate a percentage easily
	 *
	 * @param $dividend The whole
	 * @param $divisor The part
	 * @return The percentage
	 */
	static function percent($dividend, $divisor)
	{
		if (!$dividend || !$divisor)
			return 0;

		return ($dividend / $divisor) * 100;
	}

	/**
	 * Assigns color to labels to be used in a piechart
	 */
	static function get_color_values($labels=false)
	{
		if (empty($labels)) return false;
		$green 	= '#88cd18';
		$yellow = '#ffd92f';
		$orange = '#ff9d08';
		$red 	= '#f7261b';
		$grey 	= '#a0a084';

		$return = array();
		$colors = array(
			'OK' => $green,
			'UP' => $green,
			'WARNING' => $yellow,
			'UNREACHABLE' => $orange,
			'UNKNOWN' => $orange,
			'DOWN' => $red,
			'CRITICAL' => $red,
			'UNDETERMINED' => $grey,
			'EXCLUDE' => null
		);
		foreach ($labels as $key) {
			$return[] = array($colors[strtoupper($key)], NULL, NULL);
		}
		return $return;
	}

	/**
	*	Format report value output
	*/
	static function format_report_value($val)
	{
		$return = 0;
		if ($val == '0.000' || $val == '100.000')
			$return = number_format($val, 0);
		else
			$return = number_format(str_replace(',', '.', $val), 3);

		return $return;
	}

	/**
	*	Create common translated javascript strings
	*/
	public static function js_strings()
	{
		$js_strings = "var _ok_str = '"._('OK')."';\n";
		$js_strings .= "var _cancel_str = '"._('Cancel')."';\n";
		$js_strings .= "var _reports_err_str_noobjects = '"._("Please select what objects to base the report on from the left selectbox")."';\n";
		$js_strings .= "var _reports_err_str_nostatus = '"._("You must provide at least one status to filter on")."';\n";
		$js_strings .= "var _reports_invalid_startdate = \""._("You haven't entered a valid Start date")."\";\n";
		$js_strings .= "var _reports_invalid_enddate = \""._("You haven't entered a valid End date")."\";\n";
		$js_strings .= "var _reports_invalid_timevalue = \""._("You haven't entered a valid time value")."\";\n";
		$js_strings .= "var _reports_enddate_infuture = '".sprintf(_("You have entered an End date in the future.%sClick OK to change this to current time or cancel to modify."), '\n')."';\n";
		$js_strings .= "var _reports_enddate_lessthan_startdate = '"._("You have entered an End date before Start Date.")."';\n";
		$js_strings .= "var _reports_send_now = '"._('Send this report now')."';\n";
		$js_strings .= "var _reports_send = '"._('Send')."';\n";
		$js_strings .= "var _reports_invalid_email = '"._('You have entered an invalid email address')."';\n";
		$js_strings .= "var _label_direct_link = '"._('Direct link')."';\n";
		$js_strings .= "var _reports_confirm_delete = '"._("Are you really sure that you would like to remove this saved report?")."';\n";
		$js_strings .= "var _reports_confirm_delete_warning = '"._("Please note that this is a scheduled report and if you decide to delete it, \\n" .
			"the corresponding schedule(s) will be deleted as well.\\n\\n Are you really sure that this is what you want?")."';\n";

		$js_strings .= "var _reports_success = '"._('Success')."';\n";
		$js_strings .= "var _reports_error = '"._('Error')."';\n";
		$js_strings .= "var _reports_missing_objects = \""._("Some items in your saved report do not exist anymore and have been removed")."\";\n";
		$js_strings .= "var _reports_missing_objects_pleaseremove = '"._('Please modify the objects to include in your report below and then save it.')."';\n";

		return $js_strings;
	}

	/**
	*	Determine what color to assign to an event
	*/
	static function _state_colors($type='host', $state=false)
	{
		$colors = self::_state_color_table($type);
		return $colors[$state];
	}

	/**
	 * @param $type string = 'host'
	 * @return array
	 */
	static function _state_color_table($type='host') {
		$colors = array(
				'host' => array(
						Reports_Model::HOST_UP => self::$colors['green'],
						Reports_Model::HOST_DOWN => self::$colors['red'],
						Reports_Model::HOST_UNREACHABLE => self::$colors['orange'],
						Reports_Model::HOST_PENDING => self::$colors['grey'],
						Reports_Model::HOST_EXCLUDED => self::$colors['transparent']
						),
				'service' => array(
						Reports_Model::SERVICE_OK => self::$colors['green'],
						Reports_Model::SERVICE_WARNING => self::$colors['yellow'],
						Reports_Model::SERVICE_CRITICAL => self::$colors['red'],
						Reports_Model::SERVICE_UNKNOWN => self::$colors['orange'],
						Reports_Model::SERVICE_PENDING => self::$colors['grey'],
						Reports_Model::SERVICE_EXCLUDED => self::$colors['transparent']
						)
				);
		return $colors[$type];
	}

	/**
	 * Given bunch of somewhat-magical parameters, return a whole multi-object report table
	 */
	static function format_multi_object_table($data, $title, $rowdescriber, $type, $columns, $is_summary, $options, &$i=0)
	{
		$servicedefs = array(
			Reports_Model::SERVICE_OK => array('KNOWN_TIME_OK', _('Ok'), 'ok'),
			Reports_Model::SERVICE_WARNING => array('KNOWN_TIME_WARNING', _('Warning'), 'warning'),
			Reports_Model::SERVICE_CRITICAL => array('KNOWN_TIME_CRITICAL', _('Critical'), 'critical'),
			Reports_Model::SERVICE_UNKNOWN => array('KNOWN_TIME_UNKNOWN', _('Unknown'), 'unknown'),
			Reports_Model::SERVICE_PENDING => array('TOTAL_TIME_UNDETERMINED', _('Undetermined'), 'pending'),
		);
		$hostdefs = array(
			Reports_Model::HOST_UP => array('KNOWN_TIME_UP', _('Up'), 'up'),
			Reports_Model::HOST_DOWN  => array('KNOWN_TIME_DOWN', _('Down'), 'down'),
			Reports_Model::HOST_UNREACHABLE => array('KNOWN_TIME_UNREACHABLE', _('Unreachable'), 'unreachable'),
			Reports_Model::HOST_PENDING => array('TOTAL_TIME_UNDETERMINED', _('Undetermined'), 'pending'),
		);
		$coldefs = ${$type.'defs'};
		$res = '<div class="report-block">
		<table class="multiple_services">
		<thead>
		<tr>
		<th>'.$title.'</th>';
		foreach ($columns as $col)
			$res .= '<th class="headerNone" style="width: 100px">' . $coldefs[$col][1] .'</th>';
		$res .='</tr></thead><tbody>';

		foreach ($data as $k => $row) {
			if (!is_array($row) || !isset($row['states']))
				continue;
			$res .= '<tr class="'.($i++%2?'even':'odd').'">'.$rowdescriber($row);
			foreach ($columns as $col) {
				$class = 'summary '.($is_summary?'tally ':'').$col.' '.($row['states']['PERCENT_'.$coldefs[$col][0]]>0?'nonzero':'');
				$shieldname = 'icons/12x12/shield-'.($row['states']['PERCENT_'.$coldefs[$col][0]] > 0 ? '' : 'not-').$coldefs[$col][2].'.png';
				$shield = html::image(
					ninja::add_path($shieldname),
					array(
						'alt' => $coldefs[$col][1],
						'title' => $coldefs[$col][1],
						'style' => 'height: 12px; width: 12px'));
				$res .= '<td style="width: 100px" class="'.$class.'">';
				if ($options['time_format'] & 2)
					$res .= time::to_string($row['states'][$coldefs[$col][0]]);
				if ($options['time_format'] == 3)
					$res .= '<br />';
				if ($options['time_format'] & 1)
					$res .= reports::format_report_value($row['states']['PERCENT_'.$coldefs[$col][0]]).' % ';
				$res .= $shield;
				if (($col == 'ok' || $col == 'up') && $options['scheduleddowntimeasuptime'] == 2 && $row['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP']) {
					$res .= ' ('.reports::format_report_value($row['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP']).' % in other states)';
				}
				$res .= '</td>';
			}
			$res .= '</tr>';
		}
		$res .= '</tbody></table></div>';
		return $res;
	}

	/**
	 * Returns the alias for the specified object of the specified type, or false
	 * Liberated from the report controller
	 */
	static function get_alias($type, $name)
	{
		if (empty($type) || empty($name))
			return false;

		$filter = array('name' => $name);
		$res = Livestatus::instance()->{'get'.ucfirst($type)}(array('columns' => array('alias'), 'filter' => array('name' => $name)));
		if (!$res)
			return false;
		return $res[0]['alias'];
	}

	/**
	*Return utc/gmt no for timezone
	*
	*/
	static function timezone_utc_no($time_zone){
		$date = new DateTime("now", new DateTimeZone($time_zone) );
		$utc_no = 'UTC/GMT ' . date_format($date, 'P');
		return $utc_no;
	}

	/**
	*Return default set timezone string
	*
	*/
	 static function default_timezone()
	{   
	    $default_timezone = date_default_timezone_get();
	    return $default_timezone;
	}

	/**
	*Return the timezone list for report
	*
	*/
	public static function timezone_list(){
		if (!apc_exists('timezone_list')){
			ini_set('memory_limit', '256M');
			$zones_array = array();
			$sort_utc = array();
			$final = array();
			$timestamp = time();
			$timezone_list = timezone_identifiers_list();
			foreach($timezone_list as $key => &$zone) {
			    $date = new DateTime("now", new DateTimeZone($zone) );
			    $sort_utc[$zone] = (int)date_format($date, 'P');
			    $zones_array[$zone] = '[ UTC/GMT ' . date_format($date, 'P') . ' ] - '. $zone;
			}
			asort($sort_utc);
			foreach($sort_utc as $key => $value) {
			    $final[$key] = $zones_array[$key];
			}
			apc_store('timezone_list', $final);
		}
	    return apc_fetch('timezone_list');
	}
}
