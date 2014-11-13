<?php

require_once "op5/objstore.php";
require_once "op5/config.php";
require_once "op5/auth/User.php";
require_once "op5/auth/Authorization.php";

class AuthorizationTest extends PHPUnit_Framework_TestCase
{
	private static $az;

	private static $config = array(
		'auth_groups' => array(
			'limited_read' => array(
				'perm_limited_read',
				'perm_limited_read_2',
			),
			'meta_all_users' => array(
				'perm_meta_all_users'
			),
			'meta_driver_kaka' => array(
				'perm_meta_driver_kaka'
			),
			'meta_driver_boll' => array(
				'perm_meta_driver_boll'
			),
			'user_myuser' => array(
				'perm_myuser'
			),
			'meta_all_users' => array(
				'perm_meta_all_users'
			),
		),
		'log' => array()
	);

	static public function setUpBeforeClass()
	{
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->mock_add('op5config', new MockConfig(self::$config));
		self::$az = op5Authorization::factory();
	}

	public function test_meta_all_resolution() {
		$user = new op5User(array(
				'username' => 'someuser',
				'auth_method' => 'somedriver',
				'groups' => array()
		));
		self::$az->authorize( $user );
		$this->assertEquals(
				$user->auth_data,
				array(
						'perm_meta_all_users' => true
				),
				'All group resolution. If this doesn\'t work, all other tests will fail too'
		);
	}

	public function test_group_resolution() {
		$user = new op5User(array(
				'username' => 'someuser',
				'auth_method' => 'somedriver',
				'groups' => array(
						'limited_read'
				)
		));
		self::$az->authorize( $user );
		$this->assertEquals(
				$user->auth_data,
				array(
						'perm_limited_read' => true,
						'perm_limited_read_2' => true,
						'perm_meta_all_users' => true
				),
				'Simple direct group resolution'
		);
	}

	public function test_user_group_resolution() {
		$user = new op5User(array(
				'username' => 'myuser',
				'auth_method' => 'somedriver',
				'groups' => array(
				)
		));
		self::$az->authorize( $user );
		$this->assertEquals(
				$user->auth_data,
				array(
						'perm_myuser' => true,
						'perm_meta_all_users' => true
				),
				'User group resolution'
		);
	}

	public function test_meta_driver_resolution() {
		$user = new op5User(array(
				'username' => 'someuser',
				'auth_method' => 'kaka',
				'groups' => array(
				)
		));
		self::$az->authorize( $user );
		$this->assertEquals(
				$user->auth_data,
				array(
						'perm_meta_driver_kaka' => true,
						'perm_meta_all_users' => true
				),
				'Driver group resolution'
		);
	}

	/* TODO: test return value of op5Authorization::authorize */
}
