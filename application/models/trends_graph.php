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
	 * Print image (including setting header) based on key. Kill request.
	 *
	 * @param string $chart_key
	 */
	public function display_chart($chart_key) {
		$filename = sprintf($this->tmp_name_placeholder, $chart_key);
		header("Content-Type: ".mime_content_type($filename));
		readfile($filename);
		die;
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
	 * @param array $resolution_names = array()
	 * @return string
	 */
	public function get_graph_src_for_data($data, $report_start, $report_end, $title = null, $resolution_names = array()) {
		$data_suited_for_chart = array();
		$events = current($data);
		// guessed value from testing, feel free to make it better (+60 = heading)
		$graph_height = 60 + count($data) * 30;
		$graph_width = 800;
		$smallest_visible_bar_width = 4;

		$hosts = array();
		// Group log entries by object type
		foreach($data as $current_object => $events) {
			// @todo remove  stuff
			//if($current_object != 'DNS') continue;
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
			}
		}

		$seconds_per_pixel = ( $report_end - $report_start ) / $graph_width;

		// Generate a unique filename that's short, based on data and doesn't already exist
		$encoded_image_name = md5(serialize(func_get_args()));
		$strlen_needed = 7;
		do {
			$chart_key = substr($encoded_image_name, 0, $strlen_needed);
			$qualified_filename = sprintf($this->tmp_name_placeholder, $chart_key);
			$strlen_needed++;
		} while(file_exists($qualified_filename));

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
		foreach($resolution_names as $key => $name) {
			// Ugly fix for viewing longer time periods: count($resolution_names) == ~300
			// for a year when we want 12 (one label/month)
			if(' &nbsp;' == $name) {
				unset($resolution_names[$key]);
			}
		}
		$plot->x_labels = $resolution_names;
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
		$plot->SetShading(0);
		$plot->SetFont('y_label', 2, 8);
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

		return "line_point_chart/$chart_key";
	}
}
