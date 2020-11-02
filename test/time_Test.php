<?php

/**
 * Test time helper
 */
class Time_Test extends PHPUnit_Framework_TestCase {

	public function test_to_string_zero () {
		$this->assertEquals('now', time::to_string(0));
	}

	public function test_to_string_seconds () {
		$this->assertEquals('45s', time::to_string(45));
	}

	public function test_to_string_minutes () {
		$this->assertEquals('4m 7s', time::to_string(247));
	}

	public function test_to_string_hours () {
		$this->assertEquals('1h 15m', time::to_string(4521));
	}

	public function test_to_string_days () {
		$this->assertEquals('1d 1h', time::to_string((3600 * 24) + 5357));
	}

	public function test_to_string_several_days () {
		$this->assertEquals('7d 6h', time::to_string((3600 * 24 * 7) + 24513));
	}

	public function test_to_string_over_10_days () {
		$this->assertEquals('11d', time::to_string((3600 * 24 * 11) + 24513));
	}

}
