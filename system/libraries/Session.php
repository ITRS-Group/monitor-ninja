<?php defined('SYSPATH') OR die('No direct access allowed.');
require_once('op5/log.php');
/**
 * Session library.
 *
 * $Id: Session.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Session {

	// Session singleton
	private static $instance;

	/**
	 * Singleton instance of Session.
	 */
	public static function instance()
	{
		if (self::$instance == NULL)
		{
			// Create a new instance
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * On first session instance creation,
	 */
	public function __construct()
	{
		if(PHP_SAPI == 'cli') {
			return;
		}

		// Name the session, this will also be the name of the cookie
		session_name(Kohana::config('session.name'));

		// Set the session cookie parameters
		session_set_cookie_params
		(
			Kohana::config('cookie.expire'),
			Kohana::config('cookie.path'),
			Kohana::config('cookie.domain'),
			Kohana::config('cookie.secure'),
			Kohana::config('cookie.httponly')
		);

		// Start the session!
		session_start();
		if (isset($_SESSION['destroyed'])) {
			// Client provided a session id that expired due to user login.
			$destroyed_at = $_SESSION['destroyed'];
			if (time() - $destroyed_at > 60) {
				/* Should not happen usually. This could be due to attack or unstable
				 * network. Remove all authentication status of this users session.
				 */
				$_SESSION = array();
				op5log::instance('auth')->log(
					'error',
					"A client tried to use a session that was destroyed at $destroyed_at. id: '"
					. session_id() . "'. This could be due to an attack attempt or an unstable connection."
				);
			}
			elseif (isset($_SESSION['new_session_id'])) {
				/* The session id provided by client browser is marked as expired,
				 * but is still within grace period. This could be due to lost cookie
				 * by unstable network.
				 * Set the session cookie to what was stored in new_session_id on login.
				 */
				op5log::instance('auth')->log(
					'debug',
					'Session ' . session_id() . " marked for destruction at $destroyed_at, "
					. "but not yet expired, create new session with id {$_SESSION['new_session_id']}");
				session_write_close();
				ini_set('session.use_strict_mode', 0);
				session_id($_SESSION['new_session_id']);
				session_start();
			}
		}
	}

	public function csrf_token_valid($token)
	{
		return $token == $this->get(Kohana::config('csrf.csrf_token'), false);
	}

	/**
	 * Get the session id.
	 *
	 * @return  string
	 */
	public function id()
	{
		if(PHP_SAPI == 'cli') {
			return "";
		}
		return session_id();
	}

	/**
	 * Destroys the current session.
	 *
	 * @deprecated
	 * @see op5auth::session_destroy()
	 * @return  void
	 */
	public function destroy()
	{
		op5auth::instance()->session_destroy();
	}

	/**
	 * Runs the system.session_write event, then calls session_write_close.
	 *
	 * @return  void
	 */
	public function write_close()
	{
		if(PHP_SAPI == 'cli') {
			return;
		}
		session_write_close();
	}

	/**
	 * Set a session variable.
	 *
	 * @param   string|array  key, or array of values
	 * @param   mixed         value (if keys is not an array)
	 * @return  void
	 */
	public function set($keys, $val = FALSE)
	{
		if(PHP_SAPI == 'cli') {
			return;
		}
		if (empty($keys))
			return FALSE;

		if ( ! is_array($keys))
		{
			$keys = array($keys => $val);
		}

		foreach ($keys as $key => $val)
		{
			$_SESSION[$key] = $val;
		}
	}

	/**
	 * Get a variable. Access to sub-arrays is supported with key.subkey.
	 *
	 * @param   string  variable key
	 * @param   mixed   default value returned if variable does not exist
	 * @return  mixed   Variable data if key specified, otherwise array containing all session data.
	 */
	public function get($key = FALSE, $default = FALSE)
	{
		if(PHP_SAPI == 'cli') {
			return $default;
		}
		if (empty($key))
			return $_SESSION;

		$result = isset($_SESSION[$key]) ? $_SESSION[$key] : Kohana::key_string($_SESSION, $key);

		return ($result === NULL) ? $default : $result;
	}

	/**
	 * Delete one or more variables.
	 *
	 * @param   string  variable key(s)
	 * @return  void
	 */
	public function delete($keys)
	{
		if(PHP_SAPI == 'cli') {
			return;
		}
		$args = func_get_args();

		foreach ($args as $key)
		{
			unset($_SESSION[$key]);
		}
	}
}
