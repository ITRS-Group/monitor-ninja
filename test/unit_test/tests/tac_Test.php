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
				'dashboards' => array(array('id' => 1)),
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
						'name' => 'tac_hosts',
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
					)
				)
			)
		), __FUNCTION__);

		$this->tac = new Tac_Controller();
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

	private function get_widgets_data() {
		$data = (array) op5objstore::instance()->obj_instance(
			'ORMDriverMySQL default'
		);
		$wd = $data["\0*\0storage"]['dashboard_widgets'];

		// Sort widget data by ID.
		$widgets = array();
		$counter = 0;
		foreach ($wd as $w) {
			if (isset($w['id']))
				$widgets[$w['id']] = $w;
			else {
				$widgets['unknown' . $counter] = $w;
				$counter++;
			}
		}
		return $widgets;
	}

	public function test_widget_positions() {
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
		), __FUNCTION__);

		$this->tac->index();

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
						'layout' => '3,2,1'
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
		), __FUNCTION__);

		$this->tac->index();

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
						'layout' => '3,2,1'
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
		), __FUNCTION__);

		$this->tac->index();

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
						'layout' => '3,2,1'
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
		), __FUNCTION__);

		$this->tac->index();

		$count = 0;
		for($c=0;$c<6;$c++) {
			if(isset($this->tac->template->content->widgets[$c])) {
				$cells = $this->tac->template->content->widgets[$c];
				foreach($cells as $p => $widget) {
					$count++;
				}
			}
		}
		/* Verify that we have the correct number of widgets visible, even though some is ouf of range */
		$this->assertEquals(2, $count);
	}

	// Assert on_change_positions() succeeds when moving widget to the same
	// cell and position as it already was in.
	public function test_on_change_positions_same_cell() {
		$_POST['positions'] =
			'widget-placeholder0=widget-1|' .
			'widget-placeholder1=widget-2|' .
			'widget-placeholder2=widget-3|' .
			'widget-placeholder3=|' .
			'widget-placeholder4=|' .
			'widget-placeholder=widget-4';

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
		$_POST['positions'] =
			'widget-placeholder0=widget-1|' .
			'widget-placeholder1=widget-2|' .
			'widget-placeholder2=|' .
			'widget-placeholder3=|' .
			'widget-placeholder4=widget-3|' .
			'widget-placeholder=widget-4';

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
		$_POST['positions'] =
			'widget-placeholder0=|' .
			'widget-placeholder1=|' .
			'widget-placeholder2=widget-1,widget-2,widget-3,widget-4|' .
			'widget-placeholder3=|' .
			'widget-placeholder4=|' .
			'widget-placeholder=';

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

	// Assert on_change_positions() fails when given wrong widget ID.
	public function test_on_change_positions_wrong_widget_id() {
		$_POST['positions'] =
			'widget-placeholder0=widget-1|' .
			'widget-placeholder1=widget-2|' .
			'widget-placeholder2=widget-3|' .
			'widget-placeholder3=|' .
			'widget-placeholder4=|' .
			'widget-placeholder=widget-invalid';

		$this->tac->on_change_positions();

		$expected_value = array('result' => 'Unknown widget');
		$this->assertSame($expected_value, $this->tac->template->value);
		$this->assertFalse($this->tac->template->success);
	}

	public function test_on_refresh() {
		$tac = new Tac_Controller();

		// Assert it fails when given wrong widget ID.
		$_POST['key'] = 'invalid';
		$tac->on_refresh();

		$expected_value = array('result' => 'Unknown widget');
		$this->assertSame($expected_value, $tac->template->value);
		$this->assertFalse($tac->template->success);

		// Assert that we get a dead widget when widget name is invalid.
		$_POST['key'] = '1';
		$tac->on_refresh();

		$this->assertContains(
			"Widget type 'Cell0' does not seem to be installed",
			$tac->template->value['widget']
		);
		$this->assertSame('Unknown', $tac->template->value['title']);
		$this->assertSame('', $tac->template->value['custom_title']);
		$this->assertTrue($tac->template->success);

		// Assert that we get correct widget data when widget name is valid.
		$_POST['key'] = '2';
		$tac->on_refresh();

		$this->assertContains(
			'<table class="tablestat-widget"', $tac->template->value['widget']
		);
		$this->assertSame('Hosts', $tac->template->value['title']);
		$this->assertSame('', $tac->template->value['custom_title']);
		$this->assertTrue($tac->template->success);
	}

	// Assert a correct widget is created in response when correct data is
	// given.
	public function test_on_widget_add_with_correct_data() {
		$_POST['cell'] = 'addcell2';
		$_POST['widget'] = 'tac_hosts';
		$this->tac->on_widget_add();

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
		$this->assertEquals('{"c":2,"p":0}', $wd['unknown0']['position']);
		// Widget #3 have moved one step "down".
		$this->assertEquals('{"c":2,"p":1}', $wd[3]['position']);
		$this->assertEquals('tac_hosts', $wd['unknown0']['name']);
	}

	// Assert a widget is added in cell 0 when $_POST['cell'] is incorrect.
	public function test_on_widget_add_with_incorrect_cellname() {
		$_POST['cell'] = 'non-number-ending-string';
		$_POST['widget'] = 'tac_hosts';
		$this->tac->on_widget_add();

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
		$this->assertEquals('{"c":0,"p":0}', $wd['unknown0']['position']);
		// Widget #1 have moved one step "down".
		$this->assertEquals('{"c":0,"p":1}', $wd[1]['position']);
	}

	// Assert that a "dead "widget is created when widget name is incorrect.
	public function test_on_widget_add_with_incorrect_widget_name() {
		$_POST['cell'] = 'addcell2';
		$_POST['widget'] = 'kanelmuffins';
		$this->tac->on_widget_add();

		$this->assertSame(
			array('result' => 'Widget kanelmuffins can not be created'),
			$this->tac->template->value
		);
	}

	public function test_on_widget_remove() {
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
						array('id' => 1),
						array('id' => 2),
				),
				'dashboard_widgets' => array(
					array(
						'id' => 1,
						'dashboard_id' => 1,
						'name' => 'Cell0',
						'position' => '{"c":0,"p":0}',
					),
					array(
						'id' => 2,
						'dashboard_id' => 1,
						'name' => 'Cell1',
						'setting' => '{"x": "y"}',
						'position' => '{"c":1,"p":0}',
					),
					array( /* We shouldn't be able to update this */
						'id' => 3,
						'dashboard_id' => 2,
						'setting' => '{"this_is": "original"}',
						'name' => 'not_current_dashboard',
						'position' => '{"c":1,"p":0}',
					),
				)
			)
		), __FUNCTION__);

		$tac = new Tac_Controller();

		/* Fetch reference dashboard */
		$dashboard = DashboardPool_Model::all()->one();
		/* @var $dashboard Dashboard_Model */
		$widgets = array_map(function($elem) { return array($elem->get_key(), $elem->get_setting()); }, iterator_to_array($dashboard->get_dashboard_widgets_set()));
		$this->assertSame ( array (
				array (1, array()),
				array (2, array('x' => 'y'))
		), $widgets );


		/* Run on_widget_save_settings */
		$_POST = array(
				'key' => 1,
				'setting' => array(
						'a' => 'b'
				)
		);
		$this->template = null;
		$tac->on_widget_save_settings();
		$this->assertTrue($tac->template->success);
		$this->assertEquals(array('result' => 'ok'), $tac->template->value);

		/* Verify that correct widget is updated */
		$widgets = array_map(function($elem) { return array($elem->get_key(), $elem->get_setting()); }, iterator_to_array($dashboard->get_dashboard_widgets_set()));
		$this->assertSame ( array (
				array (1, array('a' => 'b')),
				array (2, array('x' => 'y'))
		), $widgets );


		/* Run on_widget_save_settings, try to update non-current-dashboard-widget */
		$_POST = array(
				'key' => 3,
				'setting' => array(
						'this_is' => 'updated'
				)
		);
		$this->template = null;
		$tac->on_widget_save_settings();
		$this->assertFalse($tac->template->success);
		$this->assertEquals(array('result' => 'Could not find a widget with that ID'), $tac->template->value);

		/* Verify that correct widget is updated */
		$widgets = array_map(function($elem) { return array($elem->get_key(), $elem->get_setting()); }, iterator_to_array($dashboard->get_dashboard_widgets_set()));
		$this->assertSame ( array (
				array (1, array('a' => 'b')),
				array (2, array('x' => 'y'))
		), $widgets );

		$widget = Dashboard_WidgetPool_Model::fetch_by_key ( 3 );
		$this->assertSame ( array (
				'dashboard' => array (
						'id' => 2,
						'name' => '',
						'username' => '',
						'layout' => ''
				),
				'id' => 3,
				'dashboard_id' => 2,
				'name' => 'not_current_dashboard',
				'setting' => array (
						'this_is' => 'original'
				),
				'position' => array('c' => 1,'p' => 0)
		), $widget->export () );
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
		foreach($menu->get("Add widget")->get_branch() as $menu_item) {
			$cur_name = strtolower($menu_item->get_label_as_html());

			/* every name should be after the previous name, thus in order */
			$this->assertGreaterThan($last_name, $cur_name);
			$last_name = $cur_name;
			$count++;
		}
		$this->assertGreaterThan(0, $count);
	}

	/**
	 * Test old model interface
	 *
	 * The old interface is $model->attr, new interface is $model->get_attr.
	 *
	 * This old interface should work, to not break old widgets. But should be
	 * flagged as deprecated
	 */
	public function test_old_model_interface() {
		/* Print out log, so we can verify that it's correct */
		op5objstore::instance()->mock_add('op5log', new MockLog(true));
		$this->expectOutputString(
				"Log: ninja notice DEPRECATION: 'Widget_Model::__get' is deprecated and should not be executed: <no message>\n".
				"Log: ninja notice DEPRECATION: 'Widget_Model::__get' is deprecated and should not be executed: <no message>\n"
				);

		$model = new Dashboard_Widget_Model();
		$model->set_setting(array("title" => "my custom title"));
		$model->set_name("my_widget");

		$this->assertEquals(array("title" => "my custom title"), $model->setting);
		$this->assertEquals("my_widget", $model->name);
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
				'username' => '',
				'layout' => $db_layout
			),
			array(
				'id' => 2,
				'name' => 'Board 2',
				'username' => '',
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
		$mock_widgets = array(
			array(
				'id' => 1,
				'dashboard_id' => 1,
				'name' => 'Cell0',
				'setting' => '{}',
				'position' => '{"c":0,"p":0}',
				),
		);
		$mock_dashboards = array(
			array(
				'id' => 1,
				'name' => 'A Dashing Board',
				'username' => '',
				'layout' => '3,2,1'
			),
		);
		$mock = array(
			'ORMDriverMySQL default' => array(
				'dashboards' => array((array('id' => 1))),
				'dashboard_widgets' => array((array('id' => 1, 'dashboard_id' => 1))),
			)
		);
		$this->mock_data($mock, __FUNCTION__);

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
}
