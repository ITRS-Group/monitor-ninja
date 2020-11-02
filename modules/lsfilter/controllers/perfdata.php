<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides a backend for perfdata sources
 */
class Perfdata_Controller extends Ninja_Controller {

	/**
 	 * @param $table string
 	 * @param $key string
	 */
	public function perf_data_sources($table = '', $key = '') {
		$table = $this->input->get('table', $table);
		$key = $this->input->get('key', $key);
		if(!in_array($table, array('hosts', 'services'), true)) {
			return json::fail(array('message' =>
				'Can only fetch performance data sources for hosts and services'), 400);
		}
		$obj = ObjectPool_Model::pool($table)->fetch_by_key($key);
		if(!$obj) {
			return json::fail(array('message' =>
				'Could not find that object', 404));
		}
		return json::ok(array('result' => array_keys($obj->get_perf_data())));
	}

}
