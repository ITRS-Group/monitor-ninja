<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Helper for basic ninja stuff
 *
 * Basically, stuff that could be in the ninja controller, if not other helpers
 * had needed it.
 */
class ninja_Core {
	private static $theme_path = false;

	/**
	 * Return the base path to the current theme
	 */
	public static function get_theme_path() {
		if (self::$theme_path)
			return self::$theme_path;
		$registry = zend::instance('Registry');

		if ($registry->isRegistered('theme_path')) {
			self::$theme_path = $registry->get('theme_path');
			return self::$theme_path;
		}

		self::$theme_path = Kohana::config('config.theme_path').Kohana::config('config.current_theme');
		$registry->set('theme_path', self::$theme_path);
		return self::$theme_path;
	}

	/**
	 * Given a file name that is relative to the current theme, find it and
	 * return the full path.
	 */
	public static function add_path($rel_path) {
		static $url_base = false;
		if (!$url_base)
			$url_base = url::base();
		$rel_path = trim($rel_path);
		if (empty($rel_path)) {
			return false;
		}

		# assume rel_path is relative from current theme
		$path = 'application/views/'.self::get_theme_path().$rel_path;
		# make sure we didn't mix up start/end slashes
		$path = str_replace('//', '/', $path);
		return $url_base . $path;
	}

	/**
	 * Return array of all installed skins
	 */
	public static function get_skins() {
		$available_skins = array();
		$required_css = array('common.css', 'status.css', 'reports.css');
		$skins = glob(APPPATH.'views/'.self::get_theme_path().'css/*', GLOB_ONLYDIR);
		if (count($skins) > 1) {
			foreach ($skins as $skin) {

				# make sure we have all requred css
				$missing_css = false;
				foreach ($required_css as $css) {
					if (glob($skin.'/'.$css) == false) {
						$missing_css = true;
						continue;
					}
				}
				if ($missing_css !== false) {
					continue;
				}

				# all required css files seems to be exist
				$skinparts = explode('/', $skin);
				if (is_array($skinparts) && !empty($skinparts)) {
					$available_skins[$skinparts[sizeof($skinparts)-1].'/'] = $skinparts[sizeof($skinparts)-1];
				}
			}
		}
		return $available_skins;
	}
}
