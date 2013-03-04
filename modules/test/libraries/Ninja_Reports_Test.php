<?php

/**
 * A model that runs tests on the reports model,
 * based on a special test-DSL
 *
 * Inherits from Reports_Model for "dammit, it's protected!" reasons
 */
class Ninja_Reports_Test_Core extends Reports_Model
{
	private $test_file = false;
	private $total = 0;
	public $description = false; /**< A string describing the purpose of this test */
	private $tests;
	private $results = array();
	private $config_files = false;
	private $passed = 0;
	private $failed = 0;
	private $logfiles = false;
	private $logfile = false;
	private $sqlfile = false;
	private $table_name = false;
	private $test_globals = array();
	private $interesting_prefixes = array();
	public $sub_reports = 0;
	private $color_red   = '';
	private $color_green = '';
	private $color_reset = '';
	private $db_name;
	private $db_user;
	private $db_pass;
	private $db_type;
	private $db_host;
	private $importer;

	/**
	 * Run new test file. Will parse the file, but not run it
	 */
	public function __construct($test_file)
	{
		if (PHP_SAPI === 'cli' && posix_isatty(STDOUT)) {
			$this->color_red   =  "\033[31m";
			$this->color_green =  "\033[32m";
			$this->color_reset =  "\033[0m";
		}

		if (!$test_file)
			return false;

		$this->tests = $this->parse_test($test_file);
		$this->test_file = $test_file;
	}

	private function red($str)
	{
		return $this->color_red.$str.$this->color_reset;
	}

	private function green($str)
	{
		return $this->color_green.$str.$this->color_reset;
	}

	private function verify_correct($duration, $correct)
	{
		$total = array();
		$this->interesting_prefixes = array();

		foreach ($correct as $k => $v) {
			if (!is_numeric($v))
				continue;
			$prefix = explode('_', $k);
			$prefix = $prefix[0];
			if (!isset($total[$prefix]))
				$total[$prefix] = $v;
			else
				$total[$prefix] += $v;
			$this->interesting_prefixes[$prefix] = $prefix;
		}
		foreach ($total as $prefix => $tot) {
			if ($tot == $duration || $prefix === 'TOTAL' || $prefix === 'PERCENT')
				continue;
			echo "Wonky 'correct' for prefix $prefix: total != duration ($tot != $duration)\n";
			print_r($correct);
			return false;
		}
		return true;
	}

	private function run_test($params)
	{
		$timeperiods = array();
		foreach ($this->test_globals as $k => $v) {
			if ($k === 'timeperiod')
				$timeperiods[] = $v;
			else if (!isset($params[$k]))
				$params[$k] = $v;
		}

		if (!$params)
			return false;

		if (!($correct = arr::search($params, 'correct'))) {
			echo "No 'correct' block set for test. Bailing out\n";
			return false;
		}
		$start_time = arr::search($params, 'start_time');
		$end_time = arr::search($params, 'end_time');
		$timeperiod = arr::search($params, 'timeperiod');
		if ($timeperiod)
			$timeperiods[] =& $timeperiod;
		unset($params['correct']);
		unset($params['timeperiod']);

		if (!$this->verify_correct($end_time - $start_time, $correct))
			return -1;

		Old_Timeperiod_Model::$precreated = array();
		foreach ($timeperiods as $idx => &$tp) {
			if (!isset($tp['timeperiod_name']))
				$tp['timeperiod_name'] = 'the_timeperiod'.$idx;

			$tpobj = Old_Timeperiod_Model::instance(array('start_time' => $start_time, 'end_time' => $end_time, 'rpttimeperiod' => $tp['timeperiod_name']));
			$tpobj->set_timeperiod_data($tp);
			$tpobj->resolve_timeperiods();
		}

		$this->sub_reports = 0;
		$opts = new Test_report_options();
		foreach ($params as $k => $v) {
			if (!$this->sub_reports && is_array($v)) {
				if ($k === 'host_name' || $k === 'service_description')
					$this->sub_reports = count($v);
			}
			if ($k === 'hostgroup' || $k === 'servicegroup') {
				foreach ($v as $groupname => $members) {
					$opts->members = array_merge($opts->members, $members);
				}
				$v = array_keys($v);
				$this->sub_reports = count($opts->members);
			}
			if (!$opts->set($k, $v))
				echo "Failed to set option '$k' to '$v'\n";
		}
		$opts->properties_copy['rpttimeperiod']['options'][$timeperiod['timeperiod_name']] = $timeperiod['timeperiod_name'];
		$opts['rpttimeperiod'] = $timeperiod['timeperiod_name'];

		# force logs to be kept so we can analyze them and make
		# sure the durations add up
		$opts['keep_logs'] = true;
		$opts['keep_sub_logs'] = true;

		$rpt = new Reports_Model($opts, $this->table_name);
		$return_arr = $rpt->get_uptime();
		$this->result = $return_arr;
		$this->report_objects[$this->cur_test] = $rpt;

		if (!$return_arr) {
			return false;
		}

		return $this->compare_test_result($return_arr, $correct, $rpt);
	}

	private function parse_test($test_file = false)
	{
		if (!$test_file)
			return false;

		$req = array('description', 'logfiles');
		$params = array();

		$buf = file_get_contents($test_file);
		$lines = explode("\n", $buf);
		$block = false;
		$pushed_blocks = array();
		$pushed_names = array();
		$block_name = false;
		$num_line = 0;
		foreach ($lines as $raw_line) {
			$num_line++;
			$line = trim($raw_line);
			if (!strlen($line) || $line{0} === '#')
				continue;

			if ($line{0} === '}') {
				if (!empty($pushed_blocks)) {
					$tmp = array_pop($pushed_blocks);
					$tmp[$block_name] = $block;
					$block = $tmp;
					$block_name = array_pop($pushed_names);
					$tmp = false;
				}
				else {
					if ($block_name === 'global_vars')
						$this->test_globals = $block;
					elseif ($block_name === 'logfiles')
						$this->logfiles = $block;
					else
						$params[$block_name] = $block;

					$block = $block_name = false;
				}
				continue;
			}

			if ($line{strlen($line) - 1} === '{') {
				$ary = preg_split("/[\t ]*{[\t ]*/", $line);
				if ($block_name) {
					array_push($pushed_blocks, $block);
					array_push($pushed_names, $block_name);
				}
				$block_name = $ary[0];
				$block = array();
				continue;
			}

			# regular variable, or possibly a single string
			$ary = preg_split("/[\t ]*=[\t ]/", $line);

			if (count($ary) !== 2) {
				if ($block !== false) {
					$block[] = $line;
				}
				else {
					echo "Line $num_line in $test_file is malformed: $line\n";
				}
				continue;
			}
			$k = $ary[0];
			$v = $ary[1];
			if ($block !== false) {
				$block[$k] = $v;
			}
			else {
				switch ($k) {
				 case 'description':
					$this->description = $v;
					break;
				 case 'config_files':
					$this->config_files = $v;
					break;
				 case 'logfile':
					$this->logfile = $v;
					break;
				 case 'sqlfile':
					$this->sqlfile = $v;
					break;
				 case 'db_table':
					$this->table_name = $v;
					break;
				 default:
					if (!is_array($v)) {
						$this->crash("Illegal variable: $k = $v\n");
						exit(1);
					}
					$params[$k] = $v;
				}
			}
		}

		#	print_r($params);
		//recurse_print($params);
		$this->params = $params;
		return $params;
	}

	/**
	 * Run the actual test file
	 */
	public function run_test_series()
	{
		echo "Preparing for test-series '" . $this->description . "'\n";

		$this->details = array();
		if ($this->sqlfile) {
			exec('mysql -u'.$this->db_user.' -p'.$this->db_pass.' '.$this->db_name.' < '.'test/unit_test/reports/'.$this->sqlfile);
			$this->table_name = substr($this->sqlfile, 0, strpos($this->sqlfile, '.'));
		}
		else {
			if ($this->logfile)
				$this->logfiles[] = "test/unit_test/reports/".$this->logfile;

			$result = $this->import_logs();
			if ($result < 0)
				return $result;
		}

		foreach ($this->tests as $test_name => $params) {
			$this->cur_test = $test_name;
			$result = $this->run_test($params);
			printf("  %-7s $test_name\n", $result === true ? $this->green('OK') : $this->red('FAILED'));
			if ($result === true)
				$this->passed++;
			else {
				$this->details[$test_name] = $result;
				$this->failed++;
			}
		}

		foreach ($this->details as $test_name => $fail_desc) {
			echo "$test_name: ";
			print_r($this->report_objects[$test_name]->st_raw);
			print_r($fail_desc);
			echo "\n";
		}
		echo "\n";
		return $this->failed;
	}

	private function import_logs()
	{
		if (!$this->logfiles) {
			echo "No logfiles to import\n";
			return true;
		}
		$lfiles = join(" ", $this->logfiles);

		$line = exec("cat $lfiles | md5sum", $output, $retcode);
		$ary = explode(" ", $line);
		$checksum = $ary[0];
		$table_name = substr($this->description, 0, 20) . substr($checksum, 0, 10);
		$table_name = preg_replace("/[^A-Za-z0-9_]/", "_", $table_name);
		$this->table_name = $table_name;

		echo "Using db table '".$this->table_name."'\n";
		$cached = true;
		$db = Database::instance();
		try {
                    $db->query("SELECT * FROM ".$this->table_name." LIMIT 1");
		}
		catch (Kohana_Database_Exception $e) {
                    $cached = false;
		}

		if ($cached) {
			echo "Data is cached\n";
		} else {
			if ($this->db_type === 'oracle')
				$sql = "CREATE TABLE $table_name AS (SELECT * FROM report_data WHERE rownum < 0)";
			else
				$sql = "CREATE TABLE $table_name AS SELECT * FROM report_data LIMIT 0";
			echo "Building table [$table_name]. This might take a moment or three...\n";
			if( ! $db->query($sql)) {
				$this->crash("Error creating table $table_name: ".$db->error_message());
			}
			echo "Importing $lfiles to '$table_name'\n";
			$cmd = $this->importer .
				" --db-name=".$this->db_name .
				" --db-table=".$this->table_name .
				" --db-user=".$this->db_user .
				" --db-pass=".$this->db_pass." " .
				" --db-host=".$this->db_host." " .
				" --db-type=".$this->db_type." " .
				join(" ", $this->logfiles).' 2>&1';
			$out = array();
			exec($cmd, $out, $retval);
			echo "$cmd\n".implode("\n", $out);
			if ($retval) {
				echo "import failed. cleaning up and skipping test\n";
				echo $cmd."\n";
				$db->query("DROP TABLE ".$this->table_name);
				return -1;
			}
		}

		return true;
	}

	private function count_sub_reports($ary)
	{
		$subs = 0;
		foreach ($ary as $k => $v) {
			if (is_numeric($k))
				$subs++;
		}

		return $subs;
	}

	private function log_duration($st_log)
	{
		if (!is_array($st_log))
			return 0;

		$duration = 0;
		foreach ($st_log as $le) {
			$duration += $le['duration'];
		}
		return $duration;
	}

	/**
	* compare_test_result
	*
	* Compare result from test with correct values
	* @return mixed true or array with diff
	*
	*/
	private function compare_test_result($full_result, $correct, $rpt)
	{
		if (empty($full_result) || empty($full_result['states']))
			$this->crash("No test result\n");
		$states = $full_result['states'];

		if (empty($correct))
			$this->crash("No \$correct\n");

		$failed = false;
		foreach ($correct as $k => $v) {
			if ($k === 'subs') {
				foreach ($v as $sub_name => $sub_correct) {
					$sub = false;
					foreach ($full_result as $_ => $obj) {
						$tmp_sub_name = '';
						if (!isset($obj['states']) || !$obj['states'])
							continue;
						$tmp_sub_name .= $obj['states']['HOST_NAME'];
						if (isset($obj['states']['SERVICE_DESCRIPTION']))
							$tmp_sub_name .= ';'.$obj['states']['SERVICE_DESCRIPTION'];
						if ($tmp_sub_name === $sub_name) {
							$sub = $obj;
							break;
						}
					}
					if (!$sub) {
						$failed[$sub_name] = "expected sub report $sub_name, but couldn't find it";
						continue;
					}
					foreach ($sub_correct as $sk => $sv) {
						if (!isset($sub['states'])) {
							$failed["$sub_name;$sk"] = "expected=$sv; lib_reports=(not set)";
							continue;
						}
						if (strcmp($sub['states'][$sk], $sv)) {
							$failed["$sub_name;$sk"] = "expected=$sv; lib_reports={$sub['states'][$sk]}";
						}
					}
				}
				continue;
			}
			if (!isset($states[$k])) {
				$failed[$k] = "expected=$v; lib_reports=(not set)";
				continue;
			}
			if (strcmp($states[$k], $v)) {
				$failed[$k] = "expected=$v; lib_reports=$states[$k]";
			}
		}

		$subs = $this->count_sub_reports($full_result);
		if ($this->sub_reports != $subs) {
			if ($this->sub_reports === false) {
				$failed['sub-reports'] = "There are sub-reports, but shouldn't be";
			}
			else {
				$failed['sub-reports'] = "Expected $this->sub_reports sub reports. Got $subs";
			}
		}

		# check duration for all sub-reports individually
		if (!empty($rpt->sub_reports)) {
			foreach ($rpt->sub_reports as $r) {
				$duration = $this->log_duration($r->st_log);
				if ($duration != $r->options['end_time'] - $r->options['start_time']) {
					$failed['st_log ' . $r->id] = "Log duration doesn't match report period duration (expected ".($r->options['end_time'] - $r->options['start_time']).", was $duration)";
				}
			}
		}
		# also check the master report
		$duration = $this->log_duration($rpt->st_log);
		if ($duration != $rpt->options['end_time'] - $rpt->options['start_time']) {
			$failed['st_log'] = "Log duration doesn't match report period duration (expected ".($rpt->options['end_time'] - $rpt->options['start_time']).", was $duration)";
		}

		if (empty($failed)) {
			return true;
		}
		foreach ($states as $k => $v) {
			$prefix = explode('_', $k);
			$prefix = $prefix[0];
			if (!isset($this->interesting_prefixes[$prefix]))
				continue;
			if ($v != 0 && empty($correct[$k])) {
				$failed[$k] = "expected=0; lib_reports=$v";
			}
		}

		return $failed;
	}

	private function crash($msg)
	{
		echo "test.php: $msg\n";
		exit(1);
	}
}
