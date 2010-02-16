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
	 * @param $var Variable to encode
	 * @return false on error, json-encoded string on success.
	 */
	public static function encode($var = false)
	{
		if (empty($var)) {
			return false;
		}
		if (function_exists('json_encode')) {
			return json_encode($var);
		}

		$json = zend::instance('json');
		return $json->encode($var);
	}

	/**
	 * Decode JSON data into PHP
	 *
	 * @param $var json-encoded string to decode
	 * @return false on error, json-decoded data on success
	 */
	public static function decode($var = false)
	{
		if (empty($var)) {
			return false;
		}
		if (function_exists('json_decode')) {
			return json_decode($var);
		}
		$json = zend::instance('json');
		return $json->decode($var);
	}
}
