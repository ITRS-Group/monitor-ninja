<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class to ease the customization of the Ninja GUI Logo
 */
class customlogo {
	/**
	 * Display the div with the logo if it's all enabled
	 */
	public static function Render()
	{
		if (!Kohana::Config('customlogo.enable'))
			return;

		$logo = customlogo::imageExists($image = customlogo::getImage());

		if ($logo) {
			$height = 42;
			echo html::image(array(
						'src' => $image,
						'height' => $height,
					),
					array(
						'alt' => '',
						'style' => '',
					)
				);
		}

		return;
	}

	/**
	 * Function to check if the image actually exists, else we won't display the logo
	 */
	function imageExists($logo)
	{
		if (is_file($logo))
			return is_readable($logo);

		return false;
	}

	/**
	 * Get the logo we should use based on some nifty logic
	 */
	function getImage()
	{
		$path = 'application/views/icons/';
		$icon = $path . Kohana::config('customlogo.path') . Kohana::config('customlogo.default_icon');

		/**
		 * Get list of icons found in the custom_logo dir and
		 * try to match it towards your pattern defined in config
		 */
		$images = customlogo::getCustomImageList();
		if (!$images) {
			return $icon;
		}

		$username = Auth::instance()->get_user()->get_username();
		if (!preg_match(Kohana::config('customlogo.pattern'), $username, $custom)) {
			return $icon;
		}

		foreach ($images as $image) {
			if (($image == $custom[1] . '.png') || ($image == $custom[1] . '.jpg')) {
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
		if ($fh = opendir(APPPATH.'views/icons/'.Kohana::Config('customlogo.path'))) {
			while (false !== ($file = readdir($fh))) {
				if ((substr($file, -4) == '.png') || (substr($file, -4) == '.jpg')) {
					$images[] = $file;
				}
			}
		}

		return $images;
	}
}
