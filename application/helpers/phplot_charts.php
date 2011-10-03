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

function phplot_color_index_by_state_color($type='host', $state=false) {
	$arr = Reports_Controller::$colors;
	$colors['host'] = array(
		Reports_Model::HOST_UP => $arr['green'],
		Reports_Model::HOST_DOWN => $arr['red'],
		Reports_Model::HOST_UNREACHABLE => $arr['orange'],
		Reports_Model::HOST_PENDING => $arr['grey']
	);
	$colors['service'] = array(
		Reports_Model::SERVICE_OK => $arr['green'],
		Reports_Model::SERVICE_WARNING => $arr['orange'],
		Reports_Model::SERVICE_CRITICAL => $arr['red'],
		Reports_Model::SERVICE_UNKNOWN => $arr['grey'],
		Reports_Model::SERVICE_PENDING => $arr['grey']
	);
	$phplot_color_array = array(
		$arr['green'],
		$arr['grey'],
		$arr['orange'],
		$arr['red']
	);
	$spelled_out_color = $colors[$type][$state];
	return array_search($spelled_out_color, $phplot_color_array);
}

/**
 * PHPlot needs a global function as callback for registering custom
 * data colors.
 *
 * @see http://phplot.sourceforge.net/phplotdocs/conc-colors-datacolor-callback.html
 * @return string rgb
 */
function color_the_trends_graph($image, $passthrough, $row, $column, $extra = 0) {
	static $counter;
	if(!$counter) {
		$counter = 0;
	}
	$color = phplot_color_index_by_state_color($passthrough[$counter]['object_type'], $passthrough[$counter]['state']);
	//echo Kohana::debug($color);
	//$color = '#FF0000';
	//$color = '#00aa00';
	$counter++;
	return $color;
}
