<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana helper class for pclzip
 */
class zip_Core {
	/**
	* Create new pclzip instance
	*/
	public function instance($filename=false)
	{
		$path = self::path();
		if ($path !== false)
		{
			require_once(dirname($path).'/pclzip.lib.php');
			$classname = 'PclZip';
			$obj = @new $classname($filename);
			return $obj;
		}
		return false;
	}

	/**
	* Fetch pclzip absolute path
	*/
	public function path()
	{
		$path = Kohana::find_file('vendor', 'pclzip/pclzip.lib', true);
		return $path;
	}
}