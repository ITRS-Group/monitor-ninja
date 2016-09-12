<?php
class Dashboard_Share_Test extends PHPUnit_Framework_TestCase {
	private function mock_data($tables) {
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add($driver, new ORMDriverNative($tables, null, $driver));
		}
	}

	public function setUp() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(),
				'permission_quarks' => array(),
			),
		));
	}

	public function tearDown() {
		op5objstore::instance()->mock_clear();
	}

	/**
	 * @group MON-9539
	 * @group Dashboard_Model::set_read_perm
	 * @group Dashboard_Model::get_read_perm
	 */
	public function test_can_share_dashboard_through_set_read_perm() {
		$my_dashboard = new Dashboard_Model();
		$read_perm = $my_dashboard->get_read_perm();
		$this->assertEquals(array(), $read_perm,
			'read_perm() should default to empty for newly '.
			'created dashboard'
		);

		$quark_id = PermissionQuarkPool_Model::build('user', 'lykke');
		$this->assertGreaterThan(0, $quark_id,
			'A quark gets an auto incremented id, i.e. a number '.
			'greater than 0'
		);

		$read_perm[] = $quark_id;
		$my_dashboard->set_read_perm($read_perm);
		$this->assertEquals(array($quark_id), $my_dashboard->get_read_perm(),
			"read_perm() should now include the wanted user's ".
			"quark's id"
		);
	}

	/**
	 * @group MON-9539
	 * @group Dashboard_Model::share_with
	 */
	public function test_can_share_dashboard_through_share_with() {
		$my_dashboard = new Dashboard_Model();
		$read_perm = $my_dashboard->get_read_perm();
		$this->assertEquals(array(), $read_perm,
			'read_perm() should default to empty for newly '.
			'created dashboard'
		);

		$my_dashboard->share_with('user', 'agnes');
		$this->assertCount(1, $my_dashboard->get_read_perm(),
			"read_perm() should now include the wanted user's ".
			"quark's id"
		);
	}

	/**
	 * @group MON-9539
	 * @group Dashboard_Model::share_with
	 */
	public function test_dashboard_share_with_is_idempotent() {
		$my_dashboard = new Dashboard_Model();
		$read_perm = $my_dashboard->get_read_perm();
		$this->assertEquals(array(), $read_perm,
			'read_perm() should default to empty for newly '.
			'created dashboard'
		);

		$my_dashboard->share_with('user', 'agnes');
		$this->assertCount(1, $my_dashboard->get_read_perm(),
			"read_perm() should now include the wanted user's ".
			"quark's id"
		);

		$my_dashboard->share_with('user', 'agnes');
		$read_perm = $my_dashboard->get_read_perm();
		$this->assertCount(1, $read_perm,
			"Even though we shared with the same user twice, the".
			"permissions should not be duplicated"
		);
		$this->assertEquals(
			$read_perm[0],
			PermissionQuarkPool_Model::build('user', 'agnes')
		);
	}

	/**
	 * @group MON-9539
	 * @group Dashboard_Model::get_shared_with
	 */
	public function test_dashboard_get_shared_with() {
		$my_dashboard = new Dashboard_Model();
		$shared_with = $my_dashboard->get_shared_with();
		$this->assertEquals(
			array(
				'user' => array(),
				'group' => array(),
			),
			$shared_with,
			'shared_with() should default to empty for newly '.
			'created dashboard'
		);

		$my_dashboard->share_with('user', 'no rest for the wicked');
		$read_perm = $my_dashboard->get_read_perm();
		$this->assertCount(1, $read_perm,
			"read_perm() should now include the wanted user's ".
			"quark's id"
		);
		$this->assertEquals(
			$read_perm[0],
			PermissionQuarkPool_Model::build('user', 'no rest for the wicked')
		);

		$shared_with = $my_dashboard->get_shared_with();
		$this->assertEquals(
			array(
				'user' => array('no rest for the wicked'),
				'group' => array(),
			),
			$shared_with,
			"Should have been able to share the dashboard"
		);
	}

	/**
	 * @group MON-9539
	 * @group Dashboard_Model::unshare_with
	 */
	public function test_can_unshare_dashboard() {
		$my_dashboard = new Dashboard_Model();
		$this->assertEquals(
			array(
				"user" => array(),
				"group" => array(),
			),
			$my_dashboard->get_shared_with(),
			"get_shared_with() should default to empty for newly ".
			"created dashboard"
		);

		$my_dashboard->share_with("user", "toxic a");
		$this->assertEquals(
			array(
				"user" => array("toxic a"),
				"group" => array(),
			),
			$my_dashboard->get_shared_with(),
			"share_with() should have shared the dashboard"
		);

		$my_dashboard->unshare_with("user", "toxic a");
		$this->assertEquals(
			array(
				'user' => array(),
				'group' => array(),
			),
			$my_dashboard->get_shared_with(),
			"unshare_with() should have unshared all of the ".
			"permissions"
		);
	}
}
