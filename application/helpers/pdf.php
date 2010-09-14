<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * PDF help class
 */
class pdf_Core
{
	public function start()
	{
		$path = self::path();
		if ($path !== false)
		{
			ini_set('include_path',
			ini_get('include_path').PATH_SEPARATOR.dirname(dirname($path)));
			require_once($path);
			require_once(dirname($path).'/config/lang/eng.php');
			return true;
		}
		return false;
	}

	/**
	* Fetch TCPDF absolute path
	*/
	public function path()
	{
		$path = Kohana::find_file('vendor', 'tcpdf/tcpdf');
		return $path;
	}

}
