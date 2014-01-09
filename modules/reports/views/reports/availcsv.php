<?php defined('SYSPATH') OR die('No direct access allowed.');
$fields = csv::avail_fields($options['report_type']);

$csv = false;
foreach ($fields as $field) {
	$csv[] = '"' . $field . '"';
}
echo implode(', ', $csv)."\n";

if(!is_array($data_arr)) {
	return;
}
foreach ($data_arr as $key => $sub_report) {
	if (!is_array($sub_report) || !isset($sub_report['states']))
		continue;
	foreach ($sub_report as $k => $data) {
		if (!is_array($data) || !isset($data['states']))
			continue;
		$states = $data['states'];

		$csv = false;
		foreach ($fields as $field_name) {
			if ($field_name == 'HOST_NAME' || $field_name == 'SERVICE_DESCRIPTION') {
				$csv[] = '"' . (is_array($states[$field_name]) ? implode(', ', $states[$field_name]) : $states[$field_name]) . '"';
			} else if ($field_name == 'HOSTGROUPS' || $field_name == 'SERVICEGROUPS') {
				$csv[] = '"' . implode(', ', $sub_report['groupname']) . '"';
			} else if (isset($states[$field_name])) {
				$csv[] = strstr($field_name, 'PERCENT') ? '"'.reports::format_report_value($states[$field_name]).'%"' : $states[$field_name];
			} else {
				$csv[] = '';
			}
		}
		echo implode(', ', $csv)."\n";
	}
}
