<?php
/**
 * Tests dashboard.
 *
 * @package NINJA
 * @author  op5
 * @license GPL
 */
class Dashboard_Test extends PHPUnit_Framework_TestCase {

	private $mock_data_path = false;

	protected function setUp() {
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
		unlink($this->mock_data_path);
		$this->mock_data_path = false;
	}

	private function mock_data($tables, $file) {
		if($this->mock_data_path !== false) {
			unlink($this->mock_data_path);
		}
		$this->mock_data_path = __DIR__ . '/' . $file . '.json';
		file_put_contents($this->mock_data_path, json_encode($tables));
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add(
				$driver,
				new ORMDriverNative($tables, $this->mock_data_path, $driver)
			);
		}
	}

	/**
	 * As a user I shouldn't have access to other users dashboards.
	 */
	public function test_dashboard_permissions() {

		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => 1,
						'username' => 'superuser',
						'layout' => '3,2,1'
					)
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1
					),
					array(
						'id' => 2,
						'dashboard_id' => 1
					)
				)
			)
		), __FUNCTION__);

		$superuser = new User_AlwaysAuth_Model();
		$inferior_user = new User_AlwaysAuth_Model();
		$inferior_user->set_username('inferior');
		$inferior_user->set_realname('Inferior User');

		// We should NOT be able to get the dashboard that belongs to
		// superuser as inferior user.
		Auth::instance(array('session_key' => false))->force_user(
			$inferior_user
		);

		$dashboard = DashboardPool_Model::fetch_by_key(1);
		$this->assertFalse($dashboard);

		// But as superuser we should.
		Auth::instance(array('session_key' => false))->force_user(
			$superuser
		);
		$dashboard = DashboardPool_Model::fetch_by_key(1);
		$this->assertInstanceOf('Dashboard_Model', $dashboard);

	}

	public function test_dashboard_export() {
		$mock_widgets = array(
			array(
				'id' => 1,
				'dashboard_id' => 1,
				'name' => 'Cell0',
				'setting' => '{}',
				'position' => '{"c":0,"p":0}',
			),
			array(
				'id' => 2,
				'dashboard_id' => 1,
				'name' => 'tac_hosts',
				'setting' => '{}',
				'position' => '{"c":1,"p":0}',
			),
			array(
				'id' => 3,
				'dashboard_id' => 2,
				'name' => 'Board 2, Cell2',
				'setting' => '{}',
				'position' => '{"c":2,"p":0}',
			),
			array(
				'id' => 4,
				'dashboard_id' => 2,
				'name' => 'Board 2, Cell3',
				'setting' => '{}',
				'position' => '{"c":3,"p":0}',
			)
		);

		$db_name = 'A Dashing Board';
		$db_layout = '3,2,1';
		$mock_dashboards = array(
			array(
				'id' => 1,
				'name' => $db_name,
				'username' => 'superuser',
				'layout' => $db_layout
			),
			array(
				'id' => 2,
				'name' => 'Board 2',
				'username' => 'superuser',
				'layout' => $db_layout
			)
		);
		$mock = array(
			'ORMDriverMySQL default' => array(
				'dashboards' => $mock_dashboards,
				'dashboard_widgets' => $mock_widgets,
			)
		);
		$this->mock_data($mock, __FUNCTION__);

		Auth::instance(array('session_key' => false))->force_user(
			new User_AlwaysAuth_Model()
		);

		$compare = array('dashboard' => array('name' => $db_name, 'layout' => $db_layout));
		$widgets = $mock_widgets;
		$comp_widgets = array();
		foreach ($widgets as $k => $w) {
			if ($w['dashboard_id'] != 1) {
				continue;
			}
			unset($w['id']);
			unset($w['dashboard_id']);
			$w['setting'] = json_decode($w['setting'], TRUE);
			$w['position'] = json_decode($w['position'], TRUE);
			$comp_widgets[$k] = $w;
		}
		$compare['widgets'] = $comp_widgets;
		$board = DashboardPool_Model::fetch_by_key(1);
		$exported = $board->export_array();
		$this->assertSame($exported['dashboard']['layout'], $db_layout);
		$this->assertSame($exported['dashboard']['name'], $db_name);
		$this->assertSame($board->export_array(), $compare);
	}

	public function test_dashboard_import() {
		/* set up mock data */
		$mock = array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => 1,
						'username' => 'superuser'
					)
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1,
						'dashboard' => array(
							'id̈́' => 1,
							'username' => 'superuser'
						)
					)
				),
			)
		);
		$this->mock_data($mock, __FUNCTION__);

		Auth::instance(array('session_key' => false))->force_user(
			new User_AlwaysAuth_Model()
		);

		/*
		 * Since we test export_array() against known data and
		 * trust that, this becomes very simple; Export first,
		 * change what we exported, import the changed version
		 * and then export it again. If they match, we're good.
		 */
		$board = DashboardPool_Model::fetch_by_key(1);
		$export1 = $board->export_array();
		$export1['dashboard']['name'] = 'a random string appears...';
		$board->import_array($export1);
		$export2 = $board->export_array();
		$this->assertSame($export1, $export2);
	}

	/**
	 * Test that initial dashboards is defined as config.
	 *
	 * Disabled due to bugs in native ORM driver, which doesn't set id correctly with
	 * auto increment. (error message: undefined field "id")
	 */
	public function disabled_initial_dashboard() {
		$mock = array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(),
				'dashboard_widgets' => array(),
			)
		);
		$this->mock_data($mock, __FUNCTION__);

		Auth::instance(array('session_key' => false))->force_user(
			new User_AlwaysAuth_Model()
		);

		$dashboard = $this->tac->_current_dashboard();

		$this->assertEquals(Kohana::config('tac.default'), $dashboard->export_array());
	}

	/*
	 * Upgrade from Ninja_Widget_Model to Dashboard_*_Model
	 *
	 * Upgrade from ninja db version 18 to 19
	 */
	public function test_upgrade_v18() {
		$mock = array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(),
				'dashboard_widgets' => array(),
				'ninja_widgets' => array(
					array(
						'username' => 'superuser',
						'page' => 'tac/index',
						'name' => 'netw_health',
						'friendly_name' => 'My little widget',
						'setting' => 'a:1:{s:8:"something";s:2:"17";}',
						'instance_id' => 13
					)
				)
			)
		);
		$this->mock_data($mock, __FUNCTION__);

		Auth::instance(array('session_key' => false))->force_user(
			new User_AlwaysAuth_Model()
		);

		$dashboard = DashboardPool_Model::all()->reduce_by('username', 'superuser', '=')->one();
		/* Empty dashboard table, none exists prior to migration */
		$this->assertNull($dashboard);

		ob_start(); /* Don't output hashbang line */
		require(__DIR__.'/../install_scripts/migrate_widgets.php');
		ob_end_clean();

		$dashboard = DashboardPool_Model::all()->reduce_by('username', 'superuser', '=')->one();
		/* This means that there exist a dashboard, where none existed earlier */
		$this->assertInstanceOf('Dashboard_Model', $dashboard);

		/* Due to problems in Native ORM driver regarding related objects, we can't validate that widgets exists */
	}
}
