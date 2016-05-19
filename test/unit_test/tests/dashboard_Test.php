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
}
