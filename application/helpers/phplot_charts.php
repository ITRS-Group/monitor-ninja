<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Charts helper class
 */
class phplot_charts_Core
{
	/**
	 * @param int $width = null
	 * @param int $height = null
	 * @return PHPlot
	 */
	public static function load($width = null, $height = null)
	{
		if(self::$_classmap) {
			// Classmap was already stored, thus the autoloader already
			// knows about the files locations
			return true;
		}
		require_once Kohana::find_file('vendor','phplot/phplot/phplot.php');
		return new PHPlot($width, $height);
	}
}
