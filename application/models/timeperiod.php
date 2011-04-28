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
	 * @param $use_array bool: Use array if true. Use object if false.
	 * @return db result
	 */
	public function get($period, $use_array=true)
	{
		$db = Database::instance();
		$query = 'SELECT * FROM timeperiod ' .
			'WHERE timeperiod_name = ' .$db->escape($period);
		$res = $db->query($query);
		if (!$res) {
			return false;
		}

		if ($use_array === true)
			$res = $res->result(false); # use arrays instead of objects
		return $res;
	}

	/**
	 * Join timeperiod with excludes
	 * @param $timeperiod_id int: Timeperiod id
	 * @param $use_array bool: Use array if true. Object if false.
	 * @return false on errors. Database result set on success.
	 */
	public function excludes($timeperiod_id=null, $use_array=true)
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
		if ($use_array === true)
			$res->result(false);
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
