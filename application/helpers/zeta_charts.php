<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Charts helper class
 */
class charts_zeta_Core
{
	private static $_classmap;

	public static function autoload($name)
	{
		if(!array_key_exists($name, self::$_classmap)) {
			return false;
		}
		require_once self::$_classmap[$name];
		return true;
	}

	public static function load()
	{
		if(self::$_classmap) {
			// Classmap was already stored, thus the autoloader already
			// knows about the files locations
			return true;
		}
		$path = Kohana::find_file('vendor','zeta/Graph/src/graph_autoload.php');
		if ($path !== false) {
			self::$_classmap = require_once $path;
			spl_autoload_register(array(__CLASS__, 'autoload'));

			echo Kohana::debug(Gotit);die;
		}
		return false;
	}
}
