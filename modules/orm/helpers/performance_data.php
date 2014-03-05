<?php

defined('SYSPATH') or die('No direct access allowed.');

/**
 * Helper methods for performance data processing.
 *
 * This should only be used in ORM, to do the backend processing. Because this
 * can be done both for services and hosts, which is entirely similar, this is
 * exported to a helper, so it's available for both.
 */
class performance_data_Core {
	/**
	 * Process performance data and return as an array
	 *
	 * @param $perf_data_str string
	 * @return array
	 */
	public static function process_performance_data($perf_data_str) {
		/* Split string in data soruce part and performance part */
		preg_match_all(
			"/(?<=^|[\\s])('(?:\\\\.|[^\\\\'])*'|[^'\\s=][^\\s=]*)=([-0-9.]*|U)([%a-zA-Z]*)(?:;([^; ]*)(?:;([^; ]*)(?:;([-0-9.]*)(?:;([-0-9.]*)|)|)|)|)(?=$|[\\s])/",
			$perf_data_str, $matches, PREG_SET_ORDER);

		$perf_data = array ();

		/* Iterate over data sources */
		foreach ($matches as $ds) {
			$ds_raw = array_shift($ds); /* Full string */
			$ds_name = array_shift($ds);
			$ds_value = array_shift($ds);
			$ds_uom = array_shift($ds);

			/* Parse name, if quoted, strip it down to data source name */
			if (substr($ds_name, 0, 1) == '\'') {
				$ds_name = stripcslashes(substr($ds_name, 1, -1));
			}

			/* build object */
			$ds_obj = array ();

			if ($ds_value !== '' && $ds_value !== 'U') {
				$ds_obj['value'] = (float) $ds_value;
			}
			if ($ds_uom !== '') {
				$ds_obj['unit'] = $ds_uom;
			}
			if (isset($ds[0]) && $ds[0] !== '') {
				$ds_obj['warn'] = $ds[0];
			}
			if (isset($ds[1]) && $ds[1] !== '') {
				$ds_obj['crit'] = $ds[1];
			}
			if (isset($ds[2]) && $ds[2] !== '') {
				$ds_obj['min'] = (float) $ds[2];
			} else if ($ds_uom == '%') {
				$ds_obj['min'] = 0.0;
			}
			if (isset($ds[3]) && $ds[3] !== '') {
				$ds_obj['max'] = (float) $ds[3];
			} else if ($ds_uom == '%') {
				$ds_obj['max'] = 100.0;
			}

			$perf_data[$ds_name] = $ds_obj;
		}
		return $perf_data;
	}
}