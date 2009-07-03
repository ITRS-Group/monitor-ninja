<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling config items transparently,
 * i.e independent of storage location (file or database)
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class config_Core
{
	/**
	*	Fetch config item from db or config file
	*	If $page is set it will fetch for a page-specific
	* 	setting for current user
	*/
	public static function get($config_str=false, $page='', $save=false, $skip_session=false)
	{
		$config_str = trim($config_str);
		if (empty($config_str) || !is_string($config_str)) {
			return false;
		}
		# first check for cached session value
		$page_val = empty($page) ? '' : '.'.$page;
		if (!$skip_session) {
			$setting_session = Session::instance()->get($config_str.$page_val, false);
		} else {
			Session::instance()->delete($config_str.$page_val);
		}

		if (!empty($setting_session)) {
			$setting = $setting_session;
		}

		# then check for database value
		if (!isset($setting)) {
			$cfg = Ninja_setting_Model::fetch_page_setting($config_str, $page);
			if ($cfg!==false) {
				$setting =  $cfg->setting;
			}
		}

		if (!isset($setting)) {
			# if nothing was found - try the config file
			$setting = Kohana::config($config_str);
			if ($save === true) {
				# save to database and session as user setting
				Ninja_setting_Model::save_page_setting($config_str, $page, $setting);
			}
		}

		# store custom setting in session
		if (!$skip_session) {
			Session::instance()->set($config_str.$page_val, $setting);
		}

		return $setting;
	}
}