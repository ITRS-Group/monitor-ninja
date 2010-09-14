<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * SQL Help class
 */
class sql_Core
{
	/**
	*	Parse the limit string and split into
	*	more sql standard LIMIT <val> OFFSET <val>
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
		} else
			$limit_str = !empty($limit) ? ' LIMIT '.$limit : '';

		return $limit_str;
	}
}
