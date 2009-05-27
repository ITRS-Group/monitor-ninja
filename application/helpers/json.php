<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling JSON encode or decode
 * since we can't always know for sure that we have
 * the built-in PHP json_encode function and should
 * use ZEND_Json instead.
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class json_Core
{
	/**
	 * Encode variable data into JSON
	 *
	 * @param 	mixed $var
	 * @return 	string
	 */
	public static function encode($var = false)
	{
		if (empty($var)) {
			return false;
		}
		$json_str = false;
		if (!function_exists('json_encode')) {
			$json = zend::instance('json');
			$json_str = $json->encode($var);
		} else {
			$json_str = json_encode($var);
		}
		return $json_str;
	}

	/**
	 * Decode JSON data into PHP
	 *
	 * @param 	str $var
	 * @return	 mixed JSON decoded data
	 */
	public static function decode($var = false)
	{
		if (empty($var)) {
			return false;
		}
		$return = false;
		if (!function_exists('json_decode')) {
			$json = zend::instance('json');
			$return = $json->decode($var);
		} else {
			$return = json_decode($var);
		}
		return $return;
	}
}