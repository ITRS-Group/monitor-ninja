<?php
require_once ("op5/objstore.php");

class support_Test extends \PHPUnit\Framework\TestCase {

	public function setUp() : void {
		Kohana::config_clear('exception');
		Kohana::config_set('exception.shell_commands', array(
			'echo test1',
			'echo test2'
		));
	}

	public function tearDown() : void {
		Kohana::config_clear('exception');
	}


	public function test_sysinfo() {
		$sut = new Support_Controller();
		$sut->sysinfo();
		$this->assertEquals(array(
			'echo test1' => 'test1',
			'echo test2' => 'test2'
		), $sut->template->data);
	}
}
