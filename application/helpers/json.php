<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling JSON encode or decode
 * since we can't always know for sure that we have
 * the built-in PHP json_encode function and should
 * use ZEND_Json instead.
 */
class json_Core
{

	/**
	 * Kills the request after echoing a structured json response
	 *
	 * @param array $response = null
	 * @param int $exit_code = 0
	 */
	private static function _send_response($response = null, $exit_code = 0) {
		echo self::encode($response);
		exit($exit_code);
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

	/**
	 * Encode variable data into JSON
	 *
	 * @param $var Variable to encode
	 * @return false on error, json-encoded string on success.
	 */
	public static function encode($var = false)
	{
		if (empty($var) && !is_array($var)) {
			return false;
		}
		if (function_exists('json_encode')) {
			return json_encode($var);
		}

		$json = zend::instance('json');
		return $json->encode($var);
	}

	/**
	 * [error] => message
	 *
	 * @param string $reason = null
	 */
	public static function fail($reason = null) {
		return self::_send_response(array('error' => $reason), 1);
	}

	/**
	 * [result] => message
	 *
	 * @param string $result = null
	 */
	public static function ok($result = null) {
		return self::_send_response(array('result' => $result));
	}
}
