<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Function needed for making cross-database SQL
 */
class db_Core {
	/**
	 * Both mysql and oracle supports logical and, but in completely different
	 * ways. Workaround by generating a list of set bits in a bitmask.
	 *
	 * @param $bitmask A bitmask
	 * @return An array of the bits that were set
	 */
	public static function bitmask_to_array($bitmask) {
		$bits = array();
		while ($bitmask > 0) {
			$bitmask /= 2;
			if (is_int($bitmask))
				$bits[] = 0;
			else
				$bits[] = 1;
			$bitmask = (int)$bitmask;
		}
		return $bits;
	}

	/**
	 * Both mysql and oracle supports logical and, but in completely different
	 * ways. Workaround by generating a comma-separated string of the set bit values
	 * @param $bitmask A bitmask
	 * @return A string of the set bits
	 */
	public static function bitmask_to_string($bitmask) {
		$bits = self::bitmask_to_array($bitmask);
		$res = "";
		foreach ($bits as $bit => $is_set) {
			if ($is_set) {
				$res .= ",".$bit;
			}
		}
		return substr($res, 1);
	}
}
