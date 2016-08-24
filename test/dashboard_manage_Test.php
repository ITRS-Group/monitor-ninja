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

		$superuser = new User_AlwaysAuth_Model();

		Auth::instance(array('session_key' => false))
			->force_user($superuser);
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
				'dashboards' => array(
					array(
						'id' => '4',
						'name' => 'My dashboard',
						'username' => 'superuser',
						'layout' => '3,2,1'
					)
				),
				'dashboard_widgets' => array()
			)
		));

		$this->assertEquals(1, count(DashboardPool_Model::all()));

		$sut = new Tac_Controller();
		$sut->new_dashboard();

		$this->assertEquals(2, count(DashboardPool_Model::all()));
	}

	public function test_renaming_dashboard() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => '4',
						'name' => 'My dashboard',
						'username' => 'superuser',
						'layout' => '3,2,1'
					)
				),
				'dashboard_widgets' => array()
			)
		));

		$sut = new Tac_Controller();

		$dashboard = DashboardPool_Model::all()->one();
		$this->assertInstanceOf('Dashboard_Model', $dashboard,
			'We can find the mocked dashboard');
		$this->assertSame('My dashboard', $dashboard->get_name(),
			'The dashboard is named by our spec');

		$_POST = array(
			'dashboard_id' => 4,
			'name' => 'Everything and anything'
		);
		$sut->rename_dashboard();

		$all = DashboardPool_Model::all();
		$this->assertSame(1, count($all),
			"We did not create a new dashboard, but ".
			"update one that already existed");
		$this->assertSame("Everything and anything", $all->one()->get_name(),
			"We successfully changed the name of a dashboard");
	}

	public function test_deleting_dashboard() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => '34',
						'name' => 'My dashboard',
						'username' => 'superuser',
						'layout' => '3,2,1'
					)
				),
				'dashboard_widgets' => array()
			)
		));

		$this->assertSame(1, count(DashboardPool_Model::all()));
		$this->assertInstanceOf('Dashboard_Model', DashboardPool_Model::fetch_by_key(34));

		$_POST = array(
			'dashboard_id' => 34
		);
		$sut = new Tac_Controller();
		$sut->delete_dashboard();

		$this->assertSame(0, count(DashboardPool_Model::all()));
		$this->assertSame(false, DashboardPool_Model::fetch_by_key(34));
	}

	/**
	 * Set as login dashboard should only be displayed when the dashboard is
	 * currently NOT the login dashboard, test that it is so
	 */
	public function test_show_set_login_dashboard_when_dashboard_is_not_login_dashboard () {

		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => '34',
						'name' => 'My dashboard',
						'username' => 'testuser',
						'layout' => '3,2,1'
					),
					array(
						'id' => '35',
						'name' => 'This dashboard should not be returned',
						'username' => 'testuser',
						'layout' => '3,2,1'
					)
				),
				'dashboard_widgets' => array(),
				'settings' => array()
			)
		));

		$user = new User_Model();
		$user->set_username('testuser');
		op5auth::instance()->force_user($user);

		/** No dashboard set to login dashboard, show set as login dashboard */
		$dashboard = dashboard::get_default_dashboard();
		$this->assertFalse(
			dashboard::is_login_dashboard($dashboard)
		);

		$login_dashboard = new Setting_Model();
		$login_dashboard->set_username('testuser');
		$login_dashboard->set_type('login_dashboard');
		$login_dashboard->set_setting(34);
		$login_dashboard->save();

		/** Dashboard set to login dashboard, do not show set as login dashboard */
		$dashboard = dashboard::get_default_dashboard();
		$this->assertTrue(
			dashboard::is_login_dashboard($dashboard)
		);

		$login_dashboard->set_setting(35);
		$login_dashboard->save();

		/** Login dashboard is not this dashboard, show set as login dashboard */
		$dashboard = DashboardPool_Model::fetch_by_key(34);
		$this->assertFalse(
			dashboard::is_login_dashboard($dashboard)
		);


	}
	public function test_set_login_dashboard_settings() {

		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => '34',
						'name' => 'My dashboard',
						'username' => 'testuser',
						'layout' => '3,2,1'
					),
					array(
						'id' => '35',
						'name' => 'This dashboard should not be returned',
						'username' => 'testuser',
						'layout' => '3,2,1'
					)
				),
				'dashboard_widgets' => array(),
				'settings' => array()
			)
		));

		$user = new User_Model();
		$user->set_username('testuser');
		op5auth::instance()->force_user($user);

		/* set login dashboard setting */
		$login_dashboard = new Setting_Model();
		$login_dashboard->set_username('testuser');
		$login_dashboard->set_type('login_dashboard');
		$login_dashboard->set_setting(34);
		$login_dashboard->save();

		$dashboard = dashboard::get_default_dashboard();
		$this->assertEquals(34, $dashboard->get_id());

		$login_dashboard->set_setting(35);
		$login_dashboard->save();

		$dashboard = dashboard::get_default_dashboard();
		$this->assertEquals(35, $dashboard->get_id());

	}

	/**
	 * Show 'Share & Delete' options only for user own dashboard's
	 *
	 */
	public function test_show_share_delete_options_on_dashboard () {

		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => '40',
						'name' => 'My dashboard',
						'username' => 'superuser',
						'layout' => '3,2,1'
					),
					array(
						'id' => '41',
						'name' => 'My dashboard 1',
						'username' => 'superuser',
						'layout' => '3,2,1'
					)
				),
				'dashboard_widgets' => array(),
				'settings' => array()
			)
		));

		// Dashboard show 'Share & Delete' options
		$user = new User_Model();
		$user->set_username('superuser');
		op5auth::instance()->force_user($user);

		$dashboard = DashboardPool_Model::fetch_by_key(40);

		$this->assertTrue($dashboard->get_can_write());

		// Dashboard do not show 'Share & Delete' options
		$user = new User_Model();
		$user->set_username('testuser');
		op5auth::instance()->force_user($user);

		$this->assertFalse($dashboard->get_can_write());
	}
}
