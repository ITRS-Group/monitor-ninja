<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Represents a user's, perhaps partial, view of the complete menu
 */
class Menu_Model extends Model
{

	/**
	 * @todo previously create_menu, refactor user_controllers' stuff into here as well
	 * Build menu structure and possibly remove some items
	 *
	 * @param $theme_path string
	 * @return array
	 */
	function create($theme_path)
	{
		include(APPPATH.'views/'.$theme_path.'menu/menu.php');
		$logged_in_users_groups = Op5Auth::factory()->get_groups();
		$ninja_menu = Op5Config::instance()->getConfig('ninja_menu');
		foreach(array_intersect($logged_in_users_groups, array_keys((array) $ninja_menu)) as $section) {
			$this->remove_menu_items($menu_base, $menu_items, $ninja_menu[$section]);
		}
		return $menu_base;
	}

	/**
	 * Remove menu item by index
	 * Both section string ['about', 'monitoring', etc]
	 * and item string ['portal', 'manual', 'support', etc] are required.
	 * As a consequence, all menu items has to be explicitly removed before removing the section
	 */
	private function remove_menu_items(&$menu_links=false, &$menu_items=false, $section_str=false, $item_str=false)
	{
		if (empty($menu_links) || empty($menu_items) || empty($section_str)) {
			return false;
		}

		if (is_array($section_str)) {
			# we have to make recursive calls
			foreach ($section_str as $section => $items) {
				foreach ($items as $item) {
					$this->remove_menu_items($menu_links, $menu_items, $section, $item);
				}
			}
		} else {
			if (empty($item_str) && isset($menu_links[$menu_items['section_'.$section_str]])
				&& empty($menu_links[$menu_items['section_'.$section_str]])) {
				# remove the section
				unset($menu_links[$menu_items['section_'.$section_str]]);
			} elseif (!empty($item_str) && isset($menu_items['section_'.$section_str]) && isset($menu_links[$menu_items['section_'.$section_str]]) && isset($menu_items[$item_str]) && isset($menu_links[$menu_items['section_'.$section_str]][$menu_items[$item_str]])) {
				unset($menu_links[$menu_items['section_'.$section_str]][$menu_items[$item_str]]);
			}
		}
	}
}
