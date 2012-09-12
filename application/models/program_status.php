<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Reads program status data
 */
class Program_status_Model extends Model {

	/**
	 * Fetch all info for local node
	 */
	public static function get_local()
	{
		$ls = Livestatus::instance();
		return $ls->getProcessInfo();
	}

	/**
	 * List all nagios daemons by instance name
	 *
	 * @return Database result object or false
	 */
	public function list_program_status()
	{
		$db = Database::instance();
		$sql = "SELECT instance_name, last_alive, is_running FROM program_status order by instance_name";
		$res = $db->query($sql);
		return (!$res || count($res) == 0) ? false : $res;
	}

	/**
	*	Check last_alive from program_status
	*	to detect when data was updated
	*/
	public static function last_alive()
	{
		return true;
		$res = false;
		try {
			$ls = Livestatus::instance();
			$status = $ls->get_local();
			if($status->program_status) {
				$res = true;
			}
		} catch (LivestatusException $e) {
			/* FIXME: This should be logged to file perhaps? */
			openlog("ninja", LOG_PID, LOG_USER);
			syslog(LOG_ERR, "last_alive() failed: $e");
			closelog();
		}
		return $res;
	}
}
