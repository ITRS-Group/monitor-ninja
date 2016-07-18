<?php

class Mock_Test extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$this->mock_data_path = false;
	}

	protected function tearDown() {
		op5objstore::instance()->mock_clear();
		// in this test case, all methods sets mock_data_path, or else...
		$this->assertTrue(unlink($this->mock_data_path), $this->mock_data_path);
	}

	private function mock_data($tables, $file) {
		$this->mock_data_path = __DIR__.'/'.$file.'.json';
		$this->assertTrue((boolean) file_put_contents(
			$this->mock_data_path,
			json_encode($tables)
		));
		foreach($tables as $driver => $tables) {
			op5objstore::instance()
				->mock_add($driver,
					new ORMDriverNative(
						$tables,
						$this->mock_data_path,
						$driver
					)
				);
		}
	}

	public function test_can_iterate_over_mocked_services_that_refers_to_a_host() {
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'hosts' => array(),
				'services' => array(
					array(
						'description' => 'Randall Daragh',
						'state' => '0',
						'host' => array(
							'name' => 'Ronny Fayiz'
						),
						//'host.name' => 'Randall',
						//'host_name' => 'Randall',
						'has_been_checked' => '1',
					),
					array(
						'description' => 'Fooooobbar',
						'state' => '0',
						//'host' => array('has_been_checked' => 1),
						'has_been_checked' => '1',
					),
					array(
						'description' => 'Attila Tadala',
						'state' => '1',
						//'host' => array('has_been_checked' => 1),
						'has_been_checked' => '1',
					),
				)
			)
		), __FUNCTION__);

		$service_pool = ObjectPool_Model::pool('services');
		$this->assertInstanceOf('ServicePool_Model', $service_pool,
			"The pool() method should return a pool specificly ".
			"suited to the method's argument");

		$all_services = $service_pool->all();
		$this->assertInstanceOf('ServiceSet_Model', $all_services,
			"A selection of a pool results in a set suited to ".
			"the pool. No stored data have been fetched yet.");
		$this->assertSame(3, count($all_services),
			"You can count() every descendant of ObjectSet_Model, ".
			"including ServiceSet_Model");

		$columns = array();
		$columns = false;
		// TODO here in lies the bug, 'false' is a way around it
		$order = array();
		$limit = false;
		$offset = false;
		$iterator = $all_services->it($columns, $order, $limit, $offset);
		$this->assertInstanceOf('ArrayIterator', $iterator,
			"it() on a set returns something that is iterable ".
			"(you can even use it() in a foreach!)");

		$first_service = current($iterator);
		$this->assertInstanceOf('Service_Model', $first_service,
			'current() on an iterator (from a set) returns a '.
			'service object.');

		$actual_service_data = $first_service->export();
		$this->assertInternalType('array', $actual_service_data,
			'This is the first time we actually '.
			'get in touch with any stored data at all.');

		$this->assertSame('Randall Daragh', $actual_service_data['description']);
		$this->assertSame('0', $actual_service_data['state'],
			'Livestatus (the layer behind the ORM in our case) '.
			'uses strings for everything. This is PHP after all');
		$this->assertSame('Ronny Fayiz', $actual_service_data['host']['name'],
			'We are allowed to look at relational data as well');
	}
}

