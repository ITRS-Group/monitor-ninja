<?php

# Unit test for timeperiod parsing

$try_paths = array('../', '', 'reports-gui/');
while (($path = array_pop($try_paths)) !== false) {
	$path .= "lib_reports.php";
	if (is_file($path)) {
		require_once($path);
		$try_paths = true;
		break;
	}
}
if ($try_paths !== true) {
	echo "Failed to load lib_reports.php\n";
	exit(125); # "git bisect skip"
}

$report_class = new report_class();

# array, array, array, array, HOORAY!!
$test_cases =
	array(array('string' => "00:00-08:00,17:00-24:00",
				'correct' => Array(Array('start' => 0, 'stop' => 28800),
								   Array('start' => 61200, 'stop' => 86400))
				),
		  array('string' => '00:00-24:00',
				'correct' => array(array('start' => 0, 'stop' => 86400)))
		  );

function human_time($stamp)
{
	if (!$stamp)
		return 0;

	$days = $hours = $mins = $secs = false;
	if ($stamp > 86400)
		$days = $stamp / 86400;
	if ($stamp > 3600)
		$hours = ($stamp % 86400) / 3600;
	if ($stamp > 60)
		$mins = ($stamp % 3600) / 60;
	$secs = $stamp % 60;

	if ($days)
		return sprintf("%dd %dh %dm %ds", $days, $hours, $mins, $secs);
	if ($hours)
		return sprintf("%dh %dm %ds", $hours, $mins, $secs);
	if ($mins)
		return sprintf("%dm %ds", $mins, $secs);

	return $secs . "s";
}

$passed = $failed = 0;
foreach ($test_cases as $test_case) {
	echo "Testing '$test_case[string]' ... ";
	$reported = $report_class->tp_parse_day($test_case['string']);
	if ($reported === $test_case['correct']) {
		$passed++;
		echo "PASS\n";
	}
	else {
		$failed++;
		echo "FAIL\n";
	}
}
$report_class = new report_class();
foreach (array('mon', 'tues', 'wednes', 'thurs', 'fri') as $day)
	$report_class->set_option($day . 'day', '08:00-17:00');

$timestamp = 1202918145; # 2008-02-13 16:55:45 (wednesday)
$nstamp = $report_class->tp_next($timestamp, 'start');
echo "Testing initial 'start' ... ";
if ($nstamp != $timestamp) {
	echo date("$timestamp: Y-m-d H:i:s\n", $timestamp);
	echo date("$nstamp: Y-m-d H:i:s\n", $nstamp);
	echo "FAIL\n";
	$failed++;
}
else {
	echo "PASS\n";
	$passed++;
}

$timestamp = 1202918145 + 3600; # 2008-02-13 17:55:45 (wednesday)
$nstamp = $report_class->tp_next($timestamp, 'start');
echo "Testing initial 'stop' ... ";
if ($nstamp != 1202972400) {
	echo date("$timestamp: Y-m-d H:i:s\n", $timestamp);
	echo date("$nstamp: Y-m-d H:i:s\n", $nstamp);
	echo "FAIL\n";
	$failed++;
}
else {
	echo "PASS\n";
	$passed++;
}

$timestamp = 1203663600;
$nstamp = $report_class->tp_next($timestamp, 'start');
echo "Testing 'start' on 'start' boundary ... ";
if ($nstamp != 1203663600) {
	echo date("$timestamp: Y-m-d H:i:s\n", $timestamp);
	echo date("$nstamp: Y-m-d H:i:s\n", $nstamp);
	echo "FAIL\n";
	$failed++;
}
else {
	echo "PASS\n";
	$passed++;
}

echo "####################################\n\n";

$active_seconds =
	array(array('start' => 1203609000, 'stop' => 1203664200, 'expect' => 1200,
				'description' => 'start and stop in active'),
		  array('start' => 1203610200, 'stop' => 1203665400, 'expect' => 1800,
				'description' => 'start in inactive, stop in active'),
		  array('start' => 1203610200, 'stop' => 1203610200, 'expect' => 0,
				'description' => 'start and stop on inactive boundary'),
		  array('start' => 1203695400, 'stop' => 1203923400, 'expect' => 1200,
				'description' => 'start and stop in different weeks'),
		  array('start' => 1203318000, 'stop' => 1204009200, 'expect' => 194400,
				'description' => 'duration crosses more than one week')
		  );

echo "Testing tp_active_time()\n";
foreach ($active_seconds as $ary) {
	$start = $ary['start'];
	$stop = $ary['stop'];
	$expect = $ary['expect'];
	$description = $ary['description'];
	$active = $report_class->tp_active_time($start, $stop);
	printf("  %-7s %s\n", $active == $expect ? "OK" : "\n  FAILED", $description);
	if ($active != $expect) {
		echo "start: $start " . date("D Y-m-d H:i:s\n", $start);
		echo "stop:  $stop " . date("D Y-m-d H:i:s\n", $stop);
		echo "active=$active (" . human_time($active) . "); " .
			"expect=$expect (" . human_time($expect) . "); " .
			"delta=" . ($active - $expect) . "(" . human_time($active - $expect) . ")\n";
		$failed++;
		echo "####################################\n";
	}
	else
		$passed++;
}

echo "####################################\n";

echo "Passed: $passed\n";
echo "Failed: $failed\n";
if ($failed)
	exit(1);

?>
