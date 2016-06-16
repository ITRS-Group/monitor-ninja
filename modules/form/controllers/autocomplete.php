<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides a backend that autocomplete.js can query.
 */
class Autocomplete_Controller extends Ninja_Controller {

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

}
