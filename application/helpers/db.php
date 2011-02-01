<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Function needed for making cross-database SQL
 */
class db_Core {
	/*
	 * Both mysql and oracle supports logical and, but in completely different
	 * ways. Workaround by genereting a list of set bits in a bitmask.
	 */
	public static function bitmask_to_string($bitmask) {
		$bits = array();
		while ($bitmask > 0) {
			$bitmask /= 2;
			if (is_int($bitmask))
				$bits[] = 0;
			else
				$bits[] = 1;
			$bitmask = (int)$bitmask;
		}
		$res = "";
		foreach ($bits as $bit => $is_set) {
			if ($is_set) {
				$res .= ",".($bit+1);
			}
		}
		print_r($res);
		return substr($res, 1);
	}
}
