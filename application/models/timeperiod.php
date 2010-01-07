<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle timeperiod data
 */
class Timeperiod_Model extends Model
{
	/**
	*	Fetch info on a timeperiod
	*/
	public function get($period, $use_array=true)
	{
		$db = new Database();
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
	*	Join timeperiod with excludes
	*/
	public function excludes($timeperiod_id=null, $use_array=true)
	{
		if (empty($timeperiod_id))
			return false;
		$timeperiod_id = (int)$timeperiod_id;
		$db = new Database();
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
	*	@name 	get_all
	*	@desc 	Fetch all timperiods
	*/
	public function get_all()
	{
		$return = "";
		$sql = "SELECT timeperiod_name FROM timeperiod ORDER BY timeperiod_name";
		$db = new Database();
		$res = $db->query($sql);
		return $res ? $res : false;
	}

}