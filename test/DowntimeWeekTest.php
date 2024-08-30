<?php

require_once 'includes/downtime.php';

function get_week_mock($stepping, $dow = array(MONDAY)) {
	$mock = new DowntimeWeekModel();
	$mock->set_recurrence($stepping, $dow);
	return $mock;
}

class DowntimeWeekTest extends \PHPUnit\Framework\TestCase {
	/**
	 * match_week_interval() should evaluate to true if scheduled
	 */
	#[Group('recurring_downtime')]
	public function test_interval_hit() {
		$mock = get_week_mock(5);
		$mock->set_start('1980-03-01');
		$schedule = new RecurringDowntime($mock);

		// Create future dates that coincides with the repeat interval
		$input1 = mock_date('1980-04-07');
		$input2 = mock_date('1980-05-10');
		$input3 = mock_date('1980-06-14');

		$this->assertTrue($schedule->match_week_interval($input1));
		$this->assertTrue($schedule->match_week_interval($input2));
		$this->assertTrue($schedule->match_week_interval($input3));
	}

	/**
	 * match_year_interval() should evaluate to true if scheduled [weekly]
	 */
	#[Group('recurring_downtime')]
	public function test_interval_hit_one() {
		$mock = get_week_mock(1);
		$mock->set_start('1980-01-01');
		$schedule = new RecurringDowntime($mock);

		$this->assertTrue($schedule->match_week_interval(mock_date('1980-01-08')));
		$this->assertTrue($schedule->match_week_interval(mock_date('1980-01-15')));
		$this->assertTrue($schedule->match_week_interval(mock_date('1980-01-22')));
	}

	/**
	 * recurrence_on should work with single items not contained in an array
	 */
	#[Group('recurring_downtime')]
	public function test_legacy_recurrence_on() {
		$mock = new DowntimeModel();
		$mock->set_start('2019-04-10');
		$mock->recurrence = array(
			'label' => 'custom',
			'no' => '1',
			'text' => 'week'
		);
		$mock->recurrence_on = array('day' => WEDNESDAY);
		$schedule = new RecurringDowntime($mock);

		$dow_output = $schedule->pluck_recurrence(DAY);
		$this->assertFalse(in_array(MONDAY, $dow_output));
		$this->assertFalse(in_array(TUESDAY, $dow_output));
		$this->assertTrue(in_array($schedule->start->get_day_of_week(), $dow_output)); // WEDNESDAY
		$this->assertFalse(in_array(THURSDAY, $dow_output));
		$this->assertFalse(in_array(FRIDAY, $dow_output));
		$this->assertFalse(in_array(SATURDAY, $dow_output));
		$this->assertFalse(in_array(SUNDAY, $dow_output));
	}

	/**
	 * match_week_interval() should evaluate to false if the stepping does /not/ match
	 */
	#[Group('recurring_downtime')]
	public function test_interval_miss() {
		$mock = get_week_mock(7);
		$mock->set_start('1980-03-01');
		$schedule = new RecurringDowntime($mock);

		$input1 = mock_date('1980-03-25');
		$input2 = mock_date('1980-04-01');
		$input3 = mock_date('1980-05-03');

		$this->assertFalse($schedule->match_week_interval($input1));
		$this->assertFalse($schedule->match_week_interval($input2));
		$this->assertFalse($schedule->match_week_interval($input3));
	}

	/**
	 * match_week_interval() should evaluate to true if schedule matches week number and dow
	 */
	#[Group('recurring_downtime')]
	public function test_nested_interval() {
		$dow_input = array(MONDAY, FRIDAY);
		$mock = get_week_mock(2, $dow_input);
		$mock->set_start('1980-03-01');
		$schedule = new RecurringDowntime($mock);
		$dow_output = $schedule->pluck_recurrence(DAY);

		// Create future dates that coincides with the repeat interval
		$input1 = mock_date('1980-03-15');
		$input2 = mock_date('1980-03-23');

		// Input days should equal output days
		$this->assertEquals($dow_input, $dow_output);

		// Check output
		var_dump($schedule->match_week_interval($input1)); 
		var_dump($schedule->match_week_interval($input2)); 

		// Interval should match
		$this->assertTrue($schedule->match_week_interval($input1));
		$this->assertTrue($schedule->match_week_interval($input2));

		// Day of week should match
		$this->assertEquals($input1->get_day_of_week(), MONDAY);
		$this->assertEquals($input2->get_day_of_week(), FRIDAY);

		// Day of week should be contained in plucked days
		$this->assertTrue(in_array($input2->get_day_of_week(), $dow_output));
		$this->assertTrue(in_array($input1->get_day_of_week(), $dow_output));

	}

	/**
	 * plucking a non-existent key should result in an empty array
	 */
	#[Group('recurring_downtime')]
	public function test_pluck_invalid_unit() {
		$mock = get_week_mock(3, array(MONDAY, TUESDAY));
		$schedule = new RecurringDowntime($mock);

		$result = $schedule->pluck_recurrence('UNKNOWN_UNIT');
		$this->assertEquals(array(), $result);
	}

	/**
	 * Test case for recurring downtimes scheduled on a Sunday
	 */
	#[Group('recurring_downtime')]
	public function test_recurring_sunday() {
		$mock = get_week_mock(1, array(SUNDAY));
		$mock->set_start('2019-01-01 11:55');
		$mock->set_end('2019-01-01 13:00');
		$schedule = new RecurringDowntime($mock);
		$input = mock_date('2019-05-26');
		// Match week
		$this->assertTrue($schedule->match_week_interval($input));
		// Match day
		$days = $schedule->pluck_recurrence('day');
		$this->assertTrue(in_array($input->get_day_of_week(), $days));
	}
}
