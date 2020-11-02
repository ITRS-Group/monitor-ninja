<?php

require_once 'includes/downtime.php';

function get_exclude_schedule($days) {
	$mock = new DowntimeDayModel();
	$mock->set_recurrence(1);
	$mock->set_exclude_days($days);
	return new RecurringDowntime($mock);
}


class Downtime_Test extends PHPUnit_Framework_TestCase {
	/**
	 * External command input type should translate into the expected downtime command
	 * @group recurring_downtime
	 */
	public function test_downtime_types() {
		function get_command_type($type) {
			$mock = new DowntimeDayModel();
			$mock->set_downtime_type($type);
			$sched = new Downtime($mock);
			$target_date = new NinjaDateTime('today');
			$mappings = $sched->get_command_mappings('test', $sched->get_window($target_date));
			return $mappings['cmd'];
		}

		$type_hosts = get_command_type('hosts');
		$this->assertEquals($type_hosts, 'SCHEDULE_HOST_DOWNTIME');

		$type_services = get_command_type('services');
		$this->assertEquals($type_services, 'SCHEDULE_SVC_DOWNTIME');

		$type_hostgroups = get_command_type('hostgroups');
		$this->assertEquals($type_hostgroups, 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME');

		$type_servicegroups = get_command_type('servicegroups');
		$this->assertEquals($type_servicegroups, 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME');
	}

	/**
	 * External command string generated should contain the expected start and end dates
	 * @group recurring_downtime
	 */
	public function test_downtime_command_start_end() {
		$start = mock_date('1984-01-13 10:12:13');
		$end = mock_date('1984-01-13 12:13:14');
		$target_date = new NinjaDateTime('today');

		$mock = new DowntimeDayModel();
		$mock->set_downtime_type('hosts');
		$mock->set_start($start->get_datetime());
		$mock->set_end($end->get_datetime());

		$sched = new Downtime($mock);
		$mappings = $sched->get_command_mappings('test', $sched->get_window($target_date));

		$expected_start = clone $target_date;
		$expected_end = clone $target_date;
		$expected_start->setTime(10, 12, 13);
		$expected_end->setTime(12, 13, 14);

		$this->assertEquals($mappings['start'], $expected_start->getTimestamp());
		$this->assertEquals($mappings['end'], $expected_end->getTimestamp());
	}

	/**
	 * is_excluded() should evaluate to true if the given date is within range
	 * @group recurring_downtime
	 */
	public function test_exclude_days_range_included() {
		$sched = get_exclude_schedule('2019-03-13 to 2019-03-16, 2019-03-13 to 2019-03-18');
		$this->assertTrue($sched->is_excluded('2019-03-13'));
		$this->assertTrue($sched->is_excluded('2019-03-14'));
		$this->assertTrue($sched->is_excluded('2019-03-15'));
		$this->assertTrue($sched->is_excluded('2019-03-16'));
		$this->assertTrue($sched->is_excluded('2019-03-17'));
		$this->assertTrue($sched->is_excluded('2019-03-18'));
		$this->assertFalse($sched->is_excluded('2019-03-19'));
		$this->assertFalse($sched->is_excluded('2019-03-12'));
	}

	/**
	 * is_excluded() should evaluate to false if the given date is /not/ within range
	 * @group recurring_downtime
	 */
	public function test_exclude_days_range_not_included() {
		$sched = get_exclude_schedule('2019-03-13 to 2019-03-16, 2019-03-17 to 2019-03-18');
		$this->assertFalse($sched->is_excluded('2019-03-19'));
		$this->assertFalse($sched->is_excluded('2019-03-12'));
	}

	/**
	 * is_excluded() should evaluate to true if the given date is excluded
	 * @group recurring_downtime
	 */
	public function test_exclude_days_single_included() {
		$sched = get_exclude_schedule('2019-03-13, 2019-03-14');
		$this->assertTrue($sched->is_excluded('2019-03-13'));
		$this->assertTrue($sched->is_excluded('2019-03-14'));
	}

	/**
	 * is_excluded() should evaluate to true if the given date is excluded
	 * @group recurring_downtime
	 */
	public function test_exclude_days_single_not_included() {
		$sched = get_exclude_schedule('1984-09-25, 1984-09-27');
		$this->assertFalse($sched->is_excluded('1984-09-24'));
		$this->assertFalse($sched->is_excluded('1984-09-26'));
		$this->assertFalse($sched->is_excluded('2019-09-28'));
	}

	/**
	 * External command string generated should not have have certain words replaced
	 * See: MON-12387
	 * @group recurring_downtime
	 */
	public function test_no_reserved_words() {
		$start = mock_date('1984-01-13 10:12:13');
		$end = mock_date('1984-01-13 12:13:14');
		$target_date = new NinjaDateTime('today');

		$mock = new DowntimeDayModel();
		$mock->set_downtime_type('hostgroups');
		$mock->set_start($start->get_datetime());
		$mock->set_end($end->get_datetime());

		$sched = new Downtime($mock);
		$cmd = $sched->get_command('cmd-obj_name-start-end-is_fixed-trigger_id-duration-author-comment', $sched->get_window($target_date));

		$name = 'cmd-obj_name-start-end-is_fixed-trigger_id-duration-author-comment';
		# Assert name in cmd
		$this->assertTrue(strpos($cmd, $name) !== false);
	}


}
