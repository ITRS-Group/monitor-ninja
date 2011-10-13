<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Charts helper class
 */
class phplot_charts_Core
{
	/**
	 * Autoload phplot, now you can use new PHPlot()
	 *
	 * This is how you save a file:
	 *
	 * <code>
	 * $plot = new PHPlot($height, $width, $filename);
	 * $plot->SetFileFormat('png');
	 * $plot->SetIsInline(true);
	 * // ...
	 * $plot->DrawGraph();
	 * </code>
	 */
	public static function load()
	{
		require_once Kohana::find_file('vendor','phplot/phplot/phplot');
	}
}
