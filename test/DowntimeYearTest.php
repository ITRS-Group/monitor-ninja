<?php

require_once 'includes/downtime.php';

function get_year_mock($stepping, $month = JANUARY, $occurrence = FIRST, $dow = MONDAY) {
	$mock = new DowntimeYearModel();
	$mock->set_recurrence($stepping, $month, $dow, $occurrence);
	return $mock;
}


class Downtime_Year_Test extends \PHPUnit\Framework\TestCase {
	/**
	 * match_year_interval() should evaluate to true if scheduled
	 * @group recurring_downtime
	 */
	public function test_interval_hit() {
		$mock = get_year_mock(6);
		$mock->set_start('1984-09-26');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_year_interval(mock_date('1990-01-01')));
		$this->assertTrue($schedule->match_year_interval(mock_date('1996-06-05')));
		$this->assertTrue($schedule->match_year_interval(mock_date('2002-12-31')));
		$this->assertTrue($schedule->match_year_interval(mock_date('2008-10-15')));
	}

	/**
	 * match_year_interval() should evaluate to true if scheduled [yearly]
	 * @group recurring_downtime
	 */
	public function test_interval_yearly() {
		$mock = get_year_mock(1);
		$mock->set_start('1984-09-26');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_year_interval(mock_date('1985-05-11')));
		$this->assertTrue($schedule->match_year_interval(mock_date('1986-02-21')));
		$this->assertTrue($schedule->match_year_interval(mock_date('1987-12-31')));
		$this->assertTrue($schedule->match_year_interval(mock_date('1988-02-29')));
	}

	/**
	 * match_year_interval() should evaluate to false if not scheduled
	 * @group recurring_downtime
	 */
	public function test_interval_miss() {
		$mock = get_year_mock(2);
		$mock->set_start('1984-09-26');
		$schedule = new RecurringDowntime($mock);

		$this->assertFalse($schedule->match_year_interval(mock_date('1985-09-01')));
		$this->assertFalse($schedule->match_year_interval(mock_date('1987-01-01')));
		$this->assertFalse($schedule->match_year_interval(mock_date('1989-12-31')));
		$this->assertFalse($schedule->match_year_interval(mock_date('1991-02-22')));
	}

	/**
	 * Getting recurrence with a scheduled dom should work
	 * @group recurring_downtime
	 */
	public function test_valid_nested_recurrence() {
		$mock = get_year_mock(8, SEPTEMBER, FIRST, FRIDAY);
		$mock->set_start('1970-01-01');
		$schedule = new RecurringDowntime($mock);

		$input = mock_date('first friday of september 1986');

		$this->assertTrue($schedule->match_year_interval($input));
		$this->assertTrue($schedule->match_day_of_month($input));
	}

	/**
	 * Getting recurrence with an unscheduled dom should fail
	 * @group recurring_downtime
	 */
	public function test_invalid_nested_recurrence() {
		$mock = get_year_mock(3, JUNE, THIRD, TUESDAY);
		$mock->set_start('1970-01-01');
		$schedule = new RecurringDowntime($mock);

		$this->assertFalse($schedule->match_day_of_month(
			mock_date('first tuesday of september 1979')
		));

		$this->assertFalse($schedule->match_day_of_month(
			mock_date('first tuesday of march 1982')
		));
	}

	/**
	 * Getting a yearly recurrence with a scheduled nested dom should work
	 * @group recurring_downtime
	 */
	public function test_valid_nested_last_recurrence() {
		$mock = get_year_mock(3,  MARCH, LAST, MONDAY);
		$mock->set_start('1975-01-01');
		$schedule = new RecurringDowntime($mock);

		// Move ahead a few years and set the expected occurrence
		$input1 = mock_date('last monday of march 1981');
		$input2 = mock_date('last monday of march 1984');

		$this->assertTrue($schedule->match_year_interval($input1));
		$this->assertTrue($schedule->match_day_of_month($input1));

		$this->assertTrue($schedule->match_year_interval($input2));
		$this->assertTrue($schedule->match_day_of_month($input2));
	}
}
