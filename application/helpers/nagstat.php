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

	const HOST_PENDING = 1;
	const HOST_UP = 2;
	const HOST_DOWN	= 4;
	const HOST_UNREACHABLE = 8;
	const HOST_STATE_ACKNOWLEDGED = 4;
	const HOST_STATE_UNACKNOWLEDGED = 8;
	const HOST_SCHEDULED_DOWNTIME = 1;
	const HOST_NO_SCHEDULED_DOWNTIME = 2;
	const HOST_CHECKS_DISABLED = 16;
	const HOST_CHECKS_ENABLED = 32;

	const SERVICE_PENDING = 1;
	const SERVICE_OK = 2;
	const SERVICE_WARNING = 4;
	const SERVICE_UNKNOWN = 8;
	const SERVICE_CRITICAL = 16;
	const SERVICE_SCHEDULED_DOWNTIME = 1;
	const SERVICE_NO_SCHEDULED_DOWNTIME	= 2;
	const SERVICE_STATE_ACKNOWLEDGED = 4;
	const SERVICE_STATE_UNACKNOWLEDGED = 8;
	const SERVICE_CHECKS_DISABLED = 16;
	const SERVICE_CHECKS_ENABLED = 32;

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

}