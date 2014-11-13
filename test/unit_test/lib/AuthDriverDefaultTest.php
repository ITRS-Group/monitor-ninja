<?php

require_once("op5/objstore.php");
require_once("op5/auth/Auth.php");

/* This tests both the Auth backend, and the Default auth driver, so they work
 * together.
*
* It assumes that Authorization works too.
*/

class AuthDriverDefaultTest extends PHPUnit_Framework_TestCase {

	static private $config = array(
			'auth' => array(
					'common' => array(
							'default_auth' => 'mydefault'
					),
					'mydefault' => array(
							'driver' => 'Default'
					)
			),
			'auth_users' => array(
					'user_plain' => array(
							'username' => 'user_plain',
							'realname' => 'Just a plain user',
							'password' => 'user_pass',
							'password_algo' => 'plain',
							'modules' => array(
								'mydefault'
							),
							'groups' => array(
									'plain_group'
							)
					),
					'user_nomodule' => array(
						'username' => 'user_nomodule',
						'realname' => 'User that hasn\'t got access to any module',
						'password' => 'user_pass',
						'password_algo' => 'plain',
						'modules' => array(
						),
						'groups' => array(
								'plain_group'
						)
					),
			),
			'auth_groups' => array(
					'plain_group' => array(
							'some_plain_access'
					)
			)
	);


	private $auth = false;

	public function setUp() {
		op5objstore::instance()->mock_add( 'op5config',
		new MockConfig(self::$config) );
		op5objstore::instance()->mock_add( 'op5log', new MockLog() );

		$this->auth = new op5Auth();
	}

	public function tearDown() {
		unset($this->auth);
		op5objstore::instance()->clear();
		op5objstore::instance()->mock_clear();
	}

	public function test_plain_login() {
		$this->assertTrue(
				$this->auth->login( 'user_plain', 'user_pass' ),
				'Couldn\'t login as user_plain' );
		$user = $this->auth->get_user();
		$this->assertEquals(
				array(
						'own_user_change_password' => true,
						'some_plain_access' => true
				),
				$user->auth_data,
				'Invalid authorization flags');
	}

	public function test_plain_failed_login() {
		$this->assertTrue(
				false==$this->auth->login( 'user_plain', 'invalid_pass' ),
				'Could login as user_plain with incorrect password' );
		$user = $this->auth->get_user();
		$this->assertTrue(
				$user instanceof op5User_NoAuth,
				'User returned without login isn\'t op5User_NoAuth');
	}

	public function test_no_module_login() {
		// Should fail login since the user hasn't got any auth modules
		$this->assertFalse($this->auth->login('user_nomodule', 'user_pass'));
		$user = $this->auth->get_user();
		$this->assertTrue(
				$user instanceof op5User_NoAuth,
				'User returned without login isn\'t op5User_NoAuth');
	}

	public function test_metadata_all() {
		$metadata = $this->auth->get_metadata();
		$this->assertEquals(
				$metadata,
				array(
						"require_user_configuration" => array("mydefault"),
						"require_user_password_configuration" => array("mydefault"),
						"login_screen_dropdown" => array("mydefault")
				),
				"Retreiving all metadata for a user isn't correct"
		);
	}

	public function test_metadata_single() {
		$metadata = $this->auth->get_metadata("require_user_configuration");
		$this->assertEquals(
				$metadata,
				array("mydefault"),
				"Retreiving all metadata for a user isn't correct"
		);
	}

	public function test_metadata_multisource() {
		op5objstore::instance()->mock_add( 'op5config',
		new MockConfig(array(
			'auth' => array(
					'common' => array(
							'default_auth' => 'mydefault'
					),
					'driver1' => array(
							'driver' => 'Default'
					),
					'driver2' => array(
							'driver' => 'Default'
					),
					'driver3' => array(
							'driver' => 'Apache'
					),
					'driver4' => array(
							'driver' => 'LDAP'
					)
			)
		))
		);

		$this->auth = new op5Auth();

		$metadata = $this->auth->get_metadata();
		$this->assertEquals(
				$metadata,
				array(
						"require_user_configuration" => array("driver1","driver2","driver3"),
						"require_user_password_configuration" => array("driver1","driver2"),
						"login_screen_dropdown" => array("driver1", "driver2", "driver4")
				),
				"Retreiving all metadata"
		);

		$metadata = $this->auth->get_metadata("require_user_configuration");
		$this->assertEquals(
				$metadata,
				array("driver1","driver2","driver3"),
				"Retreiving one metadata flag"
		);
	}
}
