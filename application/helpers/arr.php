<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Array Help class
 */
class arr_Core
{
	/**
	 * this just lets us safely access array variables
	 * that might not be set, optionally specifying a default
	 * to return in case the variable isn't found.
	 * Note that $k (for key) can be an array
	 */
	public static function search($ary, $k, $def = false)
	{
		if (is_array($k))
			$try = $k;
		else
			$try = array($k);

		foreach ($try as $k)
			if (isset($ary[$k]))
				return $ary[$k];

		return $def;
	}

}
