<?php defined('SYSPATH') OR die('No direct access allowed.');
class report_Test extends TapUnit {
	public function setUp() {
		$this->auth = Auth::instance(array('session_key' => false))->force_user(new Op5User_AlwaysAuth());
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
			$this->eq($event, Reports_Model::event_type_to_string($event, null, true));
		}
	}
}
