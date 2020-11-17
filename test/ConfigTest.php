<?php

require_once "op5/config.php";
require_once "op5/objstore.php";

class ConfigTest extends PHPUnit_Framework_TestCase
{
	const TEST_ENV_VAR = 'OP5_TURTLES_PURPLE_NAME';

	protected function setUp()
	{
		$this->config = new op5config(array(
			"basepath" => __DIR__."/fixtures"
		));
		// unset the test env var
		$this->assertSame(true, putenv(self::TEST_ENV_VAR));
	}

	protected function teardown()
	{
		$this->assertSame(true, putenv(self::TEST_ENV_VAR));
	}

	public function test_no_croak_on_nonexistent_namespace()
	{
		$this->assertNull($this->config->getConfig("iDontExist"), "Namespace that doesn't exist should return null");
	}

	public function test_missing_setting_returns_null()
	{
		$this->assertSame(null, $this->config->getConfig("i.love.lamp"), "Should've been null.. try again");
	}

	public function test_case_sensitive_config()
	{
		$this->assertSame(null, $this->config->getConfig("turtles.purple.Name"), "Try to keep all category names lowercased..");
	}

	public function test_no_folder_inclution_anymore()
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

	public function test_reserved_prefixes()
	{
		$this->assertSame(array(), $this->config->getConfig("something_new"), "We shouldn't get any prefixed values here");
		$this->assertSame(array("__version" => 3), $this->config->getConfig("something_new", true), "Prefixed values should be returned when we ask for them");
	}

	/**
	 * @group MON-9199
	 */
	public function test_env_takes_precedence_over_files()
	{
		$this->assertSame(true, putenv(self::TEST_ENV_VAR));
		$this->assertSame(
			'Donatello',
			$this->config->getConfig('turtles.purple.name'),
			'Safety check for the fixture'
		);

		$this->assertSame(true, putenv(self::TEST_ENV_VAR."=Leonardo"));
		$this->assertSame('Leonardo', $this->config->getConfig('turtles.purple.name'));

		$this->assertSame(true, putenv(self::TEST_ENV_VAR));
		$this->assertSame(
			'Donatello',
			$this->config->getConfig('turtles.purple.name'),
			'A reset of the empty environvent variable should make the stored config reappear'
		);
	}

	public function config_file_permission_data_provider() {
        /*
        * Specifications of files which should be checked, and the permissions expected. And yes, I created this
        * key-value array which then is converted into a standard phpunit test array due to the phpunit standard being
        * rather unclear when it comes to readability
        */
        $configPermissionPairs = array(
            array(
                'filename' => APPPATH . '/config/database.php',
                'expectedPermission' => 440,
                'expectedOwner' => 'monitor',
                'expectedGroup' => 'apache'
            )
        );

        $returnArray = array();
        foreach ($configPermissionPairs as $permissionPair) {
            $returnArray[] = array(
                $permissionPair['filename'],
                $permissionPair['expectedPermission'],
                $permissionPair['expectedOwner'],
                $permissionPair['expectedGroup']
            );
        }

        return $returnArray;
    }

    /**
     * Verify the permissions of various config files; making sure they are accessible, but not too accessible.
     * Originally implemented due to: MON-9723
     * @dataProvider config_file_permission_data_provider
     * @param $filename String The name and path of the file tested
     * @param $expectedPermission Int Expected permission mask
     * @param $expectedUser String The expected username of the owner of the file
     * @param $expectedGroup String The expected group name of the owner of the file
     * @internal param Int $actual Actual permission mask returned from fileparms()
     */
	public function test_config_file_permissions($filename, $expectedPermission, $expectedUser, $expectedGroup) {
	    $actualPermission = (int)substr(decoct(fileperms($filename)), 3);
        $this->assertSame(
            $expectedPermission,
            $actualPermission,
            sprintf("Permission check for file %s failed, got: %d, expected: %d", $filename, $actualPermission, $expectedPermission)
        );
    }

    /**
     * Verify the owner of various config files; making sure they are accessible, but not too accessible.
     * Originally implemented due to: MON-9723
     * @dataProvider config_file_permission_data_provider
     * @param $filename String The name and path of the file tested
     * @param $expectedPermission Int Expected permission mask
     * @param $expectedUser String The expected username of the owner of the file
     * @param $expectedGroup String The expected group name of the owner of the file
     * @internal param Int $actual Actual permission mask returned from fileparms()
     */
    public function test_config_file_user($filename, $expectedPermission, $expectedUser, $expectedGroup) {
        $output = posix_getpwuid(fileowner($filename));
        $actualUser = $output['name'];
        $this->assertSame(
            $expectedUser,
            $actualUser,
            sprintf("File owner check for file %s failed, got: %s, expected: %s", $filename, $actualUser, $expectedUser)
        );
    }

    /**
     * Verify the group of various config files; making sure they are accessible, but not too accessible.
     * Originally implemented due to: MON-9723
     * @dataProvider config_file_permission_data_provider
     * @param $filename String The name and path of the file tested
     * @param $expectedPermission Int Expected permission mask
     * @param $expectedUser String The expected username of the owner of the file
     * @param $expectedGroup String The expected group name of the owner of the file
     * @internal param Int $actual Actual permission mask returned from fileparms()
     */
    public function test_config_file_group($filename, $expectedPermission, $expectedUser, $expectedGroup) {
        $output = posix_getgrgid(filegroup($filename));
        $actualGroup = $output['name'];
        $this->assertSame(
            $expectedGroup,
            $actualGroup,
            sprintf("File group check failed for file %s failed, got: %s, expected: %s", $filename, $actualGroup, $expectedGroup)
        );
    }
}
