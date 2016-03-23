<?php

/**
 * This exception should never be caught outside of unit tests.
 */
class DeprecationException extends Exception {}

/**
 * Handles dead code such as methods marked for deprecation. If you want to
 * implement support for feature-flags, this could be the place for that
 * supporting code.
 */
class flag {
	/**
	 * Should we kill the current process? Typically used in CI or in
	 * development environments.
	 *
	 * @return boolean
	 */
	static function deprecation_kills() {
		return (boolean) op5config::instance()
			->getConfig('ninja.deprecation_should_exit');
	}

	/**
	 * You want to soft-deprecate something - calling deprecated() from any
	 * Ninja method in production will be a noop, but the call will error
	 * out when in development mode. 'Development mode' is defined by @see
	 * deprecation_kills.
	 *
	 * @param $source_method_or_class string
	 * @param $message string = ""
	 *
	 * @throws DeprecationException
	 */
	static function deprecated($source_method_or_class, $message = "") {
		$message = sprintf("DEPRECATION: '%s' is deprecated and should not be executed: %s", $source_method_or_class, $message ? $message : '<no message>');

		// we always log this as an error because being consistent
		// should be valued more than accidentally making users nervous
		// (which may even make them report the error to op5, yay)
		op5log::instance('ninja')->log('notice', $message);
		if(self::deprecation_kills()) {
			throw new DeprecationException($message);
		}
	}
}

