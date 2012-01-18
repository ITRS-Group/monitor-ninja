<?php

class Ninja_Reports_Test_Core
{
	public $test_file = false;
	public $total = 0;
	public $description = false;
	public $tests;
	public $results = array();
	public $config_files = false;
	public $passed = 0;
	public $failed = 0;
	public $logfiles = false;
	public $logfile = false;
	public $sqlfile = false;
	public $table_name = false;
	public $test_globals = array();
	public $interesting_prefixes = array();
	public $sub_reports = 0;
	public $color_red   = '';
	public $color_green = '';
	public $color_reset = '';
	public $db_name;
	public $db_user;
	public $db_pass;
	public $importer;

	public function __construct($test_file)
	{
		if (PHP_SAPI === 'cli') {
			$this->color_red   =  "\033[31m";
			$this->color_green =  "\033[32m";
			$this->color_reset =  "\033[0m";
		}

		if (!$test_file)
			return false;

		$this->tests = $this->parse_test($test_file);
		$this->test_file = $test_file;
	}

	public function red($str)
	{
		return $this->color_red.$str.$this->color_reset;
	}

	public function green($str)
	{
		return $this->color_green.$str.$this->color_reset;
	}

	public function verify_correct($duration, $correct)
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

	public function run_test($params)
	{
		foreach ($this->test_globals as $k => $v) {
			if (!isset($params[$k]))
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
		unset($params['correct']);

		if (!$this->verify_correct($end_time - $start_time, $correct))
			return -1;

		$this->sub_reports = 0;
		$rpt = new Reports_Model('merlin', $this->table_name);
		foreach ($params as $k => $v) {
			if (!$this->sub_reports && is_array($v)) {
				if ($k === 'host_name' || $k === 'service_description')
					$this->sub_reports = count($v);
			}
			if (!$rpt->set_option($k, $v))
				echo "Failed to set option '$k' to '$v'\n";
		}

		# force logs to be kept so we can analyze them and make
		# sure the durations add up
		$rpt->set_option('keep_logs', true);
		$rpt->set_option('keep_sub_logs', true);

		$return_arr = $rpt->get_uptime();
		$this->result = $return_arr;
		$this->report_objects[$this->cur_test] = $rpt;

		if (!$return_arr) {
			return false;
		}

		return $this->compare_test_result($return_arr, $correct, $rpt);
	}

	public function parse_test($test_file = false)
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

	public function run_test_series()
	{
		echo "Preparing for test-series '" . $this->description . "'\n";

		$this->details = array();
		if ($this->sqlfile) {
			exec('mysql -u'.$this->db_user.' -p'.$this->db_pass.' '.$this->db_name.' < '.MODPATH.'unit_test/reports/'.$this->sqlfile);
			$this->table_name = substr($this->sqlfile, 0, strpos($this->sqlfile, '.'));
		}
		else {
			if ($this->logfile)
				$this->logfiles[] = MODPATH."unit_test/reports/".$this->logfile;

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

	public function import_logs()
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
			$sql =
				//"CREATE TABLE $table_name LIKE report_data" // not portable
				"CREATE TABLE $table_name AS SELECT * FROM report_data LIMIT 0"
				;
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
				join(" ", $this->logfiles);
				echo "$cmd\n";
			#	exit(0);
                        #echo "Running command: $cmd\n";
			system($cmd, $retval);
			if ($retval) {
				echo "import failed. cleaning up and skipping test\n";
				echo $cmd."\n";
				$db->query("DROP TABLE ".$this->table_name);
				return -1;
			}
                        #echo "Import finished :).\n";
		}

		return true;
	}

	public function count_sub_reports($ary)
	{
		$subs = 0;
		foreach ($ary as $k => $v) {
			if (is_numeric($k))
				$subs++;
		}

		return $subs;
	}

	public function log_duration($st_log)
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
	*	@name 	compare_test_result
	*	@desc 	Compare result from test with correct values
	* 	@return mixed true or array with diff
	*
	*/
	public function compare_test_result($full_result, $correct, $rpt)
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
				if ($duration != $r->end_time - $r->start_time) {
					$failed['st_log ' . $r->id] = "Log duration doesn't match report period duration (expected ".($r->end_time - $r->start_time).", was $duration)";
				}
			}
		}
		# also check the master report
		$duration = $this->log_duration($rpt->st_log);
		if ($duration != $rpt->end_time - $rpt->start_time) {
			$failed['st_log'] = "Log duration doesn't match report period duration (expected ".($r->end_time - $r->start_time).", was $duration)";
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

	public function crash($msg)
	{
		echo "test.php: $msg\n";
		exit(1);
	}
}
