<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Model for generating trend graphs
 */
class Trends_graph_Model extends Model
{

	/**
	* Fetch data to be used in the trends graph
	*
	* @param $data Data from get_sla_data
	* @return $data_chart Formated data to fit trends graph
	*/

	static public function format_graph_data ($data){

		$data_chart = array();
		$events = current($data);

		// Group log entries by object type
		foreach($data as $current_object => $events) {
			foreach($events as $event) {

				$output = '';
				if (isset($event['output']))
					$output = $event['output'];

				$object_type = strpos($current_object, ';') !== false ? 'service' : 'host';

				if(!isset($data_chart[$current_object])) {
					$data_chart[$current_object] = array();
				}

				$data_chart[$current_object][] =  array(
					'duration' => $event['duration'],
					'state' => $event['state'],
					'object_type' => $object_type,
					'output' => $output,
				);

			}
		}

		return $data_chart;
	}
}
