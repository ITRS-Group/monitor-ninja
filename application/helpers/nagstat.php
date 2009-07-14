<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Helper class for nagios status
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class nagstat_Core {
	const CMD_ENABLE_FLAP_DETECTION = 61;
	const CMD_DISABLE_FLAP_DETECTION = 62;

	const DISPLAY_HOSTS = 0;
	const DISPLAY_HOSTGROUPS = 1;
	const DISPLAY_SERVICEGROUPS = 2;

	# These differ from the ones in nagios' cgi's
	# See comment for service states below for why
	const HOST_UP = 1;
	const HOST_DOWN	= 2;
	const HOST_UNREACHABLE = 4;
	const HOST_PENDING = 64;

	const HOST_STATE_ACKNOWLEDGED = 4;
	const HOST_STATE_UNACKNOWLEDGED = 8;
	const HOST_SCHEDULED_DOWNTIME = 1;
	const HOST_NO_SCHEDULED_DOWNTIME = 2;
	const HOST_CHECKS_DISABLED = 16;
	const HOST_CHECKS_ENABLED = 32;
	const HOST_EVENT_HANDLER_DISABLED = 64;
	const HOST_EVENT_HANDLER_ENABLED = 128;
	const HOST_FLAP_DETECTION_DISABLED = 256;
	const HOST_FLAP_DETECTION_ENABLED = 512;
	const HOST_IS_FLAPPING = 1024;
	const HOST_IS_NOT_FLAPPING = 2048;
	const HOST_NOTIFICATIONS_DISABLED = 4096;
	const HOST_NOTIFICATIONS_ENABLED = 8192;
	const HOST_PASSIVE_CHECKS_DISABLED = 16384;
	const HOST_PASSIVE_CHECKS_ENABLED = 32768;
	const HOST_PASSIVE_CHECK = 65536;
	const HOST_ACTIVE_CHECK = 131072;
	const HOST_HARD_STATE = 262144;
	const HOST_SOFT_STATE = 524288;

	# These are different from the ones in nagios' cgi's,
	# because we use bitmasks in our sql queries, and that
	# doesn't work unless these consts are sequential and
	# in the same order as the *real* states are.
	# We introduce the magic number 64 (1 << 6) for PENDING
	# instead of keeping special numbers at first.
	const SERVICE_OK = 1;
	const SERVICE_WARNING = 2;
	const SERVICE_CRITICAL = 4;
	const SERVICE_UNKNOWN = 8;
	const SERVICE_PENDING = 64;

	const SERVICE_SCHEDULED_DOWNTIME = 1;
	const SERVICE_NO_SCHEDULED_DOWNTIME	= 2;
	const SERVICE_STATE_ACKNOWLEDGED = 4;
	const SERVICE_STATE_UNACKNOWLEDGED = 8;
	const SERVICE_CHECKS_DISABLED = 16;
	const SERVICE_CHECKS_ENABLED = 32;
	const SERVICE_EVENT_HANDLER_DISABLED = 64;
	const SERVICE_EVENT_HANDLER_ENABLED = 128;
	const SERVICE_FLAP_DETECTION_ENABLED = 256;
	const SERVICE_FLAP_DETECTION_DISABLED = 512;
	const SERVICE_IS_FLAPPING = 1024;
	const SERVICE_IS_NOT_FLAPPING = 2048;
	const SERVICE_NOTIFICATIONS_DISABLED = 4096;
	const SERVICE_NOTIFICATIONS_ENABLED = 8192;
	const SERVICE_PASSIVE_CHECKS_DISABLED = 16384;
	const SERVICE_PASSIVE_CHECKS_ENABLED = 32768;
	const SERVICE_PASSIVE_CHECK = 65536;
	const SERVICE_ACTIVE_CHECK = 131072;
	const SERVICE_HARD_STATE = 262144;
	const SERVICE_SOFT_STATE = 524288;

	const STYLE_OVERVIEW = 0;
	const STYLE_DETAIL = 1;
	const STYLE_SUMMARY = 2;
	const STYLE_GRID = 3;
	const STYLE_HOST_DETAIL = 4;
	/********* HOST CHECK TYPES ***********/

	const HOST_CHECK_ACTIVE = 0;	/* Nagios performed the host check */
	const HOST_CHECK_PASSIVE = 1;	/* the host check result was submitted by an external source */

	/******** SERVICE STATE TYPES ********/
	const SOFT_STATE = 0;
	const HARD_STATE = 1;

	const SORT_ASC = 'ASC';
	const SORT_DESC = 'DESC';

	/**
	 * Process macros for host- or service objects
	 */
	public function process_macros($string=false, $obj=false)
	{
		if (empty($string) || empty($obj)) {
			return false;
		}

		$macros = array(
			'$HOSTNAME$' => 'host_name',
			'$HOSTADDRESS$' => 'address',
			'$HOSTDISPLAYNAME$' => 'display_name',
			'$HOSTALIAS$' => 'alias',
			'$HOSTSTATE$' => array("status_text[%s, host]", 'current_state'), /* UP/DOWN/UNREACHABLE - callback */
			'$HOSTSTATEID$' => 'current_state',
			'$HOSTSTATETYPE$' => array('array[%s,SOFT,HARD]', 'state_type'), /* HARD/SOFT - callback */
			'$HOSTATTEMPT$' => 'current_attempt',
			'$MAXHOSTATTEMPTS$' => 'max_check_attempts',
			'$SERVICEDESC$' => 'service_description',
			'$SERVICEDISPLAYNAME$' => 'display_name',
			'$SERVICESTATE$' => array("status_text[%s, service]", 'current_state'),
		);

		$regexp = '/\$[A-Z]*\$/';
		$hits = preg_match_all($regexp, $string, $res);

		if ($hits > 0 && !empty($res)) {
			foreach ($res as $matches) {
				foreach ($matches as $match) {
					if (array_key_exists($match, $macros)) {
						$field = $macros[$match];

						if (is_array($field)) {
							$val = isset($obj->{$field[1]}) ? self::do_callback(sprintf($field[0], $obj->{$field[1]})) : false;
							if ($val !== false) {
								$string = str_replace($match, $val, $string);
							}
						} else {
							if (isset($obj->{$field})) {
								$string = str_replace($match, $obj->{$field}, $string);
							}
						}
					}
				}
			}
		}

		return $string;
	}

	/**
	* Try to figure out what (and how) to call methods/functions
	* from the callbacks passed from process_macros()
	*/
	public function do_callback(&$callback)
	{
		if (is_string($callback)) {
			if (preg_match('/^([^\[]++)\[(.+)\]$/', $callback, $matches)) {
				// Split the function and args
				$callback = $matches[1];
				$args = preg_split('/(?<!\\\\),\s*/', $matches[2]);
			}
		}

		if (is_string($callback)) {
			if (strpos($callback, '::') !== FALSE) {
				$callback = explode('::', $callback);
			} elseif (function_exists($callback)) {
				// No need to check if the callback is a method
				$callback = $callback;
			}
		}

		if ($callback === 'array' && is_array($args) && !empty($args)) {
			$val = $args[0];
			# remove first element which is the value to be "translated"
			array_shift($args);
			if (array_key_exists($val, $args)) {
				return $args[$val];
			} else {
				return false;
			}
		}

		$value = false;
		$name = false;
		if (is_callable($callback)) {
			if (is_array($callback)) {
				if (is_object($callback[0])) {
					// Object instance syntax
					$name = get_class($callback[0]).'->'.$callback[1];
				} else {
					// Static class syntax
					$name = $callback[0].'::'.$callback[1];
				}
			} else {
				// Function syntax
				$name = $callback;
			}
		}

		if (function_exists($name)) {
			$value = call_user_func_array($name, $args);
		}
		return $value;
	}


	/**
	*	Format a Nagios date format string to the
	*	PHP equivalent.
	*/
	public function date_format($nagios_format_name=false)
	{
		if (empty($nagios_format_name)) {
			$date_format_id = 'date_format';
			# try config helper first (includes session check)
			$nagios_format_name = config::get('config.'.$date_format_id);
			if (empty($nagios_format_name)) {
				# check nagios.cfg file
				$nagios_config = System_Model::parse_config_file('nagios.cfg');
				$nagios_format_name = $nagios_config[$date_format_id];
				# save to session
				Session::instance()->set('config.'.$date_format_id, $nagios_format_name);

				# save setting to db
				Ninja_setting_Model::save_page_setting('config.'.$date_format_id, '*', $nagios_format_name);
			}
		}
		$nagios_format_name = trim($nagios_format_name);
		if (empty($nagios_format_name)) {
			return false;
		}
		$date_format = false;
		switch (strtolower($nagios_format_name)) {
			case 'us': # MM-DD-YYYY HH:MM:SS
				$date_format = 'm-d-Y H:i:s';
				break;
			case 'euro': # DD-MM-YYYY HH:MM:SS
				$date_format = 'd-m-Y H:i:s';
				break;
			case 'iso8601': # YYYY-MM-DD HH:MM:SS
				$date_format = 'Y-m-d H:i:s';
				break;
			case 'strict-iso8601': # YYYY-MM-DDTHH:MM:SS
				$date_format = 'Y-m-dTH:i:s';
				break;
		}
		return $date_format;
	}

}


/**
* Helper function to get status text from current status model
* Used in callbacks in process_macros
*/
function status_text($db_status=false, $type='host')
{
	return Current_status_Model::status_text($db_status, $type);
}