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
	public static function get($config_str=false, $page='', $save=false)
	{
		$config_str = trim($config_str);
		if (empty($config_str) || !is_string($config_str)) {
			return false;
		}
		# first check for database value
		$cfg = Ninja_setting_Model::fetch_page_setting($config_str, $page);
		if ($cfg!==false) {
			$setting =  $cfg->setting;
		}

		if (!isset($setting)) {
			$setting = Kohana::config($config_str);
			if ($save === true) {
				# save to database as user setting
				Ninja_setting_Model::save_page_setting($config_str, $page, $setting);
			}
		}
		# secondly, if nothing was found - try the config file
		return $setting;
	}
}