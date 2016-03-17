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

// TODO: split into flag-unit tests and a special integration test for MON-9199

/**
 * Test deprecation of methods
 */
class Flag_Test extends PHPUnit_Framework_TestCase {

	const STASHED_CONFIG_FILE_TEMPORARY_NAME = '/tmp/klj132hj5jkndsfndjsnfj2134adsfh';
	const DEPRECATION_ENV_VAR = 'NINJA_FLAG_DEPRECATION_SHOULD_EXIT';
	private static $global_flag_config;

	function setup() {
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'='));
		$_SESSION = array();
	}

	function assertPreConditions() {
		$this->assertSame(false, is_readable(self::$global_flag_config));
	}

	function teardown() {
		$_SESSION = array();
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'='));
		@unlink(self::$global_flag_config);
	}

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
	function test_custom_config_file_should_throw_exception() {
		$flag_config = '<?php $config["deprecation_should_exit"] = true;';
		$written = file_put_contents(self::$global_flag_config, $flag_config);
		$this->assertNotSame(false, $written);
	}

	/**
	 * Needed for avoiding global debug-dying-state.. I painted myself into
	 * a corner: of course we want to have a global deprecation flag on
	 * while testing Ninja, but for testing the flags themselves, we cannot
	 * rely on global state.
	 */
	static function setUpBeforeClass() {
		$ninja_dir = __DIR__.'/../../..';
		assert(basename(realpath($ninja_dir)) === "ninja");
		self::$global_flag_config = $ninja_dir.'/application/config/custom/flag.php';
		if(file_exists(self::$global_flag_config)) {
			assert(rename(self::$global_flag_config, self::STASHED_CONFIG_FILE_TEMPORARY_NAME));
		}
	}

	static function tearDownAfterClass() {
		if(file_exists(self::STASHED_CONFIG_FILE_TEMPORARY_NAME)) {
			assert(rename(self::STASHED_CONFIG_FILE_TEMPORARY_NAME, self::$global_flag_config));
		}
	}
}
