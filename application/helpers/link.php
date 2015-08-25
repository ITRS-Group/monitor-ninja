<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling links that could contain
 * chars that would break Kohanas routing ('/')
 */
class link
{
	/**
	 * Primitively 'detect' URI:s in a text and wrap
	 * in a html anchor element.
	 *
	 * @param $text string
	 * @return string
	 */
	public static function linkify($text) {
		return preg_replace('~((ftp|https?)://[^ ]+)~', '<a target="'.config::get('nagdefault.notes_url_target', '*').'" href="$1">$1</a>', $text);
	}
}
