<?php defined('SYSPATH') OR die('No direct access allowed.');

$fields = array('YEAR', 'MONTH', 'REAL VALUE', 'SLA VALUE', 'COMPLIANCE');
array_unshift($fields, strtoupper($options['report_type']));

$csv = array();
foreach ($fields as $field) {
	$csv[] = '"' . $field . '"';
}
echo implode(', ', $csv)."\n";

if (is_array($data_arr)) {
	foreach ($data_arr as $k => $data) {
		if (!isset($data['table_data']))
			continue;
		$table = $data['table_data'];
		foreach ($table as $start => $result) {
			$csv = array();
			if (!empty($data['name']))
				$csv[] = '"'.$data['name'].'"';
			else
				$csv[] = '"'.implode(',', $data['source']).'"';
			$csv[] = date('Y', $start);
			$csv[] = '"'.date('M', $start).'"';
			$csv[] = (float)$result[0];
			$csv[] = (float)$result[1];
			$csv[] = (int)($result[0] >= $result[1]);
			echo implode(', ', $csv) . "\n";
		}
	}
}
