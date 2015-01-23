<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Exception thrown by add_path
 */
class FileLookupErrorException extends Exception {}

/**
 * Helper for basic ninja stuff
 *
 * Basically, stuff that could be in the ninja controller, if not other helpers
 * had needed it.
 */
class ninja {
	private static $loaded_modules = array();


	/**
	 * Given a file name that is relative to the views directory, find it and
	 * return the full path.
	 *
	 * @return string
	 */
	public static function add_path($rel_path, $module_name=false, $required = true) {
		static $url_base = false;
		if (!$url_base)
			$url_base = url::base();
		$rel_path = trim($rel_path);
		if (empty($rel_path)) {
			return false;
		}

		$path = Kohana::find_file('views', $rel_path, $required, '');

		/*
		 * Since find_file returns an absolute path, and we don't want that, just
		 * strip away the beginning
		 */
		if(substr($path,0,strlen(DOCROOT)) == DOCROOT) {
			$path = substr($path,strlen(DOCROOT));
		} else {
			throw new FileLookupErrorException("Can't find file ".$rel_path);
		}


		# make sure we didn't mix up start/end slashes
		$path = str_replace('//', '/', $path);
		return self::add_version_to_uri($url_base.$path);
	}

	/**
	 * Return array of all installed skins
	 */
	public static function get_skins() {
		$available_skins = array();
		$required_css = array('common.css');
		$skins = glob(APPPATH.'views/css/*', GLOB_ONLYDIR);
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

	/**
	 * @param $name string
	 * @return boolean
	 */
	static function has_module($name)
	{
		$name = (string) $name;
		if(!isset(self::$loaded_modules[$name])) {
			self::$loaded_modules[$name] = is_readable(MODPATH.$name);
		}
		return self::$loaded_modules[$name];
	}

	/**
	 * Add "?v=3.0.0" (or such) to the parameter, makes for solid
	 * cache busting.
	 *
	 * @param $uri string
	 * @return string
	 */
	static function add_version_to_uri($uri) {
		return $uri .= "?v=".config::get_version_info();
	}
}
