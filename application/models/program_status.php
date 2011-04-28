<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Reads program status data
 */
class Program_status_Model extends Model
{
	/**
	 * Fetch all info from program_status table
	 */
	public function get_all()
	{
		$db = Database::instance();
		$sql = "SELECT * FROM program_status";
		$res = $db->query($sql);
		return (!$res || count($res) == 0) ? false : $res;
	}

	/**
	 * Fetch all info for local node
	 */
	public function get_local()
	{
		$db = Database::instance();
		$sql = "SELECT * FROM program_status WHERE instance_id = 0";
		$res = $db->query($sql);
		return (!$res || count($res) == 0) ? false : $res;
	}

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
	public function last_alive()
	{
		$db = Database::instance();
		$sql = "SELECT last_alive FROM program_status WHERE instance_id = 0";
		$res = $db->query($sql);
		$cur = ($res && count($res)) ? $res->current() : false;
		return $cur ? $cur->last_alive : false;
	}

	/**
	*	Fetch current global settings for notifications
	*	and active_service_checks
	*/
	public function notifications_checks()
	{
		$db = Database::instance();
		$sql = "SELECT notifications_enabled, active_service_checks_enabled FROM program_status WHERE instance_id = 0";
		$res = $db->query($sql);
		return (!$res || count($res) == 0) ? false : $res;
	}
}
