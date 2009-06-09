<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle page data - saving and fetching
 */
class Ninja_setting_Model extends ORM
{
	/**
	*	Save page setting for a user
	* 	@param str $type [widget_order, widget, etc]
	* 	@param str $page
	* 	@param mixed $value
	*/
	public function save_page_setting($type='widget_order', $page=false, $value=false)
	{
		$type = trim($type);
		$page = trim($page);
		$value = trim($value);
		if (empty($type) || empty($value))
			return false;

		$setting = ORM::factory('ninja_setting');
		$user = Auth::instance()->get_user()->username;

		# does this setting exist? (i.e should we do insert or update)
		# by just trying to load setting for current user, page and type
		# we will get an object that will be updated or inserted when calling
		# save() below
		$setting->where(array('user'=> $user, 'page' => $page, 'type' => $type))->find();

		if (empty($user))
			return false; # saving global widget settings not allowed by this return

		$setting->user = $user;
		if (!empty($page))
			$setting->page = $page;

		$setting->type = $type;
		$setting->setting = $value;
		$setting->save();
	}

	/**
	*	Fetch page setting for a user
	* 	@param str $type [widget_order, widget, etc]
	* 	@param str $page
	* 	@param mixed $value
	*/
	public function fetch_page_setting($type='widget_order', $page=false)
	{
		$type = trim($type);
		$page = trim($page);
		if (empty($type))
			return false;

		$setting = ORM::factory('ninja_setting');
		$user = Auth::instance()->get_user()->username;
		$limit = 1; # should only be one so no use fetching more
		$offset = 0;
		$setting->where(array('user'=> $user, 'page' => $page, 'type' => $type))->find($limit, $offset);
		return $setting->loaded ? $setting : false;
	}

}