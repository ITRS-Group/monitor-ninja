<?php
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Recurring_downtime_Test extends PHPUnit_Framework_TestCase {
	var $scheduletimeofday = "12:00";
	var $scheduleendtime = "15:00";
	var $basecomment = 'Recurring Downtime Test Schedule For ';

	/**
	 *	Set up prerequisities for this test
	 */
	public function setUp() {
		$this->auth = Auth::instance(array('session_key' => false))->force_user(new User_AlwaysAuth_Model());

		$this->sd = new ScheduleDate_Model();

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

	public function tearDown() {
		$db = Database::instance();
		$db->query("TRUNCATE TABLE recurring_downtime");
		$db->query("TRUNCATE TABLE recurring_downtime_objects");

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
			exec('/usr/bin/php index.php default/cron/downtime/'.$time.' 2>&1', $output, $status);
			$this->assertEquals(0, $status, 'Return code should be zero');
			$this->assertNotEmpty($output, "$description twice should give error");
		}

		$ids = array();
		foreach ($tests as $time => $description) {
			$dt = $ls->getDowntimes(array('filter' => array('start_time' => strtotime("{$this->scheduletimeofday} +1 day", $time)), 'columns' => array('id')));
			$this->assertCount($old_count[$time], $dt, 'There should still be the same number of matching downtimes from '.$description);
			foreach ($dt as $row) {
				$ids[] = $row['id'];
			}
		}

		$this->assertCount($current_number, $ls->getDowntimes(array('filter' => array('comment' => $comment))), 'Still same number of downtimes in total with our comment');

		// Remove downtimes when tests are done.
		$cmd = (strpos($type, 'host') !== false) ? 'DEL_HOST_DOWNTIME;' : 'DEL_SVC_DOWNTIME;';
		foreach ($ids as $id) {
			$res = nagioscmd::submit_to_nagios($cmd . $id);
			$this->assertTrue($res, 'Host delete command was submitted');
		}

		$this->assertCount(0, $ls->getDowntimes(array('filter' => array('comment' => $comment))), "Downtimes are gone after deleting them");
	}

	public function cron($tests, $type, $expected_number) {
		$comment = $this->basecomment . $type;
		foreach ($tests as $time => $description) {
			$output = '';
			exec('/usr/bin/php index.php default/cron/downtime/'.$time.' 2>&1', $output, $status);
			$this->assertEmpty($output, $description ." returned output: ".implode("\n", $output));
			$this->assertEquals(0, $status, 'Return code should be zero');
		}

		sleep(3); # Y U SO SLOW?

		$ls = Livestatus::instance();
		foreach ($tests as $time => $description) {
			$dt = $ls->getDowntimes(array('filter' => array('start_time' => strtotime("{$this->scheduletimeofday} +1 day", $time)), 'columns' => array('comment'), 'auth' => false));
			$this->assertCount($expected_number, $dt, "Unexpected number of downtimes created from $description");
			foreach ($dt as $row) {
				$this->assertEquals('AUTO: ' . $comment, $row['comment'], "Downtimes matching $description should have proper comment");
			}
		}
	}

	/**
	 *	Test if everyday schedule for hosts work
	 */
	public function test_schedule_hosts() {
		$comment = $this->basecomment.'hosts';
		$data = array(
			'author' => 'me',
			"downtime_type" => "hosts",
			"objects" => array("monitor"),
			"comment" => $comment,
			"start_time" => $this->scheduletimeofday,
			"end_time" => $this->scheduleendtime,
			"duration" => "2:00",
			"fixed" => "1",
			"weekdays" => array("1","2","3","4","5","6","0"),
			"months" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		$id;
		$this->assertTrue($this->sd->edit_schedule($data, $id));
		$db = Database::instance();
		$sql = "SELECT id FROM recurring_downtime WHERE comment = {$db->escape($comment)} ORDER BY id DESC";
		$result = $db->query($sql);
		$this->assertCount(1, $result, "After creating a new schedule, there's only one with that name");

		$this->cron($this->basictests, 'hosts', 1);
		$this->resubmit_and_cleanup($this->basictests, 'hosts');

		$sql = "DELETE FROM recurring_downtime WHERE comment = {$db->escape($comment)}";
		$result = $db->query($sql);
		$this->assertCount(1, $result, "One schedule was deleted.");
	}

	/**
	 *	Test if everyday schedule for hosts work
	 */
	public function test_schedule_hostgroups() {
		$comment = $this->basecomment . 'hostgroups';
		$data = array(
			'author' => 'me',
			"downtime_type" => "hostgroups",
			"objects" => array("hostgroup_up", "hostgroup_all"),
			"comment" => $comment,
			"start_time" => $this->scheduletimeofday,
			'end_time' => $this->scheduleendtime,
			"duration" => "2:00",
			"fixed" => "1",
			"weekdays" => array("1","2","3","4","5","6","0"),
			"months" => array("1","2","3","4","5","6","7","8","9","10","11","12"));

		# The number is wrong.
		# Any overlapping hosts will be added twice.
		# However, it's slightly better with two downtimes than none.
		$number = 0;
		$ls = Livestatus::instance();
		foreach ($data['objects'] as $group) {
			$number += count($ls->getHosts(array('columns' => array('name'), 'filter' => array('groups' => array('>=' => $group)))));
		}

		$id;
		$this->assertTrue($this->sd->edit_schedule($data, $id));
		$db = Database::instance();
		$sql = "SELECT id FROM recurring_downtime WHERE comment = {$db->escape($comment)} ORDER BY id DESC";
		$result = $db->query($sql);
		$this->assertCount(1, $result, "After creating a new schedule, there's only one with that name");

		$this->cron($this->basictests, 'hostgroups', $number);
		$this->resubmit_and_cleanup($this->basictests, 'hostgroups');

		$sql = "DELETE FROM recurring_downtime WHERE comment = {$db->escape($comment)}";
		$result = $db->query($sql);
		$this->assertCount(1, $result, "One schedule was deleted.");
	}

	/**
	 *↦ Test if everyday schedule for services work
	 */
	public function test_schedule_services() {
		$comment = $this->basecomment . 'services';
		$data = array(
			'author' => 'me',
			"downtime_type" => "services",
			"objects" => array("host_down;service ok", "host_down;service critical"),
			"comment" => $comment,
			"start_time" => $this->scheduletimeofday,
			'end_time' => $this->scheduleendtime,
			"duration" => "2:00",
			"fixed" => "1",
			"weekdays" => array("1","2","3","4","5","6","0"),
			"months" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		$id;
		$this->assertTrue($this->sd->edit_schedule($data, $id));
		$db = Database::instance();
		$sql = "SELECT id FROM recurring_downtime WHERE comment = {$db->escape($comment)} ORDER BY id DESC";
		$result = $db->query($sql);
		$this->assertCount(1, $result, "After creating a new schedule, there's only one with that name");

		$this->cron($this->basictests, 'services', 2);
		$this->resubmit_and_cleanup($this->basictests, 'services');

		$db = Database::instance();
		$sql = "DELETE FROM recurring_downtime WHERE comment = {$db->escape($comment)}";
		$result = $db->query($sql);
		$this->assertCount(1, $result, "One schedule was deleted.");
	}

	/**
	 *↦ Test if everyday schedule for services work
	 */
	public function test_schedule_servicegroups() {
		$comment = $this->basecomment . 'servicegroups';
		$data = array(
			'author' => 'me',
			"downtime_type" => "servicegroups",
			"objects" => array("servicegroup_ok", "servicegroup_critical"),
			"comment" => $comment,
			"start_time" => $this->scheduletimeofday,
			'end_time' => $this->scheduleendtime,
			"duration" => "2:00",
			"fixed" => "1",
			"weekdays" => array("1","2","3","4","5","6","0"),
			"months" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		$id;
		$this->assertTrue($this->sd->edit_schedule($data, $id));
		$db = Database::instance();
		$sql = "SELECT id FROM recurring_downtime WHERE comment = {$db->escape($comment)} ORDER BY id DESC";
		$result = $db->query($sql);
		$this->assertCount(1 ,$result, "After creating a new schedule, there's only one with that name");

		# The number is wrong.
		# Any overlapping hosts will be added twice.
		# However, it's slightly better with two downtimes than none.
		$number = 0;
		$ls = Livestatus::instance();
		foreach ($data['objects'] as $group) {
			$number += count($ls->getServices(array('filter' => array('groups' => array('>=' => $group)))));
		}

		$this->cron($this->basictests, 'servicegroups', $number);
		$this->resubmit_and_cleanup($this->basictests, 'servicegroups');

		$db = Database::instance();
		$sql = "DELETE FROM recurring_downtime WHERE comment = {$db->escape($comment)}";
		$result = $db->query($sql);
		$this->assertCount(1, $result, "One schedule was deleted.");
	}

	public function test_host_noschedule() {
		$comment = $this->basecomment . "hosts";
		$data = array(
			'author' => 'me',
			"downtime_type" => "hosts",
			"objects" => array("monitor"),
			"comment" => $comment,
			"start_time" => $this->scheduletimeofday,
			'end_time' => $this->scheduleendtime,
			"duration" => "2:00",
			"fixed" => "1",
			"weekdays" => array("1","2","3","4","5","6"),
			"months" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		$id;
		$this->assertTrue($this->sd->edit_schedule($data, $id));

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
		$sql = "DELETE FROM recurring_downtime WHERE comment = {$db->escape($comment)}";
		$result = $db->query($sql);
		$this->assertCount(1, $result, "One schedule should be deleted.");
	}

	public function test_host_acrossmidnight() {
		$comment = $this->basecomment . "hosts";
		$data = array(
			'author' => 'me',
			"downtime_type" => "hosts",
			"objects" => array("monitor"),
			"comment" => $comment,
			"start_time" => $this->scheduleendtime,
			'end_time' => $this->scheduletimeofday,
			"duration" => "2:00",
			"fixed" => "1",
			"weekdays" => array("1","2","3","4","5","6","0"),
			"months" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		$id;
		$this->assertTrue($this->sd->edit_schedule($data, $id));
		$time = mktime(23, 50, 0, 11, 11, 2036);

		$output = '';
		exec('/usr/bin/php index.php default/cron/downtime/'.$time.' 2>&1', $output, $status);
		$this->assertEmpty($output, "acrossmidnight returned output: ".implode("\n", $output));
		$this->assertEquals(0, $status, 'Return code should be zero');

		sleep(3); # Y U SO SLOW?

		$ls = Livestatus::instance();
		$dt = $ls->getDowntimes(array('filter' => array('start_time' => strtotime("{$this->scheduleendtime} +1 day", $time), 'end_time' => strtotime("{$this->scheduletimeofday} +2 days", $time)), 'columns' => array('id'), 'auth' => false));
		$this->assertCount(1, $dt, "Unexpected number of downtimes created from acrossmidnight");

		// Remove downtimes when tests are done.
		$cmd = 'DEL_HOST_DOWNTIME;';
		foreach ($dt as $id) {
			$res = nagioscmd::submit_to_nagios($cmd . $id['id']);
			$this->assertTrue($res, 'Host delete command was submitted');
		}

		$this->assertCount(0, $ls->getDowntimes(array('filter' => array('start_time' => strtotime("{$this->scheduleendtime} +1 day", $time), 'end_time' => strtotime("{$this->scheduletimeofday} +2 days", $time)))), "Downtimes are gone after deleting them");
	}

	public function test_realistic_time() {
		$comment = $this->basecomment . "tomorrow";
		$data = array(
			'author' => 'me',
			"downtime_type" => "hosts",
			"objects" => array("monitor"),
			"comment" => $comment,
			"start_time" => $this->scheduleendtime,
			'end_time' => $this->scheduletimeofday,
			"duration" => "2:00",
			"fixed" => "1",
			"weekdays" => array("1","2","3","4","5","6","0"),
			"months" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		$id;
		$this->assertTrue($this->sd->edit_schedule($data, $id));

		$output = '';
		exec('/usr/bin/php index.php default/cron/downtime 2>&1', $output, $status);
		$this->assertEmpty($output, "acrossmidnight returned output: ".implode("\n", $output));
		$this->assertEquals(0, $status, 'Return code should be zero');

		sleep(3); # Y U SO SLOW?

		$ls = Livestatus::instance();
		$dt = $ls->getDowntimes(array('filter' => array('start_time' => strtotime("{$this->scheduleendtime} +1 day"), 'end_time' => strtotime("{$this->scheduletimeofday} +2 days")), 'columns' => array('id'), 'auth' => false));
		$this->assertCount(1, $dt, "Unexpected number of downtimes created from acrossmidnight");

		// Remove downtimes when tests are done.
		$cmd = 'DEL_HOST_DOWNTIME;';
		foreach ($dt as $id) {
			$res = nagioscmd::submit_to_nagios($cmd . $id['id']);
			$this->assertTrue($res, 'Host delete command was submitted');
		}

		$this->assertCount(0, $ls->getDowntimes(array('filter' => array('start_time' => strtotime("{$this->scheduleendtime} +1 day"), 'end_time' => strtotime("{$this->scheduletimeofday} +2 days")))), "Downtimes are gone after deleting them");
	}

	public function test_migration()
	{
		$db = Database::instance();
		$db->query('INSERT INTO recurring_downtime(id, author, downtime_type, data) VALUES (1337, "monitor", "hosts", \'a:9:{s:11:"report_type";s:5:"hosts";s:9:"host_name";a:1:{i:0;s:7:"monitor";}s:7:"comment";s:15:"I am a comment.";s:4:"time";s:5:"12:00";s:8:"duration";s:4:"2:00";s:5:"fixed";s:1:"1";s:12:"triggered_by";s:1:"0";s:13:"recurring_day";a:7:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";i:5;s:1:"6";i:6;s:1:"0";}s:15:"recurring_month";a:12:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";i:5;s:1:"6";i:6;s:1:"7";i:7;s:1:"8";i:8;s:1:"9";i:9;s:2:"10";i:10;s:2:"11";i:11;s:2:"12";}}\')');
		$db->query('INSERT INTO recurring_downtime(id, author, downtime_type, data) VALUES (1338, "monitor", "services", \'a:8:{s:11:"report_type";s:8:"services";s:19:"service_description";a:1:{i:0;s:20:"monitor;Disk usage /";}s:7:"comment";s:36:"This is a comment of a service rsdt.";s:4:"time";s:5:"16:00";s:8:"duration";s:4:"4:00";s:5:"fixed";s:1:"1";s:12:"triggered_by";s:1:"0";s:13:"recurring_day";a:1:{i:0;s:1:"4";}}\')');
		$db->query('INSERT INTO recurring_downtime(id, author, downtime_type, data) VALUES (1339, "monitor", "services", \'a:8:{s:11:"report_type";s:8:"services";s:19:"service_description";a:1:{i:0;s:20:"monitor;Disk usage /";}s:7:"comment";s:46:"This scheduled downtime is as long as longcat.";s:4:"time";s:4:"0:00";s:8:"duration";s:5:"24:00";s:5:"fixed";s:1:"1";s:12:"triggered_by";s:1:"0";s:13:"recurring_day";a:1:{i:0;s:1:"4";}}\')');
		exec('/usr/bin/php index.php cli/upgrade_recurring_downtime 2>&1', $output, $status);
		$this->assertEquals(0, $status, "Wanted the exit code 0, got '$status'. Output: ".var_export($output, true));
		$this->assertEquals(array(), $output);
		$res = $db->query('SELECT * FROM recurring_downtime WHERE id = 1337');
		$res = $res->result_array(false);
		$this->assertCount(1, $res);
		unset($res[0]['data']);
		unset($res[0]['id']);
		unset($res[0]['last_update']);
		$this->assertEquals(
			array(
				'author' => 'monitor',
				'downtime_type' => 'hosts',
				'comment' => 'I am a comment.',
				'start_time' => '43200',
				'end_time' => '50400',
				'duration' => '7200',
				'fixed' => '1',
				'weekdays' => 'a:7:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";i:5;s:1:"6";i:6;s:1:"0";}',
				'months' => 'a:12:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";i:5;s:1:"6";i:6;s:1:"7";i:7;s:1:"8";i:8;s:1:"9";i:9;s:2:"10";i:10;s:2:"11";i:11;s:2:"12";}'
			),
			$res[0]
		);
		$res = $db->query('SELECT * FROM recurring_downtime WHERE id = 1338');
		$res = $res->result_array(false);
		$this->assertCount(1, $res);
		unset($res[0]['data']);
		unset($res[0]['id']);
		unset($res[0]['last_update']);
		$this->assertEquals(
			array(
				'author' => 'monitor',
				'downtime_type' => 'services',
				'comment' => 'This is a comment of a service rsdt.',
				'start_time' => '57600',
				'end_time' => '72000',
				'duration' => '14400',
				'fixed' => '1',
				'weekdays' => 'a:1:{i:0;s:1:"4";}',
				'months' => 'a:0:{}'
			),
			$res[0]
		);
		$res = $db->query('SELECT * FROM recurring_downtime WHERE id = 1339');
		$res = $res->result_array(false);
		$this->assertCount(1, $res);
		unset($res[0]['data']);
		unset($res[0]['id']);
		unset($res[0]['last_update']);
		$this->assertEquals(
			array(
				'author' => 'monitor',
				'downtime_type' => 'services',
				'comment' => 'This scheduled downtime is as long as longcat.',
				'start_time' => '0',
				'end_time' => '86400',
				'duration' => '86400',
				'fixed' => '1',
				'weekdays' => 'a:1:{i:0;s:1:"4";}',
				'months' => 'a:0:{}'
			),
			$res[0]
		);
	}
}
