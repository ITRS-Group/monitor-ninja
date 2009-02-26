<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for user session data
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class user_Core {

	/**
	 * Fetch session data for certain variable
	 *
	 * @param	string $var
	 * @return	mixed value from session or false if not found
	 */
	public function session($var = false)
	{
		$return = false;
		return !empty(Auth::instance()->get_user()->$var) ? Auth::instance()->get_user()->$var : false;
		$user_data = Session::instance()->get('user_data', false);
		if (is_object($user_data) && isset($user_data->$var)) {
			$return = $user_data->$var;
		}
		if (!$return) {
			$return = Session::instance()->get($var, false);
		}
		return $return;
	}
}