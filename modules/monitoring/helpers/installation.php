<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * installation helper
 */
class installation
{
	private static $timestamp = false;

	/**
	 * @return int timestamp
	 */
	public static function get_installation_time() {
		if(self::$timestamp !== false) {
			return self::$timestamp;
		}
		$db = Database::instance();
		$timerow = $db->query('SELECT MIN(timestamp) FROM report_data')->result_array(false, MYSQL_NUM);
		$installation_time = $timerow[0][0];
		if($installation_time === NULL) {
			$installation_time = time();
		}
		self::$timestamp = intval($installation_time);
		return self::$timestamp;
	}
}
