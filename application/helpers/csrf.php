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
		if (($token = csrf::current_token()) === FALSE || $force === true || csrf::current_token_expired() === true) {

			# save token to session
			Session::instance()->set(Kohana::config('csrf.csrf_token'), ($token = text::random('alnum', 41)));

			# save session timestamp to session
			Session::instance()->set(Kohana::config('csrf.csrf_timestamp'), time());
		}

		return $token;
	}

	/**
	 * Checks if current token has expired
	 *
	 * @return boolean
	 **/
	public static function current_token_expired() {
		if (csrf::current_token() !== false && csrf::current_timestamp() + csrf::lifetime() < time()) {
			return true;
		}
		return false;
	}

	/**
	 * Validate token
	 * @param $token The csrf token
	 * @return true if validation was successful, false otherwise
	 */
	public static function valid($token)
	{
		# not valid if tokens differ or has expired
		if ($token !== csrf::current_token() || csrf::current_token_expired() === true) {
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

	/**
	 * Return current csrf timestamp
	 */
	public static function current_timestamp()
	{
		return Session::instance()->get(Kohana::config('csrf.csrf_timestamp'), false);
	}

	/**
	 * Return lifetime for current csrf token
	 */
	public static function lifetime()
	{
		return (int)Kohana::config('csrf.csrf_lifetime');
	}

	/**
	 * Return a string representation of a form element with the current CSRF token
	 * @param $name The name of the form element
	 */
	public static function form_field($name='') {
		if (Kohana::config('csrf.csrf_token')=='' || Kohana::config('csrf.active') === false) {
			return;
		}

		if (empty($name)) $name = Kohana::config('csrf.csrf_token');
		return '<input type="hidden" name="'.$name.'" value="'.self::token(true).'">';
	}
}
