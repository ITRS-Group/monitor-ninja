#!/usr/bin/php
<?php

# Kohana likes to do exit() on us, so we must always fork new processes run
# tests in, generate coverage in the test, output that data from the forked
# process, parse it in the master process, and only then do we get the output.

require_once('application/vendor/phptap/generate_coverage.php');

$errors = 0;

if (count($argv) > 1)
	$prefix = $argv[1];
else
	$prefix = '/opt/monitor/op5/ninja';

$coverage = false;

function runTest($line)
{
	if (!$line)
		return;
	global $coverage;
	$data = explode(' ', $line);
	$test = escapeshellarg($data[0]);
	$user = escapeshellarg($data[1]);
	exec("/usr/bin/php ".dirname(__FILE__)."/testcoverage.php $test $user", $output, $code);
	eval('$test_coverage = '.implode(" ", $output).';');
	foreach ($test_coverage as $file => $lines) {
		if (!isset($coverage[$file])) {
			$coverage[$file] = $lines;
			continue;
		}
		foreach ($lines as $line => $state) {
			if (!isset($coverage[$file][$line])) {
				$coverage[$file][$line] = $state;
				continue;
			}
			$coverage[$file][$line] = max($coverage[$file][$line], $state);
		}
	}
}

# first, report tests
exec("/usr/bin/php $prefix/test/testcoverage.php ninja_unit_test/reports modules/unit_test/reports/*.tst", $output, $code);
eval('$coverage = '.implode(' ', $output).';');

# ci tests
$files = array('test/ci/ninjatests.txt', 'test/ci/limited_tests.txt');
foreach ($files as $file) {
	$h = fopen($file, 'rb');
	while ($line = fgets($h)) {
		$line = trim($line);
		runTest($line);
	}
}

exit(generate_coverage($coverage));
