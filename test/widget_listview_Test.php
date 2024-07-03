<?php

class Widget_Listview_Test extends \PHPUnit\Framework\TestCase {

	protected function setUp() : void {

		$widget_model = new Ninja_Widget_Model();
		$widget_model->set_name('listview');
		$widget_model->set_friendly_name('ListView');

		$widget = $widget_model->build();
		$this->widget = $widget;

	}

	public function test_listview_widget_exists () {
		$this->assertInstanceOf('Listview_Widget', $this->widget);
	}

	public function test_options_is_array_of_option_instances () {
		$options = $this->widget->options();
		$this->assertIsArray($options);
		$this->assertContainsOnlyInstancesOf('option', $options);
	}

	#[Group('MON-9365')]
	public function test_options_available () {

		$options = $this->widget->options();
		$option_names = array_map(function ($option) {
			return $option->name;
		}, $options);

		/* Listview widget should not have refresh option!
		 * But should gain Custom title from widget_Base */
		$this->assertEquals(array(
			'title',
			'query',
			'columns',
			'limit',
			'order'
		), $option_names);

	}

	#[Group('MON-9365')]
	public function test_suggested_title_for_default_filter () {

		$title = $this->widget->get_title();
		$this->assertEquals('List of hosts', $title);

	}

	#[Group('MON-9365')]
	public function test_suggested_title_for_set_filter () {

		$settings = $this->widget->model->get_setting();
		$settings['query'] = '[ninja_widgets] all';

		$this->widget->model->set_setting($settings);
		$title = $this->widget->get_title();

		$this->assertEquals('List of ninja widgets', $title);

	}

}

