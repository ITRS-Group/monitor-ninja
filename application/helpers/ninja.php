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
		$registry = zend::instance('Registry');
		if (self::$theme_path)
			return self::$theme_path;

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
		$rel_path = trim($rel_path);
		if (empty($rel_path)) {
			return false;
		}

		# assume rel_path is relative from current theme
		$path = 'application/views/'.self::get_theme_path().$rel_path;
		# make sure we didn't mix up start/end slashes
		$path = str_replace('//', '/', $path);
		return $path;
	}
}
