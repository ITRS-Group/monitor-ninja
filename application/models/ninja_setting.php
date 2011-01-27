<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle page data - saving and fetching
 */
class Ninja_setting_Model extends Model
{
	const USERFIELD = 'username';
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
		$user = Auth::instance()->get_user()->username;
		if (empty($type) || empty($user))
			return false;

		$db = new Database();

		$sql = "SELECT * FROM ninja_settings WHERE ".self::USERFIELD."=".$db->escape($user).
			" AND page=".$db->escape($page)." AND type=".$db->escape($type);

		# does this setting exist? (i.e should we do insert or update)
		$res = $db->query($sql);
		if (count($res)!=0) {
			$sql = "UPDATE ninja_settings SET setting=".$db->escape($value).
				" WHERE ".self::USERFIELD."=".$db->escape($user)." AND type=".
				$db->escape($type)." AND page=".$db->escape($page);
		} else {
			$sql = "INSERT INTO ninja_settings(page, type, setting, ".self::USERFIELD.") ".
				"VALUES(".$db->escape($page).", ".$db->escape($type).", ".$db->escape($value).
				", ".$db->escape($user).")";
		}
		unset($res);
		$db->query($sql);
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

		$db = new Database();
		$res = false;
		$sql_base = "SELECT * FROM ninja_settings";
		$user = Auth::instance()->get_user()->username;
		if ($default === true) {
			# We have a request for default value
			$sql = $sql_base." WHERE ".self::USERFIELD."='' AND page=".$db->escape($page)." AND type=".
				$db->escape($type);
		} else {
			# first, try user setting
			$sql = $sql_base." WHERE ".self::USERFIELD."=".$db->escape($user)." AND page=".$db->escape($page).
				" AND type=".$db->escape($type);

			$res = $db->query($sql);
			if (count($res)==0) {
				# try default if nothing found
				$sql = $sql_base." WHERE ".self::USERFIELD."='' AND page=".$db->escape($page)." AND type=".
					$db->escape($type);
				$res = false;
			}
		}
		$result = ($res!== false && count($res)) ? $res : $db->query($sql);
		return count($result) !=0 ? $result->current() : false;
	}
}
