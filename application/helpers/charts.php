<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Charts helper class
 */
class charts
{
	public static function load($type='pie')
	{
		$path = Kohana::find_file('vendor','mfchart/'.ucfirst($type).'Chart');
		if ($path !== false) {
			require_once(dirname($path).'/Utilities.php');
			require_once(dirname($path).'/Gradient.php');

			require_once(dirname($path).'/Chart.php');
			require_once(dirname($path).'/BarChart.php');
			require_once(dirname($path).'/PieChart.php');
			require_once(dirname($path).'/LinePointChart.php');

			if (!defined('FONT_TAHOMA'))
				define("FONT_TAHOMA", dirname($path).'/fonts/tahoma.ttf');
			if (!defined("FONT_DEJAVUSANS"))
				define("FONT_DEJAVUSANS", dirname($path).'/fonts/DejaVuSans.ttf');
			if (!defined("FONT_DEJAVUSANS_CONDENSED"))
				define("FONT_DEJAVUSANS_CONDENSED", dirname($path).'/fonts/DejaVuSansCondensed.ttf');
			if (!defined("FONT_DEJAVUSERIF"))
				define("FONT_DEJAVUSERIF", dirname($path).'/fonts/DejaVuSerif.ttf');
			if (!defined("FONT_DEJAVUSERIF_CONDENSED"))
				define("FONT_DEJAVUSERIF_CONDENSED", dirname($path).'/fonts/DejaVuSerifCondensed.ttf');

			include_once( $path );

			return $path;
		}
		return false;
	}
}
