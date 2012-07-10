<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Auth_User_Model extends Model {

	// Columns to ignore
	protected $ignored_columns = array('password_confirm');

	public function __set($key, $value)
	{
		parent::__set($key, $value);
	}

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
