<?php
/**
 * Tests ...
 *
 * @package NINJA
 * @author  op5
 * @license GPL
 */
class Convert_Layout_Test extends PHPUnit_Framework_TestCase {

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

		Auth::instance(array('session_key' => false))->force_user(
			new User_AlwaysAuth_Model()
		);

		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => 1,
						'layout' => '3,2,1'
					)
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1,
						'name' => 'Cell0',
						'position' => '{"c":0,"p":0}'
					),
					array(
						'id' => 2,
						'dashboard_id' => 1,
						'name' => 'Cell1',
						'position' => '{"c":1,"p":0}'
					),
					array(
						'id' => 3,
						'dashboard_id' => 1,
						'name' => 'Cell2',
						'position' => '{"c":2,"p":0}'
					),
					array(
						'id' => 4,
						'dashboard_id' => 1,
						'name' => 'Cell3',
						'position' => '{"c":3,"p":0}'
					),
					array(
						'id' => 5,
						'dashboard_id' => 1,
						'name' => 'Cell4',
						'position' => '{"c":4,"p":0}'
					),
					array(
						'id' => 6,
						'dashboard_id' => 1,
						'name' => 'Cell5',
						'position' => '{"c":5,"p":0}'
					)
				)
			)
		), __FUNCTION__);
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

	public function test_convert_321_to_132() {
		$board = DashboardPool_Model::fetch_by_key(1);

		// Convert dashboard and all its widgets to the new layout.
		widget::convert_layout($board, '1,3,2');

		// We want to load the dashboard layout from the database.
		$board = DashboardPool_Model::fetch_by_key(1);
		$this->assertSame("1,3,2", $board->get_layout());
		$widgets = $board->get_dashboard_widgets_set();

		$widgets_by_id = array();
		foreach ($widgets as $w) {
			$widgets_by_id[$w->get_id()] = $w;
		}

		// Positions will remain the same as the number of cells is equal.
		$w1_pos = $widgets_by_id[1]->get_position();
		$this->assertSame(array('c' => 0, 'p' => 0), $w1_pos);
		$w2_pos = $widgets_by_id[2]->get_position();
		$this->assertSame(array('c' => 1, 'p' => 0), $w2_pos);
		$w3_pos = $widgets_by_id[3]->get_position();
		$this->assertSame(array('c' => 2, 'p' => 0), $w3_pos);
		$w4_pos = $widgets_by_id[4]->get_position();
		$this->assertSame(array('c' => 3, 'p' => 0), $w4_pos);
		$w5_pos = $widgets_by_id[5]->get_position();
		$this->assertSame(array('c' => 4, 'p' => 0), $w5_pos);
		$w6_pos = $widgets_by_id[6]->get_position();
		$this->assertSame(array('c' => 5, 'p' => 0), $w6_pos);
	}

	public function test_convert_132_to_321() {
		$board = DashboardPool_Model::fetch_by_key(1);
		$board->set_layout('1,3,2');
		$board->save();

		// Convert dashboard and all its widgets to the new layout.
		widget::convert_layout($board, '3,2,1');

		// We want to load the dashboard layout from the database.
		$board = DashboardPool_Model::fetch_by_key(1);
		$this->assertSame("3,2,1", $board->get_layout());
		$widgets = $board->get_dashboard_widgets_set();

		$widgets_by_id = array();
		foreach ($widgets as $w) {
			$widgets_by_id[$w->get_id()] = $w;
		}

		// Positions will remain the same as the number of cells is equal.
		$w1_pos = $widgets_by_id[1]->get_position();
		$this->assertSame(array('c' => 0, 'p' => 0), $w1_pos);
		$w2_pos = $widgets_by_id[2]->get_position();
		$this->assertSame(array('c' => 1, 'p' => 0), $w2_pos);
		$w3_pos = $widgets_by_id[3]->get_position();
		$this->assertSame(array('c' => 2, 'p' => 0), $w3_pos);
		$w4_pos = $widgets_by_id[4]->get_position();
		$this->assertSame(array('c' => 3, 'p' => 0), $w4_pos);
		$w5_pos = $widgets_by_id[5]->get_position();
		$this->assertSame(array('c' => 4, 'p' => 0), $w5_pos);
		$w6_pos = $widgets_by_id[6]->get_position();
		$this->assertSame(array('c' => 5, 'p' => 0), $w6_pos);
	}
}
