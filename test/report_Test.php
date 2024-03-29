<?php

require_once('op5/auth/Auth.php');
require_once('op5/objstore.php');

class report_Test extends \PHPUnit\Framework\TestCase {

	/**
	 * Make sure the enviornment is clean, and livestatus is mocked
	 */
	public function setUp() : void {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();

		$auth = op5auth::instance(array('session_key' => false));
		$auth->force_user(new User_AlwaysAuth_Model());
	}

	/**
	 * Remove mock environment
	 */
	public function tearDown() : void {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();
		Report_options::$now = null;
	}

	/**
	 * Check that the result Summary_Reports::histogram() looks correct.
	 *
	 * We only look at the array keys to check that we the correct states (OK,
	 * Warning, Critical etc.) for all slots (time slots, and max, min etc.).
	 *
	 * I.e the event data is not checked.
	 *
	 * @group MON-6031
	 */
	public function test_summary_report_histogram() {
		// Hourly time slots.
		$time_slots = array(
			0  => "00",
			1  => "01",
			2  => "02",
			3  => "03",
			4  => "04",
			5  => "05",
			6  => "06",
			7  => "07",
			8  => "08",
			9  => "09",
			10 => "10",
			11 => "11",
			12 => "12",
			13 => "13",
			14 => "14",
			15 => "15",
			16 => "16",
			17 => "17",
			18 => "18",
			19 => "19",
			20 => "20",
			21 => "21",
			22 => "22",
			23 => "23",
			24 => "24"
		);

		// Assert Service Filter works, i.e. we should get a report for
		// all states except 0 and 2 (which is 1 and 3 in this case).
		// Existing states are: OK, Warning, Critical, Unknown and Undetermined.
		$opts = new Summary_options(array(
			'breakdown'             => 'hourly',
			'report_type'           => 'services',
			'newstatesonly'         => false,
			'service_filter_status' => array(0 => '-2', 2 => '-2'),
		));
		$sum_reports = new Summary_Reports_Model($opts);
		$summary = $sum_reports->histogram($time_slots);

		$visible_states = array(0 => 1, 1 => 3);
		$this->assert_report_result_states($summary, $visible_states, $time_slots);

		// Assert Host Filter works, i.e. we should get a report for
		// all states except 1 (which is 0 and 2 in this case).
		// Existing states are: Up, Down, Unreachable and Undetermined.
		$opts = new Summary_options(array(
			'breakdown'          => 'hourly',
			'report_type'        => 'hosts',
			'newstatesonly'      => false,
			'host_filter_status' => array(1 => '-2')
		));

		$sum_reports = new Summary_Reports_Model($opts);
		$summary = $sum_reports->histogram($time_slots);

		$visible_states = array(0 => 0, 1 => 2);
		$this->assert_report_result_states($summary, $visible_states, $time_slots);
	}

	/**
	 * Assert $result_array looks OK.
	 */
	private function assert_report_result_states(
		array $result_array, array $visible_states, array $time_slots
	) {
		$this->assert_visible_states($result_array, 'min', $visible_states);
		$this->assert_visible_states($result_array, 'max', $visible_states);
		$this->assert_visible_states($result_array, 'avg', $visible_states);
		$this->assert_visible_states($result_array, 'sum', $visible_states);

		$this->assertArrayHasKey('data', $result_array);

		// Thanks to PHP's auto type conversion the result from array_keys()
		// contains integers for the values 10-24 instead of strings.
		// Therefore assertEquals() is used instead of assertSame().
		$this->assertEquals($time_slots, array_keys($result_array['data']));
		foreach ($result_array['data'] as $hour => $state) {
			$this->assertSame($visible_states, array_keys($state));
		}
	}

	/**
	 * Assert $result_array[$key] exists and contains the keys in $visible_states.
	 */
	private function assert_visible_states(array $result_array, $key, array $visible_states) {
		$this->assertArrayHasKey($key, $result_array);
		$this->assertSame($visible_states, array_keys($result_array[$key]));
	}

	/**
	 * @group nonlocal
	 */
	public function test_restricted_access() {
		/* Store old user, so we can reset afterward */
		$authmod = op5auth::instance();
		$stasheduser = $authmod->get_user();

		/* Setup limited user, we can't replace the user, but only it's
		 * content. Singleton objects stashes the user object
		 */
		$authmod->force_user($user = new User_AlwaysAuth_Model(), false);
		$user->set_authorized_for('host_view_all', false);
		$user->set_authorized_for('service_view_all', false);
		$user->set_authorized_for('hostgroup_view_all', false);
		$user->set_authorized_for('servicegroup_view_all', false);
		$user->set_username('limited');

		/* Run test */

		$opts = new Alert_history_options(array('start_time'=>0, 'end_time'=>time()));
		$querym = new Report_query_builder_Model('report_data', $opts);

		/* We're not interested in filtering anything, just see the permissions.
		 * Therefore, treat it as an API-call
		 */
		$query = $querym->build_alert_summary_query();

		/* This string should represent the filter to filter out only allowed
		 * objects
		 */

		$substr = "AND "
				."("
					."((host_name IN ('monitor')) and (service_description = ''))"
					." OR "
					."((host_name, service_description) IN ("
							."('host_down_acknowledged', 'service critical'), "
							."('host_down_notifications_disabled', 'service ok scheduled'), "
							."('monitor', 'Disk usage /'), "
							."('monitor', 'Local hardware status'), "
							."('monitor', 'MySQL'), "
							."('monitor', 'SSH'), "
							."('monitor', 'Swap Usage'), "
							."('monitor', 'System Load'), "
							."('monitor', 'Users'), "
							."('monitor', 'Zombie Processes'), "
							."('monitor', 'cron process'), "
							."('monitor', 'syslogd process')"
					."))"
				.")";

		$this->assertStringContainsString($substr, $query, 'Could not find permission check substring in query', true);

		try {
			$db = Database::instance();
			$dbr = $db->query('EXPLAIN '.$query);
		} catch( Kohana_Database_Exception $e ) {
			$this->fail("Could not run query: ".$e->getMessage());
		}


		/* Reset user */
		$authmod->force_user($stasheduser, false);
	}

	/**
	 * @group nonlocal
	 */
	public function test_overlapping_timeperiods() {
		$db = Database::instance();
		$opts = array(
			'start_time' => strtotime('1999-01-01'),
			'end_time' => strtotime('2012-01-01'),
			'rpttimeperiod' => 'weird-stuff');
		Old_Timeperiod_Model::$precreated = array();
		$report = Old_Timeperiod_Model::instance($opts);
		$report->resolve_timeperiods();
		$this->assertNotEmpty($report->tp_exceptions, 'There should be timeperiod exceptions, based on '.var_export($db->query('SELECT * FROM timeperiod inner join custom_vars on obj_id=id where timeperiod_name="weird-stuff"')->result_array(false), true));
		// fixme: validate output
	}

	private function run_and_diag() {
		$opts = new Avail_options(array('start_time' => 0, 'end_time' => time()));
		$db = Database::instance();
		$msg = '';
		if (op5auth::instance()->authorized_for('host_view_all'))
			$msg .= ' with host_view_all';
		if (op5auth::instance()->authorized_for('service_view_all'))
			$msg .= ' with service_view_all';

		$out = Livestatus::instance()->getHosts(array('columns' => array('name')));
		$res = array();
		foreach ($out as $row) {
			$res[] = $row['name'];
		}
		$opts['report_type'] = 'hosts';
		$opts['objects'] = $res;
		$result = array();
		for ($host_state = 1; $host_state <= 7; $host_state++) {
			$opts['host_states'] = $host_state;
			for ($service_state = 1; $service_state <= 15; $service_state++) {
				$opts['service_states'] = $service_state;
				for ($state_types = 1; $state_types <= 3; $state_types++) {
					$opts['state_types'] = $state_types;
					for ($alert_types = 1; $alert_types <= 3; $alert_types++) {
						$opts['alert_types'] = $alert_types;
						$rpt = new Report_query_builder_Model('report_data', $opts);
						$query = $rpt->build_alert_summary_query();
						$this->assertIsString($query, "No query returned when $msg for host_state:$host_state;service_state:$service_state;state_type:$state_types;alert_types:$alert_types");
						$this->assertObjectHasAttribute('select_type', $db->query("EXPLAIN " . $query)->current());
					}
				}
			}
		}
	}

	public function test_run_summary_test_queries() {
		$user = op5auth::instance()->get_user();

		$user->set_authorized_for('host_view_all', true);
		$user->set_authorized_for('service_view_all', true);
		$this->run_and_diag();

		$user->set_authorized_for('host_view_all', true);
		$user->set_authorized_for('service_view_all', false);
		$this->run_and_diag();

		$user->set_authorized_for('host_view_all', false);
		$user->set_authorized_for('service_view_all', true);
		$this->run_and_diag();

		$user->set_authorized_for('host_view_all', false);
		$user->set_authorized_for('service_view_all', false);
		$this->run_and_diag();
	}

	/**
	 * Very important to not change, since the HTTP API
	 * relies on this.
	 */
	function test_event_types()
	{
		$events = array(
			Reports_Model::PROCESS_SHUTDOWN => 'monitor_shut_down',
			Reports_Model::PROCESS_RESTART => 'monitor_restart',
			Reports_Model::PROCESS_START => 'monitor_start',
			Reports_Model::SERVICECHECK => 'service_alert',
			Reports_Model::HOSTCHECK => 'host_alert',
			Reports_Model::DOWNTIME_START => 'scheduled_downtime_start',
			Reports_Model::DOWNTIME_STOP => 'scheduled_downtime_stop'
		);
		foreach($events as $code => $event) {
			$this->assertEquals($event, Reports_Model::event_type_to_string($code, null, true), sprintf("Unmatching strings: [%s] != [%s]", $event, Reports_Model::event_type_to_string($code, null, true)));
		}
	}

	/**
	 * Test bug #8602
	 *
	 * Store a filter, with same name as an hostgroup. The hostgroup should be used, and shouldn't be affected by the filter
	 * @group nonlocal
	 */
	function test_saved_filter_hostgroup_collission() {

		try {
			/* Mock a saved query */
			LSFilter_Saved_Queries_Model::save_query('hostgroup_all', '[hosts] not all', 'global');

			$the_opts = array(
				'report_name' => 'TEST_REPORT',
				'report_type' => 'hostgroups',
				'hostgroup_name' => array('hostgroup_all'),
				'report_period' => 'custom',
				'start_time' => time() - 3600,
				'end_time' => time(),
			);
			$opts = new Avail_options();
			foreach ($the_opts as $k => $v) {
				$opts[$k] = $v;
			}

			/* The hostgroup represents all hosts, the filter represents none, so lets se that we get some hosts */
			$this->assertNotEmpty($opts->get_report_members());
		} catch(Exception $e) {
			/* Just so we can clean up */
			$db = Database::instance();
			$dbr = $db->query('DELETE FROM '.LSFilter_Saved_Queries_Model::tablename.' WHERE filter_name="hostgroup_all"');
			throw $e;
		}
		/* Clean up... PHP 5.5 is the first to have try {} catch {} finally {do this cleanup}, so copy/paste */
		$db = Database::instance();
		$dbr = $db->query('DELETE FROM '.LSFilter_Saved_Queries_Model::tablename.' WHERE filter_name="hostgroup_all"');
	}

	/**
	 * Test bug #8602
	 *
	 * Store a filter, with same name as an hostgroup. The hostgroup should be used, and shouldn't be affected by the filter
	 *
	 * @group nonlocal
	 */
	function test_saved_filter_servicegroup_collission() {

		try {
			/* Mock a saved query */
			LSFilter_Saved_Queries_Model::save_query('servicegroup_all', '[services] not all', 'global');

			$the_opts = array(
				'report_name' => 'TEST_REPORT',
				'report_type' => 'servicegroups',
				'servicegroup_name' => array('servicegroup_all'),
				'report_period' => 'custom',
				'start_time' => time() - 3600,
				'end_time' => time(),
			);
			$opts = new Avail_options();
			foreach ($the_opts as $k => $v) {
				$opts[$k] = $v;
			}

			/* The hostgroup represents all hosts, the filter represents none, so lets se that we get some hosts */
			$this->assertNotEmpty($opts->get_report_members());
		} catch(Exception $e) {
			/* Just so we can clean up */
			$db = Database::instance();
			$dbr = $db->query('DELETE FROM '.LSFilter_Saved_Queries_Model::tablename.' WHERE filter_name="servicegroup_all"');
			throw $e;
		}
		/* Clean up... PHP 5.5 is the first to have try {} catch {} finally {do this cleanup}, so copy/paste */
		$db = Database::instance();
		$dbr = $db->query('DELETE FROM '.LSFilter_Saved_Queries_Model::tablename.' WHERE filter_name="servicegroup_all"');
	}

	/**
	 * The expectation is that - like regular reports - CSV reports should have
	 * one line per host if it's a host report, one per service if it's a
	 * service report, one per host if it's a hostgroup report, one per
	 * service if it's a servicegroup report.
	 *
	 * Unless it's a SLA, then we want one line per month always, unless it's a
	 * multi-group-thingy, then we want one line per month and group - for the
	 * record, I'm object to this whole specialcase-multigroup-logic.
	 *
	 * When a host belongs to two groups, we will print it once per group. This is
	 * funny, but anything else becomes weird.
	 *
	 * We also need to remember to test the single-obj-case vs multi-obj-case,
	 * because those have a tendency to be tricky.
	 *
	 * Because all those cases are boring to test, and the CSV output is easy
	 * to test, let's automate!
	 *
	 * We don't care about output, but almost anything that can go wrong will
	 * print errors on lines, which we implicitly catch here, so we should be OK
	 *
	 * @group nonlocal
	 */
	function test_csv()
	{
		$month = date('n') - 1;
		if ($month < 1)
			$month += 12;
		$Avail_opts = array('output_format' => 'csv', 'report_period' => 'last7days');
		$Sla_opts = array('output_format' => 'csv', 'report_period' => 'lastmonth', 'months' => array($month => 9));
		$Avail_tests = array(
			'single host' => array(
				'obj' => array('report_type' => 'hosts', 'objects' => array('host_pending')),
				'expected' => 2
			),
			'multi host' => array(
				'obj' => array('report_type' => 'hosts', 'objects' => array('host_pending', 'host_up')),
				'expected' => 3
			),
			'single service' => array(
				'obj' => array('report_type' => 'services', 'objects' => array('host_pending;service critical')),
				'expected' => 2
			),
			'multi service, same host' => array(
				'obj' => array('report_type' => 'services', 'objects' => array('host_pending;service critical', 'host_pending;service ok')),
				'expected' => 3
			),
			'multi service, different host' => array(
				'obj' => array('report_type' => 'services', 'objects' => array('host_pending;service critical', 'host_up;service ok')),
				'expected' => 3
			),
			'single hostgroup with two members' => array(
				'obj' => array('report_type' => 'hostgroups', 'objects' => array('hostgroup_acknowledged')),
				'expected' => 3
			),
			'multi hostgroups' => array(
				'obj' => array('report_type' => 'hostgroups', 'objects' => array('hostgroup_acknowledged', 'hostgroup_all')),
				'expected' => 26
			),
			'single servicegroup, 88 members' => array(
				'obj' => array('report_type' => 'servicegroups', 'objects' => array('servicegroup_pending')),
				'expected' => 89,
			),
			'multi servicegroups' => array(
				'obj' => array('report_type' => 'servicegroups', 'objects' => array('servicegroup_pending', 'servicegroup_ok')),
				'expected' => 111,
			),
		);
		// @TODO: This is totally stupid and should be extended by setting report_period and months in
		// obj below - but that becomes boring due to the current month (and thus its report period) being fluid
		$Sla_tests = array(
			'single host' => array(
				'obj' => array('report_type' => 'hosts', 'objects' => array('host_pending')),
				'expected' => 2
			),
			'multi host' => array(
				'obj' => array('report_type' => 'hosts', 'objects' => array('host_pending', 'host_up')),
				'expected' => 2
			),
			'single service' => array(
				'obj' => array('report_type' => 'services', 'objects' => array('host_pending;service critical')),
				'expected' => 2
			),
			'multi service, same host' => array(
				'obj' => array('report_type' => 'services', 'objects' => array('host_pending;service critical', 'host_pending;service ok')),
				'expected' => 2
			),
			'multi service, different host' => array(
				'obj' => array('report_type' => 'services', 'objects' => array('host_pending;service critical', 'host_up;service ok')),
				'expected' => 2
			),
			'single hostgroup with two members' => array(
				'obj' => array('report_type' => 'hostgroups', 'objects' => array('hostgroup_acknowledged')),
				'expected' => 2
			),
			'multi hostgroups' => array(
				'obj' => array('report_type' => 'hostgroups', 'objects' => array('hostgroup_acknowledged', 'hostgroup_all')),
				'expected' => 3
			),
			'single servicegroup, 88 members' => array(
				'obj' => array('report_type' => 'servicegroups', 'objects' => array('servicegroup_pending')),
				'expected' => 2,
			),
			'multi servicegroups' => array(
				'obj' => array('report_type' => 'servicegroups', 'objects' => array('servicegroup_pending', 'servicegroup_ok')),
				'expected' => 3,
			),
		);
		foreach (array('Avail', 'Sla') as $report_type) {
			foreach (${$report_type.'_tests'} as $test_name => $details) {
				$ctrl_class = $report_type.'_Controller';
				$opt_class = $report_type.'_options';
				$ctrl = new $ctrl_class();
				$ctrl->auto_render = false;
				$option = new $opt_class();
				$this->assertTrue($option->set_options(${$report_type.'_opts'}), "Setting initial options for $report_type $test_name should be fine");
				foreach ($details['obj'] as $k => $v) {
					$this->assertTrue($option->set($k, $v), "Setting $k for $report_type $test_name should work");
				}
				$ctrl->generate($option);
				$out = $ctrl->template->render();

				$this->assertEquals(count(explode("\n", trim($out))), $details['expected'], "Unexpected number of lines generated for $report_type $test_name, output was: $out");
				$this->assertSame(strpos($out, '""'), false, "Expected no empty parameters for $report_type $test_name, found in $out");
				if ($report_type != 'Sla' || $option['report_type'] != 'services') # Because that case has comma-separated host-and-description names. Obviously.
					$this->assertSame(strpos($out, ';'), false, "Expected no semi-colons in output for $report_type $test_name, found in $out");
			}
		}
	}

	function test_discover_sla_options() {
		$input = array(
			'report_period' => 'custom',
			'start_year' => 2013,
			'start_month' => 2,
			'end_year' => 2013,
			'end_month' => 2
		);
		$output = Sla_options::discover_options($input);
		$this->assertEquals(date('Y-m-d H:i:s', $output['start_time']), '2013-02-01 00:00:00', 'We should start on the first of Febuary');
		$this->assertEquals(date('Y-m-d H:i:s', $output['end_time']), '2013-02-28 23:59:59', 'We should end on the last of Febuary');
	}

	/**
	 * The assumption for report_data is that "only valid values are allowed"
	 * for e.g. enums. This can be easily verified by looking at validate_value.
	 *
	 * However, there's a sneaky backdoor-potential if a user provides a
	 * value that lacks a meaning in a subreporttype - i.e. manually submits a
	 * key that should be disallowed to API reports, or submits a summary report
	 * with a value that only alert history knows how to validate.
	 */
	function test_invalid_options()
	{
		$obj = Report_options::setup_options_obj(
			'summary',
			array(
				'summary_items' => 777,
				'page' => 3
		));
		$this->assertArrayHasKey('summary_items', $obj->options);
		$this->assertEquals(777, $obj->options['summary_items']);
		$this->assertArrayNotHasKey('page', $obj->options);

		/* this message sucks, it should be invalid key.
		 * however, that message is harder to generate.
		 */
		$this->expectException('ReportValidationException');
		$this->expectExceptionMessage("Invalid value for option 'report_name'");
		$obj = Report_options::setup_options_obj(
			'httpApiState',
			array(
				'report_name' => 'foo'
		));
	}

	function test_renamed_options()
	{
		$obj = Report_options::setup_options_obj(
			'summary',
			array(
				'alert_types' => 1,
		));
		$this->assertArrayNotHasKey('alert_types', $obj->options);
		$this->assertArrayHasKey('service_filter_status', $obj->options);
		$this->assertEquals(array(0 => -2, 1 => -2, 2 => -2, 3 => -2), $obj->options['service_filter_status']);
		$obj = Report_options::setup_options_obj(
			'summary',
			array(
				'alert_types' => 'hosts',
		));
		$this->assertArrayNotHasKey('alert_types', $obj->options);
		$this->assertArrayNotHasKey('service_filter_status', $obj->options);

		$obj = Report_options::setup_options_obj(
			'httpApiEvent',
			array(
				'alert_types' => 'host',
		));
		$this->assertArrayNotHasKey('alert_types', $obj->options);
		$this->assertArrayHasKey('service_filter_status', $obj->options);
		$this->assertEquals(array(0 => -2, 1 => -2, 2 => -2, 3 => -2), $obj->options['service_filter_status']);
	}

	/**
	 * @group nonlocal
	 */
	function test_timeperiod_import()
	{
			/*
		 * The expected data is generated before changing to livestatus backend
		 * for timeperiods, thus treated as reference for regression bugs in
		 * the merlin-DB to livestatus port
		 */
		$expcted = array(
			'period' => array(
				1 => array(
					array(
						'start' => 0,
						'stop' => 32400
					),
					array(
						'start' => 61200,
						'stop' => 86400
					)
				)
			),
			'exceptions' => array(
				'unresolved' => array(
					array(
						'type' => 0,
						'syear' => 1999,
						'smon' => 1,
						'smday' => 28,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 1999,
						'emon' => 1,
						'emday' => 28,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 0,
						'timeranges' => array(
							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 0,
						'syear' => 2007,
						'smon' => 1,
						'smday' => 1,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 2008,
						'emon' => 2,
						'emday' => 1,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 0,
						'timeranges' => array(
							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),

					array(
						'type' => 0,
						'syear' => 2007,
						'smon' => 1,
						'smday' => 1,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 2008,
						'emon' => 2,
						'emday' => 1,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 3,
						'timeranges' => array(
							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 0,
						'syear' => 2008,
						'smon' => 4,
						'smday' => 1,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 2008,
						'emon' => 4,
						'emday' => 1,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 7,
						'timeranges' => array(
							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),

					array(
						'type' => 1,
						'syear' => 0,
						'smon' => 4,
						'smday' => 10,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 0,
						'emon' => 5,
						'emday' => 15,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 0,
						'timeranges' => array(
							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 2,
						'syear' => 0,
						'smon' => 0,
						'smday' => 1,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 0,
						'emon' => 0,
						'emday' => 15,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 2,
						'syear' => 0,
						'smon' => 0,
						'smday' => 1,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 0,
						'emon' => 0,
						'emday' => 15,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 5,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 2,
						'syear' => 0,
						'smon' => 0,
						'smday' => 2,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 0,
						'emon' => 0,
						'emday' => 2,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 2,
						'syear' => 0,
						'smon' => 0,
						'smday' => 20,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 0,
						'emon' => 0,
						'emday' => - 1,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 1,
						'syear' => 0,
						'smon' => 2,
						'smday' => - 1,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 0,
						'emon' => 2,
						'emday' => - 1,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 1,
						'syear' => 0,
						'smon' => 2,
						'smday' => 10,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 0,
						'emon' => 2,
						'emday' => 10,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 4,
						'syear' => 0,
						'smon' => 0,
						'smday' => 0,
						'swday' => 5,
						'swday_offset' => - 2,
						'eyear' => 0,
						'emon' => 0,
						'emday' => 0,
						'ewday' => 5,
						'ewday_offset' => - 2,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 1,
						'syear' => 0,
						'smon' => 7,
						'smday' => 10,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 0,
						'emon' => 7,
						'emday' => 15,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 1,
						'syear' => 0,
						'smon' => 7,
						'smday' => 10,
						'swday' => 0,
						'swday_offset' => 0,
						'eyear' => 0,
						'emon' => 7,
						'emday' => 15,
						'ewday' => 0,
						'ewday_offset' => 0,
						'skip_interval' => 2,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),

					array(
						'type' => 4,
						'syear' => 0,
						'smon' => 0,
						'smday' => 0,
						'swday' => 1,
						'swday_offset' => 3,
						'eyear' => 0,
						'emon' => 0,
						'emday' => 0,
						'ewday' => 1,
						'ewday_offset' => 3,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 4,
						'syear' => 0,
						'smon' => 0,
						'smday' => 0,
						'swday' => 1,
						'swday_offset' => 3,
						'eyear' => 0,
						'emon' => 0,
						'emday' => 0,
						'ewday' => 4,
						'ewday_offset' => 4,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 4,
						'syear' => 0,
						'smon' => 0,
						'smday' => 0,
						'swday' => 1,
						'swday_offset' => 3,
						'eyear' => 0,
						'emon' => 0,
						'emday' => 0,
						'ewday' => 4,
						'ewday_offset' => 4,
						'skip_interval' => 2,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 3,
						'syear' => 0,
						'smon' => 11,
						'smday' => 0,
						'swday' => 4,
						'swday_offset' => - 1,
						'eyear' => 0,
						'emon' => 11,
						'emday' => 0,
						'ewday' => 4,
						'ewday_offset' => - 1,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 36000,
								'stop' => 43200
							)
						)
					),
					array(
						'type' => 3,
						'syear' => 0,
						'smon' => 4,
						'smday' => 0,
						'swday' => 2,
						'swday_offset' => 1,
						'eyear' => 0,
						'emon' => 5,
						'emday' => 0,
						'ewday' => 5,
						'ewday_offset' => 2,
						'skip_interval' => 0,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					),
					array(
						'type' => 3,
						'syear' => 0,
						'smon' => 4,
						'smday' => 0,
						'swday' => 2,
						'swday_offset' => 1,
						'eyear' => 0,
						'emon' => 5,
						'emday' => 0,
						'ewday' => 5,
						'ewday_offset' => 2,
						'skip_interval' => 6,
						'timeranges' => array(

							array(
								'start' => 0,
								'stop' => 86400
							)
						)
					)
				)
			),
			'excludes' => array()
		);

		$compare_fnc = function($a, $b) {
			$diff = $a['type'] - $b['type'];
			if ($diff != 0)
				return $diff;
			$diff = $a['syear'] - $b['syear'];
			if ($diff != 0)
				return $diff;
			$diff = $a['smon'] - $b['smon'];
			if ($diff != 0)
				return $diff;
			$diff = $a['smday'] - $b['smday'];
			if ($diff != 0)
				return $diff;
			$diff = $a['swday'] - $b['swday'];
			if ($diff != 0)
				return $diff;
			$diff = $a['swday_offset'] - $b['swday_offset'];
			if ($diff != 0)
				return $diff;
			$diff = $a['eyear'] - $b['eyear'];
			if ($diff != 0)
				return $diff;
			$diff = $a['emon'] - $b['emon'];
			if ($diff != 0)
				return $diff;
			$diff = $a['emday'] - $b['emday'];
			if ($diff != 0)
				return $diff;
			$diff = $a['ewday'] - $b['ewday'];
			if ($diff != 0)
				return $diff;
			$diff = $a['ewday_offset'] - $b['ewday_offset'];
			if ($diff != 0)
				return $diff;
			$diff = $a['skip_interval'] - $b['skip_interval'];
			if ($diff != 0)
				return $diff;
			return 0;
		};

		Old_Timeperiod_Model::$precreated = array ();
		$tp = Old_Timeperiod_Model::instance ( array (
				'rpttimeperiod' => 'weird-stuff',
				'start_time' => 0,
				'end_time' => 0
		) );

		$actual = $tp->test_export();

		/* The order of exceptions shouldn't affect behaviour, so verify they are the same order */
		usort($expcted['exceptions']['unresolved'], $compare_fnc);
		usort($actual['exceptions']['unresolved'], $compare_fnc);

		$this->assertEquals ( $expcted, $actual );
	}

	/**
	 * It looks a bit silly to create a dataProvider for such a small set of
	 * options, but we really, really, do not want phpunit to stop testing
	 * after the first failure.
	 */
	public function time_input_for_report_options() {
		return array(
			array(
				'lastweek',
				'2015-08-03 17:00:00', // a Monday
				'2015-07-27 00:00:00',
				'2015-08-02 23:59:59'
			),
			array(
				'lastweek',
				'2015-08-04 17:00:00', // a Tuesday
				'2015-07-27 00:00:00',
				'2015-08-02 23:59:59'
			),
			array(
				'lastweek',
				'2015-08-05 17:00:00', // a Wednesday
				'2015-07-27 00:00:00',
				'2015-08-02 23:59:59'
			),
			array(
				'lastweek',
				'2015-08-06 17:00:00', // a Thursday
				'2015-07-27 00:00:00',
				'2015-08-02 23:59:59'
			),
			array(
				'lastweek',
				'2015-08-07 17:00:00', // a Friday
				'2015-07-27 00:00:00',
				'2015-08-02 23:59:59'
			),
			array(
				'lastweek',
				'2015-08-08 17:00:00', // a Saturday
				'2015-07-27 00:00:00',
				'2015-08-02 23:59:59'
			),
			array(
				'lastweek',
				'2015-08-09 17:00:00', // a Sunday
				'2015-07-27 00:00:00',
				'2015-08-02 23:59:59'
			),
			array(
				'last7days',
				'2015-08-04 17:00:00', // a Tuesday
				'2015-07-28 17:00:00',
				'2015-08-04 17:00:00',
			),
			array(
				'last31days',
				'2015-08-02 17:00:00',
				'2015-07-02 17:00:00',
				'2015-08-02 17:00:00',
			),
			array(
				'thisyear',
				'2015-08-23 17:00:00',
				'2015-01-01 00:00:00',
				'2015-08-23 17:00:00',
			),
			array(
				'thismonth',
				'2015-08-23 17:00:00',
				'2015-08-01 00:00:00',
				'2015-08-23 17:00:00',
			),
			array(
				'lastmonth',
				'2015-08-01 17:00:00',
				'2015-07-01 00:00:00',
				'2015-08-01 00:00:00',
			),
			array(
				'lastmonth',
				'2015-08-01 00:00:00',
				'2015-07-01 00:00:00',
				'2015-08-01 00:00:00',
			),
			array(
				'lastyear',
				'2015-01-01 17:00:00',
				'2014-01-01 00:00:00',
				'2015-01-01 00:00:00',
			),
			array(
				'last12months',
				'2015-08-01 00:00:00',
				'2014-08-01 00:00:00',
				'2015-08-01 00:00:00',
			),
			array(
				'last3months',
				'2015-08-01 00:00:00',
				'2015-05-01 00:00:00',
				'2015-08-01 00:00:00',
			),
			array(
				'last6months',
				'2015-03-01 00:00:00',
				'2014-09-01 00:00:00',
				'2015-03-01 00:00:00',
			),
			array(
				'lastquarter',
				'2015-02-01 00:00:00',
				'2014-10-01 00:00:00',
				'2015-01-01 00:00:00',
			),
			array(
				'lastquarter',
				'2015-03-31 00:00:00',
				'2014-10-01 00:00:00',
				'2015-01-01 00:00:00',
			),
			array(
				'thisweek',
				'2016-07-04 00:00:01', // Monday
				'2016-07-04 00:00:00',
				'2016-07-04 00:00:01',
			),
			array(
				'thisweek',
				'2015-08-11 00:12:23', // Tuesday
				'2015-08-10 00:00:00',
				'2015-08-11 00:12:23',
			),
			array(
				'thisweek',
				'2015-08-23 23:59:59', // Sunday
				'2015-08-17 00:00:00', // Monday
				'2015-08-23 23:59:59',
			),
		);
	}

	/**
	 * @dataProvider time_input_for_report_options
	 * @group MON-7264
	 * @group time::get_limits
	 */
	public function test_relative_timeperiods_for_report_options($report_period, $now, $expected_start, $expected_end) {
		$now = strtotime($now);

		$friendly = function($expected, $actual) {
			return sprintf("Wanted\n%s -> %s\n, got\n%s -> %s",
				date('Y-m-d H:i:s', $expected[0]),
				date('Y-m-d H:i:s', $expected[1]),
				date('Y-m-d H:i:s', $actual[0]),
				date('Y-m-d H:i:s', $actual[1])
			);
		};

		$actual = time::get_limits($report_period, $now);
		$start = strtotime($expected_start);
		$this->assertNotSame(false, $start,
			'Sanity check: do not pass invalid date as $expected_start'
		);
		$end = strtotime($expected_end);
		$this->assertNotSame(false, $end,
			'Sanity check: do not pass invalid date as $expected_end'
		);
		$expected = array($start, $end);
		$this->assertSame($expected, $actual,
			$friendly($expected, $actual));
	}

	/**
	 * @group time::get_limits
	 */
	public function test_relative_timeperiod_throws_exception_on_invalid_report_type() {
		$this->expectException('InvalidTimePeriod_Exception');
		$this->expectExceptionMessage("'non-existing' is not a valid value for \$time_period");
		time::get_limits('non-existing', time());
	}
}
