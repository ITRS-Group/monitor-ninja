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
class User_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public function user_model_exists_test()
	{
		$user = new User_Model();
		$this->assert_true_strict(is_object($user));
		unset($user);
	}

	public function user_model_complete_login_exists_test()
	{
		$user = new User_Model();
		$this->assert_true_strict(method_exists($user, 'complete_login'));
		unset($user);
	}

	public function table_users_exists_test()
	{
		$db = new Database();
		$table = 'users';
		$this->assert_true_strict($db->table_exists($table), "Unable to find table $table");
		$this->assert_false_strict($db->table_exists('foo'));
	}

	public function users_exists_test()
	{
		$db = new Database();
		$table = 'users';
		$sql = "SELECT COUNT(*) as cnt FROM users";
		$result = $db->query($sql);

		$this->assert_true(count($result));
	}

	public function table_ninja_settings_exists_test()
	{
		$db = new Database();
		$table = 'ninja_settings';
		$this->assert_true_strict($db->table_exists($table), "Unable to find table $table");
	}

	/**
	 * Check that we have the ninja_user_authorization table
	 */
	public function table_ninja_user_authorization_exists_test()
	{
		$db = new Database();
		$table = 'ninja_user_authorization';
		$this->assert_true_strict($db->table_exists($table), "Unable to find table $table");
	}

	public function table_ninja_user_authorization_test()
	{
		$db = new Database();
		$table = 'ninja_user_authorization';
		#$this->assert_true_strict($db->table_exists($table), "Unable to find table $table");
		$sql = "SELECT * FROM ".$table;
		$result = $db->query($sql);
		$this->assert_true(count($result), 'No data exists for '.$table);
	}

	public function menu_icons_exists_test()
	{
		$menu_path = APPPATH.'views/themes/default/icons/menu/*.png';
		$menu_path_dark = APPPATH.'views/themes/default/icons/menu-dark/*.png';
		$menu = glob($menu_path);
		$menu_dark = glob($menu_path_dark);
		$missing = false;
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
		}
		$this->assert_true((sizeof($menu) == sizeof($menu_dark)), 'Missing: '.implode(', ', $missing));
	}

}