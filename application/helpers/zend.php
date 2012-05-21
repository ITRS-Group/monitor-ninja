<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana loader class for Zend
 */
class zend_Core
{
	/**
	 * Create and return instance of Zend class.
	 * Takes a Zend component/class name as input and tries to find
	 * and instantiate it.
	 * Example: $db = zend::instance('Db');
	 * 		Creates an instance of the Zend_Db class and returns it
	 * Note that some classes won't be able to call this way since their
	 * constructors are protected.
	 *
	 * @param $class string: Class name, case insensitive
	 * @return object on success. false on error
	 */
	public static function instance($class = false)
	{
		# sanitize input and make sure (only) first letter is uppercase
		$class = addslashes(trim(ucfirst(strtolower($class))));
		$obj = false;
		if (!empty($class)) {
			# load Zend module
			$path = self::set_zend_path($class);
			if (!empty($path)) {
				require_once($path);
				$classname = "Zend_".$class;
				if ($class == 'Auth') {
					# The Zend_Auth class implements the Singleton pattern - only one instance
					# of the class is available - through its static getInstance() method.
					# This means that using the new operator and the clone keyword will not
					# work with the Zend_Auth class; use Zend_Auth::getInstance() instead.
					$obj = Zend_Auth::getInstance();
				} else {
					$obj = @new $classname();
				}
			}
		}
		if (is_object($obj)) {
			return $obj;
		}
		return false;
	}

	/**
	 * Set include path to zend libraries
	 *
	 * @param $class string: Class-name
	 * @return path returned by kohana::find_file on succes. false on error
	 */
	public static function set_zend_path($class = false)
	{
		$filename = !empty($class) ? $class : 'Exception';
		$class = addslashes(trim(ucfirst(strtolower($class))));
		if ($path = Kohana::find_file('vendor', 'Zend/'.$filename))
		{
			ini_set('include_path',
			ini_get('include_path').PATH_SEPARATOR.dirname(dirname($path)));
			return $path;
		}
		return false;
	}
}
