<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana loader class for xajax
 *
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class get_xajax_Core {
	/**
	*
	*
	*/
	public function instance()
	{
		$path = self::path();
		if ($path !== false)
		{
			ini_set('include_path',
			ini_get('include_path').PATH_SEPARATOR.dirname(dirname($path)));
			require_once(dirname($path).'/xajax_core/xajax.inc.php');
			$classname = 'xajax';
			$obj = @new $classname();
			return $obj;
		}
		return false;
	}

	/**
	* Fetch xajax absolute path
	*/
	public function path()
	{
		$path = Kohana::find_file('vendor', 'xajax/copyright.inc');
		return $path;
	}

	/**
	*	Fetch and return xajax web path
	*/
	public function web_path()
	{
		return Kohana::config('config.site_domain').'application/vendor/xajax/xajax_core';
	}
}