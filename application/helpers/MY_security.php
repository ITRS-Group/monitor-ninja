<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Add functionality to kohanas built-in security helper
 */
class security extends security_Core {
	/**
	 * Escape scring only if told to
	 */
	public static function xss_clean($str)
	{
		if (config::get_cgi_cfg_key('escape_html_tags'))
			return parent::xss_clean($str);
		return $str;
	}
}
