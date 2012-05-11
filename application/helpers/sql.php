<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * SQL Help class
 */
class sql_Core
{
	/**
	 * Parse the limit string and split into
	 * more sql standard LIMIT (val) OFFSET (val)
	 */
	public function limit_parse($str)
	{
		$str = trim($str);
		if (empty($str))
			return false;
		$limit_str = false;
		if (strstr($str, ',')) {
			$limit_parts = explode(',', $str);
			if (!empty($limit_parts) && count($limit_parts)==2) {
				$limit = $limit_parts[1];
				$offset = $limit_parts[0];
				$limit_str = " LIMIT ".$limit." OFFSET ".$offset;
			}
		} elseif(is_numeric($str)) {
			$limit_str = !empty($str) ? ' LIMIT '.(int)$str : '';
		}

		return $limit_str;
	}

	/**
	*	Concatenate arguments for use in sql query
	*	Since we are using 3 arguments, this method
	* 	handles just this and nothing else.
	* 	Arguments 1 and 3 are assumed to be field names
	* 	and argument 2 i assumed to be a string.
	*/
	public static function concat($arg1, $arg2, $arg3)
	{
		switch (Kohana::config('database.default.connection.type'))
		{
			case 'mysql':
				return " CONCAT(".$arg1.", '".$arg2."', ".$arg3.") ";
				break;
			default:
				return " ".$arg1."||'".$arg2."'||".$arg3." ";
		}
	}
}
