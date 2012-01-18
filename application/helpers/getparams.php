<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * There seems to be a weird bug somewhere that I can't track down, where all
 * invalid characters are dropped from $_GET. Since Ninja is now UTF-8, there
 * is a high probabilitiy that there will be lots of invalid, latin1
 * characters. This method returns a GET parameter with them intact.
 */
class getparams_Core {
	static function get_raw_param ($desired_param, $default) {
		$querypieces = explode('&', $_SERVER['QUERY_STRING']);
		foreach ($querypieces as $param) {
			if (!strncmp($desired_param, $param, strlen($desired_param))) {
				return urldecode(substr($param, strlen($desired_param) + 1));
			}
		}
		if (isset($default))
			return $default;
		return false;
	}
}
?>
