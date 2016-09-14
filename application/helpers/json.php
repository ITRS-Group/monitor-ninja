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
	 * Contrary to json::fail(), this method will not exit() but rather
	 * return a prepared special View for you. This makes testing the
	 * result of a controller's method possible.
	 *
	 * @param $reason mixed
	 * @return View
	 */
	public static function fail_view($reason) {
		$view = new View('json');
		$view->success = false;
		$view->value = array('result' => $reason);
		return $view;
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
	 * Contrary to json::ok(), this method will not exit() but rather
	 * return a prepared special View for you. This makes testing the
	 * result of a controller's method possible.
	 *
	 * @param $result mixed
	 * @return View
	 */
	public static function ok_view($result) {
		$view = new View('json');
		$view->success = true;
		$view->value = array('result' => $result);
		return $view;
	}

	/**
	 * Serialize JSON data in pretty-printed form, in PHP < 5.4 compatible way.
	 *
	 * @param $data mixed The object to serialize
	 * @param $indent int Width of the indentation
	 * @returns string A plain-text, pretty-printed json representation of $data
	 */
	public static function pretty($data, $indent = 0)
	{
		$res = "";
		$offset = str_repeat(' ', $indent);
		// with PHP 5.4, this can be replaced with JSON_PRETTY_PRINT
		switch(gettype($data)) {
		case 'object':
		case 'array':
			$tmpres = json_encode($data);
			if ($tmpres[0] == '[') {
				$res .= "{$offset}[\n";
				foreach ($data as $val) {
					$res .= json::pretty($val, $indent + 2).",\n";
				}
				$res = substr($res, 0, -2)."\n";
				$res .= "{$offset}]\n";
			} else if ($tmpres[0] == '{') {
				$res .= "{$offset}{\n";
				$start = str_repeat(' ', $indent + 2);
				foreach ($data as $key => $val) {
					$res .= $start.json_encode((string)$key).": ".ltrim(json::pretty($val, $indent + 2)).",\n";
				}
				$res = substr($res, 0, -2)."\n";
				$res .= "{$offset}}";
			} else {
				$res .= $offset . json_encode($data);
			}
			break;
		default:
			$res .= $offset . json_encode($data);
		}
		return $res;
	}
}
