<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling icons
 */
class icon
{
	/**
	 * Returns HTML for an icon
	 *
	 * @param $name string
	 * @return string
	 */
	public static function get ($name) {
		return '<span class="icon-' . $name . '"></span>';
	}

	/**
	 * Returns HTML for an icon link
	 *
	 * @param $name string
	 * @param $url string
	 * @param $title string
	 * @return string
	 */
	public static function get_linked ($name, $url, $title = "") {
		return '<a ' . ($title ? 'title="' . $title . '"' : '') . ' class="link-icon" href="' . $url . '">'
			. icon::get($name)
			. '</a>';
	}

}
