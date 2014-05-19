<?php
class report_Test extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->auth = Auth::instance(array('session_key' => false))->force_user(new Op5User_AlwaysAuth());
	}

	public function test_restricted_access() {
		/* Store old user, so we can reset afterward */
		$authmod = Auth::instance();
		$stasheduser = $authmod->get_user();

		/* Setup limited user, we can't replace the user, but only it's
		 * content. Singleton objects stashes the user object
		 */
		$authmod->force_user($user = new Op5User_AlwaysAuth());
		$user->set_authorized_for('host_view_all', false);
		$user->set_authorized_for('service_view_all', false);
		$user->set_authorized_for('hostgroup_view_all', false);
		$user->set_authorized_for('servicegroup_view_all', false);
		$user->username = 'limited';

		/* Run test */

		$opts = new Alert_history_options(array('start_time'=>0, 'end_time'=>time()));
		$querym = new Report_query_builder_Model($opts);

		/* We're not interested in filtering anything, just see the permissions.
		 * Therefore, treat it as an API-call
		 */
		$query = $querym->build_alert_summary_query();

		/* This string should represent the filter to filter out only allowed
		 * objects
		 */

		$substr = "AND "
				."("
					."((host_name IN ('monitor')) AND (service_description = ''))"
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

		$this->assertTrue(strpos($query, $substr) !== false, 'Could not find permission check substring in query');

		try {
			$db = Database::instance();
			$dbr = $db->query('EXPLAIN '.$query);
		} catch( Kohana_Database_Exception $e ) {
			$this->fail("Could not run query: ".$e->getMessage());
		}


		/* Reset user */
		$authmod->force_user($stasheduser);
	}

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
		if ($this->auth->authorized_for('host_view_all'))
			$msg .= ' with host_view_all';
		if ($this->auth->authorized_for('service_view_all'))
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
						$rpt = new Report_query_builder_Model('report_data');
						$query = $rpt->build_alert_summary_query();
						$this->assertInternalType('string', $query, "No query returned when $msg for host_state:$host_state;service_state:$service_state;state_type:$state_types;alert_types:$alert_types");
						$this->assertObjectHasAttribute('select_type', $db->query("EXPLAIN " . $query)->current());
					}
				}
			}
		}
	}

	public function test_run_summary_test_queries() {
		$this->auth->set_authorized_for('host_view_all', true);
		$this->auth->set_authorized_for('service_view_all', true);
		$this->run_and_diag();

		$this->auth->set_authorized_for('host_view_all', true);
		$this->auth->set_authorized_for('service_view_all', false);
		$this->run_and_diag();

		$this->auth->set_authorized_for('host_view_all', false);
		$this->auth->set_authorized_for('service_view_all', true);
		$this->run_and_diag();

		$this->auth->set_authorized_for('host_view_all', false);
		$this->auth->set_authorized_for('service_view_all', false);
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
	 * To begin with, test bug #6821
	 */

	/**
	 * Test bug #8602
	 *
	 * Store a filter, with same name as an hostgroup. The hostgroup should be used, and shouldn't be affected by the filter
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
			$opts = new Avail_Options();
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
			$opts = new Avail_Options();
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
}
