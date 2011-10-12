<?php defined('SYSPATH') OR die('No direct access allowed.');

class base_url_Core {

	/**
	 * @return string similar to https://192.168.1.211/ninja/index.php/
	 */
	public static function get() {
		$site_domain = Kohana::config('config.site_domain', true);
		return 'http'.(!empty($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].(80 != $_SERVER['SERVER_PORT']         ? ':'.$_SERVER['SERVER_PORT'] : '').$site_domain.'index.php/';
	}
}
