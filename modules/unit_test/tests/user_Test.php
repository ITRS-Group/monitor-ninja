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
	public function test_user_model_exists()
	{
		$user = new User_Model();
		$this->ok(is_object($user), "User model exists");
		unset($user);
	}

	public function test_user_model_complete_login_exists()
	{
		$user = new User_Model();
		$this->ok(method_exists($user, 'complete_login'), "Complete login method exists");
		unset($user);
	}

	public function test_table_users_exists()
	{
		$db = Database::instance();
		$table = 'users';
		$this->ok($db->table_exists($table), "Unable to find table $table");
		$this->ok(!$db->table_exists('foo'), "Random tables doesn't exist");
	}

	public function test_create_user()
	{
		User_Model::add_user(array('username' => 'monitor'));
		Ninja_user_authorization_Model::insert_user_auth_data('monitor', array('system_information'=>1));
	}

	public function test_users_exists()
	{
		$db = Database::instance();
		$table = 'users';
		$sql = "SELECT COUNT(*) as cnt FROM users";
		$result = $db->query($sql);

		$this->ok(count($result) > 0, "There are users");
	}

	public function test_table_ninja_settings_exists()
	{
		$db = Database::instance();
		$table = 'ninja_settings';
		$this->ok($db->table_exists($table), "Unable to find table $table");
	}

	/**
	 * Check that we have the ninja_user_authorization table
	 */
	public function test_table_ninja_user_authorization_exists()
	{
		$db = Database::instance();
		$table = 'ninja_user_authorization';
		$this->ok($db->table_exists($table), "Unable to find table $table");
	}

	public function test_table_ninja_user_authorization()
	{
		$db = Database::instance();
		$table = 'ninja_user_authorization';
		#$this->assert_true_strict($db->table_exists($table), "Unable to find table $table");
		$sql = "SELECT * FROM ".$table;
		$result = $db->query($sql);
		$this->ok(count($result) > 0, 'No data exists for '.$table);
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
