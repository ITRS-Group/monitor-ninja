<?php defined('SYSPATH') OR die('No direct access allowed.');

/*
TODO: Authorized for.
*/

class Auth_Dummy_User_Model extends Auth_User_Model {

	protected $fields = array(
		'username' => 'monitor',
		'password' => 'monitor'
	);

	public function __set($key, $value)
	{
		$this->fields[$key] = $value;
	}

	public function __get($key) {
		return $this->fields[$key];
	}

	public function __construct() {
	}

	/**
	* @param 	string 		authorization point
	* @return 	boolean 	
	*/
	public function authorized_for($auth_point) {
		return true;
	}

	/**
	 * Validates an array for a matching password and password_confirm field.
	 *
	 * @param  array    values to check
	 * @param  string   save the user if
	 * @return boolean
	 */
	public function change_password(array & $array, $save = FALSE)
	{
		return false;
	}

	/**
	 * Allows a model to be loaded by username or email address.
	 */
	public function unique_key($id)
	{
		return 'monitor';
	}

} // End Auth User Model
