<?php
require_once('op5/objstore.php');

/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Ninja_widgets_Test extends PHPUnit_Framework_TestCase {
	/**
	 * a widget that should exist and be instanceable, used as test widget
	 */
	const DUMMY_WIDGET = 'netw_health';
	const DUMMY_WIDGET_CLASS = 'Netw_health_Widget';

	public function setUp() {
		op5objstore::instance()->mock_clear();
		Auth::instance(array('session_key' => false))->force_user(new User_AlwaysAuth_Model());
	}

	/**
	 * Verifies that get_available_widgets doesn't return any unresolved widgets.
	 */
	public function test_get_available_widgets() {
		$this->orig_widgets = Dashboard_WidgetPool_Model::get_available_widgets();
		$this->assertThat($this->orig_widgets, $this->isType('array'));
		$this->assertNotEmpty($this->orig_widgets);

		foreach ($this->orig_widgets as $widget_name => $friendly_name)
			$this->assertNotSame(false, $widget_name);
	}

	/**
	 * Verifies that a new widget can be built
	 */
	public function test_build_widget() {
		$widget_model = new Dashboard_Widget_Model();
		$widget_model->set_setting(array('random' => 'info'));
		$widget_model->set_name(self::DUMMY_WIDGET);

		$widget = $widget_model->build();
		$this->assertInstanceOf(self::DUMMY_WIDGET_CLASS, $widget);
		/* @var $widget Netw_Health_Widget */
		$this->assertSame($widget_model, $widget->model);
	}

	/**
	 * Verify that create, update and delete widget settings works.
	 */
	public function test_create_update_delete() {
		$dashboard = new Dashboard_Model();
		$dashboard->set_username('superuser');
		$dashboard->save();

		/* Step 1 - Create */
		$widget_model = new Dashboard_Widget_Model();
		$widget_model->set_dashboard_id($dashboard->get_id());
		$widget_model->set_setting(array('random' => 'save_setting_test'));
		$widget_model->set_name(self::DUMMY_WIDGET);
		$widget_model->save();
		unset($widget_model);

		/* Step 2 - Read + Update */
		$widget_model = $dashboard->get_dashboard_widgets_set()->one();
		$this->assertInstanceOf('Dashboard_Widget_Model', $widget_model);
		$this->assertEquals(array('random' => 'save_setting_test'), $widget_model->get_setting());

		$widget_model->set_setting(array('random' => 'some_other_settings'));
		$widget_model->save();
		unset($widget_model);

		/* Step 3 - Read + Delete */
		$widget_model = $dashboard->get_dashboard_widgets_set()->one();
		$this->assertInstanceOf('Dashboard_Widget_Model', $widget_model);
		$this->assertEquals(array('random' => 'some_other_settings'), $widget_model->get_setting());

		$widget_model->delete();
		unset($widget_model);

		/* Finish - Verify it's deleted */
		$widget_model = $dashboard->get_dashboard_widgets_set()->one();
		$this->assertSame(false, $widget_model);

		$dashboard->delete();
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
}
