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


		// When upgrading from <5.3.2 to >=5.3.2, this is needed
		$ret = @unserialize(@utf8_encode($string));
		if ($ret !== false)
			return $ret;

		// This shouldn't ever happen, but why not try? If we get this far,
		// all the reasonable methods have already failed.
		return @unserialize(@utf8_decode($string));
	}
}
