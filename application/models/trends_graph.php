<?php defined('SYSPATH') OR die('No direct access allowed.');

class Trends_graph_Model extends Model
{

	/**
	 * Location temporary image files
	 *
	 * @var string
	 */
	private $tmp_name_placeholder = "/tmp/%s.png";

	/**
	 * Src attribute for image files
	 *
	 * @var string
	 */
	private $src_placeholder = "line_point_chart/%s";

	/**
	 * Format the x-axis of the graph accordingly to input dates
	 *
	 * @param int $report_start
	 * @param int $report_end
	 */
	private function _get_resolution_names($report_start, $report_end) {
		$resolution_names = array();
		$length = $report_end-$report_start;
		$days = floor($length/86400);
		$time = $report_start;
		$df = nagstat::date_format();
		$df_parts = explode(' ', $df);
		if (is_array($df_parts) && !empty($df_parts)) {
			$df = $df_parts[0];
		} else {
			$df = 'Y-m-d';
		}

		switch ($days) {
			case 1: # 'today', 'last24hours', 'yesterday' or possibly custom:
				$df = 'H';
				while ($time < $report_end) {
					$h = date($df, $time);
					$resolution_names[] = $h;
					$time += (60*60);
				}
				break;
			case 7: # thisweek', last7days', 'lastweek':
				while ($time < $report_end) {
					$resolution_names[] = date($df, $time);
					$time += 86400;
				}
				break;
			case ($days > 90) :
				$prev = '';
				$df = 'M';
				while ($time < $report_end) {
					$h = date($df, $time);
					if ($prev != $h) {
						$resolution_names[] = $h;
					}
					$time += 86400;
					$prev = $h;
				}

				break;
			case ($days > 7) :
				$df = 'd';
				while ($time < $report_end) {
					$h = date($df, $time);
					$resolution_names[] = $h;
					$time += 86400;
				}
				break;
			default: # < 7 days, custom report period, defaulting to day names
				$df = 'w';
				while ($time < $report_end) {
					$h = date($df, $time);
					$resolution_names[] = $this->abbr_day_names[$h];
					$time += 86400;
				}
				break;
		}
		$last_timestamp = date($df, $report_end);
		if(end($resolution_names) != $last_timestamp) {
			$resolution_names[] = $last_timestamp;
		}
		return $resolution_names;
	}

	/**
	 * Print image (including setting header) based on key. Kill request.
	 *
	 * @param string $chart_key
	 */
	public function display_chart($chart_key) {
		$filename = $this->get_filename_for_key($chart_key);
		if(!is_readable($filename)) {
			return;
		}
		header("Content-Type: ".mime_content_type($filename));
		header("Content-Length: ".filesize($filename));
		readfile($filename);
		unlink($filename);
		die;
	}

	public function get_filename_for_key($chart_key) {
		return sprintf($this->tmp_name_placeholder, $chart_key);
	}

	public function get_filename_for_src($src) {
		$chart_key = pathinfo($src, PATHINFO_BASENAME);
		return $this->get_filename_for_key($chart_key);
	}

	/**
	 * A graph is generated based on input, and saved in tmp files. If the graph
	 * already has been generated, it's used.
	 *
	 * @param array $data
	 * @param int $report_start
	 * @param int $report_end
	 * @param string $title = null
	 * @return string
	 */
	public function get_graph_pdf_src_for_data($data, $report_start, $report_end, $title = null) {
		return $this->_generate_graph($data, $report_start, $report_end, $title, true);
	}

	/**
	 * A graph is generated based on input, and saved in tmp files. If the graph
	 * already has been generated, it's used.
	 *
	 * @uses PHPlot
	 * @param array $data
	 * @param int $report_start
	 * @param int $report_end
	 * @param string $title = null
	 * @param boolean $fit_pdf = false
	 * @return string
	 */
	private function _generate_graph($data, $report_start, $report_end, $title = null, $fit_pdf = false) {

		$data_suited_for_chart = array();
		$events = current($data);

		// Guessed value from testing, feel free to make it better (+60 = heading)
		$graph_height = 60 + count($data) * ($fit_pdf ? 15 : 19);
		$max_graph_height_in_pdf = 900;
		if($fit_pdf && $graph_height > $max_graph_height_in_pdf) {
			$graph_height = $max_graph_height_in_pdf;
		}
		$graph_width = $fit_pdf ? 700 : 800;

		// In pixels. Set to > 0 to enable the expanding of narrow bars.
		$smallest_visible_bar_width = 0;

		$hosts = array();
		$number_of_objects = 0;
		// Group log entries by object type
		foreach($data as $current_object => $events) {
			foreach($events as $event) {
				$hosts[] = $event['host_name'];
				$object_type = isset($event['service_description']) && !empty($event['service_description']) ? 'service' : 'host';
				if(!isset($data_suited_for_chart[$current_object])) {
					$data_suited_for_chart[$current_object] = array();
				}
				$data_suited_for_chart[$current_object][] =  array(
					'duration' => $event['duration'],
					'state' => $event['state'],
					'object_type' => $object_type
				);
				$number_of_objects++;
			}
		}


		// Generate a unique filename that's short, based on data and doesn't already exist
		$encoded_image_name = md5(serialize(func_get_args()));
		$strlen_needed = 7;
		do {
			$chart_key = substr($encoded_image_name, 0, $strlen_needed);
			$qualified_filename = $this->get_filename_for_key($chart_key);
			$strlen_needed++;
		} while(file_exists($qualified_filename));

		$data = array();
		$remove_host_from_object_name = false;
		if(count(array_unique($hosts)) == 1) {
			$remove_host_from_object_name = true;
		}
		$seconds_per_pixel = ( $report_end - $report_start ) / $graph_width;
		foreach($data_suited_for_chart as $service => $state_changes) {
			if($remove_host_from_object_name) {
				// Turn "linux-server1;FTP" into "FTP" if all objects are from "linux-server1"
				$service = preg_replace('/^([^;]*);/', null, $service);
			}
			$current_row = array($service);
			for($i = 0; $i < count($state_changes); $i++) {
				$bar_width = $state_changes[$i]['duration'] / $seconds_per_pixel;
				if ($bar_width < $smallest_visible_bar_width) {
					// @todo proper check previous & next values in array for extra pixels
					// if bar_width is too slim. Alternatively: check longest bar and
					// pad the rest.
					$bar_width = $smallest_visible_bar_width;
				}
				$current_row[] = number_format($bar_width, 1, '.', null);
				$extra_information_phplot_colors[] = $state_changes[$i];
			}
			$data[] = $current_row;
		}

		phplot_charts::load();
		$plot = new PHPlot($graph_width, $graph_height, $qualified_filename);
		$plot->x_labels = $this->_get_resolution_names($report_start, $report_end);
		$plot->SetCallback('data_color', 'color_the_trends_graph', $extra_information_phplot_colors);
		$arr = Reports_Controller::$colors;
		$colors = array(
			$arr['green'],
			$arr['grey'],
			$arr['orange'],
			$arr['red']
		);
		$plot->SetDataColors($colors);
		$plot->SetDataBorderColors($colors);
		$plot->SetDataValues($data);
		//$plot->SetPlotAreaPixels(null, null, $graph_width);
		$plot->SetShading(0);
		$plot->SetFont('y_label', 2, 8);
		if($fit_pdf && $number_of_objects > 30) {
			$plot->SetFont('y_label', 1, 6);
		}
		$plot->SetDataType('text-data-yx');
		$plot->SetPlotType('stackedbars');
		if($title) {
			$plot->SetTitle($title);
		}
		$plot->SetYTickPos('none');
		$plot->SetXDataLabelPos('none'); // plotstack for inline label values
		$plot->SetFileFormat('png');
		$plot->SetIsInline(true);
		$plot->DrawGraph();

		return $fit_pdf ? $this->get_filename_for_key($chart_key) : sprintf($this->src_placeholder, $chart_key);
	}

	/**
	 * A graph is generated based on input, and saved in tmp files. If the graph
	 * already has been generated, it's used.
	 *
	 * @param array $data
	 * @param int $report_start
	 * @param int $report_end
	 * @param string $title = null
	 * @return string
	 */
	public function get_graph_src_for_data($data, $report_start, $report_end, $title = null) {
		return $this->_generate_graph($data, $report_start, $report_end, $title);
	}
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
	if(count($passthrough) == $counter) {
		// Weird bug: generating pdf uses up one extra iteration
		// so $counter must be reset when the color information
		// is all used up
		$counter = 0;
	}
	$color = phplot_color_index_by_state_color($passthrough[$counter]['object_type'], $passthrough[$counter]['state']);
	$counter++;
	return $color;
}

/**
 * Helper for the above function: color_the_trends_graph()
 *
 * @param string $type = 'host'
 * @param string $state = false
 * @return string|false
 */
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
