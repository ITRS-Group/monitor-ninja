<?php defined('SYSPATH') OR die('No direct access allowed.');
class report_Test extends TapUnit {
	public function test_overlapping_timeperiods() {
		$report = new Reports_Model();
		$report->set_option('start_time', strtotime('1999-01-01'));
		$report->set_option('end_time', strtotime('2012-01-01'));
		$report->set_option('report_timeperiod', 'weird-stuff');
		$report->resolve_timeperiods();
		$this->pass('Could resolve timperiod torture-test');
		$this->ok(!empty($report->tp_exceptions), 'There are exclusions');
		// fixme: validate output
	}
}
