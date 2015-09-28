<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Date help class
 */
class cal
{
	/**
	*	decide what date format to use for calendar
	*
	*	NOTE!!! date::date_format has the same thing, with time
	*/
	public static function get_calendar_format($get_php=false)
	{
		$date_format = false;
		$nagios_config = System_Model::parse_config_file('nagios.cfg');
		$nagios_format_name = $nagios_config['date_format'];
		switch (strtolower($nagios_format_name)) {
			case 'us': # MM-DD-YYYY
				$date_format = 'mm/dd/yyyy';
				break;
			case 'euro': # DD-MM-YYYY
				$date_format = 'dd/mm/yyyy';
				break;
			case 'iso8601': # YYYY-MM-DD
				$date_format = 'yyyy-mm-dd';
				break;
			case 'strict-iso8601': # YYYY-MM-DD
				$date_format = 'yyyy-mm-dd';
				break;
		}

		# convert to PHP equivalent
		if ($get_php === true) {
			$date_format = str_replace('yyyy', 'Y', $date_format);
			$date_format = str_replace('mm', 'm', $date_format);
			$date_format = str_replace('dd', 'd', $date_format);
		}
		return $date_format;
	}

}
