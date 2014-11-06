<?php
require_once ('op5/objstore.php');
class change_password_Test extends PHPUnit_Framework_TestCase {

	/**
	 * The virtual user configuration
	 *
	 * @var array
	 */
	protected $config_array = array (
			'auth' => array (
					'common' => array (
							'session_key' => false,
							'default_auth' => 'Default',
							'apc_enabled' => false,
							'apc_ttl' => 60,
							'apc_store_prefix' => 'op5_login_',
							'version' => 4
					),
					'Default' => array (
							'driver' => 'Default'
					)
			),
			'auth_users' => array (
					'monitor' => array (
							'username' => 'monitor',
							'realname' => 'Monitor Admin',
							/* Password is monitor */
							'password' => '$1$VGn0CdSG$AMJjvHoF8M2nSy8SiPrW70',
							'groups' => array (
									0 => 'admins'
							),
							'password_algo' => 'crypt',
							'modules' => array (
									0 => 'Default'
							)
					)
			),
			'auth_groups' => array (
					'admins' => array ()
			)
	);
	/**
	 * Contains a reference to the mocked config, so we can see some debug
	 * variables
	 *
	 * @var MockCofnig
	 */
	protected $conf = false;

	/**
	 * Contain a reference to the change_password_Controller, which should be
	 * tested
	 */
	protected $controller = false;
	private function do_change($postdata) {
		$_POST = $postdata;
		$this->controller->index();
	}

	/**
	 * Make sure the enviornment is clean, and configuration is mocked
	 */
	public function setUp() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();

		$this->conf = new MockConfig( $this->config_array );
		op5objstore::instance()->mock_add( 'op5config', $this->conf );

		$this->assertTrue( op5auth::instance()->login( 'monitor', 'monitor' ) );

		$this->controller = new Change_Password_Controller();
	}
	/**
	 * Remove mock environment
	 */
	public function tearDown() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();
	}

	/**
	 * Test that we don't do anything if it isn't a post request
	 */
	public function test_no_action_post() {
		$this->do_change( false );
		$this->assertEquals( '$1$VGn0CdSG$AMJjvHoF8M2nSy8SiPrW70', $this->conf->config['auth_users']['monitor']['password'] );
	}

	/**
	 * Test that we can change to a valid password
	 */
	public function test_change_password() {
		$this->do_change( array (
				'current_password' => 'monitor',
				'new_password' => 'newpass',
				'confirm_password' => 'newpass'
		) );

		$this->assertEquals( 'Password changed successfully', $this->controller->template->content->status_msg );
		$this->assertTrue( $this->controller->template->content->successful );

		/* Assume that the password has changed */
		$this->assertNotEquals( '$1$VGn0CdSG$AMJjvHoF8M2nSy8SiPrW70', $this->conf->config['auth_users']['monitor']['password'] );
	}

	/**
	 * Test that we doesn't change if we entered an incorrect password
	 */
	public function test_change_password_missing() {
		$this->do_change( array (
				'current_password' => 'invalid',
				'new_password' => 'newpass',
				'confirm_password' => 'newpass'
		) );

		$this->assertEquals( 'You entered incorrect current password.', $this->controller->template->content->status_msg );
		$this->assertFalse( $this->controller->template->content->successful );

		/* Assume that the password hasn't changed */
		$this->assertEquals( '$1$VGn0CdSG$AMJjvHoF8M2nSy8SiPrW70', $this->conf->config['auth_users']['monitor']['password'] );
	}

	/**
	 * Test that we doesn't change if password is missing
	 */
	public function test_change_password_invalid_password() {
		$this->do_change( array (
				'new_password' => 'newpass',
				'confirm_password' => 'newpass'
		) );

		$this->assertEquals( 'You entered incorrect current password.', $this->controller->template->content->status_msg );
		$this->assertFalse( $this->controller->template->content->successful );

		/* Assume that the password hasn't changed */
		$this->assertEquals( '$1$VGn0CdSG$AMJjvHoF8M2nSy8SiPrW70', $this->conf->config['auth_users']['monitor']['password'] );
	}

	/**
	 * Test that we don't change to a password that is to short
	 */
	public function test_change_password_short_password() {
		$this->do_change( array (
				'current_password' => 'monitor',
				'new_password' => 'shrt',
				'confirm_password' => 'shrt'
		) );

		$this->assertEquals( 'The password must be at least 5 characters long.', $this->controller->template->content->status_msg );
		$this->assertFalse( $this->controller->template->content->successful );

		/* Assume that the password hasn't changed */
		$this->assertEquals( '$1$VGn0CdSG$AMJjvHoF8M2nSy8SiPrW70', $this->conf->config['auth_users']['monitor']['password'] );
	}

	/**
	 * Test that we don't change if password isn't repated correctly
	 */
	public function test_change_password_missmatch() {
		$this->do_change( array (
				'current_password' => 'monitor',
				'new_password' => 'something',
				'confirm_password' => 'somethingelse'
		) );

		$this->assertEquals( 'New password did not match repeated password.', $this->controller->template->content->status_msg );
		$this->assertFalse( $this->controller->template->content->successful );

		/* Assume that the password hasn't changed */
		$this->assertEquals( '$1$VGn0CdSG$AMJjvHoF8M2nSy8SiPrW70', $this->conf->config['auth_users']['monitor']['password'] );
	}
}
