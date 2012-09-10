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

	private function run_and_diag($auth) {
		$auth->hosts = false;
		$auth->services = false;
		$msg = 'Run summary test queries without syntax errors';
		if ($auth->view_hosts_root)
			$msg .= ' with view_hosts_root';
		if ($auth->view_services_root)
			$msg .= ' with view_services_root';
		try {
			$res = $this->rpt->test_summary_queries($auth);
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
		$this->rpt = new Reports_Model();
		$this->rpt->set_option('start_time', 0);
		$this->rpt->set_option('end_time', time());
		$auth = Nagios_auth_Model::instance();
		$res = Database::instance()->query('SELECT id FROM contact LIMIT 1');
		//whatever, as long as it's valid (and has at least one of each)
		$auth->id = $res->current()->id;

		$auth->view_hosts_root = false;
		$auth->view_services_root = false;
		$this->run_and_diag($auth);

		$auth->view_hosts_root = true;
		$auth->view_services_root = false;
		$this->run_and_diag($auth);

		$auth->view_hosts_root = true;
		$auth->view_services_root = true;
		$this->run_and_diag($auth);

		$auth->view_hosts_root = false;
		$auth->view_services_root = true;
		$auth->hosts = false;
		$auth->services = false;
		$this->run_and_diag($auth);
	}
}
