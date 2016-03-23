<?php

require_once "op5/config.php";
require_once "op5/objstore.php";

class ConfigTest extends PHPUnit_Framework_TestCase
{
	const TEST_ENV_VAR = 'OP5_TURTLES_PURPLE_NAME';

	function setUp()
	{
		$this->config = new op5config(array(
			"basepath" => __DIR__."/fixtures"
		));
		$this->assertSame(true, putenv(self::TEST_ENV_VAR."="));
	}

	function teardown()
	{
		$this->assertSame(true, putenv(self::TEST_ENV_VAR."="));
	}

	function test_no_croak_on_nonexistent_namespace()
	{
		$this->assertNull($this->config->getConfig("iDontExist"), "Namespace that doesn't exist should return null");
	}

	function test_missing_setting_returns_null()
	{
		$this->assertSame(null, $this->config->getConfig("i.love.lamp"), "Should've been null.. try again");
	}

	function test_case_sensitive_config()
	{
		$this->assertSame(null, $this->config->getConfig("turtles.purple.Name"), "Try to keep all category names lowercased..");
	}

	function test_no_folder_inclution_anymore()
	{
		/*
		 * This test needs an explanation... Earlier we could explode yml files to
		 * a folder containing yml files. But we didn't use it. To simplify the
		 * configuration class, we remove that feature, and thus make it possible
		 * to add other op5 systems to sub directories in the /etc/op5 directory
		 * without risk of namespace collissions.
		 *
		 * This test verifies that we can't resolve directories, since we have
		 * truck/wheels.yml containing quantity: 6
		 */
		$this->assertSame(null, $this->config->getConfig("truck"), "Yml in directories isn't allwed anymore");
		$this->assertSame(null, $this->config->getConfig("truck.wheels.quantity"), "Yml in directories isn't allwed anymore");
	}

	function test_reserved_prefixes()
	{
		$this->assertSame(array(), $this->config->getConfig("something_new"), "We shouldn't get any prefixed values here");
		$this->assertSame(array("__version" => 3), $this->config->getConfig("something_new", true), "Prefixed values should be returned when we ask for them");
	}

	/**
	 * @group MON-9199
	 * @group lolo
	 */
	function test_env_takes_precedence_over_files()
	{
		$this->assertSame(true, putenv(self::TEST_ENV_VAR."="));
		$this->assertSame(
			array('name' => 'Donatello'),
			$this->config->getConfig('turtles.purple'),
			'Safety check for the fixture'
		);

		$this->assertSame(true, putenv(self::TEST_ENV_VAR."=Leonardo"));
		$this->assertSame(array('name' => 'Leonardo'), $this->config->getConfig('turtles.purple'));

		$this->assertSame(true, putenv(self::TEST_ENV_VAR."="));
		$this->assertSame(
			array('name' => 'Donatello'),
			$this->config->getConfig('turtles.purple'),
			'A reset of the empty environvent variable should make the stored config reappear'
		);
	}
}
