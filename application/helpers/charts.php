<?php defined('SYSPATH') OR die('No direct access allowed.');

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

			define("FONT_TAHOMA", dirname($path).'/fonts/tahoma.ttf');
			define("FONT_DEJAVUSANS", dirname($path).'/fonts/DejaVuSans.ttf');
			define("FONT_DEJAVUSANS_CONDENSED", dirname($path).'/fonts/DejaVuSansCondensed.ttf');
			define("FONT_DEJAVUSERIF", dirname($path).'/fonts/DejaVuSerif.ttf');
			define("FONT_DEJAVUSERIF_CONDENSED", dirname($path).'/fonts/DejaVuSerifCondensed.ttf');

			include_once( $path );

			return $path;
		}
		return false;
	}
}