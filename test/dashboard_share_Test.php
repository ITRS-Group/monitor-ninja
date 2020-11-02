<?php
class Dashboard_Share_Test extends PHPUnit_Framework_TestCase {

	public function setUp() {
		op5objstore::instance()->mock_add(
			"ORMDriverMySQL default",
			new ORMDriverNative(
				array(
					'dashboards' => array(),
					'permission_quarks' => array(),
				),
				null,
				"ORMDriverMySQL default"
			)
		);
	}

	public function tearDown() {
		op5objstore::instance()->mock_clear();
	}

	/**
	 * @group MON-9539
	 */
	public function test_dashboard_set_read_perm_shares_dashboard() {
		$my_dashboard = new Dashboard_Model();
		$read_perm = $my_dashboard->get_read_perm();
		$this->assertSame(array(), $read_perm,
			'get_read_perm() should default to empty for newly '.
			'created dashboard'
		);

		$my_dashboard->set_read_perm(array('users' => array('lykke')));
		$this->assertSame(
			array(
				"users" => array(
					"lykke"
				)
			),
			$my_dashboard->get_read_perm(),
			"The dashboard is shared with a single user"
		);
	}

	/**
	 * @group MON-9539
	 * @depends test_dashboard_set_read_perm_shares_dashboard
	 */
	public function test_dashboard_set_read_perm_overwrites_existing() {
		$my_dashboard = new Dashboard_Model();
		$read_perm = $my_dashboard->get_read_perm();
		$this->assertSame(array(), $read_perm,
			'get_read_perm() should default to empty for newly '.
			'created dashboard'
		);

		$my_dashboard->set_read_perm(array(
			"users" => array(
				"tony",
				"rufus"
			)
		));
		$this->assertSame(
			array(
				"users" => array(
					"tony",
					"rufus"
				)
			),
			$my_dashboard->get_read_perm(),
			"The dashboard is shared with two users"
		);

		$my_dashboard->set_read_perm(array(
			"usergroups" => array(
				"don juan"
			)
		));
		$this->assertSame(
			array(
				"usergroups" => array(
					"don juan"
				)
			),
			$my_dashboard->get_read_perm(),
			"The dashboard is now only shared with a usergroup"
		);
	}

	/**
	 * @group MON-9539
	 */
	public function test_dashboard_add_read_perm_does_not_create_duplicates() {
		$my_dashboard = new Dashboard_Model();
		$read_perm = $my_dashboard->get_read_perm();
		$this->assertSame(array(), $read_perm,
			'get_read_perm() should default to empty for newly '.
			'created dashboard'
		);

		$my_dashboard->add_read_perm('users', 'agnes');
		$read_perm = $my_dashboard->get_read_perm();
		$this->assertCount(1, $read_perm,
			"There should be a single type of tables stored as ".
			"permitted to read the dashboard"
		);
		$this->assertArrayHasKey("users", $read_perm,
			"We should have stored a list of users"
		);
		$this->assertCount(1, $read_perm["users"],
			"read_perm() should now include the wanted user"
		);

		$my_dashboard->add_read_perm('users', 'agnes');
		$read_perm_after_second_add = $my_dashboard->get_read_perm();
		$this->assertCount(1, $read_perm_after_second_add["users"],
			"Even though we shared with the same user twice, the".
			"permissions should not be duplicated"
		);

		$this->assertEquals($read_perm, $read_perm_after_second_add,
			"The read permissions should look the same after ".
			"a second call to add_read_perm"
		);
	}

	/**
	 * @group MON-9539
	 */
	public function test_dashboard_remove_read_perm_unshares_dashboard() {
		$my_dashboard = new Dashboard_Model();
		$this->assertEquals(
			array(),
			$my_dashboard->get_read_perm(),
			"get_read_perm() should default to empty for newly ".
			"created dashboard"
		);

		$my_dashboard->set_read_perm(array(
			"users" => array(
				"toxic a",
				"belgoody"
			)
		));
		$this->assertEquals(
			array(
				"users" => array("toxic a", "belgoody"),
			),
			$my_dashboard->get_read_perm(),
			"set_read_perm() should have shared the dashboard"
		);

		$my_dashboard->remove_read_perm("users", "toxic a");
		$this->assertEquals(
			array(
				"users" => array("belgoody"),
			),
			$my_dashboard->get_read_perm(),
			"remove_read_perm() should have unshared the ".
			"dashboard with toxic a"
		);
	}
}
