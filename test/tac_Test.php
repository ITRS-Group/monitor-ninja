<?php
/**
 * Tests all public methods except index() in Tac_Controller.
 *
 * @package NINJA
 * @author  op5
 * @license GPL
 */
class Tac_Test extends PHPUnit_Framework_TestCase {

	/**
	 *
	 * @var Tac_Controller
	 */
	private $tac;

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

		// Mock some data. If we don't mock anything REAL DBs will be used :/
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Randall Daragh',
					),
				)
			),
			'ORMDriverMySQL default' => array(
				'dashboards' => array()
			)
		));

		$this->tac = new Tac_Controller();
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

	private function get_widgets_data() {
		$data = (array) op5objstore::instance()->obj_instance(
			'ORMDriverMySQL default'
		);
		$wd = $data["\0*\0storage"]['dashboard_widgets'];

		// Sort widget data by ID.
		$widgets = array();
		foreach ($wd as $w) {
			$widgets[$w['id']] = $w;
		}
		return $widgets;
	}

	private function setup_some_widgets() {
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Randall Daragh',
						'state' => '0',
						'has_been_checked' => '1',
						'scheduled_downtime_depth' => '1',
						'acknowledged' => 1,
						'checks_enabled' => 1
					),
				)
			),
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array('id' => 1, 'username' => 'superuser')
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1,
						'name' => 'Cell0',
						'position' => '{"c":0,"p":0}',
						'dashboard' => array(
							'id̈́' => 1,
							'username' => 'superuser'
						)
					),
					array(
						'id' => 2,
						'dashboard_id' => 1,
						'name' => 'tac_hosts',
						'position' => '{"c":1,"p":0}',
						'dashboard' => array(
							'id̈́' => 1,
							'username' => 'superuser'
						)
					),
					array(
						'id' => 3,
						'dashboard_id' => 1,
						'name' => 'Cell2',
						'position' => '{"c":2,"p":0}',
						'dashboard' => array(
							'id̈́' => 1,
							'username' => 'superuser'
						)
					),
					array(
						'id' => 4,
						'dashboard_id' => 1,
						'name' => 'Cell3',
						'position' => '{"c":3,"p":0}',
						'dashboard' => array(
							'id̈́' => 1,
							'username' => 'superuser'
						)
					)
				)
			)
		));
	}

	public function test_no_dashboard() {
		$this->tac->index(1);

		$content = $this->tac->template->content;
		$this->assertInstanceOf('View', $content);

		$no_dash_file = 'ninja/modules/widgets/views/tac/nodashboards.php';
		$this->assertStringEndsWith($no_dash_file, $content->kohana_filename);
	}

	public function test_no_widgets() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array('id' => 1, 'username' => 'superuser')
				)
			)
		));

		$this->tac->index(1);

		$content = $this->tac->template->content;
		$this->assertInstanceOf('View', $content);

		$no_widget_file = 'ninja/modules/widgets/views/tac/nowidgets.php';
		$this->assertStringEndsWith($no_widget_file, $content->kohana_filename);
		$this->assertEmpty($this->tac->template->content->widgets);
	}

	public function test_widget_positions() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => 1,
						'layout' => '3,2,1',
						'username' => 'superuser'
					)
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1,
						'name' => 'netw_health',
						'position' => '{"c":4,"p":3}'
					),
					array(
						'id' => 2,
						'dashboard_id' => 1,
						'name' => 'netw_health',
						'setting' => '{"title":"badboll"}',
						'position' => '{"c":2,"p":4}'
					)
				)
			)
		));

		$this->tac->index(1);

		$this->assertEquals(array(3,2,1), $this->tac->template->content->tac_column_count);
		$this->assertEquals("Network health", $this->tac->template->content->widgets[4][3]->get_title());
		$this->assertEquals("badboll", $this->tac->template->content->widgets[2][4]->get_title());
	}

	/*
	 * In the case of importing, due to migration or backups, or other reasons,
	 * the position field is missing in the database. We should handle that
	 * gracefully
	 *
	 * What is important is that all widgets is visible, so the database will
	 * be able to self heal when the uer tries to fix the position manually
	 *
	 * This should be a rare case, which might occur due to errors, or by
	 * migration/upgrade is missing information.
	 */
	public function test_widget_missing_position() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => 1,
						'layout' => '3,2,1',
						'username' => 'superuser'
					)
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1,
						'name' => 'netw_health',
						'position' => ''
					),
					array(
						'id' => 2,
						'dashboard_id' => 1,
						'name' => 'netw_health',
						'setting' => '{"title":"badboll"}',
						'position' => '{"c":2,"p":4}'
					)
				)
			)
		));

		$this->tac->index(1);

		$count = 0;
		foreach($this->tac->template->content->widgets as $c => $cells) {
			foreach($cells as $p => $widget) {
				$count++;
			}
		}
		$this->assertEquals(2, $count);
	}

	/*
	 * In the case of importing, due to migration or backups, or other reasons,
	 * two widgets might have the same position defined. And one widget shouldn't
	 * overwrite the other one.
	 *
	 * What is important is that all widgets is visible, so the database will
	 * be able to self heal when the uer tries to fix the position manually
	 *
	 * This should be a rare case, which might occur due to errors.
	 */
	public function test_widget_conflicting_positions() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => 1,
						'layout' => '3,2,1',
						'username' => 'superuser'
					)
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1,
						'name' => 'netw_health',
						'position' => '{"c":2,"p":4}'
					),
					array(
						'id' => 2,
						'dashboard_id' => 1,
						'name' => 'netw_health',
						'setting' => '{"title":"badboll"}',
						'position' => '{"c":2,"p":4}'
					)
				)
			)
		));

		$this->tac->index(1);

		$count = 0;
		foreach($this->tac->template->content->widgets as $c => $cells) {
			foreach($cells as $p => $widget) {
				$count++;
			}
		}
		/* Verify that we have the correct number of widgets visible */
		$this->assertEquals(2, $count);
	}


	/*
	 * In the case of layout swithing, in combination of bugs, or possible
	 * migrations, widgets might be put out of range of the current layout.
	 * Those widgets should be placed somewhere on the page.
	 *
	 * What is important is that all widgets is visible, so the database will
	 * be able to self heal when the uer tries to fix the position manually
	 *
	 * This should be a rare case, which might occur due to errors, but should
	 * still be tested and supported.
	 */
	public function test_widget_out_of_range_cells() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
					array(
						'id' => 1,
						'layout' => '3,2,1',
						'username' => 'superuser'
					)
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1,
						'name' => 'netw_health',
						'position' => '{"c":2,"p":4}'
					),
					array(
						'id' => 2,
						'dashboard_id' => 1,
						'name' => 'netw_health',
						'setting' => '{"title":"badboll"}',
						'position' => '{"c":77,"p":4}'
					)
				)
			)
		));

		$this->tac->index(1);

		$count = 0;
		for($c=0;$c<6;$c++) {
			if(isset($this->tac->template->content->widgets[$c])) {
				$cells = $this->tac->template->content->widgets[$c];
				foreach($cells as $p => $widget) {
					$count++;
				}
			}
		}
		/* Verify that we have the correct number of widgets visible,
		 * even though some is out of range */
		$this->assertEquals(2, $count);
	}

	// Assert on_change_positions() succeeds when moving widget to the same
	// cell and position as it already was in.
	public function test_on_change_positions_same_cell() {
		$this->setup_some_widgets();

		$_POST = array(
			'positions' =>
				'widget-placeholder0=widget-1|' .
				'widget-placeholder1=widget-2|' .
				'widget-placeholder2=widget-3|' .
				'widget-placeholder3=|' .
				'widget-placeholder4=|' .
				'widget-placeholder=widget-4',
			'dashboard_id' => 1
			);

		$this->tac->on_change_positions();

		$expected_value = array('result' => array(
			array('widget-1'),
			array('widget-2'),
			array('widget-3'),
			array(),
			array(),
			array('widget-4')
		));

		$this->assertSame($expected_value, $this->tac->template->value);
		$this->assertTrue($this->tac->template->success);
	}

	// Assert on_change_positions() succeeds when moving widget to another cell.
	public function test_on_change_positions_another_cell() {
		$this->setup_some_widgets();

		$_POST = array(
			'positions' =>
				'widget-placeholder0=widget-1|' .
				'widget-placeholder1=widget-2|' .
				'widget-placeholder2=|' .
				'widget-placeholder3=|' .
				'widget-placeholder4=widget-3|' .
				'widget-placeholder=widget-4',
			'dashboard_id' => 1
			);

		$this->tac->on_change_positions();

		$expected_value = array('result' => array(
			array('widget-1'),
			array('widget-2'),
			array(),
			array(),
			array('widget-3'),
			array('widget-4')
		));

		$this->assertSame($expected_value, $this->tac->template->value);
		$this->assertTrue($this->tac->template->success);

		// Check saved data.
		$wd = $this->get_widgets_data();
		$this->assertEquals('{"c":0,"p":0}', $wd[1]['position']);
		$this->assertEquals('{"c":1,"p":0}', $wd[2]['position']);
		$this->assertEquals('{"c":4,"p":0}', $wd[3]['position']);
		$this->assertEquals('{"c":5,"p":0}', $wd[4]['position']);
	}

	// Assert on_change_positions() succeeds when moving all widgets to
	// one cell.
	public function test_on_change_positions_one_cell() {
		$this->setup_some_widgets();

		$_POST = array(
			'positions' =>
				'widget-placeholder0=|' .
				'widget-placeholder1=|' .
				'widget-placeholder2=widget-1,widget-2,widget-3,widget-4|' .
				'widget-placeholder3=|' .
				'widget-placeholder4=|' .
				'widget-placeholder=',
			'dashboard_id' => 1
			);

		$this->tac->on_change_positions();

		$expected_value = array('result' => array(
			array(),
			array(),
			array('widget-1', 'widget-2', 'widget-3', 'widget-4'),
			array(),
			array(),
			array()
		));

		$this->assertSame($expected_value, $this->tac->template->value);
		$this->assertTrue($this->tac->template->success);

		// Check saved data.
		$wd = $this->get_widgets_data();
		$this->assertEquals('{"c":2,"p":0}', $wd[1]['position']);
		$this->assertEquals('{"c":2,"p":1}', $wd[2]['position']);
		$this->assertEquals('{"c":2,"p":2}', $wd[3]['position']);
		$this->assertEquals('{"c":2,"p":3}', $wd[4]['position']);
	}

	// Assert on_change_positions() works, even though one ID doesn't exist.
	public function test_on_change_positions_wrong_widget_id() {
		$this->setup_some_widgets();

		$_POST = array(
			'positions' =>
				'widget-placeholder0=widget-1|' .
				'widget-placeholder1=widget-2|' .
				'widget-placeholder2=widget-3|' .
				'widget-placeholder3=|' .
				'widget-placeholder4=|' .
				'widget-placeholder=widget-invalid',
			'dashboard_id' => 1
			);

		$this->tac->on_change_positions();

		$expected_value = array('result' => array(
			array(0 => 'widget-1'),
			array(0 => 'widget-2'),
			array(0 => 'widget-3'),
			array(),
			array(),
			array(0 => 'widget-invalid')
		));
		$this->assertSame($expected_value, $this->tac->template->value);
		$this->assertTrue($this->tac->template->success);
	}

	public function test_on_refresh() {
		$this->setup_some_widgets();

		// Assert it fails when given wrong widget ID.
		$_POST = array(
			'key' => 'invalid',
			'dashboard_id' => '1'
		);
		$this->tac->on_refresh();

		$expected_value = array('result' => 'Unknown widget');
		$this->assertSame($expected_value, $this->tac->template->value);
		$this->assertFalse($this->tac->template->success);

		// Assert that we get a dead widget when widget name is invalid.
		$_POST = array(
			'key' => '1',
			'dashboard_id' => '1'
		);
		$this->tac->on_refresh();

		$this->assertContains(
			"Widget type 'Cell0' does not seem to be installed",
			$this->tac->template->value['widget']
		);
		$this->assertSame('Unknown', $this->tac->template->value['title']);
		$this->assertSame('', $this->tac->template->value['custom_title']);
		$this->assertTrue($this->tac->template->success);

		// Assert that we get correct widget data when widget name is valid.
		$_POST = array(
			'key' => '2',
			'dashboard_id' => '1'
		);
		$this->tac->on_refresh();

		$this->assertContains(
			'<table class="tablestat-widget"', $this->tac->template->value['widget']
		);
		$this->assertSame('Hosts', $this->tac->template->value['title']);
		$this->assertSame('', $this->tac->template->value['custom_title']);
		$this->assertTrue($this->tac->template->success);
	}

	// Assert a correct widget is created in response when correct data is
	// given.
	public function test_on_widget_add_with_correct_data() {
		$this->setup_some_widgets();

		$_POST = array(
			'cell' => 'addcell2',
			'widget' => 'tac_hosts',
			'dashboard_id' => '1'
		);
		$wd = $this->get_widgets_data();

		$this->tac->on_widget_add();
		// 4 widgets in default data, thus new id=5

		$this->assertContains(
			'<table class="tablestat-widget"',
			$this->tac->template->value['widget']
		);
		$this->assertNotContains(
			'alert error', $this->tac->template->value['widget']
		);
		$this->assertTrue($this->tac->template->success);

		// Check saved data.
		$wd = $this->get_widgets_data();

		// The new widget have "stolen" widget #3 previous position.
		$this->assertEquals('{"c":2,"p":0}', $wd[4]['position']);
		// Widget #3 have moved one step "down".
		$this->assertEquals('{"c":2,"p":1}', $wd[3]['position']);
		$this->assertEquals('tac_hosts', $wd[4]['name']);
	}

	// Assert a widget is added in cell 0 when $_POST['cell'] is incorrect.
	public function test_on_widget_add_with_incorrect_cellname() {
		$this->setup_some_widgets();

		$_POST = array(
			'cell' => 'non-number-ending-string',
			'widget' => 'tac_hosts',
			'dashboard_id' => '1'
		);
		$this->tac->on_widget_add();
		// 4 widgets in default data, thus new id=5

		$this->assertContains(
			'<table class="tablestat-widget"',
			$this->tac->template->value['widget']
		);
		$this->assertNotContains(
			'alert error', $this->tac->template->value['widget']
		);
		$this->assertTrue($this->tac->template->success);

		// Check saved data.
		$wd = $this->get_widgets_data();
		// The new widget have "stolen" widget #1 previous position.
		$this->assertEquals('{"c":0,"p":0}', $wd[4]['position']);
		// Widget #1 have moved one step "down".
		$this->assertEquals('{"c":0,"p":1}', $wd[1]['position']);
	}

	// Assert that a "dead "widget is created when widget name is incorrect.
	public function test_on_widget_add_with_incorrect_widget_name() {
		$this->setup_some_widgets();

		$_POST = array(
			'cell' => 'addcell2',
			'widget' => 'kanelmuffins',
			'dashboard_id' => '1'
		);
		$this->tac->on_widget_add();

		$this->assertSame(
			array('result' => 'Widget kanelmuffins can not be created'),
			$this->tac->template->value
		);
	}

	public function test_on_widget_remove() {
		$this->setup_some_widgets();

		$_POST = array(
			'dashboard_id' => '1'
		);
		$_POST['key'] = 3;
		$this->tac->on_widget_remove();
		$this->assertSame(array('result' => 'ok'), $this->tac->template->value);

		// Check saved data.
		$wd = $this->get_widgets_data();
		$this->assertArrayNotHasKey(3, $wd);

		$_POST['key'] = 73;
		$this->tac->on_widget_remove();
		$this->assertSame(
			array('result' => 'error'), $this->tac->template->value
		);
	}

	public function test_on_widget_save_settings() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array(
						array(
							'id' => 1,
							'username' => 'superuser'
						),
						array(
							'id' => 2,
							'username' => 'superuser'
						),
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1,
						'name' => 'Cell0',
						'position' => '{"c":0,"p":0}',
						'dashboard' => array(
							'id̈́' => 1,
							'username' => 'superuser'
						)
					),
					array(
						'id' => 2,
						'dashboard_id' => 1,
						'name' => 'Cell1',
						'setting' => '{"x": "y"}',
						'position' => '{"c":1,"p":0}',
						'dashboard' => array(
							'id̈́' => 1,
							'username' => 'superuser'
						)
					),
					array( /* We shouldn't be able to update this */
						'id' => 3,
						'dashboard_id' => 2,
						'setting' => '{"this_is": "original"}',
						'name' => 'not_current_dashboard',
						'position' => '{"c":1,"p":0}',
						'dashboard' => array(
							'id̈́' => 2,
							'username' => 'superuser'
						)
					),
				)
			)
		));

		/* Fetch reference dashboard */
		$dashboard = DashboardPool_Model::all()->one();
		/* @var $dashboard Dashboard_Model */
		$widgets = array_map(function($elem) {
			return array($elem->get_key(), $elem->get_setting());
		}, iterator_to_array($dashboard->get_dashboard_widgets_set()));

		$this->assertSame(array(
			array(1, array()),
			array(2, array('x' => 'y'))
		), $widgets);

		/* Run on_widget_save_settings */
		$_POST = array(
			'key' => 1,
			'setting' => array('a' => 'b'),
			'dashboard_id' => '1'
		);
		$this->template = null;
		$this->tac->on_widget_save_settings();
		$this->assertTrue($this->tac->template->success, var_export($this->tac->template->value, true));
		$this->assertEquals(array('result' => 'ok'), $this->tac->template->value);

		/* Verify that correct widget is updated */
		$widgets = array_map(function($elem) {
			return array($elem->get_key(), $elem->get_setting());
		}, iterator_to_array($dashboard->get_dashboard_widgets_set()));

		$this->assertSame(array(
			array(1, array('a' => 'b')),
			array(2, array('x' => 'y'))
		), $widgets);

		/* Run on_widget_save_settings, try to update non-current-dashboard-widget */
		$_POST = array(
			'key' => 3,
			'setting' => array('this_is' => 'updated'),
			'dashboard_id' => '1'
		);
		$this->template = null;
		$this->tac->on_widget_save_settings();
		$this->assertFalse($this->tac->template->success);
		$this->assertEquals(
			array('result' => 'Could not find a widget with that ID'),
			$this->tac->template->value
		);

		/* Verify that correct widget is updated */
		$widgets = array_map(function($elem) {
			return array($elem->get_key(), $elem->get_setting()); },
		iterator_to_array($dashboard->get_dashboard_widgets_set()));

		$this->assertSame(array(
			array(1, array('a' => 'b')),
			array(2, array('x' => 'y'))
		), $widgets);

		$widget = Dashboard_WidgetPool_Model::fetch_by_key ( 3 );
		$this->assertSame(array(
			'dashboard' => array(
				'id' => 2,
				'name' => '',
				'username' => 'superuser',
				'layout' => ''
			),
			'id' => 3,
			'dashboard_id' => 2,
			'name' => 'not_current_dashboard',
			'setting' => array(
				'this_is' => 'original'
			),
			'position' => array('c' => 1,'p' => 0)
		), $widget->export());
	}

	/**
	 * This test actually verifies the order of the Add Widget menu.
	 *
	 * It does it in a level that means we need to parse html, but should
	 * actually be done by exporting
	 */
	public function test_widget_menu_order() {
		$menu = $this->tac->_get_add_widget_menu();

		$count = 0;
		$last_name = '';
		foreach($menu->get_branch() as $menu_item) {
			$cur_name = strtolower($menu_item->get_label_as_html());

			/* every name should be after the previous name, thus in order */
			$this->assertGreaterThan($last_name, $cur_name);
			$last_name = $cur_name;
			$count++;
		}
		$this->assertGreaterThan(0, $count);
	}
}
