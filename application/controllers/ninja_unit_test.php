<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Unit_Test controller.
 */
class Ninja_unit_test_Controller extends Ninja_Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index($user=false)
	{
		#Auth::instance()->force_login($user);

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
		$db_host = 'localhost';
		$db_type = 'mysql';
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
			$test->db_type = $db_type;
			$test->db_host = $db_host;
			if ($test->run_test_series() === -1) {
				echo "    $test->test_file : '$test->description' failed to run\n";
				exit(1);
			}
			$all[] = $test;
			$passed += $test->passed;
			$failed += $test->failed;
		}
		if ($failed) {
			echo "Failed test-cases:\n";
			foreach ($all as $test) {
				if (!empty($test->failed))
					echo "    " . $test->test_file . ", '" . $test->description . "'\n";
			}
		}
		echo "$passed/".($passed+$failed)." tests passed.".($failed==0?" Hooray!":"")."\n";
		if ($failed)
			exit(1);
		exit(0); //removing this causes ninja to print a 302 to tac

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
            $db = Database::instance();
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
