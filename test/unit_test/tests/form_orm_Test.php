<?php

class Form_ORM_Test extends PHPUnit_Framework_TestCase {

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
	}

	private function mock_orm_tables(array $tables) {
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
		if($this->mock_data_path !== false) {
			unlink($this->mock_data_path);
			$this->mock_data_path = false;
		}
		op5objstore::instance()->mock_clear();
	}

	public function get_form() {
		return new Form_Model('my_action_url', array(
			new Form_Field_Text_Model('name', "your name?"),
			new Form_Field_ORMObject_Model('da_contact', 'What is the object?', array('contacts'))
		));
	}

	/**
	 * Verify that incorrect object keys throws an exception
	 *
	 * @group MON-9409
	 * @expectedException FormException
	 * @expectedExceptionMessage da_contact does not point at a valid object
	 */
	public function test_process_fail() {
		$tables = array(
			'ORMDriverLS default' => array(
				'contacts' => array(
					array( 'name' => 'Someone' ),
					array( 'name' => 'Tomtenisse' )
				)
			)
		);
		$this->mock_orm_tables($tables);
		$form = $this->get_form();

		$result = $form->process_data(array(
			'name' => "Something",
			'da_contact' => 'doesntexist'
		));
	}

	/**
	 * Verifies that receiving data given a key unpacks the object correctly,
	 * and that it is put back as default value for the next form
	 *
	 * @group MON-9409
	 */
	public function test_process() {
		$tables = array(
			'ORMDriverLS default' => array(
				'contacts' => array(
					array( 'name' => 'Someone' ),
					array( 'name' => 'Tomtenisse' )
				)
			)
		);
		$this->mock_orm_tables($tables);
		$form_for_processing = $this->get_form();
		$form_with_defaults = $this->get_form();

		$result = $form_for_processing->process_data(array(
			'name' => "Something",
			'da_contact' => array(
				'value' => 'Someone',
				'table' => 'contacts'
			)
		));

		$this->assertInstanceOf('Contact_Model', $result['da_contact']);
		$this->assertEquals('Someone', $result['da_contact']->get_name());

		$form_with_defaults->set_values($result);

		/* When rendering, only the selected host should be available */
		$content = $form_with_defaults->get_view()->render(false);
		$this->assertContains('value="Someone"', $content);
		$this->assertNotContains('value="Tomtenisse"', $content);
	}

	/**
	 * @group MON-9409
	 */
	public function test_perfdata_option_successful_validation_depending_on_orm_object() {
		$tables = array(
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Sueridus',
						'perf_data_raw' => 'pkt=1;0;0;0;5 rta=0.007;2000.000;2000.000;; pl=0%;95;100;;'
					)
				)
			)
		);
		$this->mock_orm_tables($tables);

		$form = new Form_Model(
			'pump action',
			array(
				new Form_Field_ORMObject_Model('host', 'Which host do you want to see perfdata for?', array('hosts')),
				new Form_Field_Perfdata_Model('host_perfdata', 'Host perfdata source', 'host')
			)
		);

		$result = $form->process_data(array(
			'host' => array(
				'value' => 'Sueridus',
				'table' => 'hosts',
			),
			'host_perfdata' => 'pkt'
		));

		$this->assertSame('pkt', $result['host_perfdata']);
	}

	/**
	 * @group MON-9409
	 * @expectedException FormException
	 * @expectedExceptionMessage The performance data source 'Munny Saelee' is not found on the given object
	 */
	public function test_perfdata_option_failing_validation_depending_on_orm_object() {
		$tables = array(
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Sueridus',
						'perf_data_raw' => 'pkt=1;0;0;0;5 rta=0.007;2000.000;2000.000;; pl=0%;95;100;;'
					)
				)
			)
		);
		$this->mock_orm_tables($tables);

		$form = new Form_Model(
			'pump action',
			array(
				new Form_Field_ORMObject_Model('host', 'Which host do you want to see perfdata for?', array('hosts')),
				new Form_Field_Perfdata_Model('host_perfdata', 'Host perfdata source', 'host')
			)
		);

		$form->process_data(array(
			'host' => array(
				'value' => 'Sueridus',
				'table' => 'hosts',
			),
			'host_perfdata' => 'Munny Saelee'
		));
		$this->assertTrue(false, "process_data() should have thrown an exception");
	}
}
