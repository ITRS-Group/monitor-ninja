<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class to ease the customization of the Ninja GUI Logo
 */
class customlogo_Core {
	/**
	 * Get the logo we should use based on some nifty logic
	 */
	function getImage($view = false)
	{
		$normal_path = 'application/views/themes/default/icons/';
		$noc_path = 'icons/';

		if ($view == 'noc') {
			$path = $noc_path;
		} else {
			$path = $normal_path;
		}

		$icon = $path . "icon.png";

		if (!Kohana::Config('customlogo.enable')) {
			return $icon;
		}

		/**
		 * Get list of icons found in the custom_logo dir and
		 * try to match it towards your pattern defined in config
		 */
		$images = customlogo::getCustomImageList();
		if (!$images) {
			return $icon;
		}

		if (!preg_match(Kohana::config('customlogo.pattern'), user::session('username'), $custom)) {
			return $icon;
		}

		foreach ($images as $image) {
			if ($image == $custom[1] . '.png') {
				return $path . Kohana::config('customlogo.path') . $image;
			}
		}

		return $icon;
	}


	/**
	 * Read the custom dir defined in customlogo config file
	 * return an array of the names.
	 */
	function getCustomImageList()
	{
		$images = false;
		if ($fh = opendir(APPPATH.'views/themes/default/icons/'.Kohana::Config('customlogo.path'))) {
			while (false !== ($file = readdir($fh))) {
				if (substr($file, -4) == '.png') {
					$images[] = $file;
				}
			}
		}

		return $images;
	}
}
