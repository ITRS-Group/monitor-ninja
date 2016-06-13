<?php

class Form_Test extends PHPUnit_Framework_TestCase {

	private $mock_data_path = false;

	protected function setUp() {
		op5objstore::instance()->mock_clear();
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

		$tables = array(
			'ORMDriverLS default' => array(
				'contacts' => array(
					array( 'name' => 'Someone' ),
					array( 'name' => 'Tomtenisse' )
				)
			)
		);

		if($this->mock_data_path !== false) {
			unlink($this->mock_data_path);
		}
		$this->mock_data_path = __DIR__ . '/' . $this->getName(false) . '.json';
		file_put_contents($this->mock_data_path, json_encode($tables));
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add(
				$driver,
				new ORMDriverNative($tables, $this->mock_data_path, $driver)
			);
		}
	}

	protected function tearDown() {
		unlink($this->mock_data_path);
		$this->mock_data_path = false;
	}

	public function get_form() {
		return new Form_Model('my_action_url', array(
			new Form_Field_Text_Model('name', "your name?"),
			new Form_Field_ORM_Object_Model('da_contact', 'What is the object?', 'contacts')
		));
	}

	/**
	 * Verify that incorrect object keys throws an exception
	 *
	 * @expectedException FormException
	 * @expectedExceptionMessage da_contact doesn't point to a valid object
	 */
	public function test_process_fail() {
		$form = $this->get_form();

		$result = $form->process_data(array(
			'name' => "Something",
			'da_contact' => 'doesntexist'
		));
	}

	/**
	 * Verifies that receiving data given a key unpacks the object correctly,
	 * and that it is put back as default value for the next form
	 */
	public function test_process() {
		$form_for_processing = $this->get_form();
		$form_with_defaults = $this->get_form();

		$result = $form_for_processing->process_data(array(
			'name' => "Something",
			'da_contact' => 'Someone'
		));

		$this->assertInstanceOf('Contact_Model', $result['da_contact']);
		$this->assertEquals('Someone', $result['da_contact']->get_name());

		$form_with_defaults->set_values($result);

		/* When rendering, only the selected host should be available */
		$content = $form_with_defaults->get_view()->render(false);
		$this->assertContains('value="Someone"', $content);
		$this->assertNotContains('value="Tomtenisse"', $content);
	}
}
