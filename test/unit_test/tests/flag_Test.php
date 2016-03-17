<?php

/**
 * A class that contains a deprecated method.
 */
class SouthWest_Hospital {
	/**
	 * Steve at marketing really needs this.
	 */
	public static function write_budget_for_1984() {
		flag::deprecated(__METHOD__, "It is 1987 now, you are late");
		return 'one hundred billion dollars';
	}
}

/**
 * Test deprecation of methods
 */
class Flag_Test extends PHPUnit_Framework_TestCase {

	const DEPRECATION_ENV_VAR = 'OP5_NINJA_DEPRECATION_SHOULD_EXIT';

	protected function setup() {
		$_SESSION = array();
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR));
	}

	protected function teardown() {
		$_SESSION = array();
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR));
		op5objstore::instance()->mock_clear();
	}

	/**
	 * @group MON-9199
	 */
	public function test_env_disabled_should_be_ignored() {
		$this->assertSame('one hundred billion dollars', SouthWest_Hospital::write_budget_for_1984());
	}

	/**
	 * @group MON-9199
	 * @expectedException DeprecationException
	 * @expectedExceptionMessage DEPRECATION: 'SouthWest_Hospital::write_budget_for_1984' is deprecated and should not be executed: It is 1987 now, you are late
	 */
	public function test_default_env_enabled_should_throw_exception() {
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'=1'));
		$this->assertSame(true, flag::deprecation_kills());
		SouthWest_Hospital::write_budget_for_1984();
	}

	/**
	 * @group MON-9199
	 * @expectedException DeprecationException
	 * @expectedExceptionMessage DEPRECATION: 'User_Model::__get' is deprecated and should not be executed: Backwards-compatibility after op5user => User_Model
	 */
	public function test_user_model_fails_if_deprecation_is_not_wanted() {
		// now we just test that our dev environment actually has the
		// possibility to die if we don't want to tolerate deprecations
		// when working with Ninja
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'=1'));
		op5auth::instance()->get_user()->username;
	}
}
