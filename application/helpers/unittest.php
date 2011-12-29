<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana help class for unit tests
 *
 * @author     op5 AB
 */
class unittest_Core {

	/**
	 * Create and return a new PHPTap instance
	 *
	 * @return object
	 */
	public function instance()
	{
		$path = self::tap_path();
		if ($path !== false) {
			require_once($path);
			$tap = new phptap();
			return $tap;
		}
		return false;
	}

	/**
	*	Check if testfile is available
	*/
	public function get_testfile($file = false)
	{
		$file = trim($file);
		if (empty($file))
			return false;

		return Kohana::find_file('views', 'tests/'.$file);
	}

	/**
	*	Return path to phptap
	*/
	public function tap_path()
	{
		return Kohana::find_file('vendor', 'phptap/phptap');
	}
}
