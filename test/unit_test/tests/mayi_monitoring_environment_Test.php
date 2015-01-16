<?php
require_once ('op5/objstore.php');
require_once ('op5/livestatus.php');
class MayI_monitoring_environemnt_Test extends PHPUnit_Framework_TestCase {

	/**
	 * The virtual user configuration
	 *
	 * @var array
	 */
	protected $config_array = array ();

	/**
	 * The virtual environment this test is using
	 *
	 * @var array
	 */
	protected $objects = array (
		"services" => array (
			array (
				'description' => 's_a',
				'host_name' => 'h_a',
				'hourly_value' => 1
			),
			array (
				'description' => 's_b',
				'host_name' => 'h_b',
				'hourly_value' => 2
			),
			array (
				'description' => 's_c',
				'host_name' => 'h_c',
				'hourly_value' => 4
			),
			array (
				'description' => 's_d',
				'host_name' => 'h_c',
				'hourly_value' => 8
			),
			array (
				'description' => 's_e',
				'host_name' => 'h_f',
				'hourly_value' => 16
			)
		),
		"hosts" => array (
			array (
				'name' => 'h_a',
				'childs' => array (
					'h_b',
					'h_c'
				),
				'hourly_value' => 32,
				'num_services' => 0,
				'state' => 0
			),
			array (
				'name' => 'h_b',
				'childs' => array (),
				'hourly_value' => 64,
				'num_services' => 0,
				'state' => 0
			),
			array (
				'name' => 'h_c',
				'childs' => array (
					'h_d',
					'h_e'
				),
				'hourly_value' => 128,
				'num_services' => 0,
				'state' => 0
			),
			array (
				'name' => 'h_d',
				'childs' => array (),
				'hourly_value' => 256,
				'num_services' => 0,
				'state' => 0
			),
			array (
				'name' => 'h_e',
				'childs' => array (
					'h_f'
				),
				'hourly_value' => 512,
				'num_services' => 0,
				'state' => 0
			),
			array (
				'name' => 'h_f',
				'childs' => array (),
				'hourly_value' => 1024,
				'num_services' => 0,
				'state' => 0
			)
		)
	);
	/**
	 * Contains a reference to the mocked config, so we can see some debug
	 * variables
	 *
	 * @var MockCofnig
	 */
	protected $conf = false;
	/**
	 * Contains a reference to the mocked livestatus, so we can see some debug
	 * variables
	 *
	 * @var MockLivestatus
	 */
	protected $ls = false;
	/**
	 * Make sure the enviornment is clean, and livestatus is mocked
	 */
	public function setUp() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();
		$this->ls = new MockLivestatus( $this->objects, array (
			'allow_undefined_columns' => true
		) );
		op5objstore::instance()->mock_add( 'op5Livestatus', $this->ls );
	}
	/**
	 * Remove mock environment
	 */
	public function tearDown() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();
	}

	/**
	 * Test that a valid environment is returned
	 */
	public function test_verify_environment() {
		// Load information to the new MayI (from setUp)
		new monitoring_hooks();

		$this->assertSame( array (
			'monitoring' => array (
				'monitor' => array (
					'hosts' => 6,
					'services' => 0,
					'pollers' => 0,
					'peers' => 0
				)
			)
		), op5MayI::instance()->get_environment() );
	}
}
