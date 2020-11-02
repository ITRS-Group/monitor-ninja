<?php
/*
	Will parse csv output from file specified below
	and generate test case
*/

$s_header = 'HOST_NAME,
TIME_UP_SCHEDULED,
PERCENT_TIME_UP_SCHEDULED,
PERCENT_KNOWN_TIME_UP_SCHEDULED,
TIME_UP_UNSCHEDULED,
PERCENT_TIME_UP_UNSCHEDULED,
PERCENT_KNOWN_TIME_UP_UNSCHEDULED,
TOTAL_TIME_UP,
PERCENT_TOTAL_TIME_UP,
PERCENT_KNOWN_TIME_UP,
TIME_DOWN_SCHEDULED,
PERCENT_TIME_DOWN_SCHEDULED,
PERCENT_KNOWN_TIME_DOWN_SCHEDULED,
TIME_DOWN_UNSCHEDULED,
PERCENT_TIME_DOWN_UNSCHEDULED,
PERCENT_KNOWN_TIME_DOWN_UNSCHEDULED,
TOTAL_TIME_DOWN,
PERCENT_TOTAL_TIME_DOWN,
PERCENT_KNOWN_TIME_DOWN,
TIME_UNREACHABLE_SCHEDULED,
PERCENT_TIME_UNREACHABLE_SCHEDULED,
PERCENT_KNOWN_TIME_UNREACHABLE_SCHEDULED,
TIME_UNREACHABLE_UNSCHEDULED,
PERCENT_TIME_UNREACHABLE_UNSCHEDULED,
PERCENT_KNOWN_TIME_UNREACHABLE_UNSCHEDULED,
TOTAL_TIME_UNREACHABLE,
PERCENT_TOTAL_TIME_UNREACHABLE,
PERCENT_KNOWN_TIME_UNREACHABLE,
TIME_UNDETERMINED_NOT_RUNNING,
PERCENT_TIME_UNDETERMINED_NOT_RUNNING,
TIME_UNDETERMINED_NO_DATA,
PERCENT_TIME_UNDETERMINED_NO_DATA,
TOTAL_TIME_UNDETERMINED,
PERCENT_TOTAL_TIME_UNDETERMINED';

// parameters that we actually are interested in
// service params
$svc_return_values = 	array('SERVICE_DESCRIPTION',
	'TIME_OK_SCHEDULED',
	'TIME_OK_UNSCHEDULED',
	'TIME_WARNING_SCHEDULED',
	'TIME_WARNING_UNSCHEDULED',
	'TIME_UNKNOWN_SCHEDULED',
	'TIME_UNKNOWN_UNSCHEDULED',
	'TIME_CRITICAL_SCHEDULED',
	'TIME_CRITICAL_UNSCHEDULED',
	'TIME_UNDETERMINED_NOT_RUNNING',
	'TIME_UNDETERMINED_NO_DATA'
);
// host params
$return_values = array(	'TIME_UP_SCHEDULED',
	'TIME_UP_UNSCHEDULED',
	'TIME_DOWN_SCHEDULED',
	'TIME_DOWN_UNSCHEDULED',
	'TIME_UNREACHABLE_SCHEDULED',
	'TIME_UNREACHABLE_UNSCHEDULED',
	'TIME_UNDETERMINED_NOT_RUNNING',
	'TIME_UNDETERMINED_NO_DATA');

// Read csv data from file
$_newfile = file('apa1');

$header = explode(',', $s_header);

/*
	Hard coded start- and end time
	Set $is_service below to true to get servicename
	and correct return values array
*/
$start_time = 1196463600;
$end_time 	= 1199142000;
$is_service	= false;

echo "<pre>";

for ($i=1;$i<sizeof($_newfile);$i++) {
	// loop through all lines in _newfile
	$newfile = explode(',', $_newfile[$i]);

	$hostname = str_replace('"', '', $newfile[0]);
	if ($is_service) {
		$service = str_replace('"', '', $newfile[1]);
		$return_array_values = $svc_return_values;
	} else {
		$return_array_values = $return_values;
	}

	echo "test case ".$hostname." {\n";
	echo "\tstart_time = $start_time
\tend_time = $end_time
\thostname = ".$hostname;
	if ($is_service)
		echo "\tservice = ".$service;
	echo "\n";

	// print correct values
	echo "\tcorrect {";
	for ($a=0;$a<sizeof($newfile);$a++) {
		if (in_array(trim($header[$a]), $return_array_values)) {
			echo "\n\t\t".trim($header[$a]) . " =" . $newfile[$a];
		}
	}
	echo "
	\t}
}\n\n";
}

echo "</pre>";

?>