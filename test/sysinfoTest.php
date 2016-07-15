<?php
require_once ("op5/objstore.php");
require_once ("op5/sysinfo.php");

class SysinfoTest extends PHPUnit_Framework_TestCase {
	private $si;
	private $config = array (
		'hosts' => array (array ('name' => 'hstA'),array ('name' => 'hstB'),
			array ('name' => 'hstC'),array ('name' => 'hstD')),
		'services' => array (array ('description' => 'svcA'),
			array ('description' => 'svcB'),array ('description' => 'svcC'),
			array ('description' => 'svcD'),array ('description' => 'svcE'),
			array ('description' => 'svcF')));
	public function setUp() {
		$objstore = op5objstore::instance();
		$objstore->mock_add('op5livestatus', new MockLivestatus($this->config));
		$this->si = new op5sysinfo();
	}
	public function tearDown() {
		$objstore = op5objstore::instance();
		$objstore->mock_clear();
		$objstore->clear();
	}
	public function test_get_monitor_usage() {
		$this->assertSame(4, $this->si->get_monitor_usage());
	}
	public function test_get_monitor_service_usage() {
		$this->assertSame(6, $this->si->get_monitor_service_usage());
	}
	/* Not as easy to mock...
	public function test_get_logserver_usage() {
	}
	public function test_get_pollers_usage() {
	}
	public function test_get_peers_usage() {
	}
	public function test_get_aps_usage() {
	}
	public function test_get_trapper_usage() {
	}
	*/
}
