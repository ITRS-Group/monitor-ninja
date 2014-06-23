<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling links that could contain
 * chars that would break Kohanas routing ('/')
 */
class link
{
	/**
	 * Encode a string so that it is possible to
	 * use it without risk in Kohana.
	 * The 'all' string has been given a special meaning
	 * and is therefore not encoded.
	 * @param $str
	 * @return str encoded string
	 */
	public function encode($str)
	{
		$str = trim($str);
		if (strtolower($str) == 'all') {
			return $str;
		}
		return rawurlencode(base64_encode($str));
	}

	/**
	 * Decode a string that has been encoded
	 * using the encode method.
	 * The 'all' string has been given a special meaning
	 * and is therefore not decoded.
	 * @param $str
	 * @return decoded string
	 */
	public function decode($str)
	{
		$str = trim($str);
		if (strtolower($str) == 'all') {
			return $str;
		}
		return rawurldecode(base64_decode($str));
	}

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
