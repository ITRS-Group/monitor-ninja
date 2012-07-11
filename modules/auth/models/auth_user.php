<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Auth_User_Model {

	abstract public function __set($key, $value);

	/**
	* @param 	string 		authorization point
	* @return 	boolean 	
	*/
	abstract public function authorized_for($auth_point);

	/**
	 * Updates the password of the user.
	 *
	 * @param  string    new password
	 * @return boolean
	 */
	abstract public function change_password( $password );

} // End Auth User Model
