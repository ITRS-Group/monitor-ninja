<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling config items transparently,
 * i.e independent of storage location (file or database)
 */
class config_Core
{
	const CONFIG_NOT_FOUND = null;
	
	/**
        *       Fetch config item from db or config file
        *       If $page is set it will fetch for a page-specific
        *       setting for current user
        */
	public static function get($config_str=false, $page='', $save=false, $skip_session=false)
	{
		$config_str = trim($config_str);
		if (empty($config_str) || !is_string($config_str)) {
			return false;
		}
		# first check for cached session value
		$page_val = empty($page) ? '' : '.'.$page;
		$setting = self::CONFIG_NOT_FOUND;
		if ($skip_session) {
			Session::instance()->delete($config_str.$page_val);
		} else {
			$setting = Session::instance()->get($config_str.$page_val, self::CONFIG_NOT_FOUND);
		}

		# then check for database value
		if ($setting === self::CONFIG_NOT_FOUND) {
			$cfg = Ninja_setting_Model::fetch_page_setting($config_str, $page);
			if ($cfg!==false) {
				$setting = $cfg->setting;
			}
		}

		if ($setting === self::CONFIG_NOT_FOUND) {
			# if nothing was found - try the config file
			$setting = Kohana::config($config_str, false, false);
			if (is_array($setting) && empty($setting)) {
				$setting = false;
			} elseif ($save) {
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

	/**
	*	Fetch specific key from config file
	* 	Default is cgi.cfg
	*/
	public static function get_cgi_cfg_key($key=false, $file='cgi.cfg')
	{
		$key = trim($key);
		if (empty($key) || empty($file) || !Auth::instance()->logged_in())
			return false;

		$session = Session::instance();
		$val = $session->get($key, null);
		if ($val === null) {
			$val = arr::search(System_Model::parse_config_file($file), $key, null);
			if (!is_null($val)) {
				# store value in session
				$session->set($key, $val);
			}
		}
		return $val;
	}

	/**
	 * On a OP5 Monitor system, return the system version
	 */
	public static function get_version_info()
	{
		$file = Kohana::config('config.version_info');
		if (file_exists($file)) {
			$handle = fopen($file, 'r');
			$contents = fread($handle, filesize($file));
			fclose($handle);
			return str_replace('VERSION=','',$contents);
		}
	}
}
