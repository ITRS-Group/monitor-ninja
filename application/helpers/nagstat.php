<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Helper class for nagios status
 */
class nagstat_Core {
	const CMD_ENABLE_FLAP_DETECTION = 61; /**< The nagios code for the command to enable flap detection */
	const CMD_DISABLE_FLAP_DETECTION = 62; /**< The nagios code for the command to disable flap detection */

	const DISPLAY_HOSTS = 0; /**< FIXME: don't know, unused? */
	const DISPLAY_HOSTGROUPS = 1; /**< FIXME: don't know, unused? */
	const DISPLAY_SERVICEGROUPS = 2; /**< FIXME: don't know, unused? */

	# These differ from the ones in nagios' cgi's
	# See comment for service states below for why
	const HOST_UP = 1; /**< Nagios host up code as a bit flag */
	const HOST_DOWN	= 2; /**< Nagios host down code as a bit flag */
	const HOST_UNREACHABLE = 4; /**< Nagios host unreachable code as a bit flag */
	const HOST_PENDING = 64; /**< Our arbitrary code for not-yet-checked hosts */

	const SERVICE_DOWNTIME= 1;	/**< service downtime */
	const HOST_DOWNTIME = 2;	/**< host downtime */
	const ANY_DOWNTIME = 3;		/**< host or service downtime */

	const HOST_SCHEDULED_DOWNTIME = 1; /**< Code for hosts in scheduled downtime, bit flag */
	const HOST_NO_SCHEDULED_DOWNTIME = 2; /**< Code for hosts not in scheduled downtime, bit flag */
	const HOST_STATE_ACKNOWLEDGED = 4; /**< Code for hosts in state acknowledged, bit flag */
	const HOST_STATE_UNACKNOWLEDGED = 8; /**< Code for hosts not in state acknowledged, bit flag */
	const HOST_CHECKS_DISABLED = 16; /**< Code for hosts with disabled checks, bit flag */
	const HOST_CHECKS_ENABLED = 32; /**< Code for hosts with enabled checks, bit flag */
	const HOST_EVENT_HANDLER_DISABLED = 64; /**< Code for hosts with no enabled event handler, bit flag */
	const HOST_EVENT_HANDLER_ENABLED = 128; /**< Code for hosts with an enabled event handler, bit flag */
	const HOST_FLAP_DETECTION_DISABLED = 256; /**< Code for hosts with disabled flap detection, bit flag */
	const HOST_FLAP_DETECTION_ENABLED = 512; /**< Code for hosts with enabled flap detection, bit flag */
	const HOST_IS_FLAPPING = 1024; /**< Code for hosts that are flapping, bit flag */
	const HOST_IS_NOT_FLAPPING = 2048; /**< Code for hosts that are not flapping, bit flag */
	const HOST_NOTIFICATIONS_DISABLED = 4096; /**< Code for hosts that has disabled notifications, bit flag */
	const HOST_NOTIFICATIONS_ENABLED = 8192; /**< Code for hosts with enabled notifications, bit flag */
	const HOST_PASSIVE_CHECKS_DISABLED = 16384; /**< Code for hosts with disabled passive checks, bit flag */
	const HOST_PASSIVE_CHECKS_ENABLED = 32768; /**< Code for hosts with enabled passive checks, bit flag */
	const HOST_PASSIVE_CHECK = 65536; /**< Code for hosts that were last checked by a passive check, bit flag */
	const HOST_ACTIVE_CHECK = 131072; /**< Code for hosts that were last checked by an active check, bit flag */
	const HOST_HARD_STATE = 262144; /**< Code for hosts in a hard state, bit flag */
	const HOST_SOFT_STATE = 524288; /**< Code for hosts in a soft state, bit flag */

	# These are different from the ones in nagios' cgi's,
	# because we use bitmasks (actually, we don't, but that's an oracle
	# implementation detail) in our sql queries, and that
	# doesn't work unless these consts are sequential and
	# in the same order as the *real* states are.
	# We introduce the magic number 64 (1 << 6) for PENDING
	# instead of keeping special numbers at first.
	const SERVICE_OK = 1; /**< Nagios service ok code as a bit flag */
	const SERVICE_WARNING = 2; /**< Nagios service warning code as a bit flag */
	const SERVICE_CRITICAL = 4; /**< Nagios service critical code as a bit flag */
	const SERVICE_UNKNOWN = 8; /**< Nagios service unknown code as a bit flag */
	const SERVICE_PENDING = 64; /**< Our arbitrary code for not-yet-checked services */

	const SERVICE_SCHEDULED_DOWNTIME = 1; /**< Code for services in scheduled downtime, bit flag */
	const SERVICE_NO_SCHEDULED_DOWNTIME	= 2; /**< Code for services not in scheduled downtime, bit flag */
	const SERVICE_STATE_ACKNOWLEDGED = 4; /**< Code for services in state acknowledged, bit flag */
	const SERVICE_STATE_UNACKNOWLEDGED = 8; /**< Code for services not in state acknowledged, bit flag */
	const SERVICE_CHECKS_DISABLED = 16; /**< Code for services with disabled checks, bit flag */
	const SERVICE_CHECKS_ENABLED = 32; /**< Code for services with enabled checks, bit flag */
	const SERVICE_EVENT_HANDLER_DISABLED = 64; /**< Code for services with no enabled event handler, bit flag */
	const SERVICE_EVENT_HANDLER_ENABLED = 128; /**< Code for services with an enabled event handler, bit flag */
	const SERVICE_FLAP_DETECTION_ENABLED = 256; /**< Code for services with enabled flap detection, bit flag FIXME: This is inverted from the host states */
	const SERVICE_FLAP_DETECTION_DISABLED = 512; /**< Code for services with disabled flap detection, bit flag FIXME: This is inverted from the host states */
	const SERVICE_IS_FLAPPING = 1024; /**< Code for services that are flapping, bit flag */
	const SERVICE_IS_NOT_FLAPPING = 2048; /**< Code for services that are not flapping, bit flag */
	const SERVICE_NOTIFICATIONS_DISABLED = 4096; /**< Code for services that has disabled notifications, bit flag */
	const SERVICE_NOTIFICATIONS_ENABLED = 8192; /**< Code for services with enabled notifications, bit flag */
	const SERVICE_PASSIVE_CHECKS_DISABLED = 16384; /**< Code for services with disabled passive checks, bit flag */
	const SERVICE_PASSIVE_CHECKS_ENABLED = 32768; /**< Code for services with enabled passive checks, bit flag */
	const SERVICE_PASSIVE_CHECK = 65536; /**< Code for services that were last checked by a passive check, bit flag */
	const SERVICE_ACTIVE_CHECK = 131072;  /**< Code for services that were last checked by an active check, bit flag */
	const SERVICE_HARD_STATE = 262144; /**< Code for services in a hard state, bit flag */
	const SERVICE_SOFT_STATE = 524288; /**< Code for services in a soft state, bit flag */

	const STYLE_OVERVIEW = 0; /**< FIXME: don't know, unused? */
	const STYLE_DETAIL = 1; /**< FIXME: don't know, unused? */
	const STYLE_SUMMARY = 2; /**< FIXME: don't know, unused? */
	const STYLE_GRID = 3; /**< FIXME: don't know, unused? */
	const STYLE_HOST_DETAIL = 4; /**< FIXME: don't know, unused? */
	/********* HOST CHECK TYPES ***********/

	const HOST_CHECK_ACTIVE = 0;	/**< Nagios performed the host check */
	const HOST_CHECK_PASSIVE = 1;	/**< the host check result was submitted by an external source */

	/******** SERVICE STATE TYPES ********/
	const SOFT_STATE = 0; /**< soft state */
	const HARD_STATE = 1; /**< hard state */

	const SORT_ASC = 'ASC'; /**< Code for when sorting ascending */
	const SORT_DESC = 'DESC'; /**< Code for when sorting descending */

	/********* SCHEDULING QUEUE TYPES *********/
	const CHECK_OPTION_NONE = 0; /**< Check was normal */
	const CHECK_OPTION_FORCE_EXECUTION = 1; /**< Check was forced */
	const CHECK_OPTION_FRESHNESS_CHECK = 2; /**< Check was a freshness check */
	const CHECK_OPTION_ORPHAN_CHECK = 4; /**< Check was an orphan check */

	/********* NOTIFICATION TYPES *********/

	const NOTIFICATION_ALL = 0; /**< all service and host notifications */
	const NOTIFICATION_SERVICE_ALL = 1; /**< all types of service notifications */
	const NOTIFICATION_HOST_ALL	=	2; /**< all types of host notifications */

	// service states
	const NOTIFICATION_SERVICE_RECOVERY	= 0; /**< Service recovery notification */
	const NOTIFICATION_SERVICE_WARNING = 1; /**< Service went warning notification */
	const NOTIFICATION_SERVICE_UNKNOWN	= 3; /**< Service went unknown notification */
	const NOTIFICATION_SERVICE_CRITICAL	= 2; /**< Service went critical notification */

	// host states
	const NOTIFICATION_HOST_RECOVERY = 0; /**< Host recovery notification */
	const NOTIFICATION_HOST_DOWN = 1; /**< Host went down notification */
	const NOTIFICATION_HOST_UNREACHABLE	= 2; /**< Host went unreachable notification */

	// reason type - every uncertain
	/// Service acknowledgement notification
	const NOTIFICATION_SERVICE_ACK = 2; // ? 0, 99?
	/// Service flapping notification
	const NOTIFICATION_SERVICE_FLAP = 3; // ? 0, 99
	/// Host acknowledgement notification
	const NOTIFICATION_HOST_ACK = 2; // ?
	/// Host flapping notification
	const NOTIFICATION_HOST_FLAP = 3; // ?

	const FIND_HOST = 1; /**< FIXME: don't know, unused? */
	const FIND_CONTACT = 2; /**< FIXME: don't know, unused? */
	const FIND_SERVICE = 3; /**< FIXME: don't know, unused? */

	const MAX_QUERYNAME_LENGTH	= 256; /**< FIXME: don't know, unused? */


	const HOST_NOTIFICATION	= 0; /**< The notification was for a host */
	const SERVICE_NOTIFICATION	= 1; /**< The notification was for a service */


	/**
	 * Process macros for host- or service objects
	 */
	public static function process_macros($string=false, &$obj=false)
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
			'$HOSTGROUPNAME$' => 'hostgroup_name',
			'$HOSTGROUPALIAS$' => 'alias',
			'$SERVICEDESC$' => 'service_description',
			'$SERVICEDISPLAYNAME$' => 'display_name',
			'$SERVICEGROUPNAME$' => 'servicegroup_name',
			'$SERVICESTATE$' => array("status_text[%s, service]", 'current_state'),
			'$SERVICEGROUPALIAS$' => 'alias',
			'$CURRENT_USER$' => array('current_user', 'host_name')
		);

		$regexp = '/\$[A-Z_]*\$/';
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
		$args = false;
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
	public static function date_format($nagios_format_name=false)
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
			case 'us': # MM/DD/YYYY HH:MM:SS
				$date_format = 'm/d/Y H:i:s';
				break;
			case 'euro': # DD-MM-YYYY HH:MM:SS
				$date_format = 'd/m/Y H:i:s';
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

	/**
	*	Convert a date format string back to a timestamp
	*/
	public function timestamp_format($format_str = false, $date_str=false)
	{
		if (empty($format_str))
			$format_str = self::date_format(); # fetch if not set

		# use now as date if nothing supplied as input
		$date_str = empty($date_str) ? date($format_str) : $date_str;
		$format_str = trim($format_str);
		$timestamp_format = false;
		if ($format_str == 'm-d-Y H:i:s') {
			$date_str = str_replace('-', '/', $date_str);
		}
		return strtotime($date_str);
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

/**
*	Callback to return username of current user
*/
function current_user()
{
	return Auth::instance()->get_user()->username;
}
