<?php

require_once "op5/auth/AuthDriver_LDAP.php";
require_once "op5/objstore.php";

class AuthDriverLDAPTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var op5AuthDriver
	 */
	protected $drv = false;

	/**
	 * Default configuration, can be overridden in each connect
	 * @var array
	 */
	protected $config = array(
			/* Should be tested in generic AuthDriver tests */
			'name' => 'Test driver',

			/* Not tested yet */
			'server' => '127.0.0.1',
			'port' => 13389,
			'encryption' => false,

			/* test_service_account */
			/* test_service_account_invalid_password */
			/* test_service_account_invalid_user */
			'bind_dn' => false,
			'bind_secret' => false,

			/* Not used, can't be tested */
			'base_dn' => 'o=op5test',

			/* test_group_base_dn */
			'group_base_dn' => 'o=op5test',

			/* test_user_base_dn */
			'user_base_dn' => 'o=op5test',

			/* test_user_filter */
			/* test_user_filter_malformed */
			'user_filter' => '(objectClass=inetOrgPerson)',

			/* test_group_filter */
			/* test_group_filter_malformed */
			'group_filter' => '(objectClass=groupOfNames)',

			/* test_groupkey_incorrect */
			'groupkey' => 'cN',

			/* test_group_recursion */
			'group_recursive' => false,

			/* test_attribute_resolution */
			'userkey' => 'uID',
			'userkey_realname' => 'cN',
			'userkey_email' => 'mAIL',
			'memberkey' => 'mEMBER',

			/* Not tested: Can't be tested without Active Directory */
			'upn_suffix' => '',
			'userkey_is_upn' => false,
			'bind_with_upn' => false,

			/* Not tested */
			'memberkey_is_dn' => true,
			'ldap_options' => array(
					'LDAP_OPT_PROTOCOL_VERSION' => 3
			)
	);

	/**
	 * Setup mock LDAP enviornment
	*/
	public static function setUpBeforeClass() {
		system( __DIR__. '/env/ldap/slapd_mock_start.sh' );
		op5objstore::instance()->mock_add( 'op5log', new MockLog() );
	}

	/**
	 * Shut down mock LDAP environment
	 */
	public static function tearDownAfterClass() {
		system( __DIR__. '/env/ldap/slapd_mock_stop.sh' );
		op5objstore::instance()->clear();
		op5objstore::instance()->mock_clear();
	}

	/**
	 * Make sure we're disconnected from LDAP
	 */
	function setUp() {
		if( $this->drv !== false ) {
			$this->drv->disconnect();
		}
		$this->drv = false;
	}

	/**
	 * Make sure we're disconnected from LDAP
	 */
	function tearDown() {
		if( $this->drv !== false ) {
			$this->drv->disconnect();
		}
		$this->drv = false;
	}

	/**
	 * (Re)connect to the LDAP server. Takes settings to override as argument
	 * @param array $extra_config
	 */
	function connect($extra_config = array()) {
		$this->drv = new op5AuthDriver_LDAP(
				array_merge(
						$this->config,
						$extra_config
				)
		);
	}


	/*******************************************
	 * START OF TESTS
	*******************************************/
	function test_group_membership() {
		$this->connect();
		$user = $this->drv->login('nogroup', 'nogrouppassword');
		$this->assertTrue($user !== false, "No user object returned, couldn't login as nogroup");
		$this->assertEquals( $user->groups, array(),
				'Incorrect groups returned for user nogroup');

		$user = $this->drv->login('singlegroup', 'singlegrouppassword');
		$this->assertTrue($user !== false, "No user object returned, couldn't login as singlegroup");
		$this->assertEquals( $user->groups, array('Depth 1 of 1'),
				'Incorrect groups returned for user singlegroup');

		$user = $this->drv->login('multigroup', 'multigrouppassword');
		$this->assertTrue($user !== false, "No user object returned, couldn't login as multigroup");
		$this->assertEquals( $user->groups, array('Depth 1 of 1', 'Depth 1 of 1 Nr 2'),
				'Incorrect groups returned for user multigroup');
	}

	function test_group_recursion() {
		$this->connect(array('group_recursive' => false));
		$user = $this->drv->login('nestedgroup', 'nestedgrouppassword');
		$this->assertTrue($user !== false, "No user object returned, couldn't login as nestedgroup");
		$this->assertEquals( $user->groups, array('Depth 1 of 2'),
				'Incorrect groups returned for user nestedgroup');

		$this->connect(array('group_recursive' => true));
		$user = $this->drv->login('nestedgroup', 'nestedgrouppassword');
		$this->assertTrue($user !== false, "No user object returned, couldn't login as nestedgroup");
		$this->assertEquals( $user->groups, array('Depth 1 of 2', 'Depth 2 of 2'),
				'Incorrect groups returned for user nestedgroup');
	}

	function test_attribute_resolution() {
		$this->connect();
		$user = $this->drv->login('nogroup', 'nogrouppassword');
		$this->assertEquals( $user->username, 'nogroup', 'Invalid username for user');
		$this->assertEquals( $user->realname, 'No Group User', 'Invalid realname for user');
		$this->assertEquals( $user->email, 'nogroup@op5test.op5.com', 'Invalid mail for user');
	}

	function test_invalid_password() {
		$this->connect();
		$user = $this->drv->login('nogroup', 'incorrect password');
		$this->assertEquals( $user, false, 'User returned with incorrect password');
	}

	function test_invalid_username() {
		$this->connect();
		$user = $this->drv->login('incorrectuser', 'incorrect password');
		$this->assertEquals( $user, false, 'User returned with incorrect password');
	}

	function test_service_account() {
		$this->connect(array(
				'bind_dn' => 'cn=No Group User,ou=Users,o=op5test',
				'bind_secret' => 'nogrouppassword',
		));
		$user = $this->drv->login('nogroup', 'nogrouppassword');
		$this->assertTrue($user !== false, "Could not login when service account is used");
	}

	function test_service_account_invalid_password() {
		try {
			$this->connect(array(
					'bind_dn' => 'cn=No Group User,ou=Users,o=op5test',
					'bind_secret' => 'incorrect password',
			));
			$user = $this->drv->login('nogroup', 'nogrouppassword');
			$this->fail("login passed with invalid service account password, but exeption should have been thrown");
		} catch( op5AuthException $e ) {
			$this->assertEquals(
					'op5AuthDriver_LDAP / Test driver: Could not bind using config user to LDAP server (49: Invalid credentials)',
					$e->getMessage(),
					'Incorrect exception message');
		}
	}

	function test_service_account_invalid_user() {
		try {
			$this->connect(array(
					'bind_dn' => 'cn=Nonexisting User,ou=Users,o=op5test',
					'bind_secret' => 'incorrect password',
			));
			$user = $this->drv->login('nogroup', 'nogrouppassword');
			$this->fail("login passed with invalid service account user, but exeption should have been thrown");
		} catch( op5AuthException $e ) {
			$this->assertEquals(
					'op5AuthDriver_LDAP / Test driver: Could not bind using config user to LDAP server (49: Invalid credentials)',
					$e->getMessage(),
					'Incorrect exception message');
		}
	}

	function test_group_base_dn() {
		$this->connect(array('group_base_dn' => 'ou=nonexisting,o=op5test'));
		try {
			$user = $this->drv->login('singlegroup', 'singlegrouppassword');
			$this->fail('Login should fail with exception, nonexisting group_base_dn');
		} catch( op5AuthException $e ) {
			$this->assertTrue(
					0!=preg_match(':^op5AuthDriver_LDAP / Test driver\\: Error during LDAP search using query:', $e->getMessage()),
					'Incorrect exception message');
		}
	}

	function test_user_base_dn() {
		$this->connect(array('user_base_dn' => 'ou=nonexisting,o=op5test'));
		try {
			$user = $this->drv->login('singlegroup', 'singlegrouppassword');
			$this->fail('Login should fail with exception, nonexisting group_base_dn');
		} catch( op5AuthException $e ) {
			$this->assertTrue(
					0!=preg_match(':^op5AuthDriver_LDAP / Test driver\\: Error during LDAP search using query:', $e->getMessage()),
					'Incorrect exception message');
		}
	}

	function test_user_filter() {
		$this->connect(array('user_filter' => '(objectClass=incorrect)'));

		$user = $this->drv->login('singlegroup', 'singlegrouppassword');
		$this->assertTrue($user === false, "User returned, but user_filter shouldn't match anything");
	}

	function test_user_filter_malformed() {
		$this->connect(array('user_filter' => 'malformed('));

		try {
			$user = $this->drv->login('singlegroup', 'singlegrouppassword');
			$this->assertTrue($user === false, "User returned, but user_filter shouldn't match anything");
		} catch( op5AuthException $e ) {
			$this->assertEquals(
					'op5AuthDriver_LDAP / Test driver: Error during LDAP search using query "(&(&(uID=singlegroup))malformed()" at "o=op5test" (-7: Bad search filter)',
						$e->getMessage(),
					'Incorrect exception message');
		}
	}

	function test_group_filter() {
		$this->connect(array('group_filter' => '(objectClass=incorrect)'));

		$user = $this->drv->login('singlegroup', 'singlegrouppassword');
		$this->assertTrue($user !== false, "No user object returned, couldn't login as singlegroup");
		$this->assertEquals( $user->groups, array(),
				'Groups returned for user singlegroup, but group_filter is incorrect');
	}

	function test_group_filter_malformed() {
		$this->connect(array('group_filter' => 'malformed('));

		try {
			$user = $this->drv->login('singlegroup', 'singlegrouppassword');
			$this->assertTrue($user === false, "User returned, but user_filter shouldn't match anything");
		} catch( op5AuthException $e ) {
			$this->assertEquals(
					'op5AuthDriver_LDAP / Test driver: Error during LDAP search using query "(&(&(mEMBER=cn=Single Group User,ou=Users,o=op5test))malformed()" at "o=op5test" (-7: Bad search filter)',
					$e->getMessage(),
					'Incorrect exception message');
		}
	}

	function test_groups_available() {
		$this->connect();
		$available = $this->drv->groups_available(
				array(
						'Depth 1 of 2',
						'Depth 2 of 2',
						'nonexisting'
				)
		);
		$this->assertEquals($available,
				array (
						'Depth 1 of 2' => true,
						'Depth 2 of 2' => true,
						'nonexisting' => false,
				),
				'Available groups doesn\'t match'
		);
	}

	function test_groupkey_incorrect() {
		$this->connect(array('groupkey' => 'incorrect'));
		$available = $this->drv->groups_available(
				array(
						'Depth 1 of 2',
						'Depth 2 of 2',
						'nonexisting'
				)
		);
		$this->assertEquals($available,
				array (
						'Depth 1 of 2' => false,
						'Depth 2 of 2' => false,
						'nonexisting' => false,
				),
				'Available groups doesn\'t match'
		);
	}
}
