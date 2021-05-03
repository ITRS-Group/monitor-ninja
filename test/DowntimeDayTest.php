<?php

require_once 'includes/downtime.php';


function get_day_mock($stepping) {
	$mock = new DowntimeDayModel();
	$mock->set_recurrence($stepping);
	return $mock;
}


class Downtime_Day_Test extends \PHPUnit\Framework\TestCase {
	/**
	 * match_day_interval() should evaluate to true if the given date is scheduled
	 * @group recurring_downtime
	 */
	public function test_repeat_hit() {
		$mock = get_day_mock(6);
		$mock->set_start('1984-01-13 10:00');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_day_interval(mock_date('1984-01-19')));
		$this->assertTrue($schedule->match_day_interval(mock_date('1984-01-25')));
		$this->assertTrue($schedule->match_day_interval(mock_date('1984-01-31')));
		$this->assertTrue($schedule->match_day_interval(mock_date('1984-02-06')));
	}

	/**
	 * match_day_interval() should evaluate to true if the given date is scheduled
	 * @group recurring_downtime
	 */
	public function test_repeat_hit_one() {
		$mock = get_day_mock(1);
		$mock->set_start('1984-09-26 10:00');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_day_interval(mock_date('1984-09-26')));
		$this->assertTrue($schedule->match_day_interval(mock_date('1984-09-27')));
		$this->assertTrue($schedule->match_day_interval(mock_date('1984-09-28')));
		$this->assertTrue($schedule->match_day_interval(mock_date('1984-09-29')));
	}

	/**
	 * match_day_interval() should evaluate to false if the given date is not scheduled
	 * @group recurring_downtime
	 */
	public function test_repeat_miss() {
		$mock = get_day_mock(6);
		$mock->set_start('1984-01-13 10:00');
		$schedule = new RecurringDowntime($mock);

		$this->assertFalse($schedule->match_day_interval(mock_date('1984-01-18')));
		$this->assertFalse($schedule->match_day_interval(mock_date('1984-01-20')));
		$this->assertFalse($schedule->match_day_interval(mock_date('1984-01-24')));
		$this->assertFalse($schedule->match_day_interval(mock_date('1984-01-26')));
		$this->assertFalse($schedule->match_day_interval(mock_date('1984-02-05')));
		$this->assertFalse($schedule->match_day_interval(mock_date('1984-02-07')));
	}
}
