<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_LDAP_User_Model extends Auth_User_Model {

	/**
	 * Updates the password of the user.
	 *
	 * @param  string    new password
	 * @return boolean   returns if change was successful
	 */
	public function change_password( $password )
	{
		return false;
	}

} // End Auth User Model
