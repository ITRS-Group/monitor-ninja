<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Nagvis config reader class
 */

Class nagvisconfig_Core {

	/**
	 * Function to read Nagvis config file and return array with key->values
	*/
	public function get($ConfigFile) {
		$raw = file_get_contents($ConfigFile);
		$lines = explode("\n", $raw);

		$data = array();
		$cat = '';
		foreach ($lines as $line) {
			// Comments and empty lines, don't care
			if (preg_match("/^;/", $line) || $line == "") {
				continue;
			}

			// A category tagged [name]
			if (preg_match("/^\[(.*)\]/", $line, $category)) {
				$cat = $category[1];
			}

			// A value under a category, key=value
			if (preg_match('/^(.*)="(.*)"/', $line, $values)) {
				$data[$cat][$values[1]] = str_replace('"', '', $values[2]);
			}
		}

		return $data;
	}
}
?>
