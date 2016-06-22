<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides a backend that autocomplete.js can query.
 */
class Form_Controller extends Ninja_Controller {

	/**
	 * @param $tables array = array()
	 * @param $term string = ""
	 */
	public function autocomplete (array $tables = array(), $term = "") {
		$tables = $this->input->get('tables', $tables);
		$term = $this->input->get('term', $term);
		$results = array();

		foreach ($tables as $table) {
			try {
				$settings = autocomplete::get_settings($table);
				$set = ObjectPool_Model::get_by_query(sprintf($settings['query'], html::specialchars($term)));
				foreach ($set->it(array('key', $settings['display']), array(), 15, 0) as $object) {
					$results[] = array(
						"name" => $object->get_readable_name(),
						"table" => $table,
						"key" => $object->get_key()
					);
				}
			} catch (ORMException $e) {
				op5log::instance('ninja')->log('warning', __METHOD__.': '.$e->getMessage());
			}
		}
		json::ok($results);
	}

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
