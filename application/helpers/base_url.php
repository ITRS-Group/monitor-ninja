<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Helper to get the proper complete URL to the server
 */
class base_url_Core {

	/**
	 * This relies on the setting op5reports.site_address or a webserver
	 * CLI will fail to resolve an URI without having the mentioned config key
	 *
	 * @return string similar to https://192.168.1.211/ninja/index.php/
	 */
	public static function get() {
		$check_port = 80;
		$protocol = 'http';
		if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) {
			$protocol .= 's';
			$check_port = 443;
		}
		$host = Kohana::config('op5reports.site_address', false, false);
		if(!$host && isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		}
		// trim port from $host since we append it just below
		$host_parts = explode(":", $host);
		$host = $host_parts[0];
		$port = '';
		if(isset($_SERVER['SERVER_PORT']) && $check_port != $_SERVER['SERVER_PORT']) {
			$port = ':'.$_SERVER['SERVER_PORT'];
		}
		$site_domain = Kohana::config('config.site_domain', true);
		$uri = $protocol.'://'.$host.$port.$site_domain.'index.php/';
		return $uri;
	}
}
