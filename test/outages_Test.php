<?php
require_once ('op5/objstore.php');
require_once ('op5/livestatus.php');
class outages_Test extends PHPUnit_Framework_TestCase {

	/**
	 * The virtual environment this test is using
	 *
	 * @var array
	 */
	protected $objects = array (
			"contacts" => array (
				array (
					'name' => 'superuser'
				)
			),
			"contactgroups" => array (
				array(
					'name' => 'admins',
					'members' => array('superuser')
				)
			),
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
	 * Contains a reference to the mocked livestatus, so we can see some debug
	 * variables
	 *
	 * @var MockLivestatus
	 */
	protected $ls = false;
	/**
	 * The controller under test
	 */
	protected $sut = false;
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
		$this->sut = new Outages_Controller();
	}
	/**
	 * Remove mock environment
	 */
	public function tearDown() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();
	}
	/**
	 * Test that when you fetch all columns, you get all columns exported,
	 * including virtual columns
	 */
	public function test_no_outages() {
		$this->sut->index();
		$this->assertEquals( array (), $this->sut->template->content->outage_data );
	}
	/**
	 * Test that when you fetch all columns, you get all columns exported,
	 * including virtual columns
	 */
	public function test_root_outage() {
		/* h_a - root */
		$this->ls->data['hosts'][0]['state'] = 1;

		$this->sut->index();

		$outage_data = $this->sut->template->content->outage_data;

		$this->assertCount( 1, $outage_data );
		$this->assertEquals( 'h_a', $outage_data[0]['name'] );
		$this->assertEquals( 2047, $outage_data[0]['severity'] );
	}
	/**
	 * Test that when you fetch all columns, you get all columns exported,
	 * including virtual columns
	 */
	public function test_subtree_outage() {
		/* h_c => h_a and h_b available */
		$this->ls->data['hosts'][2]['state'] = 1;

		$this->sut->index();

		$outage_data = $this->sut->template->content->outage_data;

		$this->assertCount( 1, $outage_data );
		$this->assertEquals( 'h_c', $outage_data[0]['name'] );
		$this->assertEquals( 2047 - 1 - 2 - 32 - 64, $outage_data[0]['severity'] );
	}
	/**
	 * Test that when you fetch all columns, you get all columns exported,
	 * including virtual columns
	 */
	public function test_multiple_outage() {
		$this->ls->data['hosts'][1]['state'] = 1; /* h_a */
		$this->ls->data['hosts'][2]['state'] = 1; /* h_c */

		$this->sut->index();

		$outage_data = $this->sut->template->content->outage_data;
		$this->assertCount( 2, $outage_data );

		$this->assertEquals( 'h_b', $outage_data[0]['name'] );
		$this->assertEquals( 64 + 2, $outage_data[0]['severity'] );

		$this->assertEquals( 'h_c', $outage_data[1]['name'] );
		$this->assertEquals( 2047 - 1 - 2 - 32 - 64, $outage_data[1]['severity'] );
	}
}
