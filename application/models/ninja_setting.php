<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle page data - saving and fetching
 */
class Ninja_setting_Model extends ORM
{
	const USERFIELD = 'user';
	/**
	 * Save page setting for a user
	 *
	 * @param $type string: {widget_order, widget, etc...}
	 * @param $page string: The page we're looking at.
	 * @param $value mixed: The value to set.
	 * @return False on error. True on success.
	 */
	public function save_page_setting($type='widget_order', $page=false, $value=false)
	{
		$type = trim($type);
		$page = trim($page);
		$value = trim($value);
		if (empty($type))
			return false;

		$setting = ORM::factory('ninja_setting');
		$user = Auth::instance()->get_user()->username;

		# does this setting exist? (i.e should we do insert or update)
		# by just trying to load setting for current user, page and type
		# we will get an object that will be updated or inserted when calling
		# save() below
		$setting->where(array(self::USERFIELD => $user, 'page' => $page, 'type' => $type))->find();

		if (empty($user))
			return false; # saving global widget settings not allowed by this return

		$setting->{self::USERFIELD} = $user;
		if (!empty($page))
			$setting->page = $page;

		$setting->type = $type;
		$setting->setting = $value;
		$setting->save();
		return true;
	}

	/**
	 * Fetch page setting for a user. Assumes only one value is returned.
	 *
	 * @param $type string: {widget_order, widget, etc...}
	 * @param $page string: The page we're looking at.
	 * @param $default bool: Request default or not.
	 */
	public function fetch_page_setting($type='widget_order', $page=false, $default=false)
	{
		$type = trim($type);
		$page = trim($page);
		if (empty($type))
			return false;

		$setting = ORM::factory('ninja_setting');
		$user = Auth::instance()->get_user()->username;
		if ($default === true) {
			# We have a request for default value
			$setting->where(array(self::USERFIELD => '', 'page' => $page, 'type' => $type))->find();
		} else {
			# first, try user setting
			$setting->where(array(self::USERFIELD => $user, 'page' => $page, 'type' => $type))->find();
			if (!$setting->loaded) {
				# try default if nothing found
				$setting->where(array(self::USERFIELD => '', 'page' => $page, 'type' => $type))->find();
			}
		}
		return $setting->loaded ? $setting : false;
	}
}
