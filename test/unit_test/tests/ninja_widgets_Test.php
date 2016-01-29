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
		Auth::instance(array('session_key' => false))->force_user(new Op5User_AlwaysAuth());
	}

	/**
	 * Verifies that get_available_widgets doesn't return any unresolved widgets.
	 */
	public function test_available_widgets()
	{
		$this->orig_widgets = Ninja_WidgetPool_Model::get_available_widgets();

		$this->assertThat($this->orig_widgets, $this->isType('array'));

		$this->assertNotEmpty($this->orig_widgets);

		foreach ($this->orig_widgets as $widget_name => $friendly_name)
			$this->assertNotSame(false, $widget_name);
	}

	/**
	 * Verifies that a new widget can be built
	 */
	function test_build_widget() {
		$widget_model = new Ninja_Widget_Model();
		$widget_model->set_username('superuser');
		$widget_model->set_instance_id(13);
		$widget_model->set_page('test/page');
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
	function test_create_update_delete() {
		$instance_id = time();

		/* Step 1 - Create */
		$widget_model = new Ninja_Widget_Model();
		$widget_model->set_username('superuser');
		$widget_model->set_instance_id($instance_id);
		$widget_model->set_page('test/page');
		$widget_model->set_setting(array('random' => 'save_setting_test'));
		$widget_model->set_name(self::DUMMY_WIDGET);
		$widget_model->save();
		unset($widget_model);

		/* Step 2 - Read + Update */
		$widget_model = Ninja_WidgetPool_Model::all()
			->reduce_by('username', 'superuser', '=')
			->reduce_by('page', 'test/page', '=')
			->reduce_by('name', self::DUMMY_WIDGET, '=')
			->reduce_by('instance_id', $instance_id, '=')
			->one();
		$this->assertInstanceOf('Ninja_Widget_Model', $widget_model);
		$this->assertEquals(array('random' => 'save_setting_test'), $widget_model->get_setting());

		$widget_model->set_setting(array('random' => 'some_other_settings'));
		$widget_model->save();
		unset($widget_model);

		/* Step 3 - Read + Delete */
		$widget_model = Ninja_WidgetPool_Model::all()
			->reduce_by('username', 'superuser', '=')
			->reduce_by('page', 'test/page', '=')
			->reduce_by('name', self::DUMMY_WIDGET, '=')
			->reduce_by('instance_id', $instance_id, '=')
			->one();
		$this->assertInstanceOf('Ninja_Widget_Model', $widget_model);
		$this->assertEquals(array('random' => 'some_other_settings'), $widget_model->get_setting());

		$widget_model->delete();
		unset($widget_model);

		/* Finish - Verify it's deleted */
		$widget_model = Ninja_WidgetPool_Model::all()
			->reduce_by('username', 'superuser', '=')
			->reduce_by('page', 'test/page', '=')
			->reduce_by('name', self::DUMMY_WIDGET, '=')
			->reduce_by('instance_id', $instance_id, '=')
			->one();
		$this->assertSame(false, $widget_model);
	}
}
