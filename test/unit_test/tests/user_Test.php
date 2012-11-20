<?php defined('SYSPATH') OR die('No direct access allowed.');
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
class User_Test extends TapUnit {
	public function test_table_ninja_settings_exists()
	{
		$db = Database::instance();
		$table = 'ninja_settings';
		$this->ok($db->table_exists($table), "Unable to find table $table");
	}

	public function disabled_test_ldap_case_sensitivity() /* TODO */
	{
		$ldapauth = Auth::factory( array( 'driver' => 'LDAP' ) );
		
		$this->ok( is_object( $ldapauth ), "Could not create LDAP Auth module" );
		$this->ok( $ldapauth->driver instanceof Auth_LDAP_Driver, "Auth is not an Auth_LDAP_Driver. Auth is a ".get_class( $ldapauth->driver ) );
		
		$this->ok( $ldapauth->driver->login( 'test2', 'losen', false ), "Could not authenticate with LDAP user, correct case." );
		$user1 = $ldapauth->get_user();
		$this->ok( $user1->username == 'test2', "Returned username isn't the same as database username with, using correct case for login" );
		
		$this->ok( $ldapauth->driver->login( 'TEst2', 'losen', false ), "Could not authenticate with LDAP user, incorrect case." );
		$user2 = $ldapauth->get_user();
		$this->ok( $user2->username == 'test2', "Returned username isn't the same as database username with, using incorrect case for login" );
		
		$this->ok( $user1->id == $user2->id, "Same username with different case returns different user id:s" );
		
		unset( $ldapauth );
	}

	public function test_menu_icons_exists()
	{
		$menu_path = APPPATH.'views/themes/default/icons/menu/*.png';
		$menu_path_dark = APPPATH.'views/themes/default/icons/menu-dark/*.png';
		$menu = glob($menu_path);
		$menu_dark = glob($menu_path_dark);
		$missing = false;
		$missing_str = false;
		if ((sizeof($menu) != sizeof($menu_dark))) {
			$menu_icons = false;
			foreach ($menu as $icon_path) {
				$parts = explode('/', $icon_path);
				$icon_name = false;
				if (is_array($parts)) {
					$icon_name = array_pop($parts);
					$menu_icons[] = $icon_name;
				}
			}

			$menu_dark_icons = false;
			foreach ($menu_dark as $icon_path) {
				$parts = explode('/', $icon_path);
				$icon_name = false;
				if (is_array($parts)) {
					$icon_name = array_pop($parts);
					$menu_dark_icons[] = $icon_name;
				}
			}

			if (is_array($menu_dark_icons)) {
				foreach ($menu_icons as $icon) {
					if (!in_array($icon, $menu_dark_icons)) {
						$missing[] = $icon;
					}
				}
			}
			$missing_str = (!empty($missing) && is_array($missing) ) ? implode(', ', $missing) : '';
		}
		$this->ok((sizeof($menu) == sizeof($menu_dark)), 'Missing dark icons: '. $missing_str, TAP_TODO);
	}

}
