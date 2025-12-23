<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle page data - saving and fetching
 */
class Ninja_setting_Model extends Model {

	private static function fetch_default_setting ($type, $page = false) {

		$set = SettingPool_Model::all()
			->reduce_by('username', '', '=')
			->reduce_by('type', $type, '=')
			->reduce_by('page', $page, '=');

		return (count($set)) ? (object) $set->one()->export() : false;

	}

	/**
	 * Save page setting for a user
	 *
	 * @param $type string: {widget_order, widget, etc...}
	 * @param $page string: The page we're looking at.
	 * @param $value mixed: The value to set.
	 * @param $username string: Username if not current user
	 * @return False on error. True on success.
	 */
	public static function save_page_setting($type, $page=false, $value=false, $username=false)
	{
		$type = trim($type);
		$page = trim($page);
		$value = trim($value);
		$username = empty($username) ? op5Auth::instance()->get_user()->get_username() : $username;

		if (empty($type)) return false;
		$set = SettingPool_Model::all()
			->reduce_by('username', $username, '=')
			->reduce_by('type', $type, '=')
			->reduce_by('page', $page, '=');

		/* Setting already exists, update it */
		if (count($set)) {
			$setting = $set->one();
			$setting->set_setting($value);
			$setting->save();
		} else {
			$setting = new Setting_Model();
			$setting->set_username($username);
			$setting->set_type($type);
			$setting->set_page($page);
			$setting->set_setting($value);
			$setting->save();
		}

		return true;

	}

	/**
	 * Fetch page setting for a user. Assumes only one value is returned.
	 *
	 * @param $type string: {widget_order, widget, etc...}
	 * @param $page string: The page we're looking at.
	 * @param $default bool: Request default or not.
	 */
	public static function fetch_page_setting($type, $page=false, $default=false)
	{

		$type = trim($type);
		if (!$page)
			$page = '*';
		$page = trim($page);

		if (empty($type)) return false;

		if ($default) {
			return self::fetch_default_setting($type, $page);
		}

		$username = op5Auth::instance()->get_user()->get_username();
		$set = SettingPool_Model::all()
			->reduce_by('username', $username, '=')
			->reduce_by('type', $type, '=')
			->reduce_by('page', $page, '=');

		if (count($set)) {
			$obj = $set->one()->export();
			/**
			 * In some rare cases, for unknown reasons, duplicate
			 * quicklinks exist at this point. This is a failsafe
			 * to ensure that doesnt happen.
			 */
			if ($page === 'tac' && $type === 'dojo-quicklinks') {
				$json_obj = $obj['setting'];
				$decoded_array = json_decode($obj['setting']);
				$unique = array_unique($decoded_array, SORT_REGULAR);
				/* We need to iterate over the array here, as
				 * array_unique might return arrays with { [0] => ... [2] => ...
				 * As the above would result in json_encode to encode the json
				 * objects with indexes, which breaks things later on */
				$unique_ordered_keys = array();
				foreach ($unique as &$quicklink) {
					array_push($unique_ordered_keys, $quicklink);
				}
				$obj['setting'] = json_encode($unique_ordered_keys);
			}
			return (object) $obj;
		}

		return self::fetch_default_setting($type, $page);

	}

	/**
	 * Fetch page setting for a specifik user.
	 * Assumes only one value is returned.
	 *
	 * @param $type string: {widget_order, widget, etc...}
	 * @param $page string: The page we're looking at.
	 * @param $username string: User to fetch setting for
	 */
	public static function fetch_user_page_setting($type, $page=false, $username=false)
	{
		$type = trim($type);
		$page = trim($page);

		if (empty($type)) return false;

		$set = SettingPool_Model::all()
			->reduce_by('username', $username, '=')
			->reduce_by('type', $type, '=')
			->reduce_by('page', $page, '=');

		if (count($set)) {
			return (object) $set->one()->export();
		}

		return false;
	}
}
