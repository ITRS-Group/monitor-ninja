<?php defined('SYSPATH') OR die('No direct access allowed.');
$fields = csv::avail_fields($options['report_type']);

$csv = false;
foreach ($fields as $field) {
	$csv[] = '"' . $field . '"';
}
echo implode(', ', $csv)."\n";

if (!arr::search($data_arr, 0)) // single item
	$data_arr = array($data_arr);

foreach ($data_arr as $k => $data) {
	if (!is_numeric($k))
		continue;
	$states = $data['states'];

	$csv = false;
	foreach ($fields as $field_name) {
		if ($field_name == 'HOST_NAME' || $field_name == 'SERVICE_DESCRIPTION') {
			$csv[] = '"' . implode(', ', $states[$field_name]) . '"';
		} else if ($field_name == 'HOSTGROUPS' || $field_name == 'SERVICEGROUPS') {
			$csv[] = '"' . $data['groupname'] . '"';
		} else if (isset($states[$field_name])) {
			$csv[] = strstr($field_name, 'PERCENT') ? '"'.reports::format_report_value($states[$field_name]).'%"' : $states[$field_name];
		} else {
			$csv[] = '';
		}
	}
	echo implode(', ', $csv)."\n";
}
