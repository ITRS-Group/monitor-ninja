<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Unit_Test controller.
 */
class Ninja_unit_test_Controller extends Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index($user=false)
	{
		$authentic = new Auth;
		Auth::instance()->force_login($user);

		// Run tests and show results!
		echo new Ninja_Unit_Test;
	}

	/**
	*
	*
	*/
	public function reports()
	{
		#$authentic = new Auth;
		#Auth::instance()->force_login($user);

		$test_results = array();
		$db_name = 'merlin';
		$db_user = 'merlin';
		$db_pass = 'merlin';
		$importer = 'mon log import';
		$test_file = array();
		$argv = isset($argv) ? $argv : $GLOBALS['argv'];
		$argc = isset($argc) ? $argc : $GLOBALS['argc'];
		for ($i = 1; $i < $argc; $i++) {
			switch ($argv[$i]) {
			 case '--importer':
				$importer = $argv[$i + 1];
				break;
			 case '--db-name':
				$db_name = $argv[$i + 1];
				break;
			 case '--db-user':
				$db_user = $argv[$i + 1];
				break;
			 case '--db-pass':
				$db_user = $argv[$i + 1];
				break;
			}
			if (substr($argv[$i], 0, 2) == '--') {
				$i++;
				continue;
			}

			if (is_file($argv[$i])) {
				echo "Adding $argv[$i] to testfiles\n";
				$test_file[] = $argv[$i];
			}
		}
		$passed = 0;
		$failed = 0;
		foreach ($test_file as $tfile) {
			$test = new Ninja_Reports_Test($tfile);
			$test->importer = $importer;
			$test->db_name = $db_name;
			$test->db_user = $db_user;
			$test->db_pass = $db_pass;
			if ($test->run_test_series() === -1) {
				echo "    $test->test_file : '$test->description' failed to run\n";
				exit(1);
			}
			$all[] = $test;
			$passed += $test->passed;
			$failed += $test->failed;
		}
		if ($failed) {
			echo "Passed: $passed\n";
			echo "Failed: $failed\n";
			echo "Failed test-cases:\n";
			foreach ($all as $test) {
				if (!empty($test->failed))
					echo "    " . $test->test_file . ", '" . $test->description . "'\n";
			}
			exit(1);
		}

		echo "All $passed tests passed. Hooray!\n";


	}

	public function reports_test_crash($msg)
	{
		echo "test.php: $msg\n";
		exit(1);
	}

        /**
         Drops all tables in Database() which "appear to be"
         test data.
        */
        public function wipe_tables()
        {
            $db = new Database();
            $tlist = $db->list_tables();
            if( !count($tlist) ) {
                throw new Exception("Query failed!");
            }
            foreach( $tlist as $table ) {
                #echo "Checking table $table...\n";
                if (strlen($table) >= 40) {
                    echo "Dropping table $table...\n";
                    if (!$db->query("drop table $table")) {
                        echo "Failed to drop table $table:" . $db->error_message() . "\n";
                    }
                }
            }
        }
}
