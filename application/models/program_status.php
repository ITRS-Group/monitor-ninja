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
		$db = new Database();
		$sql = "SELECT * FROM program_status";
		$res = $db->query($sql);
		return (!$res || count($res) == 0) ? false : $res;
	}

}
