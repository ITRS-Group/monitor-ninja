<?php

require_once "op5/auth/AuthDriver_Apache.php";
require_once "op5/objstore.php";

class AuthDriverApacheTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Device under test
	 *
	 * @var op5AuthDriver_Apache
	 */
	private $dut;

	private $default_config = array(
		'auth' => array(
			'Apa' => array(
				'driver' => 'Apache'
			),
			'common' => array(
				'session_key' => 'auth_user',
				'default_auth' => 'Default',
				'apc_enabled' => 'false',
				'apc_ttl' => '60',
				'apc_store_prefix' => 'op5_login_'
			)
		),
		'auth_groups' => array(
			'yolo' => array(
				'host_view_all'
			)
		),
		'auth_users' => array(
			'usra' => array(
				'username' => 'usra',
				'realname' => 'User A',
				'groups' => array(
					'grpa'
				),
				'modules' => array(
					'Apa'
				)
			),
			'usrb' => array(
				'username' => 'usrb',
				'realname' => 'User B',
				'groups' => array(
					'grpa',
					'grpb'
				),
				'modules' => array(
					'Apa'
				)
			),
			'usrd' => array(
				'username' => 'usrd',
				'realname' => 'User D',
				'groups' => array(
					'grpa',
					'grpd'
				),
				'modules' => array(
					'Apa'
				)
			),
			'usre' => array(
				'username' => 'usre',
				'realname' => 'User no Auth Modules',
				'groups' => array(
					'grpa',
					'grpd'
				),
				'modules' => array(
				)
			),
			'usrf' => array(
				'username' => 'usrf',
				'realname' => 'User F',
				'groups' => array(
					'yolo'
				),
				'modules' => array(
					'Apa'
				)
			)
		)
	);

	private function init_config($module_name) {
		op5objstore::instance()->mock_add('op5config', new MockConfig($this->default_config));
		$config = $this->default_config['auth'][$module_name];

		$module = new AuthModule_Model();
		$module->set_modulename($module_name);
		$module->set_properties($config);

		$this->dut = new op5AuthDriver_Apache($module);
	}

	/**
	 * Make sure all tests are isolated
	 */
	function setUp() {
		op5objstore::instance()->clear();
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->mock_add('op5log', new MockLog(true));

		$this->init_config('Apa');
	}

	public function tearDown() {
		unset($this->dut);
		op5objstore::instance()->clear();
		op5objstore::instance()->mock_clear();
	}

	function test_groups_available() {
		$this->assertEquals(
			$this->dut->groups_available(array(
				'grpa',
				'grpb',
				'grpc',
				'user_usra',
				'user_usrb',
				'user_usrc',
				'apache_auth_user'
			)),
			array(
				'grpa' => true,
				'grpb' => true,
				'grpc' => false,
				'apache_auth_user' => true
			),
			'Test group list returns expected data'
		);
	}

	function test_no_user() {
		unset($_SERVER['PHP_AUTH_USER']);
		$user = $this->dut->auto_login();
		$this->assertEquals($user, false, 'Test that no user is returned without username');
	}

	function test_user_does_not_have_apache_module_driver_auth_lol() {
		$_SERVER['PHP_AUTH_USER'] = 'usre';
		$user = $this->dut->auto_login();
		$this->assertEquals(array(
			'apache_auth_user'
			),
			$user->get_groups(),
			"User should only receive 'apache_auth_user' group since it doesn't use the Apa module"
		);
	}

	function test_group_resolution() {
		$_SERVER['PHP_AUTH_USER'] = "usra";
		$user = $this->dut->auto_login();
		$this->assertTrue($user instanceof User_Model, 'Test auto_login returns a User_Model');

		$this->assertEquals($user->get_groups(),
				array(
						'grpa',
						'apache_auth_user'

				),
				'Test group resolution'
		);
	}

	function test_groups_for_user() {
		$groups = $this->dut->groups_for_user('usra');
		$this->assertEquals(
				array(
						'grpa',
						'apache_auth_user'
				),
				$groups,
				'Test group resolution without login'
		);
	}

	function test_get_user() {
		$_SERVER['PHP_AUTH_USER'] = "usrf";
		$auth = new op5auth(array('enable_auto_login' => true));
		$user = $auth->get_user();
		$this->assertNotInstanceOf('User_NoAuth_Model', $user);
		$this->assertInstanceOf('User_Model', $user);
	}

}
