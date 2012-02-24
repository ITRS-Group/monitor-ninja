<?php defined('SYSPATH') OR die('No direct access allowed.');
class report_Test extends TapUnit {
	public function test_overlapping_timeperiods() {
		$report = new Reports_Model();
		$report->set_option('start_time', strtotime('1999-01-01'));
		$report->set_option('end_time', strtotime('2012-01-01'));
		$report->set_option('report_timeperiod', 'weird-stuff');
		$report->resolve_timeperiods();
		$this->pass('Could resolve timperiod torture-test');
		$this->ok(!empty($report->tp_exceptions), 'There are exceptions');
		// fixme: validate output
	}

	public function test_run_summary_test_queries() {
		// found this method while trying to memorize ninja's source code
		// turns out, I'd just broken it and nothing told me, so let's always
		// run this so it'll yell at me for next time
		$sum = new Summary_Controller();
		$auth = new Nagios_auth_Model();
		$auth->view_hosts_root = true;
		try {
			$this->ok($sum->test_queries($auth), 'Run summary test queries without syntax errors with view_host_root');
		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}
		$auth->view_services_root = true;
		try {
			$this->ok($sum->test_queries($auth), 'Run summary test queries without syntax errors with view_host_root and view_services_root');
		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}
		$auth->view_hosts_root = false;
		try {
			$this->ok($sum->test_queries($auth), 'Run summary test queries without syntax errors with view_services_root');
		} catch (Exception $e) {
			$this->fail($e->getMessage());
		}
	}
}
