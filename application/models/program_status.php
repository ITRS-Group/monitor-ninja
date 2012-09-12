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
		try {
			if(self::get_local()) {
				return true;
			}
		}catch(Exception $ex) {}
		return false;
	}
}
