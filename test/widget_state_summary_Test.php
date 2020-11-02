<?php

class Widget_State_Summary_Test extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$widget_model = new Ninja_Widget_Model();
		$widget_model->set_name('state_summary');
		$widget_model->set_friendly_name('State summary');
		$widget = $widget_model->build();
		$this->assertInstanceOf('State_summary_Widget', $widget);

		$this->mock_data_path = false;

		$this->widget = $widget;
	}

	protected function tearDown() {
		op5objstore::instance()->mock_clear();
		// in this test case, all methods sets mock_data_path, or else...
		$this->assertTrue(unlink($this->mock_data_path), $this->mock_data_path);
	}

	private function mock_data ($tables, $file) {
		$this->mock_data_path = __DIR__.'/'.$file.'.json';
		$this->assertTrue((boolean) file_put_contents($this->mock_data_path, json_encode($tables)));
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add($driver, new ORMDriverNative($tables, $this->mock_data_path, $driver));
		}
	}

	/**
	 * @group MON-9212
	 */
	public function test_hardcoded_host_all_widget_filter() {
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Randall Daragh',
						'state' => '0',
						'has_been_checked' => '1',
					),
					array(
						'name' => 'Fooooobbar',
						'state' => '0',
						'has_been_checked' => '1',
					),
					array(
						'name' => 'Attila Tadala',
						'state' => '1',
						'has_been_checked' => '1',
					),
				)
			)
		), __FUNCTION__);

		$filter_id = -200;
		$data = $this->widget->get_filtered_data($filter_id);

		$this->assertSame('Up', $data['state_definitions']['states'][0]['label']);
		$this->assertSame(2, $data['stats'][0], var_export($data, true));

		$this->assertSame('Down', $data['state_definitions']['states'][1]['label']);
		$this->assertSame(1, $data['stats'][1]);

		$this->assertSame('Unreachable', $data['state_definitions']['states'][2]['label']);
		$this->assertSame(0, $data['stats'][2]);

		$this->assertSame('Pending', $data['state_definitions']['states'][3]['label']);
		$this->assertSame(0, $data['stats'][3]);
	}

	/**
	 * @group MON-9212
	 */
	public function test_hardcoded_service_all_widget_filter() {
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'hosts' => array(),
				'services' => array(
					array(
						'description' => 'Randall Daragh',
						'state' => '0',
						'host' => array('has_been_checked' => 1),
						'has_been_checked' => '1',
					),
					array(
						'description' => 'Fooooobbar',
						'state' => '0',
						'host' => array('has_been_checked' => 1),
						'has_been_checked' => '1',
					),
					array(
						'description' => 'Attila Tadala',
						'state' => '1',
						'host' => array('has_been_checked' => 1),
						'has_been_checked' => '1',
					),
				)
			)
		), __FUNCTION__);

		$filter_id = -100;
		$data = $this->widget->get_filtered_data($filter_id);

		$this->assertSame('OK', $data['state_definitions']['states'][0]['label']);
		$this->assertSame(2, $data['stats'][0], var_export($data, true));

		$this->assertSame('Warning', $data['state_definitions']['states'][1]['label']);
		$this->assertSame(1, $data['stats'][1]);

		$this->assertSame('Critical', $data['state_definitions']['states'][2]['label']);
		$this->assertSame(0, $data['stats'][2]);

		$this->assertSame('Unknown', $data['state_definitions']['states'][3]['label']);
		$this->assertSame(0, $data['stats'][3]);

		$this->assertSame('Pending', $data['state_definitions']['states'][4]['label']);
		$this->assertSame(0, $data['stats'][4]);
	}

	/**
	 * @group MON-9212
	 */
	public function test_saved_widget_filter() {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'saved_filters' => array(
					array(
						'id' => 2,
						'username' => '',
						'filter_name' => 'OK hosts',
						'filter_table' => 'hosts',
						'filter' => '[hosts] state = 0',
						'filter_description' => 'OK hosts',
					),
				)
			),
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Randall Daragh',
						'state' => '0',
						'has_been_checked' => '1',
					),
					array(
						'name' => 'Fooooobbar',
						'state' => '0',
						'has_been_checked' => '1',
					),
					array(
						'name' => 'Attila Tadala',
						'state' => '1',
						'has_been_checked' => '1',
					),
				)
			)
		), __FUNCTION__);

		$filter_id = 2;
		$data = $this->widget->get_filtered_data($filter_id);

		$this->assertSame('Up', $data['state_definitions']['states'][0]['label']);
		$this->assertSame(2, $data['stats'][0], var_export($data, true));

		$this->assertSame('Down', $data['state_definitions']['states'][1]['label']);
		$this->assertSame(0, $data['stats'][1]);

		$this->assertSame('Unreachable', $data['state_definitions']['states'][2]['label']);
		$this->assertSame(0, $data['stats'][2]);

		$this->assertSame('Pending', $data['state_definitions']['states'][3]['label']);
		$this->assertSame(0, $data['stats'][3]);
	}

	/**
	 * @group MON-9212
	 */
	public function test_hardcoded_widget_filter_without_pending () {
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Attila Tadala',
						'state' => '0',
						'has_been_checked' => '1',
					),
				)
			)
		), __FUNCTION__);

		$filter_id = -200;
		$data = $this->widget->get_filtered_data($filter_id);

		$this->assertSame('Pending', $data['state_definitions']['states'][3]['label']);
		$this->assertSame(0, $data['stats'][3]);

		$css_class = $data['state_definitions']['states'][3]['css_class'];
		$this->assertSame('no-display', $css_class($data['stats'][3]));
	}

	/**
	 * @group MON-9212
	 * @group MON-9304
	 */
	public function test_title_reflects_content () {
		$this->mock_data(array(
			'ORMDriverMySQL default' => array(
				'saved_filters' => array(
					array(
						'id' => 3,
						'username' => '',
						'filter_name' => 'broforce',
						'filter_table' => 'hosts',
						'filter' => '[hosts] state = 0',
						'filter_description' => 'Broforce',
					),
				)
			),
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Attila Tadala',
						'state' => '0',
						'has_been_checked' => '0',
					),
				)
			)
		), __FUNCTION__);

		$this->widget->model->set_setting(array('filter_id' => 3));
		$this->assertSame('State Summary of "broforce"', $this->widget->get_title());
	}

}

