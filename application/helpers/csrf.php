<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * CSRF helper class.
 */
class csrf {

	/**
	 * Generate new token
	 * Save token to session together with time for generation
	 *
	 * @return str $token
	 */
	public static function token($force = false)
	{
		if (($token = csrf::current_token()) === FALSE || $force === true) {

			# save token to session
			Session::instance()->set(Kohana::config('csrf.csrf_token'), ($token = text::random(41)));

			# save session timestamp to session
			Session::instance()->set(Kohana::config('csrf.csrf_timestamp'), time());
		}

		return self::current_token();
	}

	/**
	 * Validate token
	 * @param $token The csrf token
	 * @return true if validation was successful, false otherwise
	 */
	public static function valid($token)
	{
		# not valid if tokens differ or has expired
		if ($token !== csrf::current_token()) {
			return false;
		}
		return true;
	}

	/**
	 * Return current csrf token
	 */
	public static function current_token()
	{
		return Session::instance()->get(Kohana::config('csrf.csrf_token'), false);
	}
}
