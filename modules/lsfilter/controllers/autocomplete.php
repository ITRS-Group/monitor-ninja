<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides a backend that autocomplete.js can query.
 */
class Autocomplete_Controller extends Ninja_Controller {

	/**
	 * @param $tables array = array()
	 * @param $term string = ""
	 */
	public function autocomplete (array $tables = array(), $term = "") {
		$tables = $this->input->get('tables', $tables);
		$term = $this->input->get('term', $term);
		$ac = Autocompleter::from_manifests();
		$results = $ac->query($term, $tables);
		return json::ok($results);
	}

}
