<?php
/**
 * This helper class provides various routines for authenticating
 * users against a database that stores passwords with multiple
 * different hash-types
 */
class ninja_auth_Core
{
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

		/*
		 * If no user: Not authenticated, handle event...
		 */
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
		else {
			/* FIXME: Is limited user? Treat all as limited for now...
			 * above else should be: else if(limited user) {
			 */
	
			/**
			 * Take care of access for limited users
			 * 
			 * Check that user has access to view some objects
			 * or logout with a message
			 */
			
			$nagauth = Nagios_auth_Model::instance();
			$hosts = $nagauth->get_authorized_hosts();

			$redirect = false;
			if (empty($hosts)) {
				$services = $nagauth->get_authorized_services();
				if (empty($services)) {
					$redirect = true;
				}
			}

			if ($redirect !== false) {
				$translate = zend::instance('Registry')->get('Zend_Translate');
				Session::instance()->set_flash('error_msg',
					$translate->_("You have been denied access since you aren't authorized for any objects."));
				return 'default/show_login';
			}
		}
		return true;
	}

	/**
	 * Check if the user has tried
	 * to login too many times
	 *
	 * @return bool
	 */
	public static function is_locked_out()
	{
		$session = Session::instance();
		if ($session->get('locked_out') && Kohana::config('auth.max_attempts')) {
			return true;
		}
		return false;
	}
}
