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
	 * @group MON-9409
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
	 * @group MON-9409
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
	 * @group MON-9409
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

	/**
	 * @group MON-9409
	 */
	public function test_default_values_can_be_scrubbed_from_result_if_left_out() {
		$mandatory_field = new Form_Field_Text_Model('title', 'Title');
		$optional_field = new Form_Field_Number_Model('refresh_rate', 'Refresh rate');

		$form = new Form_Model('unused_action');
		$form->add_field($mandatory_field);
		$form->add_field($optional_field);

		$input = array('title' => 'My title');
		$form->set_missing_fields_cb(array('refresh_rate' => ''));
		$result = $form->process_data($input);
		$this->assertSame($input, $result);
	}

	/**
	 * @group MON-9409
	 */
	public function test_default_values_can_be_overwritten_if_submitted() {
		$mandatory_field = new Form_Field_Text_Model('title', 'Title');
		$optional_field = new Form_Field_Number_Model('refresh_rate', 'Refresh rate');

		$form = new Form_Model('unused_action');
		$form->add_field($mandatory_field);
		$form->add_field($optional_field);

		$input = array('title' => 'My title', 'refresh_rate' => 50);
		$form->set_missing_fields_cb(array('refresh_rate' => ''));
		$result = $form->process_data($input);
		// make up for the fact that the number field casts the result to a float
		$this->assertSame(array('title' => 'My title', 'refresh_rate' => 50.0), $result);
	}

	/**
	 * @group MON-9409
	 * @expectedException MissingValueException
	 * @expectedExceptionMessage Missing a value for the field 'title'
	 */
	public function test_missing_mandatory_field_throws_exception() {
		$mandatory_field = new Form_Field_Text_Model('title', 'Title');
		$also_a_mandatory_field = new Form_Field_Number_Model('refresh_rate', 'Refresh rate');

		$form = new Form_Model('unused_action');
		$form->add_field($mandatory_field);
		$form->add_field($also_a_mandatory_field);

		$input = array('refresh_rate' => 50);
		$result = $form->process_data($input);
	}

	/**
	 * @group MON-9409
	 */
	public function test_form_field_that_is_member_of_a_group_can_be_optional() {
		$group_field = new Form_Field_Group_Model('All your personal information', array(
			new Form_Field_Text_Model('something_personal', 'Something personal'),
			new Form_Field_Text_Model('hair_color', 'What is hair color?')
		));

		$form = new Form_Model('unused_action');
		$form->add_field($group_field);

		$form->set_missing_fields_cb(array('something_personal' => ''));

		$input = array('hair_color' => 'brown');
		$result = $form->process_data(array());
		$this->assertSame(array(), $result);
	}


	/**
	 * @group MON-9409
	 */
	public function test_optional_form_field_can_be_a_callback() {
		$mandatory_field = new Form_Field_Text_Model('title', 'Title');
		$optional_field = new Form_Field_Number_Model('refresh_rate', 'Refresh rate');

		$form = new Form_Model('unused_action');
		$form->add_field($mandatory_field);
		$form->add_field($optional_field);

		$input = array('title' => 'My title');
		$form->set_missing_fields_cb(array(
			'refresh_rate' => function($raw_data, Form_Result_Model $result) {
				$result->set_value('refresh_date', 127.0);
			}
		));
		$result = $form->process_data($input);
		$this->assertSame(array('title' => 'My title', 'refresh_date' => 127.0), $result);
	}
}
