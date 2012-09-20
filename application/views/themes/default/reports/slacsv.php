<?php defined('SYSPATH') OR die('No direct access allowed.');

$fields = array('YEAR', 'MONTH', 'REAL VALUE', 'SLA VALUE', 'COMPLIANCE');
array_unshift($fields, strtoupper($options['type']));

$csv = array();
foreach ($fields as $field) {
	$csv[] = '"' . $field . '"';
}
echo implode(', ', $csv)."\n";

if (is_array($data_arr)) {
	foreach ($data_arr as $k => $data) {
		if (!is_numeric($k))
			continue;
		$table = $data['table_data'];
		foreach ($table as $table_data) {
			foreach ($table_data as $start => $result) {
				$csv = array();
				$csv[] = '"'.implode(',', $data['source']).'"';
				$csv[] = date('Y', $start);
				$csv[] = date('M', $start);
				$csv[] = $result[1];
				$csv[] = $result[0];
				$csv[] = (int)($result[1] >= $result[0]);
				echo implode(', ', $csv)."\n";
			}
		}
	}
}
