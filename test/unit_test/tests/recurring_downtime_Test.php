<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Recurring_downtime_Test extends TapUnit {
	var $scheduletimeofday = "12:00";
	var $basecomment = 'Recurring Downtime Test Schedule For ';

	/**
	 *	Set up prerequisities for this test
	 */
	public function setUp() {
		$this->auth = Auth::instance(array('session_key' => false))->force_user(new Op5User_AlwaysAuth());

		# Without this, super-failed test suites takes two test runs to heal. That's annoying.
		$db = Database::instance();
		$sql = "DELETE FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%'";
		$result = $db->query($sql);

		$this->basictests = array(
			mktime(23, 50, 0, 11, 11, 2036) => 'Schedule on a monday',
			mktime(23, 50, 0, 11, 12, 2036) => 'Schedule on a tuesday',
			mktime(23, 50, 0, 11, 13, 2036) => 'Schedule on a wednesday',
			mktime(23, 50, 0, 11, 14, 2036) => 'Schedule on a thursday',
			mktime(23, 50, 0, 11, 15, 2036) => 'Schedule on a friday',
			mktime(23, 50, 0, 11, 16, 2036) => 'Schedule on a saturday',
			mktime(23, 50, 0, 11, 17, 2036) => 'Schedule on a sunday',
			mktime(23, 50, 0, 11, 30, 2036) => 'Schedule the first of a new month',
			mktime(23, 50, 0, 2, 28, 2036) => 'Schedule on a leap day',
			mktime(23, 50, 0, 12, 31, 2036) => 'Schedule on new years day',
		);
	}

	public function resubmit_and_cleanup($tests, $type) {
		$ls = Livestatus::instance();
		$comment = $this->basecomment . $type;
		$current_number = count($ls->getDowntimes(array('filter' => array('comment' => $comment))));

		$old_count = array();

		foreach ($tests as $time => $description) {
			$old_count[$time] = count($ls->getDowntimes(array('filter' => array('start_time' => strtotime("{$this->scheduletimeofday} +1 day", $time)), 'columns' => array('id'))));
			$output = '';
			// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
			exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$time.' 2>&1', $output, $status);
			$this->ok_eq($status, 0, 'Return code should be zero');
			$this->ok(!empty($output), "$description twice should give error");
		}

		$ids = array();
		foreach ($tests as $time => $description) {
			$dt = $ls->getDowntimes(array('filter' => array('start_time' => strtotime("{$this->scheduletimeofday} +1 day", $time)), 'columns' => array('id')));
			$this->ok_eq(count($dt), $old_count[$time], 'There should still be the same number of matching downtimes from '.$description);
			foreach ($dt as $row) {
				$ids[] = $row['id'];
			}
		}

		$this->ok_eq($current_number, count($ls->getDowntimes(array('filter' => array('comment' => $comment)))), 'Still same number of downtimes in total with our comment');

		// Remove downtimes when tests are done.
		$cmd = (strpos($type, 'host') !== false) ? 'DEL_HOST_DOWNTIME;' : 'DEL_SVC_DOWNTIME;';
		$pipe = System_Model::get_pipe();
		foreach ($ids as $id) {
			$res = nagioscmd::submit_to_nagios($cmd . $id, $pipe);
			$this->ok($res, 'Host delete command was submitted');
		}
	}

	public function cron($tests, $type, $expected_number) {
		$comment = $this->basecomment . $type;
		foreach ($tests as $time => $description) {
			$output = '';
			exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$time.' 2>&1', $output, $status);
			$this->ok_eq($status, 0, 'Return code should be zero');
			$this->ok_empty($output, $description ." returned output: ".implode("\n", $output));
		}

		sleep(3); # Y U SO SLOW?

		$ls = Livestatus::instance();
		foreach ($tests as $time => $description) {
			$dt = $ls->getDowntimes(array('filter' => array('start_time' => strtotime("{$this->scheduletimeofday} +1 day", $time)), 'columns' => array('comment')));
			$this->ok_eq(count($dt), $expected_number, "Unexpected number of downtimes created from $description");
			foreach ($dt as $row) {
				$this->ok_eq('AUTO: ' . $comment, $row['comment'], "Downtimes matching $description should have proper comment");
			}
		}
	}

	/**
	 *	Test if everyday schedule for hosts work
	 */
	public function test_schedule_hosts() {
		$comment = $this->basecomment.'hosts';
		$data = array(
			"report_type" => "hosts",
			"host_name" => array("monitor"),
			"comment" => $comment,
			"time" => $this->scheduletimeofday,
			"duration" => "2:00",
			"fixed" => "1",
			"triggered_by" =>"0",
			"recurring_day" => array("1","2","3","4","5","6","0"),
			"recurring_month" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		ScheduleDate_Model::edit_schedule($data, false);
		$sql = "SELECT id FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%' ORDER BY id DESC";
		$db = Database::instance();
		$result = $db->query($sql);
		$this->ok_eq(count($result), 1, "After creating a new schedule, there's only one with that name");

		$this->cron($this->basictests, 'hosts', 1);
		$this->resubmit_and_cleanup($this->basictests, 'hosts');

		$db = Database::instance();
		$sql = "DELETE FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%'";
		$result = $db->query($sql);
		$this->ok_eq(count($result), 1, "One schedule was deleted.");
	}

	/**
	 *	Test if everyday schedule for hosts work
	 */
	public function test_schedule_hostgroups() {
		$comment = $this->basecomment . 'hostgroups';
		$data = array(
			"report_type" => "hostgroups",
			"hostgroup" => array("hostgroup_up", "hostgroup_all"),
			"comment" => $comment,
			"time" => $this->scheduletimeofday,
			"duration" => "2:00",
			"fixed" => "1",
			"triggered_by" =>"0",
			"recurring_day" => array("1","2","3","4","5","6","0"),
			"recurring_month" => array("1","2","3","4","5","6","7","8","9","10","11","12"));

		# The number is wrong.
		# Any overlapping hosts will be added twice.
		# However, it's slightly better with two downtimes than none.
		$number = 0;
		$ls = Livestatus::instance();
		foreach ($data['hostgroup'] as $group) {
			$number += count($ls->getHosts(array('columns' => array('name'), 'filter' => array('groups' => array('>=' => $group)))));
		}

		ScheduleDate_Model::edit_schedule($data, false);
		$sql = "SELECT id FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%' ORDER BY id DESC";
		$db = Database::instance();
		$result = $db->query($sql);
		$this->ok_eq(count($result), 1, "After creating a new schedule, there's only one with that name");

		$this->cron($this->basictests, 'hostgroups', $number);
		$this->resubmit_and_cleanup($this->basictests, 'hostgroups');

		$db = Database::instance();
		$sql = "DELETE FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%'";
		$result = $db->query($sql);
		$this->ok_eq(count($result), 1, "One schedule was deleted.");
	}

	/**
	 *↦ Test if everyday schedule for services work
	 */
	public function test_schedule_services() {
		$comment = $this->basecomment . 'services';
		$data = array(
			"report_type" => "services",
			"service_description" => array("host_down;service ok", "host_down;service critical"),
			"comment" => $comment,
			"time" => $this->scheduletimeofday,
			"duration" => "2:00",
			"fixed" => "1",
			"triggered_by" =>"0",
			"recurring_day" => array("1","2","3","4","5","6","0"),
			"recurring_month" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		ScheduleDate_Model::edit_schedule($data, false);
		$sql = "SELECT id FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%' ORDER BY id DESC";
		$db = Database::instance();
		$result = $db->query($sql);
		$this->ok_eq(count($result), 1, "After creating a new schedule, there's only one with that name");

		$this->cron($this->basictests, 'services', 2);
		$this->resubmit_and_cleanup($this->basictests, 'services');

		$db = Database::instance();
		$sql = "DELETE FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%'";
		$result = $db->query($sql);
		$this->ok_eq(count($result), 1, "One schedule was deleted.");
	}

	/**
	 *↦ Test if everyday schedule for services work
	 */
	public function test_schedule_servicegroups() {
		$comment = $this->basecomment . 'servicegroups';
		$data = array(
			"report_type" => "servicegroups",
			"servicegroup" => array("servicegroup_ok", "servicegroup_critical"),
			"comment" => $comment,
			"time" => $this->scheduletimeofday,
			"duration" => "2:00",
			"fixed" => "1",
			"triggered_by" =>"0",
			"recurring_day" => array("1","2","3","4","5","6","0"),
			"recurring_month" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		ScheduleDate_Model::edit_schedule($data, false);
		$sql = "SELECT id FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%' ORDER BY id DESC";
		$db = Database::instance();
		$result = $db->query($sql);
		$this->ok_eq(count($result), 1, "After creating a new schedule, there's only one with that name");

		# The number is wrong.
		# Any overlapping hosts will be added twice.
		# However, it's slightly better with two downtimes than none.
		$number = 0;
		$ls = Livestatus::instance();
		foreach ($data['servicegroup'] as $group) {
			$number += count($ls->getServices(array('filter' => array('groups' => array('>=' => $group)))));
		}

		$this->cron($this->basictests, 'servicegroups', $number);
		$this->resubmit_and_cleanup($this->basictests, 'servicegroups');

		$db = Database::instance();
		$sql = "DELETE FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%'";
		$result = $db->query($sql);
		$this->ok_eq(count($result), 1, "One schedule was deleted.");
	}

	public function test_host_noschedule() {
		$comment = $this->basecomment . "hosts";
		$data = array(
			"report_type" => "hosts",
			"host_name" => array("monitor"),
			"comment" => $comment,
			"time" => $this->scheduletimeofday,
			"duration" => "2:00",
			"fixed" => "1",
			"triggered_by" =>"0",
			"recurring_day" => array("1","2","3","4","5","6"),
			"recurring_month" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		ScheduleDate_Model::edit_schedule($data, false);

		$tests_expected = array(
			strtotime("2036-01-17 23:50") => "Schedule on thursday when sunday is excluded",
		);
		$tests_unexpected = array(
			strtotime("2036-01-19 23:50") => "Schedule on saturday when sunday is excluded",
		);

		$this->cron($tests_expected, 'hosts', 1);
		$this->resubmit_and_cleanup($tests_expected, 'hosts');

		$this->cron($tests_unexpected, 'hosts', 0);
		# Honey, I swear, Nothing Happened, so there's nothing to clean up!

		$db = Database::instance();
		$sql = "DELETE FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%'";
		$result = $db->query($sql);
		$this->ok_eq(count($result), 1, "One schedule was deleted.");
	}
}
