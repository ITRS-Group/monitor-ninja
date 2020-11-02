<?php
/**
 * Example Test.
 *
 * $Id$
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class User_Test extends PHPUnit_Framework_TestCase {
	public function test_table_ninja_settings_exists()
	{
		$db = Database::instance();
		$table = 'ninja_settings';
		$this->assertTrue($db->table_exists($table), "Unable to find table $table");
	}

	public function disabled_test_ldap_case_sensitivity() /* TODO */
	{
		$ldapauth = Auth::factory( array( 'driver' => 'LDAP' ) );

		$this->assertTrue( is_object( $ldapauth ), "Could not create LDAP Auth module" );
		$this->assertTrue( $ldapauth->driver instanceof Auth_LDAP_Driver, "Auth is not an Auth_LDAP_Driver. Auth is a ".get_class( $ldapauth->driver ) );

		$this->assertTrue( $ldapauth->driver->login( 'test2', 'losen', false ), "Could not authenticate with LDAP user, correct case." );
		$user1 = $ldapauth->get_user();
		$this->assertTrue( $user1->get_username() == 'test2', "Returned username isn't the same as database username with, using correct case for login" );

		$this->assertTrue( $ldapauth->driver->login( 'TEst2', 'losen', false ), "Could not authenticate with LDAP user, incorrect case." );
		$user2 = $ldapauth->get_user();
		$this->assertTrue( $user2->get_username() == 'test2', "Returned username isn't the same as database username with, using incorrect case for login" );

		$this->assertTrue( $user1->id == $user2->id, "Same username with different case returns different user id:s" );

		unset( $ldapauth );
	}
}
