<?php
/**
 * This helper class provides various routines for authenticating
 * users against a database that stores passwords with multiple
 * different hash-types
 */
class ninja_auth_Core
{
	/**
	 * Generates a password hash
	 * @param $pass Plaintext password
	 * @return Password hash
	 */
	public static function hash_password($pass)
	{
		return base64_encode(sha1($pass, true));
	}

	/**
	 * Does the required steps to log in a user via the specified auth_method
	 * (the last bit means you have to make sure that session/config has properly
	 * stringified auth_method).
	 *
	 * @param $username The user's username
	 * @param $password The user's password
	 * @returns TRUE if everything was OK, or a string controller you're suggested to redirect to
	 */
	public static function login_user($username, $password, $auth_method = false) {
		$auth = Auth::instance();

		$result = $auth->login($username, $password, $auth_method);

		if (!$result) {
			# This brute force protection is absolutely fool-proof, as long
			# as nobody uses evil hacker tools like curl or "Clean History"
			$session = Session::instance();
			$translate = zend::instance('Registry')->get('Zend_Translate');

			$session->set('login_attempts', $session->get('login_attempts')+1);

			$max_attempts = Kohana::config('auth.max_attempts');
			# set login error to user
			$error_msg = $translate->_("Login failed - please try again");
			if ($max_attempts) {
				$error_msg .= " (".($max_attempts - $session->get('login_attempts'))." left)";
			}

			if ($max_attempts && $session->get('login_attempts') >= $max_attempts) {
				$error_msg = sprintf($translate->_("You have been locked out due to %s failed login attempts"), $session->get('login_attempts'));
				$session->set('error_msg', $error_msg);
				$session->set('locked_out', true);
				return 'default/locked_out';
			}

			$session->set_flash('error_msg', $error_msg);
			return 'default/show_login';
		}
		return User_Model::complete_login();
	}
}
