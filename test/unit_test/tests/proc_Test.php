<?php
class proc_Test extends PHPUnit_Framework_TestCase {

	public function setup() {
		$this->executable = __DIR__.'/fixture_proc.php';
	}

	public function test_exit_code() {
		$command_line = (array) $this->executable;
		$result = proc::open($command_line, $stdout, $stderr, $exit_code);
		$this->assertTrue($result);
		$this->assertSame("Of all the gin joints, in all the towns, in all the world, she walks into mine.\n", $stderr);
		$this->assertSame("Louie, I think this is the beginning of a beautiful friendship.\n", $stdout);
		$this->assertSame(19, $exit_code);
	}
}
