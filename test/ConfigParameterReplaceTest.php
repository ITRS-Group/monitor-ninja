<?php

require_once("op5/config.php");
require_once("op5/auth/Auth.php");
require_once("op5/objstore.php");

/**
 * Tests replacement of config parameters
 *
 * @group group
 */
class ConfigParameterReplaceTest extends PHPUnit_Framework_TestCase
{

	private $config;

	private $default_config = array(
		'auth' => array(
			'common' => array(
				'default_auth' => 'Shredder',
				'enable_auto_login' => false
			),
			'Shredder' => array(
				'driver' => 'Default'
			),
		),
		'auth_users' => array(
			'Rocksteady' => array(
				'username' => 'Rocksteady',
				'modules' => array(
					'Shredder',
				),
				'groups' => array(
					'Splinter'
				),
			)
		),
		'auth_groups' => array(
			'Splinter' => array(
				'host_view_all',
				'service_view_all',
				'hostgroup_view_all',
				'servicegroup_view_all',
				'export',
				'FILE'
			)
		)
	);

	/**
	 * Setup before testsuite starts
	 *
	 * @return void
	 **/
	public static function setUpBeforeClass()
	{
		// Create config files
		exec("touch " . __DIR__ . "/fixtures/auth.yml");
		exec("touch " . __DIR__ . "/fixtures/auth_users.yml");
		exec("touch " . __DIR__ . "/fixtures/auth_groups.yml");
	}

	/**
	 * Teardown after testsuite ends
	 *
	 * @return void
	 **/
	public static function tearDownAfterClass()
	{
		// Delete config files
		exec('rm -f ' . __DIR__ . '/fixtures/auth.yml', $output);
		exec('rm -f ' . __DIR__ . '/fixtures/auth_users.yml', $output);
		exec('rm -f ' . __DIR__ . '/fixtures/auth_groups.yml', $output);
	}

	/**
	 * Setup before every test
	 *
	 * @return void
	 **/
	function setUp()
	{
		op5objstore::instance()->clear();
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->mock_add('op5config', new op5config(array(
			"basepath" => __DIR__."/fixtures"
		)));
		$this->init_config();
	}


	/**
	 * Teardown after every test
	 *
	 * @return void
	 **/
	function tearDown()
	{
	}

	/**
	 * Write initial config needed for tests
	 *
	 * @return void
	 **/
	private function init_config()
	{
		$this->config = op5config::instance();
		$this->config->setConfig('auth', $this->default_config['auth']);
		$this->config->setConfig('auth_users', $this->default_config['auth_users']);
		$this->config->setConfig('auth_groups', $this->default_config['auth_groups']);
		$this->auth = op5auth::instance();
	}

	/**
	 * Tests a module rename and makes sure it is renamed
	 * in all configuration files
	 *
	 * @return void
	 **/
	function test_module_rename()
	{
		$old_name = "Shredder";
		$new_name = "Krang";
		$this->auth->rename_module($old_name, $new_name);

		$expected_auth = array(
			'common' => array(
				'default_auth' => 'Krang',
				'enable_auto_login' => false
			),
			'Krang' => array(
				'driver' => 'Default'
			)
		);
		$this->assertEquals($expected_auth, $this->config->getConfig('auth'));

		$expected_auth_users = array(
			'Rocksteady' => array(
				'username' => 'Rocksteady',
				'modules' => array(
					'Krang',
				),
				'groups' => array(
					'Splinter'
				),
			)
		);
		$this->assertEquals($expected_auth_users, $this->config->getConfig('auth_users'));
	}

	/**
	 * Test a group rename and makes sure it is renamed
	 * in all configuration files
	 *
	 * @return void
	 **/
	function test_group_rename()
	{
		$old_name = 'Splinter';
		$new_name = 'April';
		$this->auth->rename_group($old_name, $new_name);

		$expected_auth_users = array(
			'Rocksteady' => array(
				'username' => 'Rocksteady',
				'modules' => array(
					'Shredder',
				),
				'groups' => array(
					'April'
				),
			)
		);
		$this->assertEquals($expected_auth_users, $this->config->getConfig('auth_users'));

		$expected_auth_groups = array(
			'April' => array(
				'host_view_all',
				'service_view_all',
				'hostgroup_view_all',
				'servicegroup_view_all',
				'export',
				'FILE'
			)
		);
		$this->assertEquals($expected_auth_groups, $this->config->getConfig('auth_groups'));
	}

	/**
	 * Test an invalid type set in the replace map
	 * An Exception should be thrown
	 *
	 * @return void
	 * @author
	 **/
	function test_non_valid_type()
	{
		try {
			$this->config->cascadeEditConfig('auth.*', 'bebop', 'Raphael', 'Donatello');
		} catch (Exception $e) {
			$this->assertEquals("Unexpected type: bebop is not valid for config parameter replacement", $e->getMessage());
		}
	}
}
