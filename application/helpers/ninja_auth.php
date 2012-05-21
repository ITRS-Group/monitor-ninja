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
	 * Validates a password using apr's md5 hash algorithm
	 */
	private static function apr_md5_validate($pass, $hash)
	{
		$pass = escapeshellarg($pass);
		$hash = escapeshellarg($hash);
		$cmd = realpath(APPPATH.'/../cli-helpers')."/apr_md5_validate $pass $hash";
		$ret = $output = false;
		exec($cmd, $output, $ret);
		return $ret === 0;
	}

	/**
	 * Validates a password using the given algorithm
	 */
	public static function valid_password($pass, $hash, $algo = '')
	{
		if ($algo === false || !is_string($algo))
			return false;
		if (empty($pass) || empty($hash))
			return false;
		if (!is_string($pass) || !is_string($hash))
			return false;

		switch ($algo) {
		 case 'sha1':
			return sha1($pass) === $hash;

		 case 'b64_sha1':
			# Passwords can be one of
			# ... base64 encoded raw sha1
			return base64_encode(sha1($pass, true)) === $hash;

		 case 'crypt':
			# ... crypt() encrypted
			return crypt($pass, $hash) === $hash;

		 case 'plain':
			# ... plaintext (stupid, but true)
			return $pass === $hash;

		 case 'apr_md5':
			# ... or a mad and weird aberration of md5
			return ninja_auth::apr_md5_validate($pass, $hash);
		 default:
			return false;
		}

		# not-reached
		return false;
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
	public static function login_user($username, $password) {
		$auth = Auth::factory();

		# check if new authorization data is available in cgi.cfg
		# this enables incremental import
		Cli_Controller::insert_user_data();

		# Kohana is stupid (well, it's Auth module is anyways) and
		# refuses to *not* hash the password it passes on to the
		# driver. However, that doesn't fly well with our need to
		# support multiple password hash algorithms, so if we're
		# using the Ninja authenticator we must call the driver
		# explicitly
		switch (Kohana::config('auth.driver')) {
		 case 'Ninja':
		 case 'LDAP':
		 case 'apache':
			$result = $auth->driver->login($username, $password, false);
			break;
		 default:
			$result = $auth->login($username, $password);
			break;
		}

		if (!$result) {
			# This brute force protection is absolutely fool-proof, as long
			# as nobody uses evil hacker tools like curl or "Clean History"
			$session = Session::instance();

			$session->set('login_attempts', $session->get('login_attempts')+1);

			$max_attempts = Kohana::config('auth.max_attempts');
			# set login error to user
			$error_msg = _("Login failed - please try again");
			if ($max_attempts) {
				$error_msg .= " (".($max_attempts - $session->get('login_attempts'))." left)";
			}

			if ($max_attempts && $session->get('login_attempts') >= $max_attempts) {
				$error_msg = sprintf(_("You have been locked out due to %s failed login attempts"), $session->get('login_attempts'));
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
