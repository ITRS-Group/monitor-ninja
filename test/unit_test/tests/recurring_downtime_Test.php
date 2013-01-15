<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Recurring_downtime_Test extends TapUnit {

	// Save the id of the created schedule to erase the right one
	private $dtschedule_id = 0;
	// Timestamp for runtime of tests
	private $test_time = 1;

	/**
	 *	Set up prerequisities for this test
	 */
	public function setUp() {
		$this->auth = Auth::instance(array('session_key' => false))->force_user(new Op5User_AlwaysAuth());
	}

	/**
	 *	Test creation of recurring schedule
	 *	This will create a schedule for every day in every month. 
	 */
	public function test_create_recurring_schedule() {
		$data = array("report_type" => "hosts", "host_name" => array("monitor"), "comment" => "Recurring Downtime Test Schedule", "time" => "12:00", "duration" => "2:00", "fixed" => "1", "triggered_by" =>"0", "recurring_day" => array("1","2","3","4","5","6","0"), "recurring_month" => array("1","2","3","4","5","6","7","8","9","10","11","12"));
		@ScheduleDate_Model::edit_schedule($data, false);
		$sql = "SELECT id FROM recurring_downtime WHERE data LIKE '%Recurring Downtime Test Schedule%' ORDER BY id DESC";
		$db = Database::instance();
		$result = $db->query($sql);
		@$this->dtschedule_id = $result[0]->id;
		$this->ok(count($result) === 1, "Test schedule was created.");
	}

	/**
	 *	Test if there is a schedule active.
	 *	FIXME! Running these tests will not create downtimes on test config. 
	 *	Instead they remain on installed config with no way to delete them during test.
	 */
	public function test_is_scheduled() {
		// Test if the host/service is scheduled on a Monday. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 11, 11, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on a Monday");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Test if the host/service is scheduled on a Tuesday. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 11, 12, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on a Tuesday");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Test if the host/service is scheduled on a Wednesday. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 11, 13, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on a Wednesday");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Test if the host/service is scheduled on a Thursday. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 11, 14, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on a Thursday");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Test if the host/service is scheduled on a Friday. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 11, 15, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on a Friday");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Test if the host/service is scheduled on a Saturday. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 11, 16, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on a Saturday");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Test if the host/service is scheduled on a Sunday. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 11, 17, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on a Sunday");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Test if the host/service is scheduled on the 1 of the following month. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 11, 30, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on the first of a new month");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Test if the host/service is scheduled on the 29 of February. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 2, 28, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on a leap day");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Test if the host/service is scheduled on the 1 of January the following year. Inject created test time.
		$this->test_time = mktime(22, 0, 0, 12, 31, 2036);
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(empty($output), "Successfully scheduled on a new years day");
		// Test that we cannot create duplicate schedules. Function should return skipping to STDERR if duplicate.
		exec('/usr/bin/php '.$_SERVER['argv'][0].' default/cron/downtime/'.$this->test_time, $output, $status);
		$this->ok(!empty($output), "Couldn't create duplicate schedule");

		// Remove downtimes when tests are done.
		$downtime_data = Old_Downtime_Model::get_downtime_data();
		$cmd = "DEL_HOST_DOWNTIME;";
		$pipe = System_Model::get_pipe();
		foreach ($downtime_data as $data) {
			nagioscmd::submit_to_nagios($cmd . $data['id'], $pipe);
		}
	}

	/**
	 *	Test deletion of the created schedule
	 */
	public function test_delete_recurring_schedule() {
		$db = Database::instance();
		$sql = "DELETE FROM recurring_downtime WHERE id = " . $db->escape($this->dtschedule_id);
		$result = $db->query($sql);
		$this->ok(count($result) === 1, "Test schedule was deleted.");
	}
}
