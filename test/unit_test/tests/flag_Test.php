<?php

/**
 * A class that contains a deprecated method.
 */
class SouthWest_Hospital {
	/**
	 * Steve at marketing really needs this.
	 */
	static function write_budget_for_1984() {
		flag::deprecated(__METHOD__, "It is 1987 now, you are late");
		return 'one hundred billion dollars';
	}
}

/**
 * Test deprecation of methods
 */
class Flag_Test extends PHPUnit_Framework_TestCase {

	const DEPRECATION_ENV_VAR = 'NINJA_FLAG_DEPRECATION_SHOULD_EXIT';

	function setup() {
		$_SESSION = array();
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'='));
	}

	function teardown() {
		$_SESSION = array();
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'='));
	}

	/**
	 * @group MON-9199
	 */
	function test_env_disabled_should_be_ignored() {
		$this->assertSame('one hundred billion dollars', SouthWest_Hospital::write_budget_for_1984());
	}

	/**
	 * @group MON-9199
	 */
	function test_default_env_enabled_should_throw_exception() {
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'=1'));
		$this->assertSame(true, flag::deprecation_kills());
		try {
			SouthWest_Hospital::write_budget_for_1984();
		} catch(DeprecationException $e) {
			$expected = "DEPRECATION: 'SouthWest_Hospital::write_budget_for_1984' is deprecated and should not be executed: It is 1987 now, you are late";
			$this->assertSame($expected, $e->getMessage());
			return;
		}
		$this->assertTrue(false, "This code path should not be reached, expected an exception to be thrown");
	}

	/**
	 * @group MON-9199
	 */
	function test_op5config_should_throw_exception() {
		$conf = array(
			'ninja' => array(
				'deprecation_should_exit' => 1
			)
		);
		op5objstore::instance()->mock_add('op5config', new MockConfig($conf));
		try {
			SouthWest_Hospital::write_budget_for_1984();
		} catch(DeprecationException $e) {
			$expected = "DEPRECATION: 'SouthWest_Hospital::write_budget_for_1984' is deprecated and should not be executed: It is 1987 now, you are late";
			$this->assertSame($expected, $e->getMessage());
			return;
		}
		$this->assertTrue(false, "This code path should not be reached, expected an exception to be thrown");
	}
}
