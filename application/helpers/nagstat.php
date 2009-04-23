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

	const SORT_ASC = 'ASC';
	const SORT_DESC = 'DESC';

	public function process_macros($string=false)
	{
		$string = trim(strtolower($string));
		if (empty($string)) {
			return false;
		}
		$macros = array(
			'$HOSTADDRESS$' => 'host_name'
		);
		foreach ($macros as $macro) {
			if (strstr($string, $macro)) {
				# how do we solve this?
				# have to know what to substitute with
				# a reference to current object?
				# db callback?
			}
		}
	}

	/**
	*	Format a Nagios date format string to the
	*	PHP equivalent.
	*/
	public function date_format($nagios_format_name='iso8601')
	{
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
