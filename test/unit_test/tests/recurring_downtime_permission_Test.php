<?php
class Recurring_downtime_permission_Test extends PHPUnit_Framework_TestCase
{
	public function createDowntime($data)
	{
		foreach (ScheduleDate_Model::$valid_fields as $field) {
			$this->assertArrayHasKey($field, $data, "Incomplete data array");
		}
		$this->assertContains($data['downtime_type'], ScheduleDate_Model::$valid_types);
		$sd = new ScheduleDate_Model();
		$id;
		$this->assertTrue($sd->edit_schedule($data, $id));
		$this->created[] = $id;
	}

	public function tearDown()
	{
		$this->auth = Auth::instance(array('session_key' => false))->force_user(new Op5User_AlwaysAuth());
		$this->auth->set_authorized_for('host_view_all', true);
		$this->auth->set_authorized_for('service_view_all', true);
		$this->auth->set_authorized_for('hostgroup_view_all', true);
		$this->auth->set_authorized_for('servicegroup_view_all', true);
		$this->auth->set_authorized_for('host_edit_all', true);
		$this->auth->set_authorized_for('service_edit_all', true);
		$this->auth->set_authorized_for('hostgroup_edit_all', true);
		$this->auth->set_authorized_for('servicegroup_edit_all', true);
		$sd = new ScheduleDate_Model();
		foreach ($this->created as $id) {
			$this->assertTrue($sd->delete_schedule($id));
		}
		$db = Database::instance();
		$res = $db->query("SELECT * FROM recurring_downtime");
		$this->assertCount(0, $res);
		$res = $db->query("SELECT * FROM recurring_downtime_objects");
		$this->assertCount(0, $res);
	}

	public function setUp()
	{
		$this->auth = Auth::instance(array('session_key' => false))->force_user(new Op5User_AlwaysAuth());
		$this->created = array();


		$this->createDowntime(array(
			'author' => 'me',
			'downtime_type' => 'hosts',
			'objects' => array('monitor'),
			'comment' => 'devs break all the hosts',
			'start_time' => '08:00', // PHP better not call that octal.
			'end_time' => '17:00',
			'duration' => false,
			'fixed' => true,
			'weekdays' => array(1,2,3,4,5,6,0),
			'months' => array(1,2,3,4,5,6,7,8,9,10,11,12)
		));
		$this->createDowntime(array(
			'author' => 'me',
			'downtime_type' => 'services',
			'objects' => array('host_down;service ok'),
			'comment' => 'devs break all the services',
			'start_time' => '9:00',
			'end_time' => '18:00',
			'duration' => false,
			'fixed' => true,
			'weekdays' => array(1,2,3,4,5,6,0),
			'months' => array(1,2,3,4,5,6,7,8,9,10,11,12)
		));
		$this->createDowntime(array(
			'author' => 'me',
			'downtime_type' => 'hostgroups',
			'objects' => array('hostgroup_up'),
			'comment' => 'devs break all the hostgroups',
			'start_time' => '10:00',
			'end_time' => '12:00',
			'duration' => '5:00',
			'fixed' => false,
			'weekdays' => array(1,2,3,4,5,6,0),
			'months' => array(1,2,3,4,5,6,7,8,9,10,11,12)
		));
		$this->createDowntime(array(
			'author' => 'me',
			'downtime_type' => 'servicegroups',
			'objects' => array('servicegroup_ok'),
			'comment' => 'devs break all the servicegroups',
			'start_time' => '11:00',
			'end_time' => '13:00',
			'duration' => '05:00',
			'fixed' => false,
			'weekdays' => array(1,2,3,4,5,6,0),
			'months' => array(1,2,3,4,5,6,7,8,9,10,11,12)
		));
	}

	public function testUnlimited()
	{
		$this->auth->set_authorized_for('host_view_all', true);
		$this->auth->set_authorized_for('service_view_all', true);
		$this->auth->set_authorized_for('hostgroup_view_all', true);
		$this->auth->set_authorized_for('servicegroup_view_all', true);
		$this->auth->set_authorized_for('host_edit_all', true);
		$stats = RecurringDowntimePool_Model::all();
		$this->assertCount(4, $stats);

		$one = $stats->it(array())->current()->export();
		$one['author'] = 'you';
		$sd = new ScheduleDate_Model();
		$id = $one['id'];
		unset($this->created[array_search($id, $this->created)]);
		$this->assertTrue($sd->edit_schedule($one, $id));

		$this->assertTrue($sd->delete_schedule($id));
		$stats = RecurringDowntimePool_Model::all();
		$this->assertCount(3, $stats);
	}

	public function testReadOnly()
	{
		$this->auth->set_authorized_for('host_view_all', true);
		$this->auth->set_authorized_for('service_view_all', true);
		$this->auth->set_authorized_for('hostgroup_view_all', true);
		$this->auth->set_authorized_for('servicegroup_view_all', true);
		$this->auth->set_authorized_for('host_edit_all', false);
		$this->auth->set_authorized_for('service_edit_all', false);
		$this->auth->set_authorized_for('hostgroup_edit_all', false);
		$this->auth->set_authorized_for('servicegroup_edit_all', false);
		$this->auth->set_authorized_for('host_edit_contact', false);
		$this->auth->set_authorized_for('service_edit_contact', false);
		$this->auth->set_authorized_for('hostgroup_edit_contact', false);
		$this->auth->set_authorized_for('servicegroup_edit_contact', false);
		$stats = RecurringDowntimePool_Model::all();
		$this->assertCount(4, $stats);

		$one = $stats->it(array())->current()->export();
		$one['author'] = 'you';
		$sd = new ScheduleDate_Model();
		$id = $one['id'];
		$this->assertFalse($sd->edit_schedule($one, $id));

		$this->assertFalse($sd->delete_schedule($id));
		$stats = RecurringDowntimePool_Model::all();
		$this->assertCount(4, $stats);
		$one = $stats->it(array('author'))->current();
		$this->assertEquals($one->get_author(), 'me');
	}

	public function testLimitedNoHost()
	{
		$this->auth->set_authorized_for('host_view_all', false);
		$this->auth->set_authorized_for('service_view_all', true);
		$this->auth->set_authorized_for('hostgroup_view_all', true);
		$this->auth->set_authorized_for('servicegroup_view_all', true);
		$stats = RecurringDowntimePool_Model::all();
		$this->assertCount(3, $stats);
	}

	public function testLimitedNoService()
	{
		$this->auth->set_authorized_for('host_view_all', true);
		$this->auth->set_authorized_for('service_view_all', false);
		$this->auth->set_authorized_for('hostgroup_view_all', true);
		$this->auth->set_authorized_for('servicegroup_view_all', true);
		$stats = RecurringDowntimePool_Model::all();
		$this->assertCount(3, $stats);
	}

	public function testLimitedNothing()
	{
		$this->auth->set_authorized_for('host_view_all', false);
		$this->auth->set_authorized_for('service_view_all', false);
		$this->auth->set_authorized_for('hostgroup_view_all', false);
		$this->auth->set_authorized_for('servicegroup_view_all', false);
		$stats = RecurringDowntimePool_Model::all();
		$this->assertCount(0, $stats);
	}

	public function testLimitedHost()
	{
		$this->auth = Auth::instance(array('session_key' => false))->force_user(new Op5User(array('username' => 'limited')));
		$stats = RecurringDowntimePool_Model::all();
		$this->assertCount(1, $stats);
		$obj = $stats->it(array('downtime_type', 'objects', 'start_time'))->current();
		$this->assertEquals('hosts', $obj->get_downtime_type());
		$this->assertEquals(array('monitor'), $obj->get_objects());
		$this->assertEquals(60 * 60 * 8 /* 08:00 as seconds */, $obj->get_start_time());
	}
}
