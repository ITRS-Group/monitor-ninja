<?php
/**
 * Functionality used for internationalization related functionality
 */

class i18n {
	/**
	 * A wrapper around PHP's unserialize that tries to cope when the database
	 * encoding has changed.
	 */
	public static function unserialize($string) {
		$ret = @unserialize($string);
		if ($ret !== false)
			return $ret;

		$string_enc = mb_detect_encoding($string, mb_detect_order(), true);
		// When upgrading from <5.3.2 to >=5.3.2, this is needed
		$ret = unserialize(mb_convert_encoding($string, 'UTF-8', $string_enc));
		if ($ret !== false)
			return $ret;

		// This shouldn't ever happen, but why not try? If we get this far,
		// all the reasonable methods have already failed.
		return unserialize(mb_convert_encoding($string, $string_enc, 'UTF-8'));
	}
}
