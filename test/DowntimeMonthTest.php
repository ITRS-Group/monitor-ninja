<?php

require_once 'includes/downtime.php';

function get_month_mock($stepping, $occurrence = FIRST, $dow = MONDAY) {
	$mock = new DowntimeMonthModel();
	$mock->set_recurrence($stepping, $dow, $occurrence);
	return $mock;
}

/**
 * Class Downtime_Month_Test
 */
class Downtime_Month_Test extends \PHPUnit\Framework\TestCase {
	/**
	 * match_month_interval() should evaluate to true if the given month is scheduled
	 * @group recurring_downtime
	 */
	public function test_interval_hit() {
		$mock = get_month_mock(6);
		$mock->set_start('2019-03-13');
		$schedule = new RecurringDowntime($mock);

		// Full dates - ensure both first and last days of month evaluates to true.
		$this->assertTrue($schedule->match_month_interval(mock_date('first day of september 2019')));
		$this->assertTrue($schedule->match_month_interval(mock_date('last day of september 2019')));
		$this->assertTrue($schedule->match_month_interval(mock_date('first day of march 2020')));
		$this->assertTrue($schedule->match_month_interval(mock_date('last day of march 2020')));
		$this->assertTrue($schedule->match_month_interval(mock_date('first day of september 2020')));
		$this->assertTrue($schedule->match_month_interval(mock_date('last day of september 2020')));
	}

	/**
	 * match_month_interval() should evaluate to false if the given month is not scheduled.
	 * @group recurring_downtime
	 */
	public function test_interval_miss() {
		$mock = get_month_mock(3);
		$mock->set_start('2019-03-15');
		$schedule = new RecurringDowntime($mock);

		// Full dates - ensure both first and last days of month evaluates to false.
		$this->assertFalse($schedule->match_month_interval(mock_date('first day of august 2019')));
		$this->assertFalse($schedule->match_month_interval(mock_date('last day of august 2019')));
		$this->assertFalse($schedule->match_month_interval(mock_date('first day of october 2019')));
		$this->assertFalse($schedule->match_month_interval(mock_date('last day of october 2019')));
		$this->assertFalse($schedule->match_month_interval(mock_date('first day of february 2020')));
		$this->assertFalse($schedule->match_month_interval(mock_date('last day of february 2020')));
		$this->assertFalse($schedule->match_month_interval(mock_date('first day of april 2020')));
		$this->assertFalse($schedule->match_month_interval(mock_date('last day of april 2020')));
		$this->assertFalse($schedule->match_month_interval(mock_date('first day of august 2020')));
		$this->assertFalse($schedule->match_month_interval(mock_date('last day of august 2020')));
		$this->assertFalse($schedule->match_month_interval(mock_date('first day of october 2020')));
		$this->assertFalse($schedule->match_month_interval(mock_date('last day of october 2020')));
	}

	/**
	 * Matching first Friday should work
	 * @group recurring_downtime
	 */
	public function test_nested_first_dow() {
		$mock = get_month_mock(5, FIRST, FRIDAY);
		$mock->set_start('2018-03-20');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_day_of_month(mock_date('first friday of june 2018')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('first friday of october 2018')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('first friday of february 2019')));
	}

	/**
	 * Matching second Tuesday should work
	 * @group recurring_downtime
	 */
	public function test_nested_second_dow() {
		$mock = get_month_mock(7, SECOND, TUESDAY);
		$mock->set_start('2018-03-10');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_day_of_month(mock_date('second tuesday of june 2018')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('second tuesday of october 2018')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('second tuesday of february 2019')));
	}

	/**
	 * Matching third Wednesday should work
	 * @group recurring_downtime
	 */
	public function test_nested_third_dow() {
		$mock = get_month_mock(3, THIRD, WEDNESDAY);
		$mock->set_start('2018-01-25');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_day_of_month(mock_date('third wednesday of april 2018')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('third wednesday of july 2018')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('third wednesday of october 2018')));
	}

	/**
	 * Matching fourth Sunday should work
	 * @group recurring_downtime
	 */
	public function test_nested_fourth_dow() {
		$mock = get_month_mock(2, FOURTH, SUNDAY);
		$mock->set_start('2015-02-12');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_day_of_month(mock_date('fourth sunday of april 2015')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('fourth sunday of june 2015')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('fourth sunday of september 2015')));
	}

	/**
	 * Matching fifth Friday should work
	 * @group recurring_downtime
	 */
	public function test_nested_fifth_dow() {
		$mock = get_month_mock(1, FIFTH, FRIDAY);
		$mock->set_start('2016-02-12');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_day_of_month(mock_date('fifth friday of march 2019')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('fifth friday of may 2019')));
	}

	/**
	 * Matching last saturday should work
	 * @group recurring_downtime
	 */
	public function test_nested_recurrences_last_dow() {
		$mock = get_month_mock(1, LAST, SATURDAY);
		$mock->set_start('2012-05-01');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_day_of_month(mock_date('last saturday of june 2015')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('last saturday of july 2015')));
		$this->assertTrue($schedule->match_day_of_month(mock_date('last saturday of august 2015')));
	}

	/**
	 * Get recurrence by an invalid ordinal should fail
	 * @group recurring_downtime
	 */
	public function test_invalid_number_ordinal() {
		$mock = get_month_mock(3, 'THIRST', FRIDAY);
		$schedule = new RecurringDowntime($mock);

		$this->expectException('UnexpectedValueException');
		$schedule->match_day_of_month($schedule->start);
	}

	/**
	 * Get recurrence by an invalid month should fail
	 * @group recurring_downtime
	 */
	public function test_invalid_day() {
		$mock = get_month_mock(3, FIRST, 'SATURMON');
		$schedule = new RecurringDowntime($mock);

		$this->expectException('UnexpectedValueException');
		$schedule->match_day_of_month($schedule->start);
	}
}
