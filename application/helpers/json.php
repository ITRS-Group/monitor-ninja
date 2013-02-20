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
	 * @param int $http_status_code = 0
	 */
	private static function _send_response($response = null, $http_status_code = 200) {
		header('Content-Type: application/json');
		$exit = 0;
		if($http_status_code > 299) {
			$exit = 1;
			header("HTTP/1.0 $http_status_code");
		}
		echo json_encode($response);
		exit($exit);
	}

	/**
	 * Give it anything, it will turn it into JSON
	 *
	 * @param $reason string
	 * @param $http_status_code int = 500
	 */
	public static function fail($reason = null, $http_status_code = 500) {
		return self::_send_response($reason, $http_status_code);
	}

	/**
	 * Give it anything, it will turn it into JSON
	 *
	 * @param $result string
	 * @param $http_status_code int = 200
	 */
	public static function ok($result = null, $http_status_code = 200) {
		return self::_send_response($result, $http_status_code);
	}
}
