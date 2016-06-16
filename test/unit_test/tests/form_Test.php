<?php

class Form_Test extends PHPUnit_Framework_TestCase {
	public function get_form() {
		// phpunit wants to be able to iterate through the dataset
		return array(array(new Form_Model('my_action_url', array(
			new Form_Field_Text_Model('name', "your name?"),
			new Form_Field_Option_Model('trouble', "what do you have problem with?", array(
				'headache' => 'Some wierd phantom pain',
				'foot_sweat' => 'People won\'t sit close to me'
			)),
			new Form_Field_Conditional_Model('trouble', 'headache',
				new Form_Field_Group_Model("Why why why", array(
					new Form_Field_Text_Model('why', "WHY?")
				))
			)
		))));
	}

	/**
	 * @dataProvider get_form
	 */
	public function test_render($form) {
		/* Set some defaults */
		$form->set_values(array(
			'why' => "because"
		));

		/* Render the form */
		$view = $form->get_view();
		$content = $view->render(false);

		/* Verify that the Action URL appears */
		$this->assertContains('action="my_action_url"', $content);

		/* Verify that the fields are available with correct names */
		$this->assertContains('name="name"', $content);
		$this->assertContains('name="trouble"', $content);
		$this->assertContains('name="why"', $content);

		/* Verify that options exists */
		$this->assertContains('value="headache"', $content);
		$this->assertContains('value="foot_sweat"', $content);

		/* Verify that the default value appears somewhere */
		$this->assertContains('value="because"', $content);
	}

	/**
	 * @dataProvider get_form
	 */
	public function test_process($form) {
		$this->assertSame(array(
			'name' => 'Someone',
			'trouble' => 'headache',
			'why' => 'humhum'
		), $form->process_data(array(
			'unknown_field' => 12,
			'trouble' => 'headache',
			'name' => 'Someone',
			'why' => 'humhum'
		)));

		$this->assertSame(array(
			'name' => 'Someone',
			'trouble' => 'foot_sweat',
		), $form->process_data(array(
			'unknown_field' => 12,
			'trouble' => 'foot_sweat',
			'name' => 'Someone',
			'why' => ''
		)));
	}

	/**
	 * @dataProvider get_form
	 * @expectedException FormException
	 * @expectedExceptionMessage trouble has not a valid option value
	 */
	public function test_process_fail($form) {
		$form->process_data(array(
			'unknown_field' => 12,
			'trouble' => 'boll',
			'name' => 'Someone',
			'why' => ''
		));
	}
}
