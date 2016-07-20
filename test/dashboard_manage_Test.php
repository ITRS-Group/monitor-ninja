<?php
/**
 * Tests dashboard management.
 *
 * This means adding, deleting and renaming dashboards, but not widget handling
 */
class Dashboard_Manage_Test extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->mock_add('op5config', new MockConfig(array(
			'auth' => array(
				'common' => array(
					'default_auth' => 'mydefault',
					'session_key'  => false
				),
				'mydefault'  => array(
					'driver' => 'Default'
				)
			)
		)));
	}

	protected function tearDown() {
		op5objstore::instance()->mock_clear();
	}

	private function mock_data($tables) {
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add(
				$driver,
				new ORMDriverNative($tables, null, $driver)
			);
		}
	}

	public function test_adding_dashboard() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(),
				'dashboard_widgets' => array()
			)
		));

		$sut = new Tac_Controller();

		/*
		 * Create a dashboard
		 *
		 * Requirement: Count increases when creating
		 */
		$this->assertEquals(0, DashboardPool_Model::all()->count());
		$sut->new_dashboard();
		$this->assertEquals(1, DashboardPool_Model::all()->count());
	}

	public function test_renaming_dashboard() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(),
				'dashboard_widgets' => array()
			)
		));

		$sut = new Tac_Controller();

		/*
		 * Store reference to the created dashboard
		 * Store it as a set to. The $set->one() actually fetches from the database
		 */
		$dashboard_id = DashboardPool_Model::all()->one()->get_id();
		$db_set = DashboardPool_Model::set_by_key($dashboard_id);

		/*
		 * Rename a dashboard
		 *
		 * Requirement: The new name isn't valid before, but after
		 */
		$this->assertNotEquals("Everything and anything", $db_set->one()->get_name());
		$_POST = array(
			'dashboard_id' => $dashboard_id,
			'name' => 'Everything and anything'
		);
		$sut->rename_dashboard();
		$this->assertEquals("Everything and anything", $db_set->one()->get_name());
	}

	public function test_deleting_dashboard() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(),
				'dashboard_widgets' => array()
			)
		));

		$sut = new Tac_Controller();

		/*
		 * Store reference to the created dashboard
		 * Store it as a set to. The $set->one() actually fetches from the database
		 */
		$dashboard_id = DashboardPool_Model::all()->one()->get_id();
		$db_set = DashboardPool_Model::set_by_key($dashboard_id);

		/*
		 * Deleting a dashboard
		 *
		 * Requirement: The count goes back to 0
		 * Requirement: The reffered dashboard isn't found anymore
		 */
		$this->assertEquals(1, DashboardPool_Model::all()->count());
		$this->assertInstanceOf('Dashboard_Model', $db_set->one());
		$_POST = array(
			'dashboard_id' => $dashboard_id
		);
		$sut->delete_dashboard();
		$this->assertEquals(0, DashboardPool_Model::all()->count());
		$this->assertNull($db_set->one());
	}
}