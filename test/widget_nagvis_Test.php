<?php

class Widget_NagVis_Test extends PHPUnit_Framework_TestCase {

	protected function setUp() {

		$widget_model = new Ninja_Widget_Model();
		$widget_model->set_name('nagvis');
		$widget_model->set_friendly_name('NagVis');

		$widget = $widget_model->build();
		$this->widget = $widget;

	}

	public function test_nagvis_widget_exists () {
		$this->assertInstanceOf('Nagvis_Widget', $this->widget);
	}

	public function test_options_is_array_of_option_instances () {
		$options = $this->widget->options();
		$this->assertInternalType('array', $options);
		$this->assertContainsOnlyInstancesOf('option', $options);
	}

	/**
	 * @group MON-9365
	 */
	public function test_options_available () {

		$options = $this->widget->options();
		$option_names = array_map(function ($option) {
			return $option->name;
		}, $options);

		/* NagVis widget should not have refresh option!
		 * But should gain Custom title from widget_Base */
		$this->assertEquals(array(
			'title',
			'map',
			'height'
		), $option_names);

	}

}

