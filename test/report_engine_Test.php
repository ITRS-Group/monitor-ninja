<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * We have a bunch of fixtures checked in, that represents events and the
 * expected calculated summaries after our report engine has transformed the
 * data.
 */
class Report_Engine_Test extends PHPUnit_Framework_TestCase {

	/**
	 * @group nonlocal
	 */
	public function test_make_sure_we_execute_tests_from_within_CET() {
		$current_offset = 3600 * (1 + date("I"));
		$this->assertEquals($current_offset, date::utc_offset(),
			"Report tests require CET as timezone (date.timezone".
			'= "CET" in php.ini, for example). If you want to,'.
			" feel free to rewrite the tests to not depend on ".
			"the current time");
	}

	public function report_test_files_provider() {
		$config = Kohana::config('database.default');
		if (isset($config['connection'])) {
			$connection = $config['connection'];
			if (isset($connection['database']) && $connection['database'])
				$db_name = $connection['database'];
			if (isset($connection['user']) && $connection['user'])
				$db_user = $connection['user'];
			if (isset($connection['pass']) && $connection['pass'])
				$db_pass = $connection['pass'];
			if (isset($connection['host']) && $connection['host'])
				$db_host = $connection['host'];
			if (isset($connection['type']) && $connection['type']) {
				$db_type = $connection['type'];
				// merlin import doesn't understand mysqli
				if ($db_type === "mysqli") {
					$db_type = "mysql";
				}
			}
		}

		$importer = '$(rpm --eval %{_libdir})/merlin/import --nagios-cfg=/tmp/ninja-test/nagios.cfg';

		$glob_path = __DIR__.'/reports/*.tst.php';
		$test_dir_glob = glob($glob_path);
		$this->assertGreaterThan(0, count($test_dir_glob), "$glob_path seems to be a bad glob path, found no test files in it");

		$tests = array();
		foreach ($test_dir_glob as $tfile) {
			$test = new Ninja_Reports_Test($tfile);
			$test->importer = $importer;
			$test->db_name = $db_name;
			$test->db_user = $db_user;
			$test->db_pass = $db_pass;
			$test->db_type = $db_type;
			$test->db_host = $db_host;
			$tests[] = array(
				$tfile,
				$test->description,
				$test
			);
		}
		return $tests;
	}

	/**
	 * @depends test_make_sure_we_execute_tests_from_within_CET
	 * @dataProvider report_test_files_provider
	 * @group nonlocal
	 */
	public function test_report_engine($test_file, $description, Ninja_Reports_Test $test) {
		ob_start();
		$failed_tests = $test->run_test_series();
		$test_result_output = ob_get_clean();
		$this->assertEquals(0, $failed_tests, $test_result_output);
	}
}
