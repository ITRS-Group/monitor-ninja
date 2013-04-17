<?php defined('SYSPATH') OR die('No direct access allowed.');
class report_Test extends TapUnit {
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

		$opts = new Avail_options(array('start_time'=>0, 'end_time'=>time()));
		$rpts = new Reports_Model($opts);

		/* We're not interested in filtering anything, just see the permissions.
		 * Therefore, treat it as an API-call
		 */
		$query = $rpts->build_alert_summary_query(null,true);

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

		$this->ok(strpos($query, $substr) !== false, 'Could not find permission check substring in query');


		/* Reset user */
		$authmod->force_user($stasheduser);
	}

	public function test_overlapping_timeperiods() {
		$opts = array(
			'start_time' => strtotime('1999-01-01'),
			'end_time' => strtotime('2012-01-01'),
			'rpttimeperiod' => 'weird-stuff');
		$report = Old_Timeperiod_Model::instance($opts);
		$report->resolve_timeperiods();
		$this->pass('Could resolve timperiod torture-test');
		$this->ok(!empty($report->tp_exceptions), 'There are timeperiod exceptions');
		// fixme: validate output
	}

	private function run_and_diag($auth) {
		$auth->hosts = false;
		$auth->services = false;
		$msg = 'Run summary test queries without syntax errors';
		if ($auth->authorized_for('view_hosts_root'))
			$msg .= ' with view_hosts_root';
		if ($auth->authorized_for('view_services_root'))
			$msg .= ' with view_services_root';
		try {
			$res = $this->rpt->test_summary_queries();
			$this->ok(is_array($res), $msg);
			if (!is_array($res))
				$this->diag($res);
		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function test_run_summary_test_queries() {
		// found this method while trying to memorize ninja's source code
		// turns out, I'd just broken it and nothing told me, so let's always
		// run this so it'll yell at me for next time
		$opts = new Avail_options(array('start_time' => 0, 'end_time' => time()));
		$this->rpt = new Reports_Model($opts);

		$this->auth->set_authorized_for('view_hosts_root', false);
		$this->auth->set_authorized_for('view_services_root', false);
		$this->run_and_diag($this->auth);

		$this->auth->set_authorized_for('view_hosts_root', true);
		$this->auth->set_authorized_for('view_services_root', false);
		$this->run_and_diag($this->auth);

		$this->auth->set_authorized_for('view_hosts_root', true);
		$this->auth->set_authorized_for('view_services_root', true);
		$this->run_and_diag($this->auth);

		$this->auth->set_authorized_for('view_hosts_root', false);
		$this->auth->set_authorized_for('view_services_root', true);
		$this->run_and_diag($this->auth);
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
			$this->ok_eq($event, Reports_Model::event_type_to_string($code, null, true), sprintf("Unmatching strings: [%s] != [%s]", $event, Reports_Model::event_type_to_string($code, null, true)));
		}
	}

	/**
	 * To begin with, test bug #6821
	 */
	function test_modify_report()
	{
		$the_opts = array(
			'report_name' => 'TEST_REPORT',
			'report_type' => 'hosts',
			'host_name' => array('monitor'),
			'report_period' => 'custom',
			'start_time' => time() - 3600,
			'end_time' => time(),
		);
		$opts = new Avail_Options();
		foreach ($the_opts as $k => $v) {
			$opts[$k] = $v;
		}
		$id = Saved_Reports_Model::edit_report_info('avail', false, $opts);
		$this->ok($id !== false, "Saving report should work, so id should not be false");
		$new_opts = Saved_Reports_Model::get_report_info('avail', $id);
		$this->ok(!empty($new_opts), "Loading a saved report should not return an empty array");
		$new_opts = Avail_Options::setup_options_obj('avail', $opts);
		foreach ($opts as $k => $v) {
			$this->ok_eq($v, $new_opts[$k], "$k should be the same after saving and loading report");
		}

		$the_modified_opts = $the_opts;
		$the_modified_opts['report_id'] = $id;
		$the_modified_opts['host_name'][] = 'host_down_acknowledged';
		$modified_opts = Avail_Options::setup_options_obj('avail', $the_modified_opts);
		foreach ($the_modified_opts as $k => $v) {
			$this->ok_eq($v, $modified_opts[$k], 'Loading a saved report should have option set to what we provided for '. $k);
		}

		$the_modified_opts['host_name'][] = 'host_pending';
		$modified_opts->options['host_name'][] = 'host_pending';
		Saved_Reports_Model::edit_report_info('avail', $id, $modified_opts);
		$new_modified_opts = Avail_Options::setup_options_obj('avail', array('report_id' => $id));
		foreach ($the_modified_opts as $k => $v) {
			if (is_array($v)) {
				sort($v);
				sort($new_modified_opts->options[$k]);
			}
			$this->ok_eq($v, $new_modified_opts->options[$k], 'Loading a saved report should have option set to what we provided for '. $k);
		}
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
		$Avail_opts = array('filename' => 'test.csv', 'report_period' => 'last7days');
		$Sla_opts = array('filename' => 'test.csv', 'report_period' => 'lastmonth', 'months' => array((date('n')-1) => 9));
		$Avail_tests = array(
			'single host' => array(
				'obj' => array('host_name' => array('host_pending')),
				'expected' => 2
			),
			'multi host' => array(
				'obj' => array('host_name' => array('host_pending', 'host_up')),
				'expected' => 3
			),
			'single service' => array(
				'obj' => array('service_description' => array('host_pending;service critical')),
				'expected' => 2
			),
			'multi service, same host' => array(
				'obj' => array('service_description' => array('host_pending;service critical', 'host_pending;service ok')),
				'expected' => 3
			),
			'multi service, different host' => array(
				'obj' => array('service_description' => array('host_pending;service critical', 'host_up;service ok')),
				'expected' => 3
			),
			'single hostgroup with two members' => array(
				'obj' => array('hostgroup' => array('hostgroup_acknowledged')),
				'expected' => 3
			),
			'multi hostgroups' => array(
				'obj' => array('hostgroup' => array('hostgroup_acknowledged', 'hostgroup_all')),
				'expected' => 26
			),
			'single servicegroup, 88 members' => array(
				'obj' => array('servicegroup' => array('servicegroup_pending')),
				'expected' => 89,
			),
			'multi servicegroups' => array(
				'obj' => array('servicegroup' => array('servicegroup_pending', 'servicegroup_ok')),
				'expected' => 111,
			),
		);
		// @TODO: This is totally stupid and should be extended by setting report_period and months in
		// obj below - but that becomes boring due to the current month (and thus its report period) being fluid
		$Sla_tests = array(
			'single host' => array(
				'obj' => array('host_name' => array('host_pending')),
				'expected' => 2
			),
			'multi host' => array(
				'obj' => array('host_name' => array('host_pending', 'host_up')),
				'expected' => 2
			),
			'single service' => array(
				'obj' => array('service_description' => array('host_pending;service critical')),
				'expected' => 2
			),
			'multi service, same host' => array(
				'obj' => array('service_description' => array('host_pending;service critical', 'host_pending;service ok')),
				'expected' => 2
			),
			'multi service, different host' => array(
				'obj' => array('service_description' => array('host_pending;service critical', 'host_up;service ok')),
				'expected' => 2
			),
			'single hostgroup with two members' => array(
				'obj' => array('hostgroup' => array('hostgroup_acknowledged')),
				'expected' => 2
			),
			'multi hostgroups' => array(
				'obj' => array('hostgroup' => array('hostgroup_acknowledged', 'hostgroup_all')),
				'expected' => 3
			),
			'single servicegroup, 88 members' => array(
				'obj' => array('servicegroup' => array('servicegroup_pending')),
				'expected' => 2,
			),
			'multi servicegroups' => array(
				'obj' => array('servicegroup' => array('servicegroup_pending', 'servicegroup_ok')),
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
				$this->ok($option->set_options(${$report_type.'_opts'}), "Setting initial options for $report_type $test_name should be fine");
				foreach ($details['obj'] as $k => $v) {
					$this->ok($option->set($k, $v), "Setting $k for $report_type $test_name should work");
				}
				$ctrl->generate($option);
				$out = $ctrl->template->render();

				$this->ok_eq(count(explode("\n", trim($out))), $details['expected'], "Unexpected number of lines generated for $report_type $test_name, output was: $out");
				$this->ok_id(strpos($out, '""'), false, "Expected no empty parameters for $report_type $test_name, found in $out");
				if ($report_type != 'Sla' || $option['report_type'] != 'services') # Because that case has comma-separated host-and-description names. Obviously.
					$this->ok_id(strpos($out, ';'), false, "Expected no semi-colons in output for $report_type $test_name, found in $out");
			}
		}
	}
}
