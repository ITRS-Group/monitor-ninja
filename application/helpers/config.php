<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling config items transparently,
 * i.e independent of storage location (file or database)
 */
class config
{
	static private $cache = array();

	/**
	 * Dummy value to compare to when ... no config was found!
	 */
	const CONFIG_NOT_FOUND = null;

	/**
	 * Fetch config item from db or config file
	 * @param $config_str string The setting to get
	 * @param $page Deprecated, do not use
	 * @param $save Deprecated, do not use
	 * @return the value of the setting, or CONFIG_NOT_FOUND if not found
	 */
	public static function get($config_str, $page='', $save=false)
	{
		$config_str = trim($config_str);
		if ($page !== '') {
			flag::deprecated('$page parameter in config::get', 'The $page
				parameter is of no use here. If you really want a page-specific
				setting, use Ninja_setting_Model::fetch_page_setting()');
		}
		if ($save !== false) {
			flag::deprecated('$save parameter in config::get', 'The $save
				parameter is of no use here. If you really want to save a
				setting, use Ninja_setting_Model::save_page_setting()');

		}

		if (empty($config_str) || !is_string($config_str)) {
			return false;
		}

		if(isset(self::$cache[$config_str])) {
			return self::$cache[$config_str];
		}

		$setting = self::CONFIG_NOT_FOUND;

		try {
			$cfg = Ninja_setting_Model::fetch_page_setting($config_str);
		} catch (Kohana_Database_Exception $e) {
			op5log::instance('ninja')->log('error', "Cannot fetch setting '$config_str': " . $e->getMessage());
			return $setting;
		}
		if ($cfg!==false) {
			$setting = $cfg->setting;
		}

		if ($setting === self::CONFIG_NOT_FOUND) {
			# if nothing was found - try the config file
			$setting = Kohana::config($config_str, false, false);
			if (is_array($setting) && empty($setting)) {
				$setting = false;
			}
		}
		self::$cache[$config_str] = $setting;

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
	 *
	 * @return string
	 */
	public static function get_version_info()
	{
		static $version = NULL;
		if ($version === NULL) {
			$file = Kohana::config('config.version_info');
			if (@is_readable($file)) {
				$handle = fopen($file, 'r');
				$contents = fread($handle, filesize($file));
				fclose($handle);
				$version = trim(str_replace('VERSION=','',$contents));
			}
		}
		return $version;
	}
}
