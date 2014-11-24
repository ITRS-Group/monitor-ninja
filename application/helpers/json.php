<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Quickly kill request while serving it(s content) as JSON
 */
class json
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

	/**
	 * Serialize JSON data in pretty-printed form, in PHP < 5.4 compatible way.
	 *
	 * @param $data mixed The object to serialize
	 * @param $base_offset int Indentation control
	 * @returns string A plain-text, pretty-printed json representation of $data
	 */
	public static function pretty($data, $base_offset = 0)
	{
		$res = "";
		$offset = str_repeat(' ', $base_offset);
		// with PHP 5.4, this can be replaced with JSON_PRETTY_PRINT
		switch(gettype($data)) {
		case 'object':
		case 'array':
			$tmpres = json_encode($data);
			if ($tmpres[0] == '[') {
				$res .= "{$offset}[\n";
				foreach ($data as $val) {
					$res .= json::pretty($val, $base_offset + 2);
				}
				$res .= "{$offset}]\n";
			} else if ($tmpres[0] == '{') {
				$res .= "{$offset}{\n";
				foreach ($data as $key => $val) {
					$start = str_repeat(' ', $base_offset + 2);
					$res .= "$start".json_encode($key).": ".ltrim(json::pretty($val, $base_offset + 2));
				}
				$res .= "{$offset}}\n";
			} else {
				$res .= $offset . json_encode($data). "\n";
			}
			break;
		default:
			$res .= $offset . json_encode($data). "\n";
		}
		return $res;
	}
}
