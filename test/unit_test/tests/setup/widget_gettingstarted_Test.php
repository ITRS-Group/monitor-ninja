<?php
/**
 * Test that the getting started widget can be found and renderered
 */
class widget_gettingstarted_Test extends PHPUnit_Framework_TestCase {
	public function test_can_render()
	{
		$widget_model = new Ninja_Widget_Model();
		$widget_model->set_name("gettingstarted");
		$widget_model->set_friendly_name("Getting started stuff");
		$widget = $widget_model->build();
		$this->assertInstanceOf('gettingstarted_widget', $widget);

		$content = $widget->render('index', false);

		/* Just verify that some output from the view is available */
		$this->assertContains("gettingstarted_container", $content);
		$this->assertContains("gettingstarted_columncontainer", $content);

		/* No exception should be available in output */
		/* Skip prefix E in test, for case insensitivity */
		$this->assertNotContains("xception", $content);
	}
}
