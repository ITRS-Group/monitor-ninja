<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle timeperiod data
 */
class Timeperiod_Model extends Model
{
	/**
	 * Fetch info on a timeperiod
	 *
	 * @param $period string: Timeperiod name
	 * @param $include_exceptions bool: If true, include the exceptions for the timeperiod
	 * @return an array of the timeperiod's properties
	 */
	public function get($period, $include_exceptions=false)
	{
		$db = Database::instance();
		$query = 'SELECT * FROM timeperiod ' .
			'WHERE timeperiod_name = ' .$db->escape($period);
		$res = $db->query($query);
		if (!$res) {
			return false;
		}

		$res = $res->result(false);
		$res = $res->current();

		if ($include_exceptions && $res) {
			$query = "SELECT variable, value FROM custom_vars WHERE obj_type = 'timeperiod' AND obj_id = {$res['id']}";
			$exception_res = $db->query($query);
			foreach ($exception_res as $exception) {
				$res[$exception->variable] = $exception->value;
			}
		}

		return $res;
	}

	/**
	 * Join timeperiod with excludes
	 * @param $timeperiod_id int: Timeperiod id
	 * @param $use_array bool: Use array if true. Object if false.
	 * @return false on errors. Database result set on success.
	 */
	public function excludes($timeperiod_id=null, $include_exceptions=false)
	{
		if (empty($timeperiod_id))
			return false;
		$timeperiod_id = (int)$timeperiod_id;
		$db = Database::instance();
		$query = "SELECT tp.* FROM timeperiod tp".
				 " JOIN timeperiod_exclude ON exclude = id ".
				 " WHERE timeperiod = $timeperiod_id";

		$res = $db->query($query);
		if (!$res)
			return false;
		$res = $res->result_array(false);

		if ($include_exceptions) {
			foreach ($res as &$exclude) {
				$query = "SELECT variable, value FROM custom_vars WHERE obj_type = 'timeperiod' AND obj_id = {$exclude['id']}";
				$exception_res = $db->query($query);
				foreach ($exception_res as $exception) {
					$exclude[$exception->variable] = $exception->value;
				}
			}
		}
		return $res;
	}

	/**
	 * Fetch all timperiods
	 * @return db result
	 */
	public function get_all()
	{
		$return = "";
		$sql = "SELECT timeperiod_name FROM timeperiod ORDER BY timeperiod_name";
		$db = Database::instance();
		$res = $db->query($sql);
		return $res ? $res : false;
	}
}
