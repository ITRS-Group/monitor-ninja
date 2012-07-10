<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author     
 * @copyright  
 * @license    
 */
class Dummy_Auth_Core extends Auth_Core {
	/**
	 * Check if there is an active session. Optionally allows checking for a
	 * specific role.
	 *
	 * @param   string   role name
	 * @return  boolean
	 */
	public function logged_in($role = NULL) {
		return true;
	}

	/**
	 * Returns the currently logged in user, or FALSE.
	 *
	 * @return  mixed
	 */
	abstract public function get_user() {
		return new Dummy_User();
	}
	
	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   string   username to log in
	 * @param   string   password to check against
	 * @param   boolean  enable auto-login
	 * @return  user	 user object or FALSE
	 */
	abstract public function login($username, $password, $remember = FALSE) {
		if( $username == 'monitor' && $password == 'monitor' ) {
			return new Dummy_User();
		}
		return false;
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean  completely destroy the session
	 * @return  boolean
	 */
	abstract public function logout($destroy = FALSE) {
	}
} // End Auth
