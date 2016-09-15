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
		$_POST = array();
	}

	protected function tearDown() {
		op5objstore::instance()->mock_clear();
		$_POST = array();
	}

	private function mock_data($tables) {
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add(
				$driver,
				new ORMDriverNative($tables, null, $driver)
			);
		}
	}

	/**
	 * @group Tac_Controller::new_dashboard
	 */
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

		$_POST = array(
			'name' => 'Another dashboard',
			'layout', '3,2,1'
		);
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

	/**
	 * @group Tac_Controller::delete_dashboard
	 */
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
	 */
	public function test_get_can_write_on_dashboards () {
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

	/**
	 * @group Tac_Controller::index
	 */
	public function test_can_switch_between_saved_dashboards() {
		$this->mock_data(array(
			"ORMDriverMySQL default" => array(
				"dashboards" => array(
					array(
						"id" => "40",
						"name" => "My dashboard",
						"username" => "superuser",
						"layout" => "3,2,1"
					),
					array(
						"id" => "41",
						"name" => "Mommy's dashboard",
						"username" => "superuser",
						"layout" => "3,2,1"
					)
				),
				"dashboard_widgets" => array(),
				"settings" => array()
			)
		));

		$tac = new Tac_Controller();
		$tac->index(40);
		$this->assertContains("My dashboard", $tac->template->title,
			"If the dashboard was located, the page's title ".
			"should have been changed"
		);
		$tac->index(41);
		$this->assertContains("Mommy's dashboard", $tac->template->title,
			"If the dashboard was located, the page's title ".
			"should have been changed"
		);
	}

	/**
	 * @group Tac_Controller::index
	 */
	public function test_visiting_a_non_existing_dashboard_redirects_to_placeholder_view() {
		$this->mock_data(array(
			"ORMDriverMySQL default" => array(
				"dashboards" => array(
					array(
						"id" => "40",
						"name" => "My dashboard",
						"username" => "superuser",
						"layout" => "3,2,1"
					),
				),
				"dashboard_widgets" => array(),
				"settings" => array()
			)
		));

		$tac = new Tac_Controller();
		$non_existing_dashboard_id = 31;
		$tac->index($non_existing_dashboard_id);

		$this->assertEquals("controller", $tac->template->target,
			"A redirect view should have said that it wanted ".
			"to redirect to a controller"
		);
		$this->assertEquals("tac/index/40", $tac->template->url,
			"The tac controller should have redirected us to ".
			"the only existing dashboard"
		);
	}

	/**
	 * @group MON-9539
	 */
	public function test_user_without_dashboard_share_auth_right_can_still_access_dashboards_shared_with_them() {
		$this->mock_data(array(
			"ORMDriverMySQL default" => array(
				"dashboards" => array(
					array(
						"id" => "1",
						"name" => "unicorn dashboard",
						"username" => "some random user",
						"read_perm" => ",15,"
					)
				),
				"permission_quarks" => array(
					array(
						"id" => "15",
						"foreign_table" => "users",
						"foreign_key" => "superuser",
					)
				)
			)
		));

		$menu = new Menu_Model();
		dashboard::set_dashboard_menu_based_on_logged_in_user($menu);

		$this->assertEquals(null, $menu->get("Dashboards.shared222"),
			"FYI the Menu_Model returns null for an unknown submenu"
		);
		$this->assertInstanceOf(
			"Menu_Model",
			$menu->get("Dashboards.shared"),
			"The Menu_Model returns another Menu_Model for a ".
			"known submenu, which we get because we're a ".
			"superuser at the moment"
		);

		// OK, everything worked as expected, now let's perform a real
		// test
		$interesting_mayi_action = "monitor.system.dashboards.shared:read";
		$mayi_denied_fixture = array(
			$interesting_mayi_action => array(
				"message" => "Nah uh"
			)
		);
		$mock_mayi = new MockMayI(array(
			"denied_actions" => $mayi_denied_fixture
		));
		op5objstore::instance()->mock_add("op5MayI", $mock_mayi);

		$menu = new Menu_Model();
		dashboard::set_dashboard_menu_based_on_logged_in_user($menu);
		$this->assertEquals(null, $menu->get("Dashboards.shared"),
			"FYI the Menu_Model returns null for an unknown submenu"
		);
	}
}
