<?php

error_reporting(E_ALL);
ini_set("display_errors","1");
@set_time_limit(10000);

define('SIMPLETEST_DIR', '../../lib/simpletest/');
require_once(SIMPLETEST_DIR.'unit_tester.php');
require_once(SIMPLETEST_DIR.'mock_objects.php');
require_once(SIMPLETEST_DIR.'reporter.php');

require_once('../config.inc.php');
require_once(HTML2PS_DIR.'pipeline.factory.class.php');

require_once('fetcher.memory.php');
require_once('generic.test.php');

$test = &new GroupTest('All tests');
$testfiles = glob('test.*.php');
foreach ($testfiles as $testfile) {
//  $test->addTestFile($testfile);
};
$test->addTestFile('test.white-space.php');
$test->run(new HtmlReporter());

?>