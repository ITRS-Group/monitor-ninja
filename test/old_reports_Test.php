<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Very old version of ninja's report tests.
 */
class Old_Reports_Test extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass() {
		if(date::utc_offset() != 3600 * (1 + date("I"))) {
			echo "Aborting: Report tests require CET as timezone (date.timezone = \"CET\" in php.ini, for example). Got ".date::utc_offset()."\n";
			exit(1);
		}
	}

	public function report_test_files()
	{
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
			if (isset($connection['type']) && $connection['type'])
				$db_type = $connection['type'];
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
	 * @dataProvider report_test_files
	 */
	public function test_old_ninja_report_files($test_file, $description, Ninja_Reports_Test $test) {
		ob_start();
		$test_result = $test->run_test_series();
		$test_result_output = ob_get_clean();
		$this->assertNotEquals(-1, $test_result, $test_result_output);
	}
}
