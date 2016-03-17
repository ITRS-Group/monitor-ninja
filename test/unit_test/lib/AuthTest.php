<?php
require_once ("op5/objstore.php");
require_once ("op5/config.php");
require_once ("op5/auth/Auth.php");

/**
 * Verifies that auth driver handles sessions correctly
 */
class AuthTest extends PHPUnit_Framework_TestCase {
	const STASHED_CONFIG_FILE_TEMPORARY_NAME = '/tmp/klj132hj5jkndsfndjsnfj2134adsfh';
	const DEPRECATION_ENV_VAR = 'NINJA_FLAG_DEPRECATION_SHOULD_EXIT';
	private static $global_flag_config;

	private static $config = array (
		'auth' => array (
			'common' => array (
				'default_auth' => 'mydefault',
				'session_key' => 'testkey'
			),
			'mydefault' => array (
				'driver' => 'Default'
			)
		),
		'auth_users' => array (
			'user_plain' => array (
				'username' => 'user_plain',
				'realname' => 'Just a plain user',
				'password' => 'user_pass',
				'password_algo' => 'plain',
				'modules' => array (
					'mydefault'
				),
				'groups' => array (
					'plain_group'
				)
			),
			'user_nomodule' => array (
				'username' => 'user_nomodule',
				'realname' => 'User that hasn\'t got access to any module',
				'password' => 'user_pass',
				'password_algo' => 'plain',
				'modules' => array (),
				'groups' => array (
					'plain_group'
				)
			)
		),
		'auth_groups' => array (
			'plain_group' => array (
				'some_plain_access'
			)
		),
		'log' => array ()
	);

	/**
	 * Make sure we don't have any lasting instances between tests
	 */
	public function setup() {
		op5objstore::instance()->clear();
		/* Start with a clean session */
		$_SESSION = array ('this_should_be_untouched' => 17);
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'='));
	}

	public function teardown() {
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'='));
	}

	/**
	 * Test a simple login cycle with login and logout, and all intermediate
	 * states
	 */
	public function test_login_logout() {
		$auth = new op5auth();

		$this->assertEquals(array ('this_should_be_untouched' => 17), $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => false,
				'username' => 'notauthenticated',
				'realname' => 'Not Logged in',
				'email' => '',
				'authorized' => array (),
				'groups' => array ()
			), $auth->getActorInfo());

		/* Session should be written during login */
		$auth->login('user_plain', 'user_pass');

		$this->assertEquals(
			array (
				'testkey' => array (
					'username' => 'user_plain',
					'realname' => 'Just a plain user',
					'email' => '',
					'modules' => array (
						'mydefault'
					),
					'auth_method' => 'mydefault',
					'auth_driver' => '',
					'auth_data' => array(
						'own_user_change_password' => true,
						'some_plain_access' => true
					),
					'password_algo' => 'plain',
					'groups' => array (
						'plain_group'
					)
				),
				'this_should_be_untouched' => 17
			), $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => true,
				'username' => 'user_plain',
				'realname' => 'Just a plain user',
				'email' => '',
				'authorized' => array (
					'own_user_change_password' => true,
					'some_plain_access' => true
				),
				'groups' => array (
					'plain_group'
				)
			), $auth->getActorInfo());

		/* Session should be emptied */
		$auth->logout();

		$this->assertEquals(array ('this_should_be_untouched' => 17), $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => false,
				'username' => 'notauthenticated',
				'realname' => 'Not Logged in',
				'email' => '',
				'authorized' => array (),
				'groups' => array (),
			), $auth->getActorInfo());
	}

	/**
	 * Test a login cycle, but close the session handling, so just the auth
	 * library is logged out, but the session remains
	 */
	public function test_login_close_logout() {
		$auth = new op5auth();

		$this->assertEquals(array ('this_should_be_untouched' => 17), $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => false,
				'username' => 'notauthenticated',
				'realname' => 'Not Logged in',
				'email' => '',
				'authorized' => array (),
				'groups' => array (),
			), $auth->getActorInfo());

		/* Session should be written during login */
		$auth->login('user_plain', 'user_pass');

		$this->assertEquals(
			array (
				'testkey' => array (
					'username' => 'user_plain',
					'realname' => 'Just a plain user',
					'email' => '',
					'auth_data' => array (
						'own_user_change_password' => true,
						'some_plain_access' => true
					),
					'modules' => array (
						'mydefault'
					),
					'auth_driver' => '',
					'password_algo' => 'plain',
					'auth_method' => 'mydefault',
					'groups' => array (
						'plain_group'
					)
				),
				'this_should_be_untouched' => 17
			), $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => true,
				'username' => 'user_plain',
				'realname' => 'Just a plain user',
				'email' => '',
				'authorized' => array (
					'own_user_change_password' => true,
					'some_plain_access' => true
				),
				'groups' => array (
					'plain_group'
				)
			), $auth->getActorInfo());

		/* Session shouldn't be emptied */
		$auth->write_close();
		$auth->logout();

		$this->assertEquals(
			array (
				'testkey' => array (
					'username' => 'user_plain',
					'realname' => 'Just a plain user',
					'email' => '',
					'auth_data' => array (
						'own_user_change_password' => true,
						'some_plain_access' => true
					),
					'modules' => array (
						'mydefault'
					),
					'auth_driver' => '',
					'password_algo' => 'plain',
					'auth_method' => 'mydefault',
					'groups' => array (
						'plain_group'
					)
				),
				'this_should_be_untouched' => 17
			), $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => false,
				'username' => 'notauthenticated',
				'realname' => 'Not Logged in',
				'email' => '',
				'authorized' => array (),
				'groups' => array ()
			), $auth->getActorInfo());
	}

	/**
	 * Test a one-shot login cycle, by closing the session before logging in and
	 * out.
	 */
	public function test_close_login_logout() {
		$auth = new op5auth();

		$this->assertEquals(array ('this_should_be_untouched' => 17), $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => false,
				'username' => 'notauthenticated',
				'realname' => 'Not Logged in',
				'email' => '',
				'authorized' => array (),
				'groups' => array (),
			), $auth->getActorInfo());

		/* Session shouldn't be written during login, but user is logged in */
		$auth->write_close();
		$auth->login('user_plain', 'user_pass');

		$this->assertEquals(array ('this_should_be_untouched' => 17), $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => true,
				'username' => 'user_plain',
				'realname' => 'Just a plain user',
				'email' => '',
				'authorized' => array (
					'own_user_change_password' => true,
					'some_plain_access' => true
				),
				'groups' => array (
					'plain_group'
				)
			), $auth->getActorInfo());

		/* Session should still be emptied */
		$auth->logout();

		$this->assertEquals(array ('this_should_be_untouched' => 17), $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => false,
				'username' => 'notauthenticated',
				'realname' => 'Not Logged in',
				'email' => '',
				'authorized' => array (),
				'groups' => array ()
			), $auth->getActorInfo());
	}

	/**
	 * Test restoring the user from the session, preserving a logged in user.
	 */
	public function test_user_load() {
		/* Mock up a user */
		$_SESSION = array (
			'testkey' => array (
				'username' => 'from_session_user',
				'realname' => 'A user from the session',
				'email' => 'i_have@an.email',
				'auth_data' => array (
					'i_can_do_stuff' => true,
					'some_advanced_access' => true
				),
				'modules' => array (
					'mydefault'
				),
				'auth_method' => 'mydefault',
				'groups' => array (
					'advanced_group_stuff'
				)
			),
			'this_should_be_untouched' => 17
		);

		$original_session = $_SESSION;

		/* User should be loaded, session untouched */
		$auth = new op5auth();

		$this->assertEquals($original_session, $_SESSION);
		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => true,
				'username' => 'from_session_user',
				'realname' => 'A user from the session',
				'email' => 'i_have@an.email',
				'authorized' => array (
					'i_can_do_stuff' => true,
					'some_advanced_access' => true
				),
				'groups' => array (
					'advanced_group_stuff'
				)
			), $auth->getActorInfo());
	}

	/**
	 * The get_metadata interface of auth sucks, but at least
	 * test that it sucks the same between changes.
	 */
	public function test_get_metadata_all() {
		$result = op5auth::instance()->get_metadata();

		$this->assertArrayHasKey('require_user_configuration', $result);
		$this->assertArrayHasKey('require_user_password_configuration', $result);
		$this->assertArrayHasKey('login_screen_dropdown', $result);

		$this->assertEquals(array("mydefault"),$result['require_user_configuration']);
		$this->assertEquals(array("mydefault"),$result['require_user_password_configuration']);
		$this->assertEquals(array("mydefault"),$result['login_screen_dropdown']);

	}

	public function test_get_metadata_specific() {
		$result = op5auth::instance()->get_metadata('require_user_password_configuration');
		$this->assertEquals(array("mydefault"), $result);
	}

	public function test_get_metadata_when_not_set() {
		$result = op5auth::instance()->get_metadata('the_module_flurpbar_setting');
		$this->assertFalse($result);
	}

	/**
	 * Test forcing a user, which means bypassing the auth mechanism.
	 * It should still write to the session
	 */
	public function test_force_user_update_session() {
		/* Mock up a user */
		$_SESSION = array (
			'testkey' => array (
				'username' => 'from_session_user',
				'realname' => 'A user from the session',
				'email' => 'i_have@an.email',
				'auth_data' => array (
					'i_can_do_stuff' => true,
					'some_advanced_access' => true
				),
				'modules' => array (
					'mydefault'
				),
				'auth_method' => 'mydefault',
				'groups' => array (
					'advanced_group_stuff'
				)
			),
			'this_should_be_untouched' => 17
		);

		/* User should be loaded */
		$auth = new op5auth();

		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => true,
				'username' => 'from_session_user',
				'realname' => 'A user from the session',
				'email' => 'i_have@an.email',
				'authorized' => array (
					'i_can_do_stuff' => true,
					'some_advanced_access' => true
				),
				'groups' => array (
					'advanced_group_stuff'
				)
			), $auth->getActorInfo());

		$auth->force_user(
			new User_Model(
				array (
					'username' => 'a_forced_user',
					'realname' => 'This is forced',
					'email' => 'i_do_have@n.email',
					'auth_data' => array (
						'i_can_do_other_stuff' => true
					),
					'groups' => array (
						'somestuff'
					)
				)), false);

		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => true,
				'username' => 'a_forced_user',
				'realname' => 'This is forced',
				'email' => 'i_do_have@n.email',
				'authorized' => array (
					'i_can_do_other_stuff' => true
				),
				'groups' => array (
					'somestuff'
				)
			), $auth->getActorInfo());

		/* Session should be untouched */
		$this->assertEquals(
			array (
				'testkey' => array (
					'username' => 'a_forced_user',
					'realname' => 'This is forced',
					'email' => 'i_do_have@n.email',
					'auth_data' => array (
						'i_can_do_other_stuff' => true
					),
					'groups' => array (
						'somestuff'
					),
					'modules' => array(),
					'auth_method' => ''
				),
				'this_should_be_untouched' => 17
			), $_SESSION);
	}

	/**
	 * Test forcing a user, after closing the session.
	 * The session should be totally untouched, but a new temporary user should
	 * be used
	 */
	public function test_close_force_user_keep_session() {
		/* Mock up a user */
		$_SESSION = array (
			'testkey' => array (
				'username' => 'from_session_user',
				'realname' => 'A user from the session',
				'email' => 'i_have@an.email',
				'auth_data' => array (
					'i_can_do_stuff' => true,
					'some_advanced_access' => true
				),
				'modules' => array (
					'mydefault'
				),
				'auth_method' => 'mydefault',
				'groups' => array (
					'advanced_group_stuff'
				)
			),
			'this_should_be_untouched' => 17
		);

		$original_session = $_SESSION;

		/* User should be loaded */
		$auth = new op5auth();

		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => true,
				'username' => 'from_session_user',
				'realname' => 'A user from the session',
				'email' => 'i_have@an.email',
				'authorized' => array (
					'i_can_do_stuff' => true,
					'some_advanced_access' => true
				),
				'groups' => array (
					'advanced_group_stuff'
				)
			), $auth->getActorInfo());

		$auth->write_close();
		$auth->force_user(
			new User_Model(
				array (
					'username' => 'a_forced_user',
					'realname' => 'This is forced',
					'email' => 'i_do_have@n.email',
					'auth_data' => array (
						'i_can_do_other_stuff' => true
					),
					'groups' => array (
						'somestuff'
					)
				)), false);

		$this->assertEquals(
			array (
				'type' => 'user',
				'authenticated' => true,
				'username' => 'a_forced_user',
				'realname' => 'This is forced',
				'email' => 'i_do_have@n.email',
				'authorized' => array (
					'i_can_do_other_stuff' => true
				),
				'groups' => array (
					'somestuff'
				)
			), $auth->getActorInfo());

		/* Session should be untouched */
		$this->assertEquals($original_session, $_SESSION);
	}

	/**
	 * @group MON-9199
	 */
	function test_user_model_compatible_with_removed_op5user() {
		$expected_username = 'Honkytonk';

		$this->_login_fixture($expected_username);

		// this used to return an op5user instance
		$old_syntax = op5auth::instance()->get_user()->username;
		// this returns a User_Model instance
		$new_syntax = op5auth::instance()->get_user()->get_username();

		$this->assertSame($expected_username, $old_syntax);
		$this->assertSame($expected_username, $new_syntax);
	}

	/**
	 * @group MON-9199
	 */
	function test_user_model_fails_if_deprecation_is_not_wanted() {
		// now we just test that our dev environment actually has the
		// possibility to die if we don't want to tolerate deprecations
		// when working with Ninja
		$this->assertSame(true, putenv(self::DEPRECATION_ENV_VAR.'=1'));

		try {
			op5auth::instance()->get_user()->username;
		} catch(DeprecationException $e) {
			$expected = "DEPRECATION: 'User_Model::__get' is deprecated and should not be executed: Backwards-compatibility after op5user => User_Model";
			$this->assertSame($expected, $e->getMessage());
			return;
		}
		$this->assertTrue(false, "This code path should not be reached, expected an exception to be thrown");
	}

	/**
	 * @group MON-9199
	 */
	function test_user_model_get_property_backwards_compatible() {
		$user = new User_Model();
		$new_username = 'MasterBlaster';
		$user->set_username($new_username);
		$this->assertSame($new_username, $user->get_username());
		$this->assertSame($new_username, $user->username);
	}

	/**
	 * @group MON-9199
	 */
	function test_user_model_set_property_backwards_compatible() {
		$user = new User_Model();
		$user->username = 'Mister Big';
		$this->assertSame('Mister Big', $user->get_username());
		$this->assertSame('Mister Big', $user->username);
	}

	/**
	 * @group MON-9199
	 */
	function test_user_model_isset_property_backwards_compatible() {
		$user = new User_Model();
		$user->username = 'Mister Big';
		$this->assertSame(true, isset($user->username));
		$this->assertSame(false, isset($user->catname));
		$this->assertSame(true, isset($user->export), "Sadly, we must expose privately used variables too, through __isset() :(");
	}

	/**
	 * @group MON-9199
	 */
	function test_user_model_set_property_that_exists_as_non_public_interface() {
		// reusing the old interface by attempting to set arbitrary
		// strings that might collide with existing properties in
		// User_Model
		$user = new User_Model();
		$user->hello = 35;
		$this->assertSame(35, $user->hello);

		$user->custom_properties = 64;
		$this->assertSame(64, $user->custom_properties, 'Setting a public variable');

		$this->assertSame(35, $user->hello, 'Make sure the internal $custom_properties variable was not altered');
	}

	/**
	 * Needed for avoiding global debug-dying-state.. I painted myself into
	 * a corner: of course we want to have a global deprecation flag on
	 * while testing Ninja, but for testing the flags themselves, we cannot
	 * rely on global state.
	 *
	 * Needed for @group MON-9199
	 */
	static public function setUpBeforeClass() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->mock_add('op5config', new MockConfig(self::$config));

		$ninja_dir = __DIR__.'/../../..';
		assert(basename(realpath($ninja_dir)) === "ninja");
		self::$global_flag_config = $ninja_dir.'/application/config/custom/flag.php';
		if(file_exists(self::$global_flag_config)) {
			assert(rename(self::$global_flag_config, self::STASHED_CONFIG_FILE_TEMPORARY_NAME));
		}
	}

	public static function tearDownAfterClass() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();

		if(file_exists(self::STASHED_CONFIG_FILE_TEMPORARY_NAME)) {
			assert(rename(self::STASHED_CONFIG_FILE_TEMPORARY_NAME, self::$global_flag_config));
		}
	}

	/**
	 * Needed for @group MON-9199
	 */
	function _login_fixture($username) {
		$mock_config = array(
			'auth' => array (
				'common' => array (
					'default_auth' => 'mydefault',
					'session_key' => 'testkey'
				),
				'mydefault' => array (
					'driver' => 'Default'
				)
			),
			'auth_users' => array (
				$username => array (
					'username' => $username,
					'realname' => $username,
					'password' => 'man',
					'password_algo' => 'plain',
					'modules' => array (
						'mydefault'
					),
					'groups' => array (
						'plain_group'
					)
				)
			),
			'auth_groups' => array (
				'plain_group' => array (
					'some_plain_access'
				)
			)
		);
		op5objstore::instance()->mock_add('op5config', new MockConfig($mock_config));
		$op5auth = op5auth::instance();
		$login_result = $op5auth->login($username, 'man');
		$this->assertSame(true, $login_result, 'We should be able to login');
	}
}
