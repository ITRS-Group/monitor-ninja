<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Model for generating trend graphs
 */
class Trends_graph_Model extends Model
{

	/**
	 * Location temporary image files
	 */
	private $tmp_name_placeholder = "/tmp/%s.png";

	/**
	 * Src attribute for image files
	 */
	private $src_placeholder = "line_point_chart/%s";

	/**
	 * Holds weekday labels
	 */
	private $abbr_day_names = array();

	public function __construct() {
		$translate = zend::instance('Registry')->get('Zend_Translate');
		$this->abbr_day_names = array(
			$translate->_('Sun'),
			$translate->_('Mon'),
			$translate->_('Tue'),
			$translate->_('Wed'),
			$translate->_('Thu'),
			$translate->_('Fri'),
			$translate->_('Sat')
		);
	}

	/**
	 * Format the x-axis of the graph accordingly to input dates
	 *
	 * @param $report_start unix timestamp
	 * @param $report_end unix timestamp
	 * @return ['resolution_names', 'offset', 'time_interval', 'end_offset']
	 */
	private function _get_chart_scope($report_start, $report_end) {
		$use_abbr_day_names = false;
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

		$time_interval = false;
		$correction_format = 'Y-m-d';
		if ($days <= 1) {
			# 'today', 'last24hours', 'yesterday' or possibly custom:
			$df = 'H';
			$time_interval = 60*60;
			$correction_format = 'Y-m-d H:00:00';
			$time = strtotime(date($correction_format, $time));
			while ($time < $report_end) {
				$resolution_names[] = date($df, $time);
				$time += $time_interval;
			}
		} elseif(7 == $days) {
			# thisweek', last7days', 'lastweek':
			$time_interval = 86400;
			$time = strtotime(date($correction_format, $time));
			while ($time < $report_end) {
				$resolution_names[] = date($df, $time);
				$time += $time_interval;
			}
		} elseif($days > 90) {
			$prev = '';
			$df = 'M';
			$time_interval = 86400;
			$correction_format = 'Y-m-01';
			$time = strtotime(date($correction_format, $time));
			while ($time < $report_end) {
				$h = date($df, $time);
				if ($prev != $h) {
					$resolution_names[] = $h;
				}
				$time = strtotime("+1 month", $time);
				$prev = $h;
			}
		} elseif($days > 7) {
			$df = 'd';
			$time_interval = 86400;
			$time = strtotime(date($correction_format, $time));
			while ($time < $report_end) {
				$h = date($df, $time);
				$resolution_names[] = $h;
				$time += $time_interval;
			}
		} else {
			# < 7 days, custom report period, defaulting to day names
			$df = 'w';
			$time_interval = 24 * 60 * 60;
			while ($time < $report_end) {
				$h = date($df, $time);
				$use_abbr_day_names = true;
				$resolution_names[] = $this->abbr_day_names[$h];
				$time += $time_interval;
			}
		}

		$offset = 0;
		// Does date() add one hour to timestamp? In that case, adjust
		if($report_start - strtotime(date($correction_format, $report_start))) {
			$offset = $time_interval - ( $report_start - strtotime(date($correction_format, $report_start)) );
		}

		$end_offset = 0;
		// Does date() add one hour to timestamp? In that case, adjust
		if($report_end - strtotime(date($correction_format, $report_end))) {
			$end_offset = $report_end - strtotime(date($correction_format, $report_end));
		}

		// Add the last timestamp again (non enumerated),
		// have it removed later if necessary
		$last_timestamp = date($df, $report_end);
		if($use_abbr_day_names) {
			$last_timestamp = $this->abbr_day_names[$last_timestamp];
		}

		return array(
		        'resolution_names' => $resolution_names,
			'offset' => $offset,
			'time_interval' => $time_interval,
			'end_offset' => $end_offset
		);
	}

	/**
	 * Print image (including setting header) based on key. Kill request.
	 *
	 * @param $chart_key
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

	/**
	 * Get the filename for a chart key
	 */
	public function get_filename_for_key($chart_key) {
		return sprintf($this->tmp_name_placeholder, $chart_key);
	}

	/**
	 * Get the filename for a path
	 */
	public function get_filename_for_src($src) {
		$chart_key = pathinfo($src, PATHINFO_BASENAME);
		return $this->get_filename_for_key($chart_key);
	}

	/**
	 * A graph is generated based on input, and saved in tmp files. If the graph
	 * already has been generated, it's used.
	 *
	 * @param $data
	 * @param $report_start
	 * @param $report_end
	 * @param $title = null
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
	 * @param $data
	 * @param $report_start
	 * @param $report_end
	 * @param $title = null
	 * @param $fit_pdf = false
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
		$earliest_object_state_change_timestamp = false;
		// Group log entries by object type
		foreach($data as $current_object => $events) {
			foreach($events as $event) {
				if (isset($event['host_name']))
					$hosts[] = $event['host_name'];
				$object_type = strpos($current_object, ';') !== false ? 'service' : 'host';
				if(!isset($data_suited_for_chart[$current_object])) {
					$data_suited_for_chart[$current_object] = array();
				}
				if(false === $earliest_object_state_change_timestamp) {
					$earliest_object_state_change_timestamp = $event['the_time'];
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
		} while(file_exists($qualified_filename) && $strlen_needed <= strlen($encoded_image_name));

		$data = array();
		$remove_host_from_object_name = false;
		if(count(array_unique($hosts)) == 1) {
			$remove_host_from_object_name = true;
		}
		foreach($data_suited_for_chart as $service => $state_changes) {
			if($remove_host_from_object_name) {
				// Turn "linux-server1;FTP" into "FTP" if all objects are from "linux-server1"
				$service = preg_replace('/^([^;]*);/', null, $service);
			}
			$current_row = array($service);
			for($i = 0; $i < count($state_changes); $i++) {
				$bar_width = $state_changes[$i]['duration'] ;
				if ($bar_width < $smallest_visible_bar_width) {
					// @todo proper check previous & next values in array for extra pixels
					// if bar_width is too slim. Alternatively: check longest bar and
					// pad the rest.
					$bar_width = $smallest_visible_bar_width;
				}
				$current_row[] = $bar_width;
				$extra_information_phplot_colors[] = $state_changes[$i];
			}
			$data[] = $current_row;
		}

		phplot_charts::load();
		$plot = new PHPlot($graph_width, $graph_height, $qualified_filename);

		// hacked phplot features.. git log phplot.php for custom mods
		$chart_scope = $this->_get_chart_scope($report_start, $report_end);
		if($chart_scope['offset']) {
			$plot->first_x_at = $chart_scope['offset'] ;
		}

		$plot->x_labels = $chart_scope['resolution_names'];
		$plot->offset = $chart_scope['offset'];
		$plot->offset_end = $chart_scope['end_offset'];

		// original phplot methods
		$plot->SetCallback('data_color', 'color_the_trends_graph', $extra_information_phplot_colors);
		$colors = array_values(Reports_Controller::$colors);
		$plot->SetDataColors($colors);
		$plot->SetDataBorderColors($colors);
		$plot->SetPlotAreaWorld(null, null, $report_end-$report_start);
		$plot->SetDataValues($data);
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
	 * @param $data
	 * @param $report_start
	 * @param $report_end
	 * @param $title = null
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
 * @param $type = 'host'
 * @param $state = false
 * @return string|false
 */
function phplot_color_index_by_state_color($type='host', $state=false) {
	$arr = Reports_Controller::$colors;
	$colors['host'] = array(
		Reports_Model::HOST_UP => $arr['green'],
		Reports_Model::HOST_DOWN => $arr['red'],
		Reports_Model::HOST_UNREACHABLE => $arr['yellow'],
		Reports_Model::HOST_PENDING => $arr['grey'],
		Reports_Model::HOST_EXCLUDED => $arr['white']
	);
	$colors['service'] = array(
		Reports_Model::SERVICE_OK => $arr['green'],
		Reports_Model::SERVICE_WARNING => $arr['yellow'],
		Reports_Model::SERVICE_CRITICAL => $arr['red'],
		Reports_Model::SERVICE_UNKNOWN => $arr['orange'],
		Reports_Model::SERVICE_PENDING => $arr['grey'],
		Reports_Model::SERVICE_EXCLUDED => $arr['white']
	);
	$spelled_out_color = $colors[$type][$state];
	$index = array_search($spelled_out_color, array_values($arr));
	return $index;
}
